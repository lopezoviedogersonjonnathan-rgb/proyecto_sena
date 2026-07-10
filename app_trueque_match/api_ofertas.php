<?php
// ============================================================
// api_ofertas.php — API de Ofertas para Trueque Match
// Evidencias GA7-AA5-EV02, EV03, EV04
// Gerson Jonnathan López Oviedo | Ficha: 3186647
// ============================================================
// Este archivo es UNA sola API que responde distinto según el
// método que le mande Postman (GET, POST, PUT o DELETE).
// Es como un mesero que atiende diferente según lo que le pidas.
//
// ACTUALIZACIÓN (Contexto v12): el PUT y el DELETE se reforzaron
// con las validaciones que antes vivían en api_editar_oferta.php
// y api_eliminar_oferta.php (que ya se eliminaron del proyecto).
// Ahora hay UN SOLO camino oficial para editar/eliminar ofertas.
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

// Lista de categorías permitidas en el proyecto.
// La definimos aquí arriba porque la vamos a usar en el PUT
// para validar, igual que se hacía en api_editar_oferta.php
$categorias_validas = ['producto', 'servicio', 'conocimiento', 'experiencia'];

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

    // Escapamos el texto antes de meterlo al SQL, para que un
    // apóstrofe o comilla en el titulo/descripcion no rompa la
    // consulta ni abra la puerta a inyección SQL
    $titulo_seguro      = mysqli_real_escape_string($conexion, $titulo);
    $descripcion_segura = mysqli_real_escape_string($conexion, $descripcion);
    $ciudad_segura      = mysqli_real_escape_string($conexion, $ciudad);

    // Armamos el INSERT con los datos recibidos
    // 'activa' queda fijo porque toda oferta nueva nace activa
    $sql = "INSERT INTO oferta (titulo, descripcion, categoria, estado, ciudad, id_usuario) 
            VALUES ('$titulo_seguro', '$descripcion_segura', '$categoria', 'activa', '$ciudad_segura', $id_usuario)";
    
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
// REFORZADO con la validación que traía api_editar_oferta.php
// ============================================================
elseif ($metodo === "PUT") {

    // OJO: a diferencia de POST, cuando el método es PUT, PHP
    // no llena automáticamente $_POST. Por eso hay que leer
    // el cuerpo de la petición "a mano" con estas dos líneas:
    // file_get_contents("php://input") lee todo lo que mandó Postman
    // parse_str() lo convierte en un arreglo, igual que $_POST
    parse_str(file_get_contents("php://input"), $datos);
    
    // intval() convierte el id a número entero de forma segura.
    // Si llegara texto raro o nada, intval devuelve 0 en vez de
    // dejar pasar algo peligroso directo al SQL
    $id_oferta      = intval($datos["id_oferta"] ?? 0);
    $titulo         = trim($datos["titulo"]         ?? "");
    $descripcion    = trim($datos["descripcion"]    ?? "");
    $categoria      = trim($datos["categoria"]      ?? "");
    $ciudad         = trim($datos["ciudad"]         ?? "");
    $valor_estimado = floatval($datos["valor_estimado"] ?? 0);

    // Validamos que llegó un ID válido, porque sin saber CUÁL
    // oferta editar, no podemos hacer nada
    if ($id_oferta <= 0) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_oferta es obligatorio y debe ser mayor a 0"
        ]);
        exit();
    }

    // Validamos que los campos obligatorios de texto sí llegaron
    if (empty($titulo) || empty($descripcion) || empty($categoria)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Titulo, descripcion y categoria son obligatorios"
        ]);
        exit();
    }

    // Verificamos que la categoría sea una de las 4 permitidas
    // en el proyecto. in_array() revisa si el valor está en la lista
    if (!in_array($categoria, $categorias_validas)) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Categoria no valida"
        ]);
        exit();
    }

    // Verificamos que la oferta exista ANTES de intentar editarla.
    // Así evitamos ejecutar un UPDATE que no cambia nada porque
    // el ID no existe, y le damos un mensaje claro a quien prueba
    $check = mysqli_query($conexion, "SELECT id_oferta FROM oferta WHERE id_oferta = $id_oferta");
    if (mysqli_num_rows($check) === 0) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "La oferta no existe"
        ]);
        exit();
    }

    // Escapamos el texto contra inyección SQL antes de armar el UPDATE
    $titulo_seguro      = mysqli_real_escape_string($conexion, $titulo);
    $descripcion_segura = mysqli_real_escape_string($conexion, $descripcion);
    $ciudad_segura      = mysqli_real_escape_string($conexion, $ciudad);

    // Armamos el UPDATE. WHERE id_oferta=$id_oferta es lo que
    // le dice a MySQL "solo cambia ESTA fila, ninguna otra"
    $sql = "UPDATE oferta 
            SET titulo='$titulo_seguro', 
                descripcion='$descripcion_segura', 
                categoria='$categoria', 
                ciudad='$ciudad_segura',
                valor_estimado=$valor_estimado
            WHERE id_oferta=$id_oferta";
    
    // Ejecutamos el UPDATE
    if (mysqli_query($conexion, $sql)) {
        echo json_encode([
            "status" => "success",
            "mensaje" => "Oferta actualizada correctamente",
            "id_oferta_editada" => $id_oferta,
            "datos_nuevos" => [
                "titulo"         => $titulo,
                "descripcion"    => $descripcion,
                "categoria"      => $categoria,
                "ciudad"         => $ciudad,
                "valor_estimado" => $valor_estimado
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
// REFORZADO con la validación que traía api_eliminar_oferta.php
// ============================================================
elseif ($metodo === "DELETE") {

    // DELETE tampoco llena $_POST automáticamente, así que
    // usamos el mismo truco de php://input que en el PUT
    parse_str(file_get_contents("php://input"), $datos);
    
    // intval() protege contra IDs raros o maliciosos, igual
    // que hacíamos en api_eliminar_oferta.php
    $id_oferta = intval($datos["id_oferta"] ?? 0);

    // Sin un ID válido no sabemos qué oferta borrar
    if ($id_oferta <= 0) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "El id_oferta es obligatorio y debe ser mayor a 0"
        ]);
        exit();
    }

    // Verificamos que la oferta exista antes de borrar. Así, si
    // alguien manda un ID que ya no existe (por ejemplo, uno que
    // ya se completó en un trueque), el mensaje es claro en vez
    // de un DELETE silencioso que no borra nada
    $check = mysqli_query($conexion, "SELECT id_oferta FROM oferta WHERE id_oferta = $id_oferta");
    if (mysqli_num_rows($check) === 0) {
        echo json_encode([
            "status" => "error",
            "mensaje" => "No existe una oferta con ese ID"
        ]);
        exit();
    }

    // DELETE FROM borra la fila completa que tenga ese id_oferta.
    // El CASCADE definido en la base de datos se encarga de borrar
    // automáticamente las solicitudes y favoritos relacionados
    $sql = "DELETE FROM oferta WHERE id_oferta=$id_oferta";
    
    // Ejecutamos el DELETE
    if (mysqli_query($conexion, $sql)) {
        // mysqli_affected_rows() dice cuántas filas se borraron
        // Si es 1, todo salió bien
        $filas = mysqli_affected_rows($conexion);

        echo json_encode([
            "status" => "success",
            "mensaje" => "Oferta eliminada correctamente de Trueque Match",
            "id_oferta_eliminada" => $id_oferta,
            "filas_borradas" => $filas
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "mensaje" => "Error al eliminar: " . mysqli_error($conexion)
        ]);
    }
}
?>