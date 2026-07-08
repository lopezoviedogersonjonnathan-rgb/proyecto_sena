<?php
// ============================================================
// api_trueques.php — API de Trueques para Trueque Match
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Un "trueque" es el intercambio ya formalizado entre dos
// personas: cada una propone una de sus ofertas a cambio de
// la del otro. Nace normalmente después de que una solicitud
// fue aceptada, aunque también se puede crear directo.
// ============================================================

// Avisamos que la respuesta va a ser JSON
header("Content-Type: application/json");

// Permitimos que Postman o la app móvil se conecten sin que
// el navegador lo bloquee por seguridad (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Traemos la conexión ya armada a MariaDB
require_once "../conexion.php";

// Guardamos qué tipo de petición llegó
$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// GET — Listar trueques
// ============================================================
if ($metodo === "GET") {

    // id_usuario es opcional: si llega, mostramos solo los
    // trueques donde ese usuario participa (como quien propone
    // o como quien recibe la propuesta)
    $id_usuario = $_GET["id_usuario"] ?? "";

    if (!empty($id_usuario)) {
        $sql = "SELECT t.id_trueque, t.estado, t.fecha_propuesta, t.fecha_trueque, 
                       t.descripcion_acuerdo, t.id_usuario_propone, t.id_oferta_propone,
                       t.id_usuario_recibe, t.id_oferta_recibe,
                       op.titulo AS titulo_oferta_propone,
                       orr.titulo AS titulo_oferta_recibe
                FROM trueque t
                INNER JOIN oferta op  ON t.id_oferta_propone = op.id_oferta
                INNER JOIN oferta orr ON t.id_oferta_recibe  = orr.id_oferta
                WHERE t.id_usuario_propone = $id_usuario OR t.id_usuario_recibe = $id_usuario
                ORDER BY t.fecha_propuesta DESC";
    } else {
        $sql = "SELECT t.id_trueque, t.estado, t.fecha_propuesta, t.fecha_trueque, 
                       t.descripcion_acuerdo, t.id_usuario_propone, t.id_oferta_propone,
                       t.id_usuario_recibe, t.id_oferta_recibe,
                       op.titulo AS titulo_oferta_propone,
                       orr.titulo AS titulo_oferta_recibe
                FROM trueque t
                INNER JOIN oferta op  ON t.id_oferta_propone = op.id_oferta
                INNER JOIN oferta orr ON t.id_oferta_recibe  = orr.id_oferta
                ORDER BY t.fecha_propuesta DESC";
    }

    $resultado = mysqli_query($conexion, $sql);
    $trueques = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $trueques[] = $fila;
    }

    echo json_encode([
        "status" => "success",
        "mensaje" => "Trueques obtenidos correctamente",
        "total" => count($trueques),
        "data" => $trueques
    ]);
}

// ============================================================
// POST — Proponer un trueque nuevo
// ============================================================
elseif ($metodo === "POST") {

    $id_usuario_propone = $_POST["id_usuario_propone"] ?? "";
    $id_oferta_propone  = $_POST["id_oferta_propone"]  ?? "";
    $id_usuario_recibe  = $_POST["id_usuario_recibe"]  ?? "";
    $id_oferta_recibe   = $_POST["id_oferta_recibe"]   ?? "";
    $descripcion_acuerdo = $_POST["descripcion_acuerdo"] ?? "";

    // Los cuatro IDs son obligatorios: sin ellos no sabemos
    // quién propone qué, ni a quién, ni a cambio de qué
    if (empty($id_usuario_propone) || empty($id_oferta_propone) || 
        empty($id_usuario_recibe)  || empty($id_oferta_recibe)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "id_usuario_propone, id_oferta_propone, id_usuario_recibe e id_oferta_recibe son obligatorios"
        ]);
        exit();
    }

    // Un usuario no puede proponerse un trueque a sí mismo
    if ($id_usuario_propone == $id_usuario_recibe) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "No puedes proponerte un trueque a ti mismo"
        ]);
        exit();
    }

    $descripcion_acuerdo = mysqli_real_escape_string($conexion, $descripcion_acuerdo);

    // El trueque siempre nace en 'pendiente' — falta que la
    // otra persona lo acepte
    $sql = "INSERT INTO trueque 
                (estado, descripcion_acuerdo, id_usuario_propone, id_oferta_propone, id_usuario_recibe, id_oferta_recibe)
            VALUES 
                ('pendiente', '$descripcion_acuerdo', $id_usuario_propone, $id_oferta_propone, $id_usuario_recibe, $id_oferta_recibe)";

    if (mysqli_query($conexion, $sql)) {
        $nuevo_id = mysqli_insert_id($conexion);

        echo json_encode([
            "status" => "success",
            "mensaje" => "Trueque propuesto correctamente",
            "id_trueque_creado" => $nuevo_id,
            "data" => [
                "estado" => "pendiente",
                "id_usuario_propone" => $id_usuario_propone,
                "id_oferta_propone" => $id_oferta_propone,
                "id_usuario_recibe" => $id_usuario_recibe,
                "id_oferta_recibe" => $id_oferta_recibe
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al proponer el trueque: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// PUT — Cambiar el estado de un trueque (aceptar, completar, cancelar)
// ============================================================
elseif ($metodo === "PUT") {

    // PUT no llena $_POST automáticamente, leemos el cuerpo a mano
    parse_str(file_get_contents("php://input"), $datos);

    $id_trueque = $datos["id_trueque"] ?? "";
    $estado     = $datos["estado"]     ?? "";

    if (empty($id_trueque)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_trueque es obligatorio"
        ]);
        exit();
    }

    // Estos son los únicos estados a los que se puede pasar
    // desde un PUT ('pendiente' no aplica aquí porque esa es
    // la propuesta recién creada)
    $estados_validos = ["aceptado", "completado", "cancelado"];

    if (!in_array($estado, $estados_validos)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El estado debe ser 'aceptado', 'completado' o 'cancelado'"
        ]);
        exit();
    }

    // Si el trueque se marca como 'completado', además de
    // cambiar el estado, ponemos la fecha_trueque con NOW()
    // y marcamos las dos ofertas involucradas como 'intercambiada'
    if ($estado === "completado") {

        $sql = "UPDATE trueque 
                SET estado = 'completado', fecha_trueque = NOW()
                WHERE id_trueque = $id_trueque";

        mysqli_query($conexion, $sql);

        // Buscamos las dos ofertas de este trueque para
        // actualizarlas también
        $buscar = mysqli_query($conexion, 
            "SELECT id_oferta_propone, id_oferta_recibe FROM trueque WHERE id_trueque = $id_trueque"
        );
        $fila = mysqli_fetch_assoc($buscar);

        if ($fila) {
            $id_of_propone = $fila["id_oferta_propone"];
            $id_of_recibe  = $fila["id_oferta_recibe"];

            mysqli_query($conexion, 
                "UPDATE oferta SET estado = 'intercambiada' WHERE id_oferta IN ($id_of_propone, $id_of_recibe)"
            );
        }

    } else {
        // Para 'aceptado' o 'cancelado' solo cambiamos el estado
        $sql = "UPDATE trueque SET estado = '$estado' WHERE id_trueque = $id_trueque";
        mysqli_query($conexion, $sql);
    }

    echo json_encode([
        "status" => "success",
        "mensaje" => "Trueque actualizado correctamente",
        "id_trueque_editado" => $id_trueque,
        "nuevo_estado" => $estado
    ]);
}

// ============================================================
// DELETE — Eliminar un trueque
// ============================================================
elseif ($metodo === "DELETE") {

    parse_str(file_get_contents("php://input"), $datos);

    $id_trueque = $datos["id_trueque"] ?? "";

    if (empty($id_trueque)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_trueque es obligatorio para eliminar"
        ]);
        exit();
    }

    $sql = "DELETE FROM trueque WHERE id_trueque = $id_trueque";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Trueque eliminado correctamente",
            "id_trueque_eliminado" => $id_trueque
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al eliminar el trueque: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// Cualquier otro método no permitido
// ============================================================
else {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Metodo no permitido"
    ]);
}
?>