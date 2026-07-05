<?php
// ============================================================
// api_registro.php — API de Registro para Trueque Match
// Pensada para que la app móvil (React Native) pueda crear
// cuentas nuevas sin depender del formulario web con sesiones
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Sigue exactamente las mismas reglas que registro.php, para
// que un usuario registrado desde la app móvil quede igual
// de válido que uno registrado desde la web
// ============================================================

// Avisamos que la respuesta va a ser JSON
header("Content-Type: application/json");

// Permitimos que la app móvil (o Postman) se conecte sin
// que el navegador lo bloquee por seguridad (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Traemos la conexión ya armada a MariaDB
require_once "../conexion.php";

// Esta API solo acepta POST, porque registrar un usuario
// siempre es "crear algo nuevo"
$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo !== "POST") {
    // Si alguien intenta usar GET, PUT o DELETE aquí, le
    // avisamos que este endpoint no lo permite
    echo json_encode([
        "status" => "error",
        "mensaje" => "Este endpoint solo acepta peticiones POST"
    ]);
    exit();
}

// Recibimos los datos que manda Postman o la app móvil
// trim() quita espacios de más al principio y al final
$nombre   = trim($_POST["nombre"]   ?? "");
$apellido = trim($_POST["apellido"] ?? "");
$correo   = trim($_POST["correo"]   ?? "");
$ciudad   = $_POST["ciudad"] ?? "Bogotá";
$pass1    = $_POST["pass1"]  ?? "";
$pass2    = $_POST["pass2"]  ?? "";

// isset() revisa si "terminos" llegó en la petición
// (en la app móvil puede venir como texto "true" o "1")
$terminos = isset($_POST["terminos"]) && $_POST["terminos"] !== "false" && $_POST["terminos"] !== "0";

// Unimos nombre y apellido en un solo campo, igual que
// se hace en registro.php
$nombre_completo = $nombre . " " . $apellido;

// ============================================================
// VALIDACIONES — las mismas que ya usa registro.php
// ============================================================

// 1. Revisamos que los campos obligatorios sí llegaron
if (empty($nombre) || empty($apellido) || empty($correo) || empty($pass1)) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Completa todos los campos obligatorios"
    ]);
    exit();
}

// 2. filter_var con FILTER_VALIDATE_EMAIL revisa que el
// correo tenga formato de correo real (algo@algo.com)
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "El correo electronico no es valido"
    ]);
    exit();
}

// 3. strlen() cuenta los caracteres de la contraseña
if (strlen($pass1) < 8) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "La contrasena debe tener minimo 8 caracteres"
    ]);
    exit();
}

// 4. Las dos contraseñas deben ser exactamente iguales
if ($pass1 !== $pass2) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Las contrasenas no coinciden"
    ]);
    exit();
}

// 5. El usuario debe aceptar los terminos y condiciones
if (!$terminos) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Debes aceptar los terminos y condiciones"
    ]);
    exit();
}

// ============================================================
// Si pasó todas las validaciones, seguimos con el registro
// ============================================================

// mysqli_real_escape_string() limpia los datos para evitar
// que alguien intente meter código SQL malicioso
$nombre_completo = mysqli_real_escape_string($conexion, $nombre_completo);
$correo          = mysqli_real_escape_string($conexion, $correo);
$ciudad          = mysqli_real_escape_string($conexion, $ciudad);

// password_hash() cifra la contraseña con bcrypt
// Nunca se guarda una contraseña en texto plano
$clave_cifrada = password_hash($pass1, PASSWORD_DEFAULT);

// Revisamos si ya existe una cuenta con ese correo,
// para no crear usuarios duplicados
$check = mysqli_query($conexion,
    "SELECT id_usuario FROM USUARIO WHERE correo = '$correo'"
);

if (mysqli_num_rows($check) > 0) {
    // mysqli_num_rows() cuenta cuántas filas encontró
    // Si encontró al menos 1, ese correo ya está registrado
    echo json_encode([
        "status" => "error",
        "mensaje" => "Ya existe una cuenta con ese correo electronico"
    ]);
    exit();
}

// Armamos el INSERT del nuevo usuario
// id_tipo_usuario = 1 significa usuario estandar (no admin)
$sql = "INSERT INTO USUARIO
            (nombre, correo, clave_acceso, ciudad, id_tipo_usuario)
        VALUES
            ('$nombre_completo', '$correo', '$clave_cifrada', '$ciudad', 1)";

if (mysqli_query($conexion, $sql)) {

    // mysqli_insert_id() nos da el ID que la base de datos
    // le asignó automáticamente al nuevo usuario
    $nuevo_id = mysqli_insert_id($conexion);

    echo json_encode([
        "status" => "success",
        "mensaje" => "Cuenta creada exitosamente",
        "id_usuario_creado" => $nuevo_id,
        "data" => [
            "nombre" => $nombre_completo,
            "correo" => $correo,
            "ciudad" => $ciudad
        ]
    ]);
} else {
    // Si algo falló, mysqli_error() nos dice el motivo exacto
    echo json_encode([
        "status" => "error",
        "mensaje" => "Error al crear la cuenta: " . mysqli_error($conexion)
    ]);
}

mysqli_close($conexion);
?>