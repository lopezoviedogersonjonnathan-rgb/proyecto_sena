<?php
/*
 * =============================================
 * TRUEQUE MATCH — editar_oferta.php
 * Demuestra UPDATE y DELETE del CRUD
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

include('conexion.php');

$mensaje = "";
$tipo_mensaje = "";

/*
 * ---- ELIMINAR (DELETE) ----
 * Cuando el usuario hace clic en "Eliminar"
 * se envía el id por GET en la URL: ?eliminar=3
 * $_GET[] lee los parámetros de la URL
 */
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar']; // (int) convierte a número para seguridad

    $sql = "DELETE FROM OFERTA WHERE id_oferta = $id";

    if (mysqli_query($conexion, $sql)) {
        $mensaje = "✅ Oferta #$id eliminada correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "❌ Error al eliminar: " . mysqli_error($conexion);
        $tipo_mensaje = "error";
    }
}

/*
 * ---- ACTUALIZAR (UPDATE) ----
 * Cuando el usuario envía el formulario de edición
 * llegamos por POST con el id y los nuevos datos
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id      = (int)$_POST['id_oferta'];
    $titulo  = mysqli_real_escape_string($conexion, $_POST['titulo']);
    $estado  = $_POST['estado'];
    $ciudad  = mysqli_real_escape_string($conexion, $_POST['ciudad']);
    $valor   = (float)$_POST['valor_estimado'];

    /*
     * UPDATE = comando SQL para modificar registros existentes
     * SET = qué campos cambiamos y con qué valores
     * WHERE = condición para no actualizar TODOS los registros
     * (sin WHERE actualizaría TODA la tabla — muy peligroso)
     */
    $sql = "UPDATE OFERTA SET
                titulo         = '$titulo',
                estado         = '$estado',
                ciudad         = '$ciudad',
                valor_estimado = $valor
            WHERE id_oferta = $id";

    if (mysqli_query($conexion, $sql)) {
        $mensaje = "✅ Oferta #$id actualizada correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "❌ Error al actualizar: " . mysqli_error($conexion);
        $tipo_mensaje = "error";
    }
}

/*
 * Leemos TODAS las ofertas para mostrarlas en la tabla
 * Aquí el profe ve el CRUD completo en una sola página
 */
$sql_listar = "SELECT o.*, u.nombre AS nombre_usuario
               FROM OFERTA o
               JOIN USUARIO u ON o.id_usuario = u.id_usuario
               ORDER BY o.id_oferta ASC";

$ofertas = mysqli_query($conexion, $sql_listar);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trueque Match — Gestionar Ofertas</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #111;
            color: #F5F0EB;
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
        }
        h1 { color: #C0392B; font-size: 28px; letter-spacing: 3px; margin-bottom: 6px; }
        .subtitulo { color: #888; font-size: 14px; margin-bottom: 24px; }
        .mensaje-exito {
            background: rgba(39,174,96,.15);
            border: 1px solid rgba(39,174,96,.4);
            color: #27AE60;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .mensaje-error {
            background: rgba(192,57,43,.15);
            border: 1px solid rgba(192,57,43,.4);
            color: #E74C3C;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1A1A1A;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        thead { background: #C0392B; color: white; }
        th, td {
            padding: 12px 14px;
            text-align: left;
            font-size: 13px;
            border-bottom: 1px solid #2A2A2A;
        }
        tr:hover { background: #222; }
        .btn-editar {
            background: #2980B9;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 4px;
        }
        .btn-eliminar {
            background: #C0392B;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-eliminar:hover { background: #E74C3C; }
        /* Modal de edición */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.activo { display: flex; }
        .modal {
            background: #1A1A1A;
            border: 1px solid #2A2A2A;
            border-radius: 16px;
            padding: 28px;
            width: 90%;
            max-width: 480px;
        }
        .modal h2 {
            color: #C0392B;
            font-size: 20px;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }
        .form-group { display: flex; flex-direction: column; gap: 5px; margin-bottom: 14px; }
        label { font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; }
        input, select {
            background: #222;
            border: 1.5px solid #2A2A2A;
            border-radius: 8px;
            color: #F5F0EB;
            padding: 10px 12px;
            font-size: 14px;
            font-family: 'Segoe UI', sans-serif;
        }
        input:focus, select:focus { outline: none; border-color: #C0392B; }
        .modal-btns { display: flex; gap: 10px; margin-top: 16px; }
        .btn-guardar {
            background: #C0392B;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            flex: 1;
        }
        .btn-cancelar {
            background: #2A2A2A;
            color: #888;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            flex: 1;
        }
        .links { margin-bottom: 20px; }
        .links a { color: #C0392B; font-size: 13px; text-decoration: none; margin-right: 16px; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h1>GESTIONAR OFERTAS</h1>
<p class="subtitulo">✏️ Editar y eliminar ofertas — UPDATE y DELETE del CRUD</p>

<div class="links">
    <a href="ofertas.php">← Ver ofertas</a>
    <a href="agregar_oferta.php">➕ Agregar oferta</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="<?= $tipo_mensaje === 'exito' ? 'mensaje-exito' : 'mensaje-error' ?>">
        <?= $mensaje ?>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Título</th>
            <th>Categoría</th>
            <th>Estado</th>
            <th>Ciudad</th>
            <th>Valor</th>
            <th>Usuario</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($o = mysqli_fetch_assoc($ofertas)): ?>
        <tr>
            <td><?= $o['id_oferta'] ?></td>
            <td><?= $o['titulo'] ?></td>
            <td><?= ucfirst($o['categoria']) ?></td>
            <td><?= ucfirst($o['estado']) ?></td>
            <td><?= $o['ciudad'] ?></td>
            <td>$ <?= number_format($o['valor_estimado'], 0, ',', '.') ?></td>
            <td><?= $o['nombre_usuario'] ?></td>
            <td>
                <!--
                    onclick="abrirModal()" = llama la función JS
                    pasamos los datos de la oferta como parámetros
                    para rellenar el formulario de edición
                -->
                <button class="btn-editar" onclick="abrirModal(
                    <?= $o['id_oferta'] ?>,
                    '<?= addslashes($o['titulo']) ?>',
                    '<?= $o['estado'] ?>',
                    '<?= $o['ciudad'] ?>',
                    <?= $o['valor_estimado'] ?>
                )">✏️ Editar</button>

                <!--
                    Al hacer clic en eliminar va a la misma página
                    con ?eliminar=ID en la URL
                    confirm() muestra un diálogo de confirmación
                -->
                <a class="btn-eliminar"
                   href="?eliminar=<?= $o['id_oferta'] ?>"
                   onclick="return confirm('¿Seguro que quieres eliminar esta oferta?')">
                   🗑️ Eliminar
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Modal de edición (oculto por defecto) -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <h2>EDITAR OFERTA</h2>

        <!--
            method="POST" = envía los datos al servidor
            El campo oculto id_oferta le dice a PHP qué registro actualizar
        -->
        <form method="POST" action="">

            <!-- Campo oculto: el usuario no lo ve pero PHP lo necesita -->
            <input type="hidden" name="id_oferta" id="modal_id">
            <!-- Campo oculto que indica que es un UPDATE -->
            <input type="hidden" name="actualizar" value="1">

            <div class="form-group">
                <label>Título</label>
                <input type="text" name="titulo" id="modal_titulo" required>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" id="modal_estado">
                    <option value="activa">Activa</option>
                    <option value="inactiva">Inactiva</option>
                    <option value="intercambiada">Intercambiada</option>
                </select>
            </div>

            <div class="form-group">
                <label>Ciudad</label>
                <select name="ciudad" id="modal_ciudad">
                    <option value="Bogotá">Bogotá</option>
                    <option value="Medellín">Medellín</option>
                    <option value="Cali">Cali</option>
                    <option value="Barranquilla">Barranquilla</option>
                    <option value="Cartagena">Cartagena</option>
                    <option value="Otra">Otra</option>
                </select>
            </div>

            <div class="form-group">
                <label>Valor estimado COP</label>
                <input type="number" name="valor_estimado" id="modal_valor" min="0">
            </div>

            <div class="modal-btns">
                <button type="submit" class="btn-guardar">💾 Guardar cambios</button>
                <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
/*
 * abrirModal() recibe los datos de la oferta
 * y los pone en el formulario del modal
 * Así el usuario ve los valores actuales al editar
 */
function abrirModal(id, titulo, estado, ciudad, valor) {
    document.getElementById('modal_id').value     = id;
    document.getElementById('modal_titulo').value = titulo;
    document.getElementById('modal_estado').value = estado;
    document.getElementById('modal_ciudad').value = ciudad;
    document.getElementById('modal_valor').value  = valor;
    // Mostramos el modal añadiendo la clase 'activo'
    document.getElementById('modalOverlay').classList.add('activo');
}

// cerrarModal() oculta el modal quitando la clase 'activo'
function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('activo');
}
</script>

<?php mysqli_close($conexion); ?>
</body>
</html>