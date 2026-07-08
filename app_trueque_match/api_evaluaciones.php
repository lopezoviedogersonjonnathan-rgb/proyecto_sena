<?php
// ============================================================
// api_evaluaciones.php — API de Evaluaciones para Trueque Match
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Una evaluación es la calificación de 1 a 5 estrellas que un
// usuario le deja a la otra persona después de un trueque.
//
// OJO: la tabla evaluacion NO guarda "a quién calificaste"
// directamente. Guarda quién escribió la calificación
// (id_usuario) y a qué trueque pertenece (id_trueque). Para
// saber quién fue calificado, buscamos el trueque y tomamos
// al OTRO participante (el que no escribió la evaluación).
// ============================================================

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../conexion.php";

$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// Función auxiliar: recalcula y guarda la reputación de un usuario
// ============================================================
// La reputación es el promedio de TODAS las calificaciones que
// ese usuario ha recibido a lo largo de todos sus trueques.
// Como el "evaluado" no está guardado directo, hay que usar
// un CASE para deducir, evaluación por evaluación, a quién
// calificaron (el que NO escribió la evaluación en ese trueque)
function recalcular_reputacion($conexion, $id_usuario_evaluado) {

    $sql = "SELECT AVG(e.puntaje) AS promedio
            FROM evaluacion e
            INNER JOIN trueque t ON e.id_trueque = t.id_trueque
            WHERE 
                (e.id_usuario = t.id_usuario_propone AND t.id_usuario_recibe = $id_usuario_evaluado)
                OR
                (e.id_usuario = t.id_usuario_recibe AND t.id_usuario_propone = $id_usuario_evaluado)";

    $resultado = mysqli_query($conexion, $sql);
    $fila = mysqli_fetch_assoc($resultado);

    // Si todavía no tiene ninguna evaluación, el promedio sale
    // en null — en ese caso dejamos la reputación en 0.00
    $promedio = $fila["promedio"] ?? 0;
    if ($promedio === null) {
        $promedio = 0;
    }

    // Redondeamos a 2 decimales, igual que el formato de la columna
    $promedio = round($promedio, 2);

    mysqli_query($conexion, 
        "UPDATE usuario SET reputacion = $promedio WHERE id_usuario = $id_usuario_evaluado"
    );

    return $promedio;
}

// ============================================================
// GET — Listar evaluaciones
// ============================================================
if ($metodo === "GET") {

    // id_usuario aquí filtra por quién ESCRIBIÓ la evaluación,
    // no por quién la recibió (para eso existe reputacion en la
    // tabla usuario, ya calculada)
    $id_usuario = $_GET["id_usuario"] ?? "";

    if (!empty($id_usuario)) {
        $sql = "SELECT * FROM evaluacion WHERE id_usuario = $id_usuario ORDER BY fecha_evaluacion DESC";
    } else {
        $sql = "SELECT * FROM evaluacion ORDER BY fecha_evaluacion DESC";
    }

    $resultado = mysqli_query($conexion, $sql);
    $evaluaciones = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $evaluaciones[] = $fila;
    }

    echo json_encode([
        "status" => "success",
        "mensaje" => "Evaluaciones obtenidas correctamente",
        "total" => count($evaluaciones),
        "data" => $evaluaciones
    ]);
}

// ============================================================
// POST — Crear una evaluación nueva
// ============================================================
elseif ($metodo === "POST") {

    $puntaje    = $_POST["puntaje"]    ?? "";
    $comentario = $_POST["comentario"] ?? "";
    $id_usuario = $_POST["id_usuario"] ?? ""; // quien escribe la calificación
    $id_trueque = $_POST["id_trueque"] ?? "";

    if (empty($puntaje) || empty($id_usuario) || empty($id_trueque)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "puntaje, id_usuario e id_trueque son obligatorios"
        ]);
        exit();
    }

    // El puntaje solo puede ser un número entero de 1 a 5
    if (!is_numeric($puntaje) || $puntaje < 1 || $puntaje > 5) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El puntaje debe ser un numero entre 1 y 5"
        ]);
        exit();
    }

    // Buscamos el trueque para confirmar que el usuario sí
    // participó en él, y para saber quién es "el otro"
    $buscar = mysqli_query($conexion, 
        "SELECT id_usuario_propone, id_usuario_recibe FROM trueque WHERE id_trueque = $id_trueque"
    );
    $trueque = mysqli_fetch_assoc($buscar);

    if (!$trueque) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "No existe un trueque con ese id_trueque"
        ]);
        exit();
    }

    // Determinamos quién es el evaluado: el que NO es id_usuario
    if ($id_usuario == $trueque["id_usuario_propone"]) {
        $id_usuario_evaluado = $trueque["id_usuario_recibe"];
    } elseif ($id_usuario == $trueque["id_usuario_recibe"]) {
        $id_usuario_evaluado = $trueque["id_usuario_propone"];
    } else {
        // Si el usuario no aparece en ninguno de los dos roles
        // de ese trueque, no puede calificarlo
        echo json_encode([
            "status" => "error",
            "mensaje" => "Este usuario no participo en ese trueque"
        ]);
        exit();
    }

    $comentario = mysqli_real_escape_string($conexion, $comentario);

    $sql = "INSERT INTO evaluacion (puntaje, comentario, id_usuario, id_trueque)
            VALUES ($puntaje, '$comentario', $id_usuario, $id_trueque)";

    if (mysqli_query($conexion, $sql)) {
        $nuevo_id = mysqli_insert_id($conexion);

        // Recalculamos la reputación del usuario que fue calificado
        $nueva_reputacion = recalcular_reputacion($conexion, $id_usuario_evaluado);

        echo json_encode([
            "status" => "success",
            "mensaje" => "Evaluacion creada correctamente",
            "id_evaluacion_creada" => $nuevo_id,
            "id_usuario_evaluado" => $id_usuario_evaluado,
            "nueva_reputacion_del_evaluado" => $nueva_reputacion
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al crear la evaluacion: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// PUT — Editar una evaluación existente
// ============================================================
elseif ($metodo === "PUT") {

    parse_str(file_get_contents("php://input"), $datos);

    $id_evaluacion = $datos["id_evaluacion"] ?? "";
    $puntaje       = $datos["puntaje"]       ?? "";
    $comentario    = $datos["comentario"]    ?? "";

    if (empty($id_evaluacion)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_evaluacion es obligatorio"
        ]);
        exit();
    }

    if (!empty($puntaje) && (!is_numeric($puntaje) || $puntaje < 1 || $puntaje > 5)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El puntaje debe ser un numero entre 1 y 5"
        ]);
        exit();
    }

    $comentario = mysqli_real_escape_string($conexion, $comentario);

    $sql = "UPDATE evaluacion 
            SET puntaje = $puntaje, comentario = '$comentario' 
            WHERE id_evaluacion = $id_evaluacion";

    if (mysqli_query($conexion, $sql)) {

        // Buscamos a quién había que recalcularle la reputación
        $buscar = mysqli_query($conexion, 
            "SELECT e.id_usuario, t.id_usuario_propone, t.id_usuario_recibe
             FROM evaluacion e
             INNER JOIN trueque t ON e.id_trueque = t.id_trueque
             WHERE e.id_evaluacion = $id_evaluacion"
        );
        $fila = mysqli_fetch_assoc($buscar);

        if ($fila) {
            $id_usuario_evaluado = ($fila["id_usuario"] == $fila["id_usuario_propone"])
                ? $fila["id_usuario_recibe"]
                : $fila["id_usuario_propone"];

            recalcular_reputacion($conexion, $id_usuario_evaluado);
        }

        echo json_encode([
            "status" => "success",
            "mensaje" => "Evaluacion actualizada correctamente",
            "id_evaluacion_editada" => $id_evaluacion
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al actualizar la evaluacion: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// DELETE — Eliminar una evaluación
// ============================================================
elseif ($metodo === "DELETE") {

    parse_str(file_get_contents("php://input"), $datos);

    $id_evaluacion = $datos["id_evaluacion"] ?? "";

    if (empty($id_evaluacion)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_evaluacion es obligatorio para eliminar"
        ]);
        exit();
    }

    // Antes de borrarla, buscamos a quién había que recalcularle
    // la reputación después
    $buscar = mysqli_query($conexion, 
        "SELECT e.id_usuario, t.id_usuario_propone, t.id_usuario_recibe
         FROM evaluacion e
         INNER JOIN trueque t ON e.id_trueque = t.id_trueque
         WHERE e.id_evaluacion = $id_evaluacion"
    );
    $fila = mysqli_fetch_assoc($buscar);

    $sql = "DELETE FROM evaluacion WHERE id_evaluacion = $id_evaluacion";

    if (mysqli_query($conexion, $sql)) {

        if ($fila) {
            $id_usuario_evaluado = ($fila["id_usuario"] == $fila["id_usuario_propone"])
                ? $fila["id_usuario_recibe"]
                : $fila["id_usuario_propone"];

            recalcular_reputacion($conexion, $id_usuario_evaluado);
        }

        echo json_encode([
            "status" => "success",
            "mensaje" => "Evaluacion eliminada correctamente",
            "id_evaluacion_eliminada" => $id_evaluacion
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al eliminar la evaluacion: " . mysqli_error($conexion)
        ]);
    }
}

else {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Metodo no permitido"
    ]);
}
?>