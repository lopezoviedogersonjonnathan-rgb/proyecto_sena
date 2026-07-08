<?php
// ============================================================
// api_solicitudes.php — API de Solicitudes para Trueque Match
// Evidencia de corrección — julio 2026
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Una "solicitud" es el paso PREVIO a un trueque: alguien ve
// una oferta que le interesa y le manda un mensaje al dueño
// preguntando si quiere intercambiar. El dueño puede aceptar
// o rechazar esa solicitud.
//
// Este archivo reemplaza la versión anterior que, por error,
// tenía código de registro de usuarios copiado y pegado.
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

// Guardamos qué tipo de petición llegó (GET, POST, PUT o DELETE)
$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// GET — Listar solicitudes
// ============================================================
// Sirve para ver, por ejemplo, todas las solicitudes que le
// han llegado a un usuario (las que tiene que responder)
if ($metodo === "GET") {

    // id_usuario_recibe es opcional: si llega, filtramos
    // solo las solicitudes de ESE usuario. Si no llega,
    // mostramos todas (útil para pruebas en Postman)
    $id_usuario_recibe = $_GET["id_usuario_recibe"] ?? "";

    if (!empty($id_usuario_recibe)) {
        $sql = "SELECT s.id_solicitud, s.mensaje, s.estado, s.fecha_solicitud, 
                       s.fecha_respuesta, s.id_usuario_solicita, s.id_usuario_recibe, s.id_oferta,
                       o.titulo AS titulo_oferta
                FROM solicitud s
                INNER JOIN oferta o ON s.id_oferta = o.id_oferta
                WHERE s.id_usuario_recibe = $id_usuario_recibe
                ORDER BY s.fecha_solicitud DESC";
    } else {
        $sql = "SELECT s.id_solicitud, s.mensaje, s.estado, s.fecha_solicitud, 
                       s.fecha_respuesta, s.id_usuario_solicita, s.id_usuario_recibe, s.id_oferta,
                       o.titulo AS titulo_oferta
                FROM solicitud s
                INNER JOIN oferta o ON s.id_oferta = o.id_oferta
                ORDER BY s.fecha_solicitud DESC";
    }

    $resultado = mysqli_query($conexion, $sql);
    $solicitudes = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $solicitudes[] = $fila;
    }

    echo json_encode([
        "status" => "success",
        "mensaje" => "Solicitudes obtenidas correctamente",
        "total" => count($solicitudes),
        "data" => $solicitudes
    ]);
}

// ============================================================
// POST — Crear una solicitud nueva (contactar por una oferta)
// ============================================================
elseif ($metodo === "POST") {

    $mensaje             = $_POST["mensaje"]             ?? "";
    $id_usuario_solicita = $_POST["id_usuario_solicita"]  ?? "";
    $id_usuario_recibe   = $_POST["id_usuario_recibe"]    ?? "";
    $id_oferta           = $_POST["id_oferta"]            ?? "";

    // Validamos que lleguen los tres IDs, sin ellos no
    // sabemos quién le escribe a quién ni sobre qué oferta
    if (empty($id_usuario_solicita) || empty($id_usuario_recibe) || empty($id_oferta)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "id_usuario_solicita, id_usuario_recibe e id_oferta son obligatorios"
        ]);
        exit();
    }

    // Un usuario no debería poder mandarse una solicitud a
    // sí mismo, así que lo bloqueamos
    if ($id_usuario_solicita == $id_usuario_recibe) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "No puedes enviarte una solicitud a ti mismo"
        ]);
        exit();
    }

    // Limpiamos el mensaje para evitar código SQL malicioso
    $mensaje = mysqli_real_escape_string($conexion, $mensaje);

    // El estado siempre nace en 'pendiente' — el dueño de la
    // oferta es quien decide después si acepta o rechaza
    $sql = "INSERT INTO solicitud (mensaje, estado, id_usuario_solicita, id_usuario_recibe, id_oferta)
            VALUES ('$mensaje', 'pendiente', $id_usuario_solicita, $id_usuario_recibe, $id_oferta)";

    if (mysqli_query($conexion, $sql)) {
        $nuevo_id = mysqli_insert_id($conexion);

        echo json_encode([
            "status" => "success",
            "mensaje" => "Solicitud enviada correctamente",
            "id_solicitud_creada" => $nuevo_id,
            "data" => [
                "mensaje" => $mensaje,
                "estado" => "pendiente",
                "id_usuario_solicita" => $id_usuario_solicita,
                "id_usuario_recibe" => $id_usuario_recibe,
                "id_oferta" => $id_oferta
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al crear la solicitud: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// PUT — Responder una solicitud (aceptar o rechazar)
// ============================================================
elseif ($metodo === "PUT") {

    // PUT no llena $_POST automáticamente, así que leemos
    // el cuerpo de la petición a mano
    parse_str(file_get_contents("php://input"), $datos);

    $id_solicitud = $datos["id_solicitud"] ?? "";
    $estado       = $datos["estado"]       ?? "";

    if (empty($id_solicitud)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_solicitud es obligatorio"
        ]);
        exit();
    }

    // Solo se permiten estos dos valores como respuesta.
    // 'pendiente' no se pone aquí porque esa es la solicitud
    // recién creada, no una respuesta
    $estados_validos = ["aceptada", "rechazada"];

    if (!in_array($estado, $estados_validos)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El estado debe ser 'aceptada' o 'rechazada'"
        ]);
        exit();
    }

    // NOW() le pide a MySQL que ponga la fecha y hora actual
    // en fecha_respuesta, para saber cuándo se respondió
    $sql = "UPDATE solicitud 
            SET estado = '$estado', fecha_respuesta = NOW()
            WHERE id_solicitud = $id_solicitud";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Solicitud actualizada correctamente",
            "id_solicitud_editada" => $id_solicitud,
            "nuevo_estado" => $estado
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al actualizar la solicitud: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// DELETE — Cancelar/eliminar una solicitud
// ============================================================
elseif ($metodo === "DELETE") {

    parse_str(file_get_contents("php://input"), $datos);

    $id_solicitud = $datos["id_solicitud"] ?? "";

    if (empty($id_solicitud)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_solicitud es obligatorio para eliminar"
        ]);
        exit();
    }

    $sql = "DELETE FROM solicitud WHERE id_solicitud = $id_solicitud";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Solicitud eliminada correctamente",
            "id_solicitud_eliminada" => $id_solicitud
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al eliminar la solicitud: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// Si llega cualquier otro método que no sea GET/POST/PUT/DELETE
// ============================================================
else {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Metodo no permitido"
    ]);
}
?>