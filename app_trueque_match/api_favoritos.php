<?php
// ============================================================
// api_favoritos.php — API de Favoritos para Trueque Match
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Un favorito es cuando un usuario guarda una oferta que le
// gustó, para encontrarla fácil después. Es una tabla N:M
// (un usuario puede tener muchos favoritos, y una oferta puede
// estar guardada por muchos usuarios distintos).
//
// Esta API solo necesita 3 acciones: ver los favoritos de un
// usuario, agregar uno nuevo, y quitar uno. No existe un PUT
// porque no hay nada que "editar" en un favorito — o está
// guardado, o no lo está.
// ============================================================

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once "../conexion.php";

$metodo = $_SERVER["REQUEST_METHOD"];

// ============================================================
// GET — Listar los favoritos de un usuario
// ============================================================
if ($metodo === "GET") {

    $id_usuario = $_GET["id_usuario"] ?? "";

    if (empty($id_usuario)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_usuario es obligatorio para listar favoritos"
        ]);
        exit();
    }

    // Unimos con la tabla oferta para traer el título y demás
    // datos, no solo el número — así la respuesta es útil de
    // verdad y no solo un montón de IDs sueltos
    $sql = "SELECT uo.id_usuario, uo.id_oferta, uo.fecha_guardado,
                   o.titulo, o.descripcion, o.categoria, o.ciudad, o.estado
            FROM usuario_oferta uo
            INNER JOIN oferta o ON uo.id_oferta = o.id_oferta
            WHERE uo.id_usuario = $id_usuario
            ORDER BY uo.fecha_guardado DESC";

    $resultado = mysqli_query($conexion, $sql);
    $favoritos = [];

    while ($fila = mysqli_fetch_assoc($resultado)) {
        $favoritos[] = $fila;
    }

    echo json_encode([
        "status" => "success",
        "mensaje" => "Favoritos obtenidos correctamente",
        "total" => count($favoritos),
        "data" => $favoritos
    ]);
}

// ============================================================
// POST — Agregar una oferta a favoritos
// ============================================================
elseif ($metodo === "POST") {

    $id_usuario = $_POST["id_usuario"] ?? "";
    $id_oferta  = $_POST["id_oferta"]  ?? "";

    if (empty($id_usuario) || empty($id_oferta)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "id_usuario e id_oferta son obligatorios"
        ]);
        exit();
    }

    // Revisamos que esa oferta no esté ya guardada como
    // favorita por ese mismo usuario, para no duplicarla
    $check = mysqli_query($conexion,
        "SELECT * FROM usuario_oferta WHERE id_usuario = $id_usuario AND id_oferta = $id_oferta"
    );

    if (mysqli_num_rows($check) > 0) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Esta oferta ya esta en los favoritos de este usuario"
        ]);
        exit();
    }

    $sql = "INSERT INTO usuario_oferta (id_usuario, id_oferta) VALUES ($id_usuario, $id_oferta)";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Oferta agregada a favoritos correctamente",
            "id_usuario" => $id_usuario,
            "id_oferta" => $id_oferta
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al agregar a favoritos: " . mysqli_error($conexion)
        ]);
    }
}

// ============================================================
// DELETE — Quitar una oferta de favoritos
// ============================================================
elseif ($metodo === "DELETE") {

    parse_str(file_get_contents("php://input"), $datos);

    $id_usuario = $datos["id_usuario"] ?? "";
    $id_oferta  = $datos["id_oferta"]  ?? "";

    if (empty($id_usuario) || empty($id_oferta)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "id_usuario e id_oferta son obligatorios para quitar un favorito"
        ]);
        exit();
    }

    // Como esta tabla no tiene un id propio, borramos usando
    // los dos IDs juntos para identificar la fila exacta
    $sql = "DELETE FROM usuario_oferta WHERE id_usuario = $id_usuario AND id_oferta = $id_oferta";

    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Oferta quitada de favoritos correctamente",
            "id_usuario" => $id_usuario,
            "id_oferta" => $id_oferta
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al quitar de favoritos: " . mysqli_error($conexion)
        ]);
    }
}

else {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Metodo no permitido"
    ]);
}
?>