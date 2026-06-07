<?php
/*
 * =============================================
 * TRUEQUE MATCH — conexion.php
 * Archivo que conecta PHP con MySQL
 * Como una tubería entre la app y la base de datos
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// mysqli_connect() es la función de PHP para conectarse a MySQL
// Recibe 4 parámetros: servidor, usuario, contraseña, nombre_bd
// El puerto 3307 va separado con ':' pegado al host
$conexion = mysqli_connect("127.0.0.1:3307", "root", "", "trueque_match_db");

// Verificamos si la conexión falló
// mysqli_connect_error() devuelve el mensaje de error si hubo uno
if (!$conexion) {
    // die() detiene todo el programa y muestra el mensaje
    die("ERROR: No se pudo conectar a la base de datos. " . mysqli_connect_error());
}

// Si llegamos aquí, la conexión fue exitosa
// Configuramos el juego de caracteres a UTF-8 para que los tildes funcionen bien
mysqli_set_charset($conexion, "utf8");

// Este mensaje es solo para probar — lo quitamos después
// echo "✅ Conexión exitosa a trueque_match_db";
?>