<?php
// ============================================================
// api_notificaciones.php — API de Notificaciones para Trueque Match
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Una notificación es una alerta que le llega a un usuario
// (por ejemplo: "te llegó una solicitud nueva" o "tu trueque
// fue aceptado"). Puede estar ligada a un trueque o a una
// oferta, o a ninguno de los dos (una notificación general).
// ============================================================

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../conexion.php";

$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// GET — Listar notificaciones de un usuario
// ============================================================
if ($metodo === "GET") {

    $id_usuario = $_GET["id_usuario"] ?? "";

    if (empty($id_usuario)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_usuario es obligatorio para listar notificaciones"
        ]);
        exit();
    }

    // solo_no_leidas es opcional: si llega como "true", solo
    // mostramos las que el usuario todavía no ha visto
    $solo_no_leidas = $_GET["solo_no_leidas"] ?? "false";

    $sql = "SELECT * FROM notificacion WHERE id_usuario = $id_usuario";

    if ($solo_no_leidas === "true") {
        $sql .= " AND leida = 0";
    }

    $sql .= " ORDER BY fecha_envio DESC";

    $resultado = mysqli_query($conexion, $sql);
    $notificaciones = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $notificaciones[] = $fila;
    }

    echo json_encode([
        "status" => "success",
        "mensaje" => "Notificaciones obtenidas correctamente",
        "total" => count($notificaciones),
        "data" => $notificaciones
    ]);
}

// ============================================================
// POST — Crear una notificación nueva
// ============================================================
elseif ($metodo === "POST") {

    $mensaje    = $_POST["mensaje"]    ?? "";
    $tipo       = $_POST["tipo"]       ?? "info";
    $id_usuario = $_POST["id_usuario"] ?? "";
    $id_trueque = $_POST["id_trueque"] ?? null;
    $id_oferta  = $_POST["id_oferta"]  ?? null;

    if (empty($mensaje) || empty($id_usuario)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "mensaje e id_usuario son obligatorios"
        ]);
        exit();
    }

    // Solo se aceptan estos 4 tipos, tal como los define la
    // columna ENUM en la base de datos
    $tipos_validos = ["info", "alerta", "sistema", "trueque"];
    if (!in_array($tipo, $tipos_validos)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El tipo debe ser info, alerta, sistema o trueque"
        ]);
        exit();
    }

    $mensaje = mysqli_real_escape_string($conexion, $mensaje);

    // id_trueque e id_oferta son opcionales — si no llegan,
    // guardamos NULL en vez de un texto vacío que dañaría el INSERT
    $valor_trueque = empty($id_trueque) ? "NULL" : $id_trueque;
    $valor_oferta  = empty($id_oferta)  ? "NULL" : $id_oferta;

    $sql = "INSERT INTO notificacion (mensaje, tipo, leida, id_usuario, id_trueque, id_oferta)
            VALUES ('$mensaje', '$tipo', 0, $id_usuario, $valor_trueque, $valor_oferta)";

    if (mysqli_query($conexion, $sql)) {
        $nuevo_id = mysqli_insert_id($conexion);

        echo json_encode([
            "status" => "success",
            "mensaje" => "Notificacion creada correctamente",
            "id_notificacion_creada" => $nuevo_id
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al crear la notificacion: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// PUT — Marcar una notificación como leída
// ============================================================
elseif ($metodo === "PUT") {

    parse_str(file_get_contents("php://input"), $datos);

    $id_notificacion = $datos["id_notificacion"] ?? "";

    if (empty($id_notificacion)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_notificacion es obligatorio"
        ]);
        exit();
    }

    // Esta API solo sirve para marcarla como leída (leida = 1).
    // No tiene sentido "desmarcarla" en un proyecto tan simple,
    // así que el PUT siempre pone leida en 1
    $sql = "UPDATE notificacion SET leida = 1 WHERE id_notificacion = $id_notificacion";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Notificacion marcada como leida",
            "id_notificacion_editada" => $id_notificacion
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al actualizar la notificacion: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// DELETE — Eliminar una notificación
// ============================================================
elseif ($metodo === "DELETE") {

    parse_str(file_get_contents("php://input"), $datos);

    $id_notificacion = $datos["id_notificacion"] ?? "";

    if (empty($id_notificacion)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_notificacion es obligatorio para eliminar"
        ]);
        exit();
    }

    $sql = "DELETE FROM notificacion WHERE id_notificacion = $id_notificacion";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Notificacion eliminada correctamente",
            "id_notificacion_eliminada" => $id_notificacion
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al eliminar la notificacion: " . mysqli_error($conexion)
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