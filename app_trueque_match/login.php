<?php
/*
 * =============================================
 * TRUEQUE MATCH — login.php
 * Verifica correo y contraseña contra la BD
 * Si es correcto crea la sesión del usuario
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

/*
 * session_start() SIEMPRE va al principio del archivo
 * Activa el sistema de sesiones de PHP
 * Una sesión es como una caja temporal donde guardamos
 * datos del usuario mientras navega por la app
 */
session_start();

include('../conexion.php');

$mensaje  = "";
$tipo_msg = "";

/*
 * Si el usuario ya tiene sesión activa, lo mandamos
 * directo al dashboard — no tiene sentido volver a loguearse
 */
if (isset($_SESSION['usuario_id'])) {
   header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $correo = trim($_POST['correo']);
    $pass   = $_POST['contrasena'];

    // Validación básica antes de consultar la BD
    if (empty($correo) || empty($pass)) {
        $mensaje  = "❌ Completa todos los campos.";
        $tipo_msg = "error";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje  = "❌ El correo no tiene un formato válido.";
        $tipo_msg = "error";

    } else {

        $correo = mysqli_real_escape_string($conexion, $correo);

        /*
         * Buscamos el usuario por correo en la BD
         * Solo traemos los campos que necesitamos
         */
        $sql = "SELECT id_usuario, nombre, correo, clave_acceso, ciudad, reputacion
                FROM USUARIO
                WHERE correo = '$correo' AND activo = 1";

        $resultado = mysqli_query($conexion, $sql);

        if (mysqli_num_rows($resultado) === 1) {

            $usuario = mysqli_fetch_assoc($resultado);

            /*
             * password_verify() compara la contraseña escrita
             * con el hash guardado en la BD
             * Devuelve true si coinciden, false si no
             * NUNCA comparamos contraseñas en texto plano
             */
            if (password_verify($pass, $usuario['clave_acceso'])) {

                /*
                 * $_SESSION[] es el array donde guardamos los datos
                 * del usuario autenticado
                 * Estos datos persisten mientras el navegador esté abierto
                 */
                $_SESSION['usuario_id']   = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_correo'] = $usuario['correo'];
                $_SESSION['usuario_ciudad'] = $usuario['ciudad'];
                $_SESSION['usuario_rep']    = $usuario['reputacion'];

                /*
                 * header('Location:') redirige al usuario a otra página
                 * exit() detiene el script después de redirigir
                 */
                header('Location: dashboard.php');
                exit();

            } else {
                // Mensaje genérico — no revelamos si falló el correo o la contraseña
                // Por seguridad, un atacante no debe saber cuál de los dos está mal
                $mensaje  = "❌ Correo o contraseña incorrectos.";
                $tipo_msg = "error";
            }

        } else {
            // Mismo mensaje genérico para ambos casos de error
            // Si dijéramos "correo no existe" un atacante sabría que puede seguir probando contraseñas
                $mensaje  = "❌ Correo o contraseña incorrectos.";
                $tipo_msg = "error";
        }
    }
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trueque Match — Iniciar Sesión</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { display:flex; min-height:100vh; }
        .auth-left {
            flex:1; background:var(--gris-card);
            border-right:1px solid var(--gris-borde);
            display:flex; flex-direction:column;
            justify-content:center; align-items:center;
            padding:60px 40px; position:relative; overflow:hidden;
        }
        .auth-left::before {
            content:''; position:absolute; inset:0;
            background:radial-gradient(ellipse 70% 60% at 30% 50%, rgba(192,57,43,0.10), transparent);
        }
        .auth-left > * { position:relative; z-index:1; }
        .auth-right {
            width:480px; flex-shrink:0;
            display:flex; flex-direction:column;
            justify-content:center; padding:60px 48px;
        }
        @media(max-width:768px){
            .auth-left{display:none;}
            .auth-right{width:100%; padding:40px 24px;}
        }
        .msg-error {
            background:rgba(192,57,43,.12);
            border:1px solid var(--rojo-border);
            border-radius:var(--radius-md);
            padding:10px 14px; font-size:13px;
            color:var(--rojo-tm); margin-bottom:16px;
        }
        .msg-exito {
            background:rgba(39,174,96,.12);
            border:1px solid rgba(39,174,96,.35);
            border-radius:var(--radius-md);
            padding:10px 14px; font-size:13px;
            color:#27AE60; margin-bottom:16px;
        }
    </style>
</head>
<body>

<!-- Panel izquierdo -->
<div class="auth-left">
    <div style="font-size:64px;">🤝</div>
    <h1 style="font-family:var(--font-display); font-size:clamp(52px,6vw,86px); line-height:0.9; letter-spacing:4px; color:var(--blanco-calido); margin:24px 0 16px;">
        TRUECA<br><span style="color:var(--rojo-tm)">SIN</span><br>DINERO
    </h1>
    <p style="color:var(--gris-medio); font-size:16px; max-width:340px; line-height:1.7;">
        Más de 2.400 colombianos ya están intercambiando productos, servicios y conocimiento.
    </p>
    <div class="flex gap-md mt-lg" style="flex-wrap:wrap;">
        <div class="badge badge-red"><i class="fa fa-shield-alt"></i> &nbsp;Seguro</div>
        <div class="badge badge-green"><i class="fa fa-users"></i> &nbsp;Comunidad</div>
        <div class="badge badge-blue"><i class="fa fa-star"></i> &nbsp;Calificado</div>
    </div>
</div>

<!-- Panel derecho -->
<div class="auth-right">

    <a href="index.html" style="display:inline-block; margin-bottom:36px;">
        <img src="LOGO_FINAL.png" alt="Trueque Match" style="height:52px; width:auto;">
    </a>

    <div style="font-family:var(--font-display); font-size:32px; color:var(--blanco-calido); margin-bottom:6px;">
        BIENVENIDO
    </div>
    <p style="color:var(--gris-medio); font-size:14px; margin-bottom:28px;">
        Ingresa a tu cuenta para continuar
    </p>

    <?php if (!empty($mensaje)): ?>
        <div class="msg-error">
            <i class="fa fa-exclamation-circle"></i> <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <!--
        action="login.php" = envía al mismo archivo
        method="POST" = correo y contraseña van ocultos
        Los name="" deben coincidir con $_POST[] en PHP
    -->
    <form action="login.php" method="POST"
          style="display:flex; flex-direction:column; gap:20px;">

        <div class="form-group">
            <label class="form-label">Correo electrónico</label>
            <input type="email"
                   name="correo"
                   class="form-control"
                   placeholder="tu@correo.com"
                   required>
        </div>

        <div class="form-group">
            <label class="form-label">Contraseña</label>
            <input type="password"
                   name="contrasena"
                   class="form-control"
                   placeholder="••••••••"
                   required>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg">
            INICIAR SESIÓN
        </button>
    </form>

    <div class="divider-text mt-lg mb-lg">o</div>

    <a href="registro.php" class="btn btn-secondary btn-full">
        ¿No tienes cuenta? <strong>&nbsp;Regístrate gratis</strong>
    </a>

    <p class="text-center text-muted mt-md" style="font-size:12px;">
        Al ingresar aceptas nuestros
        <a href="#">Términos</a> y
        <a href="#">Política de datos</a> (Ley 1581/2012)
    </p>
</div>

</body>
</html>