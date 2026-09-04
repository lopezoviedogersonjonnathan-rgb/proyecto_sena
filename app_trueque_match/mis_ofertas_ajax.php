<?php
/*
 * =============================================
 * TRUEQUE MATCH — mis_ofertas_ajax.php
 * Este archivo es como un mesero entre el dashboard
 * y la base de datos. El dashboard le pide las ofertas
 * del usuario y este archivo va a la BD, las trae
 * y las devuelve en formato JSON (como una lista ordenada)
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// session_start() abre la caja de sesiones
// Sin esto no podemos saber quién está logueado
session_start();

// Si no hay sesión activa (no está logueado)
// devolvemos un error en formato JSON y paramos todo
if (!isset($_SESSION['usuario_id'])) {
    // header() le dice al navegador que la respuesta es JSON
    // JSON es como un diccionario: { "clave": "valor" }
    header('Content-Type: application/json');
    // echo imprime el JSON de error
    // json_encode() convierte un array PHP a texto JSON
    echo json_encode(['error' => 'No autorizado']);
    exit(); // Paramos aquí, no ejecutamos nada más
}

// Guardamos el ID del usuario logueado
// para usarlo en la consulta SQL
$usuario_id = $_SESSION['usuario_id'];

// Conectamos a la base de datos
// Los .. suben una carpeta (de app_trueque_match a proyecto_sena)
include('../conexion.php');

// ============================================
// CONSULTA SQL — Traemos las ofertas del usuario
// SELECT = leer datos de la tabla
// * = traer todas las columnas
// WHERE = filtro: solo las del usuario logueado
// ORDER BY = ordenar: las más nuevas primero
// ============================================
$sql = "SELECT 
            id_oferta,      -- El ID único de cada oferta
            titulo,         -- El nombre de la oferta
            descripcion,    -- La descripción larga
            categoria,      -- producto / servicio / conocimiento / experiencia
            estado,         -- activa / inactiva / intercambiada
            ciudad,         -- Bogotá, Medellín, etc.
            valor_estimado, -- Cuánto vale en COP
            fecha_publicacion -- Cuándo fue publicada
        FROM oferta
        WHERE id_usuario = $usuario_id
        ORDER BY fecha_publicacion DESC";

$resultado = mysqli_query($conexion, $sql);

// Creamos un array vacío donde guardaremos todas las ofertas
// Imagínalo como una canasta vacía que vamos a llenar
$ofertas = [];

// while() repite el loop hasta que no haya más filas
// mysqli_fetch_assoc() trae una fila cada vez como array
while ($fila = mysqli_fetch_assoc($resultado)) {
    // Cada $fila es una oferta completa
    // La empujamos al array $ofertas
    $ofertas[] = $fila;
}

// Cerramos la conexión — buena práctica liberar recursos
mysqli_close($conexion);

// Le decimos al navegador que lo que vamos a enviar es JSON
header('Content-Type: application/json');

// json_encode() convierte el array PHP a texto JSON
// El dashboard recibirá algo así:
// [{"id_oferta":"1","titulo":"Guitarra","categoria":"producto",...}, ...]
echo json_encode($ofertas);
?>