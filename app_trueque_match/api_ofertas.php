<?php
// ============================================================
// api_ofertas.php — API de Ofertas para Trueque Match
// Evidencias GA7-AA5-EV02, EV03, EV04
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================

// Le decimos al navegador y a Postman que vamos a responder en JSON
header("Content-Type: application/json");

// Permitimos que Postman se conecte desde cualquier lugar
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Incluimos la conexión a la base de datos
require_once "../conexion.php";

// Detectamos qué tipo de petición nos está haciendo Postman
$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// GET — Listar todas las ofertas (para verificar que existen)
// ============================================================
if ($metodo === "GET") {

    // Consultamos todas las ofertas de la BD
    $sql = "SELECT id_oferta, titulo, descripcion, categoria, estado, ciudad 
            FROM oferta 
            ORDER BY id_oferta DESC";
    
    $resultado = mysqli_query($conexion, $sql);
    
    // Guardamos las ofertas en un arreglo
    $ofertas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $ofertas[] = $fila;
    }
    
    // Respondemos con JSON
    echo json_encode([
        "status" => "success",
        "mensaje" => "Ofertas obtenidas correctamente",
        "total" => count($ofertas),
        "data" => $ofertas
    ]);
}

// ============================================================
// POST — Insertar una oferta nueva
// ============================================================
elseif ($metodo === "POST") {

    // Recibimos los datos que nos manda Postman
    $titulo      = $_POST["titulo"]      ?? "";
    $descripcion = $_POST["descripcion"] ?? "";
    $categoria   = $_POST["categoria"]   ?? "producto";
    $ciudad      = $_POST["ciudad"]      ?? "Bogotá";
    $id_usuario  = $_POST["id_usuario"]  ?? 7; // ID de Gerson en la BD

    // Validamos que los campos obligatorios llegaron
    if (empty($titulo) || empty($descripcion)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El titulo y la descripcion son obligatorios"
        ]);
        exit();
    }

    // Insertamos la oferta en la BD
    $sql = "INSERT INTO oferta (titulo, descripcion, categoria, estado, ciudad, id_usuario) 
            VALUES ('$titulo', '$descripcion', '$categoria', 'activa', '$ciudad', $id_usuario)";
    
    if (mysqli_query($conexion, $sql)) {
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

    // PUT manda los datos diferente — los leemos así
    parse_str(file_get_contents("php://input"), $datos);
    
    $id_oferta   = $datos["id_oferta"]   ?? "";
    $titulo      = $datos["titulo"]      ?? "";
    $descripcion = $datos["descripcion"] ?? "";
    $ciudad      = $datos["ciudad"]      ?? "";

    // Validamos que llegó el ID
    if (empty($id_oferta)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_oferta es obligatorio para editar"
        ]);
        exit();
    }

    // Actualizamos la oferta en la BD
    $sql = "UPDATE oferta 
            SET titulo='$titulo', descripcion='$descripcion', ciudad='$ciudad' 
            WHERE id_oferta=$id_oferta";
    
    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Oferta actualizada correctamente",
            "id_oferta_editada" => $id_oferta,
            "datos_nuevos" => [
                "titulo"      => $titulo,
                "descripcion" => $descripcion,
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

    // DELETE también manda los datos así
    parse_str(file_get_contents("php://input"), $datos);
    
    $id_oferta = $datos["id_oferta"] ?? "";

    // Validamos que llegó el ID
    if (empty($id_oferta)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_oferta es obligatorio para eliminar"
        ]);
        exit();
    }

    // Eliminamos la oferta de la BD
    $sql = "DELETE FROM oferta WHERE id_oferta=$id_oferta";
    
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