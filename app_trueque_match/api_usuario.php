<?php
// ============================================================
// api_usuario.php — API para editar el perfil del usuario
// Trueque Match | Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Mismo patrón que api_ofertas.php: responde según el método
// (por ahora solo PUT, porque lo único que necesitamos es
// EDITAR el perfil, no crear ni borrar usuarios desde aquí).
//
// DIFERENCIA IMPORTANTE con api_ofertas.php: en vez de confiar
// en el id_usuario que manda quien hace la petición, usamos el
// id_usuario que ya está guardado en la SESIÓN del navegador.
// Así nadie puede editar el perfil de otra persona con solo
// cambiar un número en la petición.
// ============================================================

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

// session_start() para poder leer $_SESSION['usuario_id']
session_start();

require_once "../conexion.php";

$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// PUT — Editar el perfil del usuario que tiene la sesión activa
// ============================================================
if ($metodo === "PUT") {

    // Si no hay sesión activa, no dejamos editar nada
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "No hay sesión activa. Vuelve a iniciar sesión."
        ]);
        exit();
    }

    // El id_usuario sale de la SESIÓN, no de lo que mande el navegador
    $id_usuario = intval($_SESSION['usuario_id']);

    // Igual que en api_ofertas.php: PUT no llena $_POST solo,
    // toca leer el cuerpo a mano con parse_str()
    parse_str(file_get_contents("php://input"), $datos);

    $nombre   = trim($datos["nombre"]   ?? "");
    $telefono = trim($datos["telefono"] ?? "");
    $ciudad   = trim($datos["ciudad"]   ?? "");

    // El nombre es obligatorio, el resto puede ir vacío
    if (empty($nombre)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El nombre es obligatorio"
        ]);
        exit();
    }

    // Escapamos el texto contra inyección SQL antes del UPDATE
    $nombre_seguro   = mysqli_real_escape_string($conexion, $nombre);
    $telefono_seguro = mysqli_real_escape_string($conexion, $telefono);
    $ciudad_segura   = mysqli_real_escape_string($conexion, $ciudad);

    $sql = "UPDATE usuario
            SET nombre='$nombre_seguro',
                telefono='$telefono_seguro',
                ciudad='$ciudad_segura'
            WHERE id_usuario=$id_usuario";

    if (mysqli_query($conexion, $sql)) {

        // Actualizamos también la sesión, para que el resto del
        // dashboard (que lee de $_SESSION, no de la BD directo)
        // muestre el nombre y ciudad nuevos sin tener que cerrar
        // sesión y volver a entrar
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_ciudad'] = $ciudad;

        echo json_encode([
            "status" => "success",
            "mensaje" => "Perfil actualizado correctamente",
            "datos_nuevos" => [
                "nombre"   => $nombre,
                "telefono" => $telefono,
                "ciudad"   => $ciudad
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al actualizar: " . mysqli_error($conexion)
        ]);
    }

} else {
    // Cualquier otro método (GET, POST, DELETE) no está permitido aquí
    echo json_encode([
        "status" => "error",
        "mensaje" => "Método no permitido en esta API"
    ]);
}