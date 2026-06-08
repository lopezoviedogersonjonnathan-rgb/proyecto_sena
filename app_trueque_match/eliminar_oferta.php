<?php
/*
 * =============================================
 * TRUEQUE MATCH — eliminar_oferta.php
 * Recibe el ID de una oferta y la elimina de la BD
 * Solo puede eliminar ofertas que sean del usuario
 * logueado — así nadie borra las ofertas de otro
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// Abrimos la caja de sesiones para saber
// quién está logueado
session_start();

// Siempre respondemos en formato JSON
// Es como hablar en un idioma que JavaScript entiende
header('Content-Type: application/json');

// Si no hay sesión activa — no autorizado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit();
}

// Solo aceptamos peticiones POST
// GET sería peligroso porque cualquiera podría
// poner la URL y eliminar ofertas sin querer
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
    exit();
}

// Conectamos a la BD
include('../conexion.php');

// Recogemos el ID de la oferta a eliminar
// intval() convierte a número entero — seguridad extra
// para evitar que metan texto malicioso
$id_oferta  = intval($_POST['id_oferta'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

// Validamos que el ID sea válido
if ($id_oferta <= 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'ID de oferta inválido']);
    exit();
}

/*
 * SEGURIDAD IMPORTANTE:
 * El WHERE tiene DOS condiciones:
 * 1. id_oferta = la oferta que queremos borrar
 * 2. id_usuario = el usuario logueado
 * Así aunque alguien adivine el ID de otra oferta
 * NO puede borrarla porque no es su usuario
 * Es como tener llave + huella dactilar 🔐
 */
$sql = "DELETE FROM OFERTA 
        WHERE id_oferta = $id_oferta 
        AND id_usuario = $usuario_id";

if (mysqli_query($conexion, $sql)) {

    // mysqli_affected_rows() dice cuántas filas
    // fueron afectadas por el DELETE
    // Si es 0 significa que la oferta no era del usuario
    if (mysqli_affected_rows($conexion) > 0) {
        echo json_encode([
            'ok' => true,
            'mensaje' => 'Oferta eliminada correctamente'
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'mensaje' => 'No tienes permiso para eliminar esta oferta'
        ]);
    }

} else {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error en BD: ' . mysqli_error($conexion)
    ]);
}

mysqli_close($conexion);
?>