<?php
/*
 * =============================================
 * TRUEQUE MATCH — guardar_oferta.php
 * Este archivo recibe los datos del formulario
 * "Nueva Oferta" del dashboard y los guarda en la BD.
 * Es el CARTERO que toma el sobre del formulario
 * y lo entrega a la base de datos.
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// session_start() abre la caja de sesiones
// Sin esto no sabemos quién está logueado
session_start();

// Le decimos al navegador que vamos a responder
// en formato JSON — como una lista ordenada
// que JavaScript puede leer fácilmente
header('Content-Type: application/json');

// Si no hay sesión activa significa que
// el usuario no está logueado — paramos todo
if (!isset($_SESSION['usuario_id'])) {
    // json_encode() convierte el array PHP a texto JSON
    // ['ok'=>false] significa que algo salió mal
    echo json_encode(['ok' => false, 'mensaje' => 'No autorizado']);
    exit(); // Paramos aquí
}

// Si la petición no es POST significa que
// alguien entró directo a esta URL sin datos
// $_SERVER['REQUEST_METHOD'] nos dice cómo llegó la petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido']);
    exit();
}

// Conectamos a la base de datos
// Los .. suben una carpeta (de app_trueque_match a proyecto_sena)
include('../conexion.php');

// Recogemos los datos que mandó el formulario
// $_POST[] es el array con todo lo que envió el usuario
// trim() quita espacios al inicio y al final — como limpiar el texto
$titulo      = trim($_POST['titulo']      ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria   = trim($_POST['categoria']   ?? '');
$ciudad      = trim($_POST['ciudad']      ?? 'Bogotá');
$valor       = floatval($_POST['valor_estimado'] ?? 0);

// El ID del usuario viene de la sesión — no del formulario
// Así evitamos que alguien publique ofertas con el ID de otro
$usuario_id = $_SESSION['usuario_id'];

// ---- VALIDACIONES ----
// Verificamos que los campos obligatorios no estén vacíos
// empty() devuelve true si la variable está vacía
if (empty($titulo)) {
    echo json_encode(['ok' => false, 'mensaje' => 'El título es obligatorio']);
    exit();
}

if (empty($descripcion)) {
    echo json_encode(['ok' => false, 'mensaje' => 'La descripción es obligatoria']);
    exit();
}

if (empty($categoria)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Selecciona una categoría']);
    exit();
}

// Verificamos que la categoría sea válida
// in_array() busca si el valor está dentro del array
$categorias_validas = ['producto', 'servicio', 'conocimiento', 'experiencia'];
if (!in_array($categoria, $categorias_validas)) {
    echo json_encode(['ok' => false, 'mensaje' => 'Categoría no válida']);
    exit();
}

// ---- PROTECCIÓN CONTRA INYECCIÓN SQL ----
// mysqli_real_escape_string() limpia los datos
// Es como ponerle comillas de seguridad a todo
// para que nadie pueda meter código malicioso en la BD
$titulo      = mysqli_real_escape_string($conexion, $titulo);
$descripcion = mysqli_real_escape_string($conexion, $descripcion);
$ciudad      = mysqli_real_escape_string($conexion, $ciudad);
$categoria   = mysqli_real_escape_string($conexion, $categoria);

// ---- INSERT EN LA BD ----
// INSERT INTO = agregar un nuevo registro a la tabla OFERTA
// Cada columna entre () corresponde a un valor en VALUES()
$sql = "INSERT INTO OFERTA 
            (titulo, descripcion, categoria, ciudad, valor_estimado, id_usuario)
        VALUES 
            ('$titulo', '$descripcion', '$categoria', '$ciudad', $valor, $usuario_id)";

// mysqli_query() ejecuta el INSERT
// Devuelve true si funcionó, false si algo salió mal
if (mysqli_query($conexion, $sql)) {

    // mysqli_insert_id() devuelve el ID que le asignó
    // la BD al nuevo registro — como el número de factura
    $nuevo_id = mysqli_insert_id($conexion);

    // Respondemos con éxito y el ID nuevo
    // El dashboard usa este ID para mostrar el toast
    echo json_encode([
        'ok' => true,
        'id' => $nuevo_id,
        'mensaje' => 'Oferta creada exitosamente'
    ]);

} else {
    // Si algo falló en la BD devolvemos el error
    // mysqli_error() trae el mensaje de error de MySQL
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error en BD: ' . mysqli_error($conexion)
    ]);
}

// Cerramos la conexión — buena práctica liberar recursos
mysqli_close($conexion);
?>