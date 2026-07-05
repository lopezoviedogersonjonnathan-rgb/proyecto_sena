<?php
/*
 * =============================================
 * TRUEQUE MATCH — api_editar_oferta.php
 * API para editar una oferta desde Postman o la app móvil
 * Recibe POST con id_oferta, titulo, descripcion, categoria, ciudad, valor_estimado
 * Responde siempre con JSON
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// Iniciamos sesión para verificar que el usuario esté logueado
// Sin esto cualquiera podría editar ofertas ajenas
session_start();

include('../conexion.php');

// Toda respuesta de esta API es JSON
// Postman y la app móvil esperan esto para procesar la respuesta
header('Content-Type: application/json; charset=utf-8');

// Solo aceptamos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'mensaje' => 'Solo se acepta POST']);
    exit();
}

// ---- LEER LOS DATOS QUE LLEGARON ----
// intval() convierte el id a número entero de forma segura
// Si llegara texto o nada, intval devuelve 0
$id_oferta      = intval($_POST['id_oferta']      ?? 0);
$titulo         = trim($_POST['titulo']           ?? '');
$descripcion    = trim($_POST['descripcion']      ?? '');
$categoria      = trim($_POST['categoria']        ?? '');
$ciudad         = trim($_POST['ciudad']           ?? '');
$valor_estimado = floatval($_POST['valor_estimado'] ?? 0);

// ---- VALIDACIONES ----
if ($id_oferta <= 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'El id_oferta es obligatorio']);
    exit();
}

if (empty($titulo) || empty($descripcion) || empty($categoria)) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Titulo, descripcion y categoria son obligatorios'
    ]);
    exit();
}

// Verificamos que la categoría sea válida
// in_array() comprueba si el valor está dentro de la lista permitida
$categorias_validas = ['producto', 'servicio', 'conocimiento', 'experiencia'];
if (!in_array($categoria, $categorias_validas)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Categoria no valida']);
    exit();
}

// ---- SANITIZAR LOS DATOS ----
// mysqli_real_escape_string() protege contra inyección SQL
// Es como desinfectar los datos antes de meterlos a la BD
$titulo_seguro      = mysqli_real_escape_string($conexion, $titulo);
$descripcion_segura = mysqli_real_escape_string($conexion, $descripcion);
$ciudad_segura      = mysqli_real_escape_string($conexion, $ciudad);

// ---- VERIFICAR QUE LA OFERTA EXISTE ----
// No queremos intentar editar algo que no está en la BD
$check = mysqli_query($conexion,
    "SELECT id_oferta FROM OFERTA WHERE id_oferta = $id_oferta"
);

if (mysqli_num_rows($check) === 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'La oferta no existe']);
    exit();
}

// ---- EJECUTAR EL UPDATE ----
// SET = los campos que vamos a cambiar con sus nuevos valores
// WHERE = condición para no actualizar TODAS las ofertas de la tabla
// Sin WHERE sería un desastre — actualizaría todo
$sql = "UPDATE OFERTA SET
            titulo         = '$titulo_seguro',
            descripcion    = '$descripcion_segura',
            categoria      = '$categoria',
            ciudad         = '$ciudad_segura',
            valor_estimado = $valor_estimado
        WHERE id_oferta = $id_oferta";

if (mysqli_query($conexion, $sql)) {
    echo json_encode([
        'ok'        => true,
        'mensaje'   => 'Oferta actualizada correctamente',
        'id_oferta' => $id_oferta
    ]);
} else {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Error al actualizar: ' . mysqli_error($conexion)
    ]);
}

mysqli_close($conexion);
?>