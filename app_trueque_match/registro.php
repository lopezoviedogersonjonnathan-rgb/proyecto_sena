<?php
/*
 * =============================================
 * TRUEQUE MATCH — registro.php
 * Procesa el formulario de registro y guarda
 * el nuevo usuario en la tabla USUARIO
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// Incluimos la conexión — ruta relativa desde app_trueque_match
// Los dos puntos (..) significan "subir una carpeta"
include('../conexion.php');

$mensaje  = "";
$tipo_msg = "";

/*
 * Solo procesamos si el formulario fue enviado por POST
 * Si alguien entra directo a esta URL sin datos, no hacemos nada
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recogemos cada campo del formulario de registro.html
    // El name="" de cada input debe coincidir exactamente
    $nombre   = trim($_POST['nombre']);    // trim() quita espacios al inicio y al final
    $apellido = trim($_POST['apellido']);
    $correo   = trim($_POST['correo']);
    $ciudad   = $_POST['ciudad'];
    $pass1    = $_POST['pass1'];
    $pass2    = $_POST['pass2'];
    $terminos = isset($_POST['terminos']); // checkbox: true si está marcado

    // Nombre completo uniendo nombre y apellido
    $nombre_completo = $nombre . ' ' . $apellido;

    // ---- VALIDACIONES ----
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($pass1)) {
        $mensaje  = "❌ Completa todos los campos obligatorios.";
        $tipo_msg = "error";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        // filter_var con FILTER_VALIDATE_EMAIL verifica formato de correo
        $mensaje  = "❌ El correo electrónico no es válido.";
        $tipo_msg = "error";

    } elseif (strlen($pass1) < 8) {
        // strlen() cuenta los caracteres de la contraseña
        $mensaje  = "❌ La contraseña debe tener mínimo 8 caracteres.";
        $tipo_msg = "error";

    } elseif ($pass1 !== $pass2) {
        $mensaje  = "❌ Las contraseñas no coinciden.";
        $tipo_msg = "error";

    } elseif (!$terminos) {
        $mensaje  = "❌ Debes aceptar los términos y condiciones.";
        $tipo_msg = "error";

    } else {

        // Limpiamos los datos para evitar inyección SQL
        $nombre_completo = mysqli_real_escape_string($conexion, $nombre_completo);
        $correo          = mysqli_real_escape_string($conexion, $correo);
        $ciudad          = mysqli_real_escape_string($conexion, $ciudad);

        /*
         * password_hash() cifra la contraseña con bcrypt
         * NUNCA guardamos contraseñas en texto plano
         * PASSWORD_DEFAULT usa el algoritmo más seguro disponible
         */
        $clave_cifrada = password_hash($pass1, PASSWORD_DEFAULT);

        /*
         * Verificamos si el correo ya existe en la BD
         * para no crear usuarios duplicados
         */
        $check = mysqli_query($conexion,
            "SELECT id_usuario FROM usuario WHERE correo = '$correo'"
        );

        if (mysqli_num_rows($check) > 0) {
            $mensaje  = "❌ Ya existe una cuenta con ese correo electrónico.";
            $tipo_msg = "error";

        } else {
            /*
             * INSERT INTO usuario
             * id_tipo_usuario = 1 significa usuario estándar
             * La reputación empieza en 0.00 por defecto
             */
            $sql = "INSERT INTO usuario
                        (nombre, correo, clave_acceso, ciudad, id_tipo_usuario)
                    VALUES
                        ('$nombre_completo', '$correo', '$clave_cifrada', '$ciudad', 1)";

            if (mysqli_query($conexion, $sql)) {
                $nuevo_id = mysqli_insert_id($conexion);
                $mensaje  = "🎉 ¡Cuenta creada exitosamente! Bienvenido $nombre. Tu ID es #$nuevo_id";
                $tipo_msg = "exito";
            } else {
                $mensaje  = "❌ Error al crear la cuenta: " . mysqli_error($conexion);
                $tipo_msg = "error";
            }
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
    <title>Trueque Match — Registro</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { display:flex; min-height:100vh; }
        .auth-left { flex:1; background:var(--gris-card); border-right:1px solid var(--gris-borde); display:flex; flex-direction:column; justify-content:center; align-items:center; padding:60px 40px; position:relative; overflow:hidden; }
        .auth-left::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse 70% 60% at 70% 50%, rgba(192,57,43,0.10), transparent); }
        .auth-left > * { position:relative; z-index:1; }
        .auth-right { width:520px; flex-shrink:0; display:flex; flex-direction:column; justify-content:center; padding:48px; overflow-y:auto; }
        @media(max-width:768px){ .auth-left{display:none;} .auth-right{width:100%; padding:32px 24px;} }
        .benefits { display:flex; flex-direction:column; gap:16px; margin-top:24px; }
        .benefit { display:flex; align-items:center; gap:12px; }
        .benefit-icon { width:40px; height:40px; border-radius:var(--radius-md); background:var(--rojo-light); border:1px solid var(--rojo-border); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .benefit-text strong { display:block; font-size:14px; color:var(--blanco-calido); }
        .benefit-text span { font-size:12px; color:var(--gris-medio); }
        .msg-exito { background:rgba(39,174,96,.12); border:1px solid rgba(39,174,96,.35); border-radius:var(--radius-md); padding:16px; text-align:center; color:#27AE60; margin-bottom:16px; }
        .msg-error { background:rgba(192,57,43,.12); border:1px solid var(--rojo-border); border-radius:var(--radius-md); padding:12px 14px; font-size:13px; color:var(--rojo-tm); margin-bottom:16px; }
        .password-req { font-size:12px; color:var(--gris-medio); margin-top:4px; }
        .progress-bar { height:4px; border-radius:2px; background:var(--gris-borde); overflow:hidden; margin-top:6px; }
        .progress-fill { height:100%; border-radius:2px; transition:all .3s; background:var(--rojo-tm); width:0%; }
    </style>
</head>
<body>

<!-- Panel izquierdo decorativo -->
<div class="auth-left">
    <div style="font-size:56px;">🌱</div>
    <div style="font-family:var(--font-display); font-size:56px; color:var(--blanco-calido); line-height:.9; letter-spacing:3px; margin:20px 0 16px;">
        CREA TU<br><span style="color:var(--rojo-tm)">CUENTA</span><br>GRATIS
    </div>
    <p style="color:var(--gris-medio); max-width:300px; font-size:14px; line-height:1.7;">
        Registrarte es gratis y solo toma un minuto.
    </p>
    <div class="benefits">
        <div class="benefit">
            <div class="benefit-icon">📦</div>
            <div class="benefit-text">
                <strong>Publica sin límites</strong>
                <span>Sube todas las ofertas que quieras</span>
            </div>
        </div>
        <div class="benefit">
            <div class="benefit-icon">🔒</div>
            <div class="benefit-text">
                <strong>100% seguro</strong>
                <span>Protegemos tus datos (Ley 1581/2012)</span>
            </div>
        </div>
        <div class="benefit">
            <div class="benefit-icon">⭐</div>
            <div class="benefit-text">
                <strong>Sistema de reputación</strong>
                <span>Construye tu confianza en la comunidad</span>
            </div>
        </div>
    </div>
</div>

<!-- Panel derecho con formulario -->
<div class="auth-right">

    <a href="index.html" class="navbar-brand" style="font-size:22px; display:inline-block; margin-bottom:28px;">
        <img src="LOGO_FINAL.png" alt="Trueque Match" style="height:52px; width:auto;">
    </a>

    <div style="font-family:var(--font-display); font-size:28px; color:var(--blanco-calido); margin-bottom:4px;">
        CREAR CUENTA
    </div>
    <p style="color:var(--gris-medio); font-size:14px; margin-bottom:24px;">
        Únete a la comunidad Trueque Match
    </p>

    <?php if (!empty($mensaje)): ?>
        <?php if ($tipo_msg === 'exito'): ?>
            <div class="msg-exito">
                <div style="font-size:32px; margin-bottom:8px;">🎉</div>
                <strong style="display:block; font-size:16px;"><?= $mensaje ?></strong>
                <p style="font-size:13px; margin-top:8px;">
                    <a href="login.html" style="color:#27AE60;">← Ir a iniciar sesión</a>
                </p>
            </div>
        <?php else: ?>
            <div class="msg-error">
                <i class="fa fa-exclamation-circle"></i> <?= $mensaje ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!--
        action="registro.php" = envía los datos a este mismo archivo
        method="POST" = datos ocultos y seguros
    -->
    <form action="registro.php" method="POST"
          style="display:flex; flex-direction:column; gap:16px;">

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre"
                       class="form-control" placeholder="Tu nombre" required>
            </div>
            <div class="form-group">
                <label class="form-label">Apellido *</label>
                <input type="text" name="apellido"
                       class="form-control" placeholder="Tu apellido" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Correo electrónico *</label>
            <input type="email" name="correo"
                   class="form-control" placeholder="tu@correo.com" required>
        </div>

        <div class="form-group">
            <label class="form-label">Ciudad</label>
            <select name="ciudad" class="form-control" style="cursor:pointer;">
                <option value="Bogotá">Bogotá</option>
                <option value="Medellín">Medellín</option>
                <option value="Cali">Cali</option>
                <option value="Barranquilla">Barranquilla</option>
                <option value="Cartagena">Cartagena</option>
                <option value="Otra">Otra</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Contraseña *</label>
            <input type="password" name="pass1" id="pass1"
                   class="form-control" placeholder="Mínimo 8 caracteres"
                   required oninput="checkPass()">
            <div class="progress-bar">
                <div class="progress-fill" id="passStrength"></div>
            </div>
            <div class="password-req" id="passReq">Escribe tu contraseña</div>
        </div>

        <div class="form-group">
            <label class="form-label">Confirmar contraseña *</label>
            <input type="password" name="pass2"
                   class="form-control" placeholder="Repite tu contraseña" required>
        </div>

        <div style="display:flex; gap:8px; align-items:flex-start; font-size:12px; color:var(--gris-medio);">
            <input type="checkbox" name="terminos" id="terminos"
                   style="margin-top:3px; accent-color:var(--rojo-tm);">
            <label for="terminos">
                Acepto los <a href="#">Términos de uso</a> y la
                <a href="#">Política de datos</a> conforme a la Ley 1581 de 2012.
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg">
            CREAR MI CUENTA
        </button>
    </form>

    <div class="divider-text mt-md mb-md">o</div>
    <a href="login.html" class="btn btn-secondary btn-full">
        Ya tengo cuenta — <strong>&nbsp;Ingresar</strong>
    </a>
</div>

<script>
function checkPass() {
    const v = document.getElementById('pass1').value;
    const bar = document.getElementById('passStrength');
    const req = document.getElementById('passReq');
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const colors = ['#C0392B','#F39C12','#F39C12','#27AE60','#27AE60'];
    const labels = ['','Débil','Regular','Buena','Fuerte'];
    bar.style.width = (score * 25) + '%';
    bar.style.background = colors[score];
    req.textContent = score === 0 ? 'Mínimo 8 caracteres' : 'Seguridad: ' + labels[score];
}
</script>

</body>
</html>