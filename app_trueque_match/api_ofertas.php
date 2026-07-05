<?php
// ============================================================
// api_ofertas.php — API de Ofertas para Trueque Match
// Evidencias GA7-AA5-EV02, EV03, EV04
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Este archivo es UNA sola API que responde distinto según el
// método que le mande Postman (GET, POST, PUT o DELETE).
// Es como un mesero que atiende diferente según lo que le pidas.
// ============================================================

// header() le avisa al navegador/Postman que la respuesta
// que vamos a mandar es JSON, no HTML normal
header("Content-Type: application/json");

// Estas 3 líneas dejan que Postman (o cualquier programa)
// se conecte a esta API sin que el navegador lo bloquee
// por seguridad (esto se llama CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Traemos el archivo que ya tiene armada la conexión a MariaDB
// (usa require_once para no repetir la conexión si ya existe)
require_once "../conexion.php";

// $_SERVER["REQUEST_METHOD"] nos dice qué tipo de petición
// llegó: GET, POST, PUT o DELETE. Lo guardamos en una variable
// para no escribir $_SERVER[...] cada vez
$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// GET — Listar todas las ofertas (para verificar que existen)
// ============================================================
if ($metodo === "GET") {

    // Armamos la consulta SQL: traemos las columnas que
    // necesitamos, ordenadas de la más nueva a la más vieja
    $sql = "SELECT id_oferta, titulo, descripcion, categoria, estado, ciudad 
            FROM oferta 
            ORDER BY id_oferta DESC";
    
    // Ejecutamos la consulta contra la base de datos
    $resultado = mysqli_query($conexion, $sql);
    
    // Creamos un arreglo vacío donde vamos a ir guardando
    // cada oferta que encontremos
    $ofertas = [];

    // while recorre fila por fila el resultado de la consulta
    // y la va metiendo en el arreglo $ofertas
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ofertas[] = $fila;
    }
    
    // echo json_encode() convierte el arreglo de PHP en texto
    // JSON y lo manda de vuelta a Postman
    echo json_encode([
        "status" => "success",
        "mensaje" => "Ofertas obtenidas correctamente",
        "total" => count($ofertas), // count() cuenta cuántas ofertas hay
        "data" => $ofertas
    ]);
}

// ============================================================
// POST — Insertar una oferta nueva
// ============================================================
elseif ($metodo === "POST") {

    // Recibimos los datos que nos manda Postman desde el Body
    // El ?? "" significa: "si no llega el dato, usa texto vacío"
    // así evitamos errores de PHP si falta un campo
    $titulo      = $_POST["titulo"]      ?? "";
    $descripcion = $_POST["descripcion"] ?? "";
    $categoria   = $_POST["categoria"]   ?? "producto"; // valor por defecto
    $ciudad      = $_POST["ciudad"]      ?? "Bogotá";    // valor por defecto
    $id_usuario  = $_POST["id_usuario"]  ?? 7; // ID de Gerson en la BD

    // Validamos que los campos obligatorios sí llegaron
    // empty() revisa si la variable está vacía
    if (empty($titulo) || empty($descripcion)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El titulo y la descripcion son obligatorios"
        ]);
        exit(); // exit() detiene el script para no seguir ejecutando
    }

    // Armamos el INSERT con los datos recibidos
    // 'activa' queda fijo porque toda oferta nueva nace activa
    $sql = "INSERT INTO oferta (titulo, descripcion, categoria, estado, ciudad, id_usuario) 
            VALUES ('$titulo', '$descripcion', '$categoria', 'activa', '$ciudad', $id_usuario)";
    
    // Ejecutamos el INSERT. Si mysqli_query devuelve true, funcionó
    if (mysqli_query($conexion, $sql)) {

        // mysqli_insert_id() nos da el ID que la BD le puso
        // automáticamente a la fila que acabamos de insertar
        $nuevo_id = mysqli_insert_id($conexion);

        echo json_encode([
            "status" => "success",
            "mensaje" => "Oferta creada correctamente en Trueque Match",
            "id_oferta_creada" => $nuevo_id,
            "data" => [
                "titulo"     => $titulo,
                "descripcion"=> $descripcion,
                "categoria"  => $categoria,
                "ciudad"     => $ciudad
            ]
        ]);
    } else {
        // Si algo falló, mysqli_error() nos dice el motivo exacto
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al crear la oferta: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// PUT — Editar una oferta existente
// ============================================================
elseif ($metodo === "PUT") {

    // OJO: a diferencia de POST, cuando el método es PUT, PHP
    // no llena automáticamente $_POST. Por eso hay que leer
    // el cuerpo de la petición "a mano" con estas dos líneas:
    // file_get_contents("php://input") lee todo lo que mandó Postman
    // parse_str() lo convierte en un arreglo, igual que $_POST
    parse_str(file_get_contents("php://input"), $datos);
    
    // Sacamos cada dato del arreglo $datos que acabamos de crear
    $id_oferta   = $datos["id_oferta"]   ?? "";
    $titulo      = $datos["titulo"]      ?? "";
    $descripcion = $datos["descripcion"] ?? "";
    $categoria   = $datos["categoria"]   ?? ""; // <- agregado en esta corrección
    $ciudad      = $datos["ciudad"]      ?? "";

    // Validamos que llegó el ID, porque sin saber CUÁL oferta
    // editar, no podemos hacer nada
    if (empty($id_oferta)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_oferta es obligatorio para editar"
        ]);
        exit();
    }

    // Armamos el UPDATE. WHERE id_oferta=$id_oferta es lo que
    // le dice a MySQL "solo cambia ESTA fila, ninguna otra"
    $sql = "UPDATE oferta 
            SET titulo='$titulo', descripcion='$descripcion', categoria='$categoria', ciudad='$ciudad' 
            WHERE id_oferta=$id_oferta";
    
    // Ejecutamos el UPDATE
    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Oferta actualizada correctamente",
            "id_oferta_editada" => $id_oferta,
            "datos_nuevos" => [
                "titulo"      => $titulo,
                "descripcion" => $descripcion,
                "categoria"   => $categoria, // <- agregado en esta corrección
                "ciudad"      => $ciudad
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al editar: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// DELETE — Eliminar una oferta por ID
// ============================================================
elseif ($metodo === "DELETE") {

    // DELETE tampoco llena $_POST automáticamente, así que
    // usamos el mismo truco de php://input que en el PUT
    parse_str(file_get_contents("php://input"), $datos);
    
    $id_oferta = $datos["id_oferta"] ?? "";

    // Sin ID no sabemos qué oferta borrar, así que validamos
    if (empty($id_oferta)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_oferta es obligatorio para eliminar"
        ]);
        exit();
    }

    // DELETE FROM borra la fila completa que tenga ese id_oferta
    $sql = "DELETE FROM oferta WHERE id_oferta=$id_oferta";
    
    // Ejecutamos el DELETE
    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Oferta eliminada correctamente de Trueque Match",
            "id_oferta_eliminada" => $id_oferta
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al eliminar: " . mysqli_error($conexion)
        ]);
    }
}
?>