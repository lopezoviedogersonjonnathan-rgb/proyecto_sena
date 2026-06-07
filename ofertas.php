<?php
/*
 * =============================================
 * TRUEQUE MATCH — ofertas.php
 * Página que lee y muestra las ofertas de la BD
 * Demuestra el READ del CRUD
 * Gerson Jonnathan López Oviedo | Ficha 3186647x
 * =============================================
 */

// Incluimos el archivo de conexión que ya funciona
// include() inserta el contenido de otro archivo PHP aquí
include('conexion.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trueque Match — Ofertas</title>
    <style>
        /* Estilos del sistema de diseño Trueque Match */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #111111;
            color: #F5F0EB;
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
        }
        h1 {
            color: #C0392B;
            font-size: 32px;
            letter-spacing: 3px;
            margin-bottom: 8px;
        }
        .subtitulo {
            color: #888;
            margin-bottom: 30px;
            font-size: 14px;
        }
        /* Tabla para mostrar los datos de la BD */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1A1A1A;
            border-radius: 12px;
            overflow: hidden;
        }
        thead {
            background: #C0392B;
            color: white;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            font-size: 14px;
            border-bottom: 1px solid #2A2A2A;
        }
        tr:hover { background: #222; }
        /* Badges de categoría */
        .badge {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }
        .producto    { background: rgba(192,57,43,.2);  color: #C0392B; }
        .servicio    { background: rgba(41,128,185,.2); color: #2980B9; }
        .conocimiento{ background: rgba(39,174,96,.2);  color: #27AE60; }
        .experiencia { background: rgba(243,156,18,.2); color: #F39C12; }
        /* Estado activa/inactiva */
        .activa       { color: #27AE60; font-weight: bold; }
        .inactiva     { color: #888; }
        .intercambiada{ color: #F39C12; }
        .total {
            margin-top: 16px;
            color: #888;
            font-size: 13px;
        }
        .total span { color: #C0392B; font-weight: bold; }
    </style>
</head>
<body>

    <h1>TRUEQUE MATCH</h1>
    <p class="subtitulo">📦 Ofertas registradas en la base de datos</p>

    <?php
    /*
     * mysqli_query() ejecuta una consulta SQL en la BD
     * SELECT = leer datos (el READ del CRUD)
     * JOIN = unir dos tablas para traer el nombre del usuario
     * junto con cada oferta
     */
    $sql = "SELECT 
                o.id_oferta,
                o.titulo,
                o.categoria,
                o.estado,
                o.ciudad,
                o.valor_estimado,
                u.nombre AS nombre_usuario
            FROM OFERTA o
            JOIN USUARIO u ON o.id_usuario = u.id_usuario
            ORDER BY o.id_oferta ASC";

    // Ejecutamos la consulta y guardamos el resultado
    $resultado = mysqli_query($conexion, $sql);

    // mysqli_num_rows() cuenta cuántas filas devolvió la consulta
    $total = mysqli_num_rows($resultado);
    ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Título</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Ciudad</th>
                <th>Valor estimado</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            <?php
            /*
             * mysqli_fetch_assoc() lee fila por fila el resultado
             * Cada vez que se llama avanza a la siguiente fila
             * El while() repite hasta que no haya más filas
             */
            while ($fila = mysqli_fetch_assoc($resultado)) {
                // Cada $fila es un array con los datos de una oferta
                echo "<tr>";
                echo "<td>" . $fila['id_oferta'] . "</td>";
                echo "<td>" . $fila['titulo'] . "</td>";

                // Badge de categoría con clase CSS según el tipo
                $cat = $fila['categoria'];
                echo "<td><span class='badge $cat'>" . ucfirst($cat) . "</span></td>";

                // Estado con color según valor
                $est = $fila['estado'];
                echo "<td><span class='$est'>" . ucfirst($est) . "</span></td>";

                echo "<td>" . $fila['ciudad'] . "</td>";

                // number_format() formatea el número con separador de miles
                echo "<td>$ " . number_format($fila['valor_estimado'], 0, ',', '.') . "</td>";

                echo "<td>" . $fila['nombre_usuario'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <p class="total">Total de ofertas: <span><?php echo $total; ?></span></p>

    <?php
    // Cerramos la conexión cuando ya no la necesitamos
    // Libera la memoria del servidor
    mysqli_close($conexion);
    ?>

</body>
</html>