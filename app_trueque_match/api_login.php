<?php
// ============================================================
// api_login.php — API de autenticación para Trueque Match
// Evidencias GA7-AA5-EV02, EV03, EV04
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================

// Le decimos a Postman que vamos a responder en formato JSON
// sin esto Postman no entiende bien la respuesta
header("Content-Type: application/json");

// Permitimos conexiones desde cualquier origen (necesario para testing)
header("Access-Control-Allow-Origin: *");

// Permitimos los métodos HTTP que vamos a usar
header("Access-Control-Allow-Methods: POST");

// Iniciamos la sesión PHP para poder manejar el login
session_start();

// Incluimos el archivo de conexión a MariaDB puerto 3307
require_once "../conexion.php";

// Detectamos qué método HTTP nos está enviando Postman
$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// Solo aceptamos peticiones POST para el login
// (POST porque estamos enviando usuario y contraseña)
// ============================================================
if ($metodo !== "POST") {
    // Si alguien intenta con GET u otro método, rechazamos
    echo json_encode([
        "status"  => "error",
        "mensaje" => "Solo se aceptan peticiones POST para el login"
    ]);
    exit(); // Detenemos la ejecución aquí
}

// ============================================================
// Recibimos los datos que Postman nos envía en el Body
// ?? "" significa: si no llega ese dato, usa texto vacío
// ============================================================
$correo      = $_POST["correo"]      ?? "";
$clave       = $_POST["clave_acceso"] ?? "";

// ============================================================
// Validamos que los dos campos llegaron con datos
// ============================================================
if (empty($correo) || empty($clave)) {
    echo json_encode([
        "status"  => "error",
        "mensaje" => "El correo y la clave_acceso son obligatorios"
    ]);
    exit(); // Detenemos si falta algún campo
}

// ============================================================
// Buscamos el usuario en la BD por su correo
// Usamos comillas simples alrededor del correo en el SQL
// ============================================================
$sql = "SELECT id_usuario, nombre, correo, clave_acceso, ciudad, reputacion 
        FROM usuario 
        WHERE correo = '$correo' 
        AND activo = 1
        LIMIT 1";

// Ejecutamos la consulta en MariaDB
$resultado = mysqli_query($conexion, $sql);

// ============================================================
// Verificamos si encontramos un usuario con ese correo
// ============================================================
if (mysqli_num_rows($resultado) === 0) {
    // No encontramos el correo — respondemos con mensaje genérico
    // (no decimos si fue el correo o la clave — seguridad)
    echo json_encode([
        "status"  => "error",
        "mensaje" => "Credenciales incorrectas"
    ]);
    exit();
}

// ============================================================
// Obtenemos los datos del usuario encontrado
// ============================================================
$usuario = mysqli_fetch_assoc($resultado);

// ============================================================
// Verificamos la contraseña con bcrypt
// password_verify compara la clave escrita con el hash guardado
// ============================================================
if (!password_verify($clave, $usuario["clave_acceso"])) {
    // La clave no coincide — mismo mensaje genérico por seguridad
    echo json_encode([
        "status"  => "error",
        "mensaje" => "Credenciales incorrectas"
    ]);
    exit();
}

// ============================================================
// ¡Login exitoso! Guardamos la sesión y respondemos
// ============================================================

// Guardamos los datos del usuario en la sesión PHP
$_SESSION["usuario_id"]     = $usuario["id_usuario"];
$_SESSION["usuario_nombre"] = $usuario["nombre"];
$_SESSION["usuario_correo"] = $usuario["correo"];

// Respondemos con JSON exitoso — nunca devolvemos la clave_acceso
echo json_encode([
    "status"  => "success",
    "mensaje" => "Login exitoso — Bienvenido a Trueque Match",
    "data"    => [
        "id_usuario" => $usuario["id_usuario"],
        "nombre"     => $usuario["nombre"],
        "correo"     => $usuario["correo"],
        "ciudad"     => $usuario["ciudad"],
        "reputacion" => $usuario["reputacion"]
    ]
]);
?>