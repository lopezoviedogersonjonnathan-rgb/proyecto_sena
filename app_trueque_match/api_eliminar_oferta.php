<?php
/*
 * =============================================
 * TRUEQUE MATCH — api_eliminar_oferta.php
 * API para eliminar una oferta desde Postman o la app móvil
 * Recibe POST con id_oferta
 * Responde siempre con JSON
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// session_start() activa el sistema de sesiones de PHP
// Así podemos verificar que el usuario está logueado
// Es como verificar que tienes el carné antes de dejarte entrar
session_start();

include('../conexion.php');

// Toda respuesta es JSON — Postman y la app lo necesitan así
header('Content-Type: application/json; charset=utf-8');

// Solo aceptamos POST
// Eliminar por GET sería peligroso — cualquier link podría borrar datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'mensaje' => 'Solo se acepta POST']);
    exit();
}

// ---- LEER EL ID QUE LLEGÓ ----
// intval() convierte a número entero de forma segura
// Si alguien manda "abc" o nada, intval devuelve 0
$id_oferta = intval($_POST['id_oferta'] ?? 0);

// ---- VALIDAR QUE EL ID ES VÁLIDO ----
// Un id de 0 o negativo no puede existir en la BD
if ($id_oferta <= 0) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'El id_oferta es obligatorio y debe ser mayor a 0'
    ]);
    exit();
}

// ---- VERIFICAR QUE LA OFERTA EXISTE ----
// Antes de borrar, confirmamos que el registro existe en la BD
// Si no existe, respondemos con error en vez de ejecutar un DELETE vacío
$check = mysqli_query($conexion,
    "SELECT id_oferta FROM OFERTA WHERE id_oferta = $id_oferta"
);

if (mysqli_num_rows($check) === 0) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'No existe una oferta con ese ID'
    ]);
    exit();
}

// ---- EJECUTAR EL DELETE ----
// DELETE FROM = borrar registros de la tabla
// WHERE = condición — sin esto borraría TODA la tabla (catástrofe)
// El CASCADE en la BD se encarga de borrar automáticamente
// las solicitudes y favoritos relacionados a esta oferta
$sql = "DELETE FROM OFERTA WHERE id_oferta = $id_oferta";

if (mysqli_query($conexion, $sql)) {
    // mysqli_affected_rows() dice cuántas filas se borraron
    // Si es 1, todo salió bien
    $filas = mysqli_affected_rows($conexion);

    echo json_encode([
        'ok'           => true,
        'mensaje'      => 'Oferta eliminada correctamente',
        'id_oferta'    => $id_oferta,
        'filas_borradas' => $filas
    ]);
} else {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Error al eliminar: ' . mysqli_error($conexion)
    ]);
}

mysqli_close($conexion);
?>