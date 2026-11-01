<?php
// ============================================================
// seguridad.php — Guardia de sesión para las APIs de Trueque Match
// Evidencia GA11-220501098-AA3-EV02
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Este archivo hace dos cosas, como un portero a la entrada:
//   1. Abre la sesión de PHP para saber quién está usando la API.
//   2. Si nadie inició sesión, responde 401 y corta el programa
//      ANTES de conectarse a la base de datos.
// Después, cada API toma el usuario de $_SESSION y no de lo que
// mande la pantalla (así nadie puede hacerse pasar por otra persona).
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function exigir_sesion() {
    if (!isset($_SESSION["usuario_id"]) || intval($_SESSION["usuario_id"]) <= 0) {
        http_response_code(401);
        echo json_encode([
            "status"  => "error",
            "mensaje" => "Debes iniciar sesion para usar este servicio"
        ]);
        exit();
    }
}

// Devuelve el id del usuario que inició sesión, como número entero
function usuario_sesion() {
    return intval($_SESSION["usuario_id"] ?? 0);
}
?>
