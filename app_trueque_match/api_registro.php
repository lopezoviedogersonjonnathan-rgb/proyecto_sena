<?php
/*
 * =============================================
 * TRUEQUE MATCH — api_registro.php
 * API para registrar nuevos usuarios desde Postman o la app móvil
 * Recibe POST con nombre, correo, clave_acceso, ciudad
 * Responde siempre con JSON
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// Incluimos la conexión a la BD
// Los dos puntos (..) suben una carpeta — conexion.php está un nivel arriba
include('../conexion.php');

// Le decimos al navegador (o a Postman) que la respuesta es JSON
// Es como poner "este sobre contiene un formulario" antes de abrirlo
header('Content-Type: application/json; charset=utf-8');

// Solo aceptamos peticiones POST
// Si alguien entra por URL directa (GET), lo bloqueamos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'mensaje' => 'Solo se acepta POST']);
    exit();
}

// ---- LEER LOS DATOS QUE LLEGARON ----
// trim() quita espacios al inicio y al final
// ?? '' significa: si no existe, usar texto vacío
$nombre        = trim($_POST['nombre']        ?? '');
$correo        = trim($_POST['correo']        ?? '');
$clave_acceso  = trim($_POST['clave_acceso']  ?? '');
$ciudad        = trim($_POST['ciudad']        ?? 'Bogotá');

// ---- VALIDACIONES ----
// Verificamos que los campos obligatorios no estén vacíos
if (empty($nombre) || empty($correo) || empty($clave_acceso)) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Nombre, correo y clave_acceso son obligatorios'
    ]);
    exit();
}

// filter_var verifica que el correo tenga formato válido (con @ y dominio)
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'mensaje' => 'El correo no es válido']);
    exit();
}

// La contraseña debe tener mínimo 8 caracteres
if (strlen($clave_acceso) < 8) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'La contraseña debe tener mínimo 8 caracteres'
    ]);
    exit();
}

// ---- VERIFICAR QUE EL CORREO NO EXISTA YA ----
// Limpiamos el correo para evitar inyección SQL
$correo_seguro = mysqli_real_escape_string($conexion, $correo);

$check = mysqli_query($conexion,
    "SELECT id_usuario FROM USUARIO WHERE correo = '$correo_seguro'"
);

if (mysqli_num_rows($check) > 0) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Ya existe una cuenta con ese correo'
    ]);
    exit();
}

// ---- CIFRAR LA CONTRASEÑA ----
// password_hash() convierte "Gerson2026" en un código imposible de leer
// Ejemplo: $2y$10$abc123...
// NUNCA guardamos contraseñas en texto plano en la BD
$clave_cifrada = password_hash($clave_acceso, PASSWORD_DEFAULT);

// Limpiamos los demás campos
$nombre_seguro = mysqli_real_escape_string($conexion, $nombre);
$ciudad_segura = mysqli_real_escape_string($conexion, $ciudad);

// ---- INSERTAR EN LA BD ----
// id_tipo_usuario = 1 significa usuario estándar (no administrador)
$sql = "INSERT INTO USUARIO (nombre, correo, clave_acceso, ciudad, id_tipo_usuario)
        VALUES ('$nombre_seguro', '$correo_seguro', '$clave_cifrada', '$ciudad_segura', 1)";

if (mysqli_query($conexion, $sql)) {
    // mysqli_insert_id() devuelve el ID que MySQL le asignó al nuevo usuario
    $nuevo_id = mysqli_insert_id($conexion);

    echo json_encode([
        'ok'      => true,
        'mensaje' => 'Usuario registrado correctamente',
        'id'      => $nuevo_id,
        'nombre'  => $nombre,
        'correo'  => $correo
    ]);
} else {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Error al registrar: ' . mysqli_error($conexion)
    ]);
}

mysqli_close($conexion);
?>