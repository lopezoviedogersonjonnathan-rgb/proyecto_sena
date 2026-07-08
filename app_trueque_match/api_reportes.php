<?php
// ============================================================
// api_reportes.php — API de Reportes para Trueque Match
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Un reporte es una denuncia: un usuario avisa que otro
// usuario, una oferta o un trueque tuvo algún problema
// (fraude, incumplimiento, mal comportamiento, u otro motivo).
// Un administrador es quien después revisa y resuelve.
// ============================================================

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../conexion.php";

$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// GET — Listar reportes
// ============================================================
if ($metodo === "GET") {

    // estado es opcional: si llega, filtramos solo los
    // reportes en ese estado (por ejemplo, solo los pendientes
    // que un administrador todavía tiene que revisar)
    $estado = $_GET["estado"] ?? "";

    if (!empty($estado)) {
        $estado_seguro = mysqli_real_escape_string($conexion, $estado);
        $sql = "SELECT * FROM reporte WHERE estado = '$estado_seguro' ORDER BY fecha_reporte DESC";
    } else {
        $sql = "SELECT * FROM reporte ORDER BY fecha_reporte DESC";
    }

    $resultado = mysqli_query($conexion, $sql);
    $reportes = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $reportes[] = $fila;
    }

    echo json_encode([
        "status" => "success",
        "mensaje" => "Reportes obtenidos correctamente",
        "total" => count($reportes),
        "data" => $reportes
    ]);
}

// ============================================================
// POST — Crear un reporte nuevo
// ============================================================
elseif ($metodo === "POST") {

    $motivo               = $_POST["motivo"]               ?? "";
    $descripcion          = $_POST["descripcion"]          ?? "";
    $id_usuario_reporta   = $_POST["id_usuario_reporta"]   ?? "";
    $id_usuario_reportado = $_POST["id_usuario_reportado"] ?? null;
    $id_oferta            = $_POST["id_oferta"]            ?? null;
    $id_trueque           = $_POST["id_trueque"]            ?? null;

    if (empty($motivo) || empty($descripcion) || empty($id_usuario_reporta)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "motivo, descripcion e id_usuario_reporta son obligatorios"
        ]);
        exit();
    }

    // Solo se aceptan estos 4 motivos, tal como los define la
    // columna ENUM en la base de datos
    $motivos_validos = ["fraude", "incumplimiento", "comportamiento", "otro"];
    if (!in_array($motivo, $motivos_validos)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El motivo debe ser fraude, incumplimiento, comportamiento u otro"
        ]);
        exit();
    }

    $descripcion = mysqli_real_escape_string($conexion, $descripcion);

    // id_usuario_reportado, id_oferta e id_trueque son
    // opcionales — si no llegan, guardamos NULL
    $valor_usuario_reportado = empty($id_usuario_reportado) ? "NULL" : $id_usuario_reportado;
    $valor_oferta            = empty($id_oferta)            ? "NULL" : $id_oferta;
    $valor_trueque           = empty($id_trueque)           ? "NULL" : $id_trueque;

    $sql = "INSERT INTO reporte 
                (motivo, descripcion, estado, id_usuario_reporta, id_usuario_reportado, id_oferta, id_trueque)
            VALUES 
                ('$motivo', '$descripcion', 'pendiente', $id_usuario_reporta, $valor_usuario_reportado, $valor_oferta, $valor_trueque)";

    if (mysqli_query($conexion, $sql)) {
        $nuevo_id = mysqli_insert_id($conexion);

        echo json_encode([
            "status" => "success",
            "mensaje" => "Reporte creado correctamente",
            "id_reporte_creado" => $nuevo_id
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al crear el reporte: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// PUT — Cambiar el estado de un reporte (revisado / resuelto)
// ============================================================
elseif ($metodo === "PUT") {

    parse_str(file_get_contents("php://input"), $datos);

    $id_reporte = $datos["id_reporte"] ?? "";
    $estado     = $datos["estado"]     ?? "";

    if (empty($id_reporte)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_reporte es obligatorio"
        ]);
        exit();
    }

    // 'pendiente' no aplica aquí porque ese es el estado
    // inicial con el que nace el reporte, no una respuesta
    $estados_validos = ["revisado", "resuelto"];
    if (!in_array($estado, $estados_validos)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El estado debe ser 'revisado' o 'resuelto'"
        ]);
        exit();
    }

    // Solo ponemos fecha_resolucion cuando de verdad se resolvió
    if ($estado === "resuelto") {
        $sql = "UPDATE reporte SET estado = '$estado', fecha_resolucion = NOW() WHERE id_reporte = $id_reporte";
    } else {
        $sql = "UPDATE reporte SET estado = '$estado' WHERE id_reporte = $id_reporte";
    }

    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Reporte actualizado correctamente",
            "id_reporte_editado" => $id_reporte,
            "nuevo_estado" => $estado
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al actualizar el reporte: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// DELETE — Eliminar un reporte
// ============================================================
elseif ($metodo === "DELETE") {

    parse_str(file_get_contents("php://input"), $datos);

    $id_reporte = $datos["id_reporte"] ?? "";

    if (empty($id_reporte)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_reporte es obligatorio para eliminar"
        ]);
        exit();
    }

    $sql = "DELETE FROM reporte WHERE id_reporte = $id_reporte";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Reporte eliminado correctamente",
            "id_reporte_eliminado" => $id_reporte
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al eliminar el reporte: " . mysqli_error($conexion)
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