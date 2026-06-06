<?php
/*
 * =============================================
 * TRUEQUE MATCH — agregar_oferta.php
 * Formulario HTML + PHP para insertar ofertas
 * Demuestra el CREATE del CRUD
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// Incluimos la conexión a la base de datos
include('conexion.php');

// Variable para guardar mensajes de éxito o error
$mensaje = "";
$tipo_mensaje = "";

/*
 * $_SERVER['REQUEST_METHOD'] nos dice cómo llegó la petición
 * GET  = el usuario solo abrió la página (mostrar formulario)
 * POST = el usuario envió el formulario (procesar datos)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
     * $_POST[] es un array que contiene todos los datos
     * que el usuario escribió en el formulario
     * El nombre dentro de [] debe coincidir con el name="" del input
     */
    $titulo          = $_POST['titulo'];
    $descripcion     = $_POST['descripcion'];
    $categoria       = $_POST['categoria'];
    $ciudad          = $_POST['ciudad'];
    $valor_estimado  = $_POST['valor_estimado'];
    $id_usuario      = 1; // Por ahora fijo (Laura Gómez), después será sesión

    /*
     * mysqli_real_escape_string() protege contra inyección SQL
     * Limpia los datos antes de meterlos a la base de datos
     * Es como desinfectar los datos antes de guardarlos
     */
    $titulo      = mysqli_real_escape_string($conexion, $titulo);
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    $ciudad      = mysqli_real_escape_string($conexion, $ciudad);

    // Validación básica: verificar que los campos obligatorios no estén vacíos
    if (empty($titulo) || empty($descripcion) || empty($categoria)) {
        $mensaje = "❌ Error: Título, descripción y categoría son obligatorios.";
        $tipo_mensaje = "error";
    } else {

        /*
         * INSERT INTO = comando SQL para agregar un nuevo registro
         * VALUES = los valores que vamos a insertar
         * Las ? serían con prepared statements (avanzado)
         * Por ahora usamos escape_string para proteger
         */
        $sql = "INSERT INTO OFERTA 
                    (titulo, descripcion, categoria, ciudad, valor_estimado, id_usuario)
                VALUES 
                    ('$titulo', '$descripcion', '$categoria', '$ciudad', '$valor_estimado', $id_usuario)";

        // mysqli_query() ejecuta el INSERT
        if (mysqli_query($conexion, $sql)) {
            // mysqli_insert_id() devuelve el ID del registro recién creado
            $nuevo_id = mysqli_insert_id($conexion);
            $mensaje = "✅ ¡Oferta creada exitosamente! ID asignado: #$nuevo_id";
            $tipo_mensaje = "exito";
        } else {
            // mysqli_error() devuelve el error de MySQL si algo falló
            $mensaje = "❌ Error al guardar: " . mysqli_error($conexion);
            $tipo_mensaje = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trueque Match — Agregar Oferta</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #111111;
            color: #F5F0EB;
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
            min-height: 100vh;
        }
        h1 { color: #C0392B; font-size: 28px; letter-spacing: 3px; margin-bottom: 6px; }
        .subtitulo { color: #888; font-size: 14px; margin-bottom: 28px; }
        .card {
            background: #1A1A1A;
            border: 1px solid #2A2A2A;
            border-radius: 16px;
            padding: 32px;
            max-width: 600px;
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
        label {
            font-size: 11px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        input, textarea, select {
            background: #222;
            border: 1.5px solid #2A2A2A;
            border-radius: 10px;
            color: #F5F0EB;
            padding: 12px 14px;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
            transition: border-color 0.2s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #C0392B;
        }
        textarea { resize: vertical; min-height: 90px; }
        .btn {
            background: #C0392B;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            letter-spacing: 1px;
            transition: background 0.2s;
        }
        .btn:hover { background: #E74C3C; }
        .mensaje-exito {
            background: rgba(39,174,96,.15);
            border: 1px solid rgba(39,174,96,.4);
            color: #27AE60;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .mensaje-error {
            background: rgba(192,57,43,.15);
            border: 1px solid rgba(192,57,43,.4);
            color: #E74C3C;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .links { margin-top: 20px; display: flex; gap: 12px; }
        .links a {
            color: #C0392B;
            font-size: 13px;
            text-decoration: none;
        }
        .links a:hover { text-decoration: underline; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    </style>
</head>
<body>

    <h1>AGREGAR OFERTA</h1>
    <p class="subtitulo">➕ Publica un nuevo producto, servicio, conocimiento o experiencia</p>

    <div class="card">

        <?php
        // Si hay mensaje (éxito o error) lo mostramos aquí
        if (!empty($mensaje)) {
            $clase = ($tipo_mensaje === 'exito') ? 'mensaje-exito' : 'mensaje-error';
            echo "<div class='$clase'>$mensaje</div>";
        }
        ?>

        <!--
            action="" = envía el formulario a la misma página
            method="POST" = los datos van ocultos en el cuerpo de la petición
            (más seguro que GET que los pone en la URL)
        -->
        <form action="" method="POST">

            <div class="form-group">
                <label for="titulo">Título de la oferta *</label>
                <!--
                    name="titulo" = así lo reconoce PHP en $_POST['titulo']
                    required = el navegador no deja enviar si está vacío
                -->
                <input type="text"
                       id="titulo"
                       name="titulo"
                       placeholder="Ej: Guitarra acústica Yamaha en perfecto estado"
                       required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción *</label>
                <textarea id="descripcion"
                          name="descripcion"
                          placeholder="Describe detalladamente lo que ofreces..."
                          required></textarea>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="categoria">Categoría *</label>
                    <!--
                        select = lista desplegable
                        Los value="" son los valores que van a la BD
                        Deben coincidir exactamente con el ENUM de la tabla
                    -->
                    <select id="categoria" name="categoria" required>
                        <option value="">Selecciona...</option>
                        <option value="producto">📦 Producto</option>
                        <option value="servicio">🛠️ Servicio</option>
                        <option value="conocimiento">📚 Conocimiento</option>
                        <option value="experiencia">🎭 Experiencia</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ciudad">Ciudad</label>
                    <select id="ciudad" name="ciudad">
                        <option value="Bogotá">Bogotá</option>
                        <option value="Medellín">Medellín</option>
                        <option value="Cali">Cali</option>
                        <option value="Barranquilla">Barranquilla</option>
                        <option value="Cartagena">Cartagena</option>
                        <option value="Otra">Otra</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="valor_estimado">Valor estimado en COP (opcional)</label>
                <!--
                    type="number" = solo acepta números
                    min="0" = no permite negativos
                    step="1000" = sube de 1000 en 1000
                -->
                <input type="number"
                       id="valor_estimado"
                       name="valor_estimado"
                       placeholder="Ej: 350000"
                       min="0"
                       step="1000">
            </div>

            <!--
                type="submit" = botón que envía el formulario
                Al hacer clic, el navegador manda los datos por POST
            -->
            <button type="submit" class="btn">
                ➕ PUBLICAR OFERTA
            </button>

        </form>

        <div class="links">
            <a href="ofertas.php">← Ver todas las ofertas</a>
        </div>

    </div>

    <?php mysqli_close($conexion); ?>

</body>
</html>