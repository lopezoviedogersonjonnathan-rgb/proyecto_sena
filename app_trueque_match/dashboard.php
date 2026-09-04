<?php
/*
 * =============================================
 * TRUEQUE MATCH — dashboard.php
 * Muestra datos reales del usuario logueado
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// session_start() activa las sesiones
// Es como abrir la caja donde guardamos
// los datos del usuario que inició sesión
session_start();

// Si no hay sesión activa significa que
// el usuario no ha iniciado sesión
// Lo mandamos al login de inmediato
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Incluimos la conexión a la BD
// Los .. significan "subir una carpeta"
// porque conexion.php está en proyecto_sena/
include('../conexion.php');

// Guardamos el ID del usuario en una variable
// para usarlo en todas las consultas
$usuario_id     = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

// =============================================
// CONSULTA 1: Contar mis ofertas
// COUNT(*) cuenta cuántas filas devuelve
// WHERE filtra solo las ofertas de este usuario
// =============================================
$r_ofertas = mysqli_query($conexion,
    "SELECT COUNT(*) AS total FROM oferta
     WHERE id_usuario = $usuario_id"
);
$total_ofertas = mysqli_fetch_assoc($r_ofertas)['total'];

// =============================================
// CONSULTA 2: Contar mis trueques
// Buscamos trueques donde el usuario propone
// O donde el usuario recibe (por eso el OR)
// =============================================
$r_trueques = mysqli_query($conexion,
    "SELECT COUNT(*) AS total FROM trueque
     WHERE id_usuario_propone = $usuario_id
     OR id_usuario_recibe = $usuario_id"
);
$total_trueques = mysqli_fetch_assoc($r_trueques)['total'];

// =============================================
// CONSULTA 3: Mi reputación promedio
// COALESCE devuelve 0 si no hay evaluaciones
// AVG calcula el promedio de los puntajes
// =============================================
$r_rep = mysqli_query($conexion,
    "SELECT COALESCE(AVG(e.puntaje), 0) AS promedio
     FROM evaluacion e
     JOIN trueque t ON e.id_trueque = t.id_trueque
     WHERE t.id_usuario_propone = $usuario_id
     OR t.id_usuario_recibe = $usuario_id"
);
$reputacion = number_format(mysqli_fetch_assoc($r_rep)['promedio'], 1);

// =============================================
// CONSULTA 4: Mis favoritos guardados
// =============================================
$r_favs = mysqli_query($conexion,
    "SELECT COUNT(*) AS total FROM usuario_oferta
     WHERE id_usuario = $usuario_id"
);
$total_favs = mysqli_fetch_assoc($r_favs)['total'];

// =============================================
// CONSULTA 5: Mis trueques pendientes
// para mostrarlos en la tabla del dashboard
// =============================================
$r_pendientes = mysqli_query($conexion,
    "SELECT t.*,
            o1.titulo AS oferta_propone,
            o2.titulo AS oferta_recibe,
            u.nombre  AS nombre_otro_usuario
     FROM trueque t
     JOIN oferta o1 ON t.id_oferta_propone = o1.id_oferta
     JOIN oferta o2 ON t.id_oferta_recibe  = o2.id_oferta
     JOIN usuario u ON (
         CASE
             WHEN t.id_usuario_propone = $usuario_id
             THEN t.id_usuario_recibe
             ELSE t.id_usuario_propone
         END = u.id_usuario
     )
     WHERE t.id_usuario_propone = $usuario_id
     OR t.id_usuario_recibe = $usuario_id
     ORDER BY t.fecha_propuesta DESC
     LIMIT 5"
);

// =============================================
// CONSULTA 6: Notificaciones no leídas
// =============================================
$r_notifs = mysqli_query($conexion,
    "SELECT COUNT(*) AS total FROM notificacion
     WHERE id_usuario = $usuario_id AND leida = 0"
);
$notifs_sin_leer = mysqli_fetch_assoc($r_notifs)['total'];

mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trueque Match — Dashboard</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.app-layout { display:flex; min-height:100vh; }
.sidebar-nav {
  width:240px; flex-shrink:0;
  background:var(--gris-card);
  border-right:1px solid var(--gris-borde);
  display:flex; flex-direction:column;
  position:sticky; top:0; height:100vh;
  overflow-y:auto;
}
.sidebar-logo {
  padding:24px 20px 16px;
  font-family:var(--font-display);
  font-size:22px; color:var(--rojo-tm);
  letter-spacing:2px;
  border-bottom:1px solid var(--gris-borde);
}
.sidebar-logo span { color:var(--blanco-calido); }
.sidebar-user {
  padding:16px 20px;
  display:flex; align-items:center; gap:12px;
  border-bottom:1px solid var(--gris-borde);
}
.sidebar-menu { flex:1; padding:12px 10px; }
.menu-label { font-size:11px; font-weight:700; color:var(--gris-medio); text-transform:uppercase; letter-spacing:1px; padding:8px 12px 4px; }
.menu-item {
  display:flex; align-items:center; gap:12px;
  padding:10px 12px; border-radius:var(--radius-md);
  color:var(--gris-medio); font-size:14px; font-weight:600;
  cursor:pointer; transition:var(--transition); margin-bottom:2px;
}
.menu-item:hover { background:var(--gris-borde); color:var(--blanco-calido); }
.menu-item.active { background:var(--rojo-light); color:var(--rojo-tm); border:1px solid var(--rojo-border); }
.menu-item i { width:18px; text-align:center; }
.badge-count { margin-left:auto; background:var(--rojo-tm); color:#fff; border-radius:var(--radius-full); padding:1px 7px; font-size:11px; font-weight:800; }
.sidebar-bottom { padding:16px; border-top:1px solid var(--gris-borde); }

/* MAIN */
.main-content { flex:1; overflow-x:hidden; }
.page-header {
  background:var(--gris-card);
  border-bottom:1px solid var(--gris-borde);
  padding:20px 32px;
  display:flex; align-items:center; justify-content:space-between;
  gap:16px; flex-wrap:wrap;
}
.page-title { font-family:var(--font-display); font-size:22px; color:var(--blanco-calido); letter-spacing:1px; }
.page-body { padding:32px; }

/* STATS ROW */
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:32px; }
.stat-card {
  background:var(--gris-card); border:1px solid var(--gris-borde);
  border-radius:var(--radius-lg); padding:20px;
  transition:var(--transition);
}
.stat-card:hover { border-color:var(--rojo-border); transform:translateY(-2px); }
.stat-card-icon { font-size:28px; margin-bottom:10px; }
.stat-card-num { font-family:var(--font-display); font-size:36px; color:var(--rojo-tm); line-height:1; }
.stat-card-label { font-size:12px; color:var(--gris-medio); margin-top:4px; }
.stat-card-delta { font-size:12px; color:var(--verde); margin-top:6px; }

/* OFERTA CARD */
.oferta-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.oc {
  background:var(--gris-card); border:1px solid var(--gris-borde);
  border-radius:var(--radius-lg); overflow:hidden; transition:var(--transition);
}
.oc:hover { border-color:var(--rojo-border); transform:translateY(-3px); box-shadow:var(--shadow-glow); }
.oc-img { height:120px; background:var(--gris-borde); display:flex; align-items:center; justify-content:center; font-size:44px; position:relative; }
.oc-body { padding:14px; }
.oc-title { font-weight:700; font-size:14px; margin-bottom:6px; }
.oc-meta { font-size:12px; color:var(--gris-medio); display:flex; align-items:center; gap:8px; }
.oc-footer { padding:12px 14px; border-top:1px solid var(--gris-borde); display:flex; gap:8px; }

/* TRUEQUE ITEM */
.trueque-item {
  background:var(--gris-card); border:1px solid var(--gris-borde);
  border-radius:var(--radius-lg); padding:16px;
  display:flex; align-items:center; gap:14px; margin-bottom:10px;
  transition:var(--transition);
}
.trueque-item:hover { border-color:var(--rojo-border); }
.trueque-exchange { flex:1; display:flex; align-items:center; gap:10px; }
.t-item { font-size:13px; font-weight:600; flex:1; }
.t-arrow { color:var(--rojo-tm); font-size:18px; font-weight:900; }
.trueque-info { text-align:right; }
.trueque-user { font-size:12px; color:var(--gris-medio); margin-top:4px; }

/* CHAT */
.chat-list-item {
  display:flex; align-items:center; gap:12px;
  padding:14px 16px; border-radius:var(--radius-lg);
  cursor:pointer; transition:var(--transition); margin-bottom:6px;
  border:1px solid transparent;
}
.chat-list-item:hover { background:var(--gris-card); border-color:var(--gris-borde); }
.chat-preview { font-size:12px; color:var(--gris-medio); }
.chat-hora { font-size:11px; color:var(--gris-medio); margin-left:auto; flex-shrink:0; }
.chat-window {
  background:var(--gris-card); border:1px solid var(--gris-borde);
  border-radius:var(--radius-lg); height:420px;
  display:flex; flex-direction:column; overflow:hidden;
}
.chat-window-header { padding:14px 18px; border-bottom:1px solid var(--gris-borde); display:flex; align-items:center; gap:10px; }
.chat-messages { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:10px; }
.msg { max-width:70%; padding:10px 14px; border-radius:var(--radius-md); font-size:13px; line-height:1.5; }
.msg-in { background:var(--gris-borde); color:var(--blanco-calido); align-self:flex-start; border-bottom-left-radius:4px; }
.msg-out { background:var(--rojo-tm); color:#fff; align-self:flex-end; border-bottom-right-radius:4px; }
.chat-input-bar { padding:12px 14px; border-top:1px solid var(--gris-borde); display:flex; gap:8px; }
.chat-input-bar input { flex:1; background:var(--gris-borde); border:none; border-radius:var(--radius-full); padding:10px 16px; color:var(--blanco-calido); font-size:13px; }

/* PERFIL */
.profile-header-box {
  background:var(--gris-card); border:1px solid var(--gris-borde);
  border-radius:var(--radius-lg); padding:32px;
  text-align:center; margin-bottom:24px;
}
.profile-stats { display:flex; justify-content:center; gap:32px; margin-top:20px; }
.profile-stat-num { font-family:var(--font-display); font-size:28px; color:var(--rojo-tm); }
.profile-stat-label { font-size:12px; color:var(--gris-medio); }
.menu-item-profile {
  background:var(--gris-card); border:1px solid var(--gris-borde);
  border-radius:var(--radius-md); padding:14px 18px;
  display:flex; align-items:center; gap:12px;
  cursor:pointer; transition:var(--transition); margin-bottom:8px;
}
.menu-item-profile:hover { border-color:var(--rojo-border); }
.menu-item-profile span { font-size:14px; flex:1; }
.menu-item-profile i.arrow { color:var(--gris-medio); }

/* TABS */
.tabs-bar { display:flex; gap:4px; border-bottom:1px solid var(--gris-borde); margin-bottom:24px; }
.tab-btn {
  padding:10px 18px; font-size:13px; font-weight:700;
  color:var(--gris-medio); background:transparent; border:none;
  border-bottom:2px solid transparent; cursor:pointer; transition:var(--transition);
  margin-bottom:-1px;
}
.tab-btn:hover { color:var(--blanco-calido); }
.tab-btn.active { color:var(--rojo-tm); border-bottom-color:var(--rojo-tm); }

/* MODAL NUEVA OFERTA */
.form-cols { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.cat-selector { display:grid; grid-template-columns:repeat(2,1fr); gap:8px; }
.cat-option {
  background:var(--gris-input); border:1.5px solid var(--gris-borde);
  border-radius:var(--radius-md); padding:12px;
  text-align:center; cursor:pointer; transition:var(--transition); font-size:13px;
}
.cat-option.selected { border-color:var(--rojo-tm); background:var(--rojo-light); color:var(--rojo-tm); }
.cat-option:hover { border-color:var(--gris-medio); }

@media(max-width:768px){
  .sidebar-nav { display:none; }
  .stats-row { grid-template-columns:repeat(2,1fr); }
  .page-body { padding:16px; }
}
</style>
</head>
<body>
<div class="app-layout">

  <!-- SIDEBAR -->
  <nav class="sidebar-nav">
    <div class="sidebar-logo"><img src="LOGO_FINAL.png" alt="Trueque Match" style="height:52px; width:auto;"></div>
    <div class="sidebar-user">
      <div class="avatar avatar-md" id="sidebarAvatar">G</div>
      <div>
        <div style="font-size:14px; font-weight:700;" id="sidebarName"><?php echo $usuario_nombre; ?></div>
        <div style="font-size:12px; color:var(--verde);">● En línea</div>
      </div>
    </div>
    <div class="sidebar-menu">
      <div class="menu-label">Principal</div>
      <div class="menu-item active" onclick="showSection('home')"><i class="fa fa-home"></i> Inicio</div>
      <div class="menu-item" onclick="showSection('ofertas')"><i class="fa fa-tags"></i> Explorar Ofertas</div>
      <div class="menu-item" onclick="showSection('mis-ofertas')"><i class="fa fa-box"></i> Mis Ofertas</div>
      <div class="menu-label">Intercambios</div>
      <div class="menu-item" onclick="showSection('trueques')"><i class="fa fa-exchange-alt"></i> Mis Trueques <span class="badge-count">2</span></div>
      <div class="menu-item" onclick="showSection('chat')"><i class="fa fa-comments"></i> Chat <span class="badge-count">3</span></div>
      <div class="menu-label">Cuenta</div>
      <div class="menu-item" onclick="showSection('perfil')"><i class="fa fa-user"></i> Mi Perfil</div>
      <div class="menu-item" onclick="showSection('notificaciones')"><i class="fa fa-bell"></i> Notificaciones <span class="badge-count">5</span></div>
    </div>
    <div class="sidebar-bottom">
      <div class="menu-item" onclick="logout()" style="color:var(--rojo-tm);"><i class="fa fa-sign-out-alt"></i> Cerrar sesión</div>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="main-content">

    <!-- ===== SECCIÓN: HOME ===== -->
    <div id="sec-home">
      <div class="page-header">
        <div>
          <div class="page-title">BIENVENIDO, <span id="headerName" style="color:var(--rojo-tm)">GERSON</span> 👋</div>
          <div style="font-size:13px; color:var(--gris-medio);">Aquí tienes un resumen de tu actividad</div>
        </div>
        <button class="btn btn-primary" onclick="showModal('modalOferta')"><i class="fa fa-plus"></i> Nueva Oferta</button>
      </div>
      <div class="page-body">
        <div class="stats-row">
          <div class="stat-card"><div class="stat-card-icon">📦</div><div class="stat-card-num"><?php echo $total_ofertas; ?></div><div class="stat-card-label">Mis Ofertas</div><div class="stat-card-delta">↑ 2 esta semana</div></div>
          <div class="stat-card"><div class="stat-card-icon">🔄</div><div class="stat-card-num"><?php echo $total_trueques; ?></div><div class="stat-card-label">Trueques</div><div class="stat-card-delta">↑ 1 nuevo</div></div>
          <div class="stat-card"><div class="stat-card-icon">⭐</div><div class="stat-card-num"><?php echo $reputacion; ?></div><div class="stat-card-label">Reputación</div><div class="stat-card-delta">Top 10% de la ciudad</div></div>
          <div class="stat-card"><div class="stat-card-icon">❤️</div><div class="stat-card-num"><?php echo $total_favs; ?></div><div class="stat-card-label">Favoritos</div><div class="stat-card-delta">3 nuevos guardados</div></div>
        </div>

        <!-- TRUEQUES PENDIENTES -->
        <div class="flex justify-between items-center mb-md">
          <h3 style="font-family:var(--font-display); font-size:18px; letter-spacing:1px;">TRUEQUES PENDIENTES</h3>
          <button class="btn btn-ghost btn-sm" onclick="showSection('trueques')">Ver todos</button>
        </div>
        <div class="trueque-item">
          <div class="badge badge-yellow">⏳ Pendiente</div>
          <div class="trueque-exchange">
            <span class="t-item">🎸 Guitarra acústica</span>
            <span class="t-arrow">⇄</span>
            <span class="t-item">💻 Laptop i5</span>
          </div>
          <div class="trueque-info">
            <div class="badge badge-yellow">Pendiente</div>
            <div class="trueque-user">Con: María G.</div>
          </div>
          <div style="display:flex; gap:6px;">
            <button class="btn btn-primary btn-sm" onclick="showToast('✅ Trueque aceptado')">Aceptar</button>
            <button class="btn btn-secondary btn-sm" onclick="showToast('❌ Trueque rechazado')">Rechazar</button>
          </div>
        </div>
        <div class="trueque-item">
          <div class="badge badge-green">✅ Aceptado</div>
          <div class="trueque-exchange">
            <span class="t-item">📚 Cursos Python</span>
            <span class="t-arrow">⇄</span>
            <span class="t-item">🎨 Ilustraciones</span>
          </div>
          <div class="trueque-info">
            <div class="badge badge-green">Aceptado</div>
            <div class="trueque-user">Con: Pedro L.</div>
          </div>
          <button class="btn btn-ghost btn-sm" onclick="showSection('chat')">Chat</button>
        </div>

        <!-- OFERTAS RECIENTES -->
        <div class="flex justify-between items-center mt-lg mb-md">
          <h3 style="font-family:var(--font-display); font-size:18px; letter-spacing:1px;">OFERTAS RECIENTES</h3>
          <button class="btn btn-ghost btn-sm" onclick="showSection('ofertas')">Ver todas</button>
        </div>
        <div class="oferta-grid">
          <div class="oc"><div class="oc-img">📦<span class="badge badge-red" style="position:absolute;top:10px;left:10px;">Producto</span></div><div class="oc-body"><div class="oc-title">Bicicleta montañera rodado 26</div><div class="oc-meta"><div class="avatar avatar-sm">C</div>Carlos M. · Bogotá</div></div><div class="oc-footer"><button class="btn btn-primary btn-sm btn-full" onclick="showToast('🤝 Propuesta enviada a Carlos')">Proponer trueque</button></div></div>
          <div class="oc"><div class="oc-img">🎸<span class="badge badge-blue" style="position:absolute;top:10px;left:10px;">Servicio</span></div><div class="oc-body"><div class="oc-title">Clases de guitarra (10 sesiones)</div><div class="oc-meta"><div class="avatar avatar-sm" style="background:#2980b9">A</div>Ana P. · Medellín</div></div><div class="oc-footer"><button class="btn btn-primary btn-sm btn-full" onclick="showToast('🤝 Propuesta enviada a Ana')">Proponer trueque</button></div></div>
          <div class="oc"><div class="oc-img">🍳<span class="badge badge-yellow" style="position:absolute;top:10px;left:10px;">Experiencia</span></div><div class="oc-body"><div class="oc-title">Clases de cocina italiana</div><div class="oc-meta"><div class="avatar avatar-sm" style="background:#8e44ad">M</div>María G. · Bogotá</div></div><div class="oc-footer"><button class="btn btn-primary btn-sm btn-full" onclick="showToast('🤝 Propuesta enviada a María')">Proponer trueque</button></div></div>
        </div>
      </div>
    </div>

    <!-- ===== SECCIÓN: OFERTAS ===== -->
    <div id="sec-ofertas" style="display:none;">
      <div class="page-header">
        <div class="page-title">EXPLORAR OFERTAS</div>
        <button class="btn btn-primary" onclick="showModal('modalOferta')"><i class="fa fa-plus"></i> Publicar oferta</button>
      </div>
      <div class="page-body">
        <div class="search-bar mb-lg" style="max-width:100%;">
          <span class="search-icon"><i class="fa fa-search"></i></span>
          <input type="text" placeholder="Buscar por nombre, ciudad o categoría..." id="searchDash" oninput="filterDash()">
        </div>
        <div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
          <button class="btn btn-ghost btn-sm filter-cat active" onclick="setFilter(this,'')">Todas</button>
          <button class="btn btn-ghost btn-sm filter-cat" onclick="setFilter(this,'producto')">📦 Productos</button>
          <button class="btn btn-ghost btn-sm filter-cat" onclick="setFilter(this,'servicio')">🛠️ Servicios</button>
          <button class="btn btn-ghost btn-sm filter-cat" onclick="setFilter(this,'conocimiento')">📚 Conocimiento</button>
          <button class="btn btn-ghost btn-sm filter-cat" onclick="setFilter(this,'experiencia')">🎭 Experiencias</button>
        </div>
        <div class="oferta-grid" id="dashOfertaGrid"></div>
      </div>
    </div>

    <!-- ===== SECCIÓN: MIS OFERTAS ===== -->
    <!--
      Esta sección antes tenía datos falsos (hardcodeados).
      Ahora carga las ofertas REALES del usuario desde la BD
      usando fetch() que es como mandarle un mensajito a PHP
      y esperar la respuesta sin recargar la página.
    -->
    <div id="sec-mis-ofertas" style="display:none;">
      <div class="page-header">
        <div>
          <div class="page-title">MIS OFERTAS</div>
          <!--
            Este span se actualiza automáticamente
            con el total real de ofertas del usuario
          -->
          <div style="font-size:13px; color:var(--gris-medio);">
            Total: <span id="misOfertasTotal">...</span> publicadas
          </div>
        </div>
        <button class="btn btn-primary" onclick="showModal('modalOferta')">
          <i class="fa fa-plus"></i> Nueva oferta
        </button>
      </div>

      <div class="page-body">

        <!--
          TABS = pestañas para filtrar por estado
          Cada botón llama a filtrarMisOfertas() con el estado
          'todos', 'activa', 'intercambiada', 'inactiva'
        -->
        <div class="tabs-bar">
          <button class="tab-btn active"
                  onclick="filtrarMisOfertas(this, 'todos')">
            Todas
          </button>
          <button class="tab-btn"
                  onclick="filtrarMisOfertas(this, 'activa')">
            Activas
          </button>
          <button class="tab-btn"
                  onclick="filtrarMisOfertas(this, 'intercambiada')">
            Intercambiadas
          </button>
          <button class="tab-btn"
                  onclick="filtrarMisOfertas(this, 'inactiva')">
            Inactivas
          </button>
        </div>

        <!--
          Este div es el CONTENEDOR donde PHP va a poner
          las tarjetas de ofertas reales.
          Empieza con un mensaje de "cargando..."
          que desaparece cuando llegan los datos.
          Es como el letrero de "un momento por favor"
          de una tienda mientras te atienden.
        -->
        <div id="misOfertasGrid" class="oferta-grid">
          <div style="color:var(--gris-medio); font-size:14px; padding:20px;">
            <i class="fa fa-spinner fa-spin"></i> Cargando tus ofertas...
          </div>
        </div>

      </div>
    </div>

    <!-- ===== SECCIÓN: TRUEQUES ===== -->
    <div id="sec-trueques" style="display:none;">
      <div class="page-header"><div class="page-title">MIS TRUEQUES</div></div>
      <div class="page-body">
        <div class="tabs-bar">
          <button class="tab-btn active" onclick="filterTrueques(this,'todos')">Todos (4)</button>
          <button class="tab-btn" onclick="filterTrueques(this,'pendiente')">Pendientes (1)</button>
          <button class="tab-btn" onclick="filterTrueques(this,'aceptado')">Aceptados (1)</button>
          <button class="tab-btn" onclick="filterTrueques(this,'completado')">Completados (2)</button>
        </div>
        <div id="truequesContainer">
          <div class="trueque-item" data-estado="pendiente">
            <div class="trueque-exchange"><span class="t-item">🎸 Guitarra acústica</span><span class="t-arrow">⇄</span><span class="t-item">💻 Laptop i5</span></div>
            <div class="trueque-info"><div class="badge badge-yellow">⏳ Pendiente</div><div class="trueque-user">Con: María G. · 12 Mar 2026</div></div>
            <div style="display:flex;gap:6px;">
              <button class="btn btn-primary btn-sm" onclick="showToast('✅ Trueque aceptado con María')">Aceptar</button>
              <button class="btn btn-secondary btn-sm" onclick="showToast('❌ Trueque rechazado')">Rechazar</button>
            </div>
          </div>
          <div class="trueque-item" data-estado="aceptado">
            <div class="trueque-exchange"><span class="t-item">📚 Cursos Python</span><span class="t-arrow">⇄</span><span class="t-item">🎨 Ilustraciones</span></div>
            <div class="trueque-info"><div class="badge badge-green">✅ Aceptado</div><div class="trueque-user">Con: Pedro L. · 5 Mar 2026</div></div>
            <button class="btn btn-ghost btn-sm" onclick="showSection('chat')">💬 Chat</button>
          </div>
          <div class="trueque-item" data-estado="completado">
            <div class="trueque-exchange"><span class="t-item">🔧 Reparación PC</span><span class="t-arrow">⇄</span><span class="t-item">🍳 Clases cocina</span></div>
            <div class="trueque-info"><div class="badge badge-blue">🏆 Completado</div><div class="trueque-user">Con: Laura V. · 28 Feb 2026</div></div>
            <button class="btn btn-primary btn-sm" onclick="showToast('⭐ Abriendo evaluación...')">Evaluar</button>
          </div>
          <div class="trueque-item" data-estado="completado">
            <div class="trueque-exchange"><span class="t-item">📱 iPhone 11</span><span class="t-arrow">⇄</span><span class="t-item">🎮 PS4</span></div>
            <div class="trueque-info"><div class="badge badge-blue">🏆 Completado</div><div class="trueque-user">Con: Juan R. · 20 Feb 2026</div></div>
            <div class="badge badge-green">⭐ 5/5 evaluado</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== SECCIÓN: CHAT ===== -->
    <div id="sec-chat" style="display:none;">
      <div class="page-header"><div class="page-title">MENSAJES</div></div>
      <div class="page-body">
        <div class="flex gap-lg" style="height:480px;">
          <!-- Lista chats -->
          <div style="width:280px; flex-shrink:0; overflow-y:auto;">
            <div class="chat-list-item" onclick="openChat('María García','M','#C0392B')">
              <div class="avatar avatar-md">M</div>
              <div style="flex:1; min-width:0;">
                <div style="font-weight:700; font-size:14px;">María García</div>
                <div class="chat-preview">¿Aún tienes la guitarra?</div>
              </div>
              <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                <span class="chat-hora">10:32</span>
                <span class="badge-count">2</span>
              </div>
            </div>
            <div class="chat-list-item" onclick="openChat('Pedro López','P','#2980b9')">
              <div class="avatar avatar-md" style="background:#2980b9">P</div>
              <div style="flex:1; min-width:0;">
                <div style="font-weight:700; font-size:14px;">Pedro López</div>
                <div class="chat-preview">Perfecto, acepto el trueque 🤝</div>
              </div>
              <div><span class="chat-hora">Ayer</span></div>
            </div>
            <div class="chat-list-item" onclick="openChat('Laura Vargas','L','#27ae60')">
              <div class="avatar avatar-md" style="background:#27ae60">L</div>
              <div style="flex:1; min-width:0;">
                <div style="font-weight:700; font-size:14px;">Laura Vargas</div>
                <div class="chat-preview">Gracias por la reparación 😊</div>
              </div>
              <div><span class="chat-hora">Lun</span></div>
            </div>
            <div class="chat-list-item" onclick="openChat('Ana Pérez','A','#8e44ad')">
              <div class="avatar avatar-md" style="background:#8e44ad">A</div>
              <div style="flex:1; min-width:0;">
                <div style="font-weight:700; font-size:14px;">Ana Pérez</div>
                <div class="chat-preview">¿Podemos acordar la fecha?</div>
              </div>
              <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                <span class="chat-hora">Dom</span>
                <span class="badge-count">1</span>
              </div>
            </div>
          </div>
          <!-- Ventana chat -->
          <div class="chat-window" style="flex:1;">
            <div class="chat-window-header">
              <div class="avatar avatar-sm" id="chatAvatar">M</div>
              <div style="font-weight:700; font-size:14px;" id="chatName">María García</div>
              <div style="margin-left:auto; display:flex; gap:8px;">
                <button class="btn btn-ghost btn-sm" onclick="showToast('📞 Llamando...')"><i class="fa fa-phone"></i></button>
                <button class="btn btn-ghost btn-sm" onclick="showToast('🔄 Ver trueque relacionado')"><i class="fa fa-exchange-alt"></i></button>
              </div>
            </div>
            <div class="chat-messages" id="chatMessages">
              <div class="msg msg-in">Hola! Vi tu oferta de la guitarra, me interesa 🎸</div>
              <div class="msg msg-out">¡Hola María! Sí, está disponible. ¿Qué tienes para intercambiar?</div>
              <div class="msg msg-in">Tengo una laptop i5 8va generación, 8GB RAM, SSD 256GB. ¿Te interesa?</div>
              <div class="msg msg-out">Suena bien! ¿Puedes enviarme fotos de la laptop?</div>
              <div class="msg msg-in">¿Aún tienes la guitarra disponible? 😊</div>
            </div>
            <div class="chat-input-bar">
              <input type="text" id="chatInput" placeholder="Escribe un mensaje..." onkeydown="if(event.key==='Enter') sendMsg()">
              <button class="btn btn-primary btn-sm" onclick="sendMsg()"><i class="fa fa-paper-plane"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== SECCIÓN: PERFIL ===== -->
    <div id="sec-perfil" style="display:none;">
      <div class="page-header"><div class="page-title">MI PERFIL</div><button class="btn btn-secondary" onclick="showToast('✏️ Modo edición activado')"><i class="fa fa-edit"></i> Editar perfil</button></div>
      <div class="page-body">
        <div class="profile-header-box">
          <div class="avatar avatar-xl" style="margin:0 auto 16px;">G</div>
          <div style="font-family:var(--font-display); font-size:26px; letter-spacing:1px;" id="profileName">GERSON LÓPEZ</div>
          <div style="color:var(--gris-medio); font-size:14px; margin-top:4px;" id="profileEmail">gerson@correo.com</div>
          <div style="color:var(--gris-medio); font-size:13px; margin-top:4px;"><i class="fa fa-map-marker-alt"></i> Bogotá, Colombia</div>
          <div class="profile-stats">
            <div><div class="profile-stat-num">12</div><div class="profile-stat-label">Trueques</div></div>
            <div><div class="profile-stat-num">8</div><div class="profile-stat-label">Ofertas</div></div>
            <div><div class="profile-stat-num">4.8⭐</div><div class="profile-stat-label">Reputación</div></div>
            <div><div class="profile-stat-num">24</div><div class="profile-stat-label">Favoritos</div></div>
          </div>
        </div>
        <div style="max-width:560px; margin:0 auto;">
          <div class="menu-item-profile" onclick="showSection('mis-ofertas')"><span style="font-size:18px;">📦</span><span>Mis ofertas publicadas</span><i class="fa fa-chevron-right arrow"></i></div>
          <div class="menu-item-profile" onclick="showToast('❤️ Abriendo favoritos...')"><span style="font-size:18px;">❤️</span><span>Favoritos guardados</span><i class="fa fa-chevron-right arrow"></i></div>
          <div class="menu-item-profile" onclick="showSection('notificaciones')"><span style="font-size:18px;">🔔</span><span>Notificaciones</span><i class="fa fa-chevron-right arrow"></i></div>
          <div class="menu-item-profile" onclick="showToast('🔒 Abriendo seguridad...')"><span style="font-size:18px;">🔒</span><span>Privacidad y seguridad</span><i class="fa fa-chevron-right arrow"></i></div>
          <div class="menu-item-profile" onclick="showToast('🛡️ Ley 1581/2012 - Protección de datos')"><span style="font-size:18px;">🛡️</span><span>Protección de datos (Ley 1581/2012)</span><i class="fa fa-chevron-right arrow"></i></div>
          <div class="menu-item-profile" onclick="showToast('⭐ Abriendo calificaciones...')"><span style="font-size:18px;">⭐</span><span>Mis calificaciones</span><i class="fa fa-chevron-right arrow"></i></div>
          <div class="menu-item-profile" onclick="logout()" style="border-color:var(--rojo-border);"><span style="font-size:18px;">🚪</span><span style="color:var(--rojo-tm);">Cerrar sesión</span><i class="fa fa-chevron-right arrow"></i></div>
        </div>
      </div>
    </div>

    <!-- ===== SECCIÓN: NOTIFICACIONES ===== -->
    <div id="sec-notificaciones" style="display:none;">
      <div class="page-header"><div class="page-title">NOTIFICACIONES</div><button class="btn btn-ghost btn-sm" onclick="showToast('✅ Todas marcadas como leídas')">Marcar todas leídas</button></div>
      <div class="page-body">
        <div style="display:flex; flex-direction:column; gap:10px; max-width:700px;">
          <div class="card" style="border-left:3px solid var(--rojo-tm);">
            <div style="display:flex; gap:12px; align-items:flex-start;">
              <span style="font-size:24px;">🤝</span>
              <div><strong style="font-size:14px;">Nueva propuesta de trueque</strong><br><span style="color:var(--gris-medio); font-size:13px;">María García quiere intercambiar su Laptop i5 por tu Guitarra acústica.</span><br><span style="font-size:11px; color:var(--gris-medio);">Hace 2 horas</span></div>
            </div>
          </div>
          <div class="card" style="border-left:3px solid var(--verde);">
            <div style="display:flex; gap:12px; align-items:flex-start;">
              <span style="font-size:24px;">✅</span>
              <div><strong style="font-size:14px;">Trueque completado</strong><br><span style="color:var(--gris-medio); font-size:13px;">Tu trueque con Laura Vargas fue completado exitosamente.</span><br><span style="font-size:11px; color:var(--gris-medio);">Hace 1 día</span></div>
            </div>
          </div>
          <div class="card" style="border-left:3px solid var(--azul);">
            <div style="display:flex; gap:12px; align-items:flex-start;">
              <span style="font-size:24px;">💬</span>
              <div><strong style="font-size:14px;">Nuevo mensaje de Pedro López</strong><br><span style="color:var(--gris-medio); font-size:13px;">"Perfecto, acepto el trueque 🤝"</span><br><span style="font-size:11px; color:var(--gris-medio);">Hace 2 días</span></div>
            </div>
          </div>
          <div class="card">
            <div style="display:flex; gap:12px; align-items:flex-start;">
              <span style="font-size:24px;">⭐</span>
              <div><strong style="font-size:14px;">Nueva evaluación recibida</strong><br><span style="color:var(--gris-medio); font-size:13px;">Laura Vargas te dio 5 estrellas: "Excelente reparación, muy profesional"</span><br><span style="font-size:11px; color:var(--gris-medio);">Hace 3 días</span></div>
            </div>
          </div>
          <div class="card">
            <div style="display:flex; gap:12px; align-items:flex-start;">
              <span style="font-size:24px;">🎉</span>
              <div><strong style="font-size:14px;">¡Bienvenido a Trueque Match!</strong><br><span style="color:var(--gris-medio); font-size:13px;">Tu cuenta fue creada exitosamente. ¡Publica tu primera oferta!</span><br><span style="font-size:11px; color:var(--gris-medio);">Hace 5 días</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<!-- MODAL NUEVA OFERTA -->
<div class="modal-overlay" id="modalOferta" onclick="if(event.target===this)closeModal('modalOferta')">
  <div class="modal" style="max-width:560px;">
    <div class="modal-title">PUBLICAR NUEVA OFERTA</div>
    <form id="formNuevaOferta"
      style="display:flex; flex-direction:column; gap:16px;"
      onsubmit="submitOferta(event)">

  <div class="form-group">
    <label class="form-label">Título de la oferta</label>
    <input type="text"
           id="oferta_titulo"
           name="titulo"
           class="form-control"
           placeholder="¿Qué ofreces?"
           required
           maxlength="200">
  </div>

  <div class="form-group">
    <label class="form-label">Descripción</label>
    <textarea id="oferta_descripcion"
              name="descripcion"
              class="form-control"
              placeholder="Describe tu oferta con detalle..."
              required></textarea>
  </div>

  <div class="form-group">
    <label class="form-label">Categoría</label>
    <input type="hidden"
           id="oferta_categoria"
           name="categoria"
           value="producto">
    <div class="cat-selector">
      <div class="cat-option selected" onclick="selectCat(this,'producto')">📦 Producto</div>
      <div class="cat-option" onclick="selectCat(this,'servicio')">🛠️ Servicio</div>
      <div class="cat-option" onclick="selectCat(this,'conocimiento')">📚 Conocimiento</div>
      <div class="cat-option" onclick="selectCat(this,'experiencia')">🎭 Experiencia</div>
    </div>
  </div>

  <div class="form-cols">
    <div class="form-group">
      <label class="form-label">Ciudad</label>
      <select id="oferta_ciudad"
              name="ciudad"
              class="form-control">
        <option value="Bogotá">Bogotá</option>
        <option value="Medellín">Medellín</option>
        <option value="Cali">Cali</option>
        <option value="Barranquilla">Barranquilla</option>
        <option value="Cartagena">Cartagena</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Valor estimado (COP)</label>
      <input type="number"
             id="oferta_valor"
             name="valor_estimado"
             class="form-control"
             placeholder="Opcional"
             min="0"
             step="1000">
    </div>
  </div>

  <div style="display:flex; gap:10px; margin-top:8px;">
    <button type="button"
            class="btn btn-ghost btn-full"
            onclick="closeModal('modalOferta')">
      Cancelar
    </button>
    <button type="submit"
            id="btnPublicar"
            class="btn btn-primary btn-full">
      PUBLICAR OFERTA
    </button>
  </div>
  
</form>
  </div>
</div>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

<script>
// --- INIT ---
window.onload = function() {
  const user = sessionStorage.getItem('tm_user') || 'Gerson';
  const name = user.charAt(0).toUpperCase() + user.slice(1);
  document.getElementById('sidebarName').textContent = name;
  document.getElementById('sidebarAvatar').textContent = name[0].toUpperCase();
  document.getElementById('headerName').textContent = name.toUpperCase();
  document.getElementById('profileName').textContent = name.toUpperCase() + ' LÓPEZ';
  document.getElementById('profileEmail').textContent = user + '@correo.com';
  renderDashOfertas(allOfertas);
};

// --- SECTIONS ---
const sections = ['home','ofertas','mis-ofertas','trueques','chat','perfil','notificaciones'];
function showSection(id) {
  // Ocultamos todas las secciones primero
  sections.forEach(s => {
    document.getElementById('sec-'+s).style.display = 'none';
  });
  // Mostramos solo la sección pedida
  document.getElementById('sec-'+id).style.display = 'block';

  // Actualizamos qué item del menú lateral está activo
  document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
  document.querySelectorAll('.menu-item').forEach(m => {
    if (m.getAttribute('onclick') && m.getAttribute('onclick').includes("'"+id+"'"))
      m.classList.add('active');
  });

  /*
   * NUEVO: Si el usuario abre "Mis Ofertas"
   * cargamos las ofertas reales de la BD automáticamente.
   * Es como encender la tele cuando entras a la sala —
   * no tienes que hacer nada extra, solo entrar.
   */
  if (id === 'mis-ofertas') {
    cargarMisOfertas();
  }
}

// --- OFERTAS ---
const allOfertas = [
  { icon:'📦', titulo:'Bicicleta montañera rodado 26', cat:'producto', ciudad:'Bogotá', user:'Carlos M.', av:'C', avC:'#C0392B' },
  { icon:'🎸', titulo:'Clases de guitarra (10 sesiones)', cat:'servicio', ciudad:'Medellín', user:'Ana P.', av:'A', avC:'#2980b9' },
  { icon:'💻', titulo:'Curso Python completo', cat:'conocimiento', ciudad:'Cali', user:'Luis R.', av:'L', avC:'#27ae60' },
  { icon:'🍳', titulo:'Clases de cocina italiana', cat:'experiencia', ciudad:'Bogotá', user:'María G.', av:'M', avC:'#8e44ad' },
  { icon:'🔧', titulo:'Reparación de computadores', cat:'servicio', ciudad:'Barranquilla', user:'Pedro L.', av:'P', avC:'#e67e22' },
  { icon:'📱', titulo:'iPhone 11 seminuevo', cat:'producto', ciudad:'Medellín', user:'Sandra V.', av:'S', avC:'#C0392B' },
];
const catBadge = { producto:'badge-red', servicio:'badge-blue', conocimiento:'badge-green', experiencia:'badge-yellow' };
const catLabel = { producto:'Producto', servicio:'Servicio', conocimiento:'Conocimiento', experiencia:'Experiencia' };
let currentFilter = '';

function renderDashOfertas(list) {
  const g = document.getElementById('dashOfertaGrid');
  if (!g) return;
  g.innerHTML = list.map(o => `
    <div class="oc">
      <div class="oc-img">${o.icon}<span class="badge ${catBadge[o.cat]}" style="position:absolute;top:10px;left:10px;">${catLabel[o.cat]}</span></div>
      <div class="oc-body">
        <div class="oc-title">${o.titulo}</div>
        <div class="oc-meta"><div class="avatar avatar-sm" style="background:${o.avC}">${o.av}</div>${o.user} · ${o.ciudad}</div>
      </div>
      <div class="oc-footer">
        <button class="btn btn-primary btn-sm btn-full" onclick="showToast('🤝 Propuesta enviada a ${o.user}')">Proponer trueque</button>
        <button class="btn btn-ghost btn-sm btn-icon" onclick="showToast('❤️ Guardado en favoritos')"><i class="fa fa-heart"></i></button>
      </div>
    </div>`).join('');
}

function setFilter(el, cat) {
  document.querySelectorAll('.filter-cat').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  currentFilter = cat;
  filterDash();
}
function filterDash() {
  const q = (document.getElementById('searchDash')||{value:''}).value.toLowerCase();
  let list = allOfertas;
  if (currentFilter) list = list.filter(o => o.cat === currentFilter);
  if (q) list = list.filter(o => o.titulo.toLowerCase().includes(q) || o.ciudad.toLowerCase().includes(q));
  renderDashOfertas(list);
}

// --- TRUEQUES FILTER ---
function filterTrueques(el, estado) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  document.querySelectorAll('#truequesContainer .trueque-item').forEach(item => {
    item.style.display = (estado === 'todos' || item.dataset.estado === estado) ? 'flex' : 'none';
  });
}

// --- CHAT ---
function openChat(name, initial, color) {
  document.getElementById('chatName').textContent = name;
  document.getElementById('chatAvatar').textContent = initial;
  document.getElementById('chatAvatar').style.background = color;
}
function sendMsg() {
  const input = document.getElementById('chatInput');
  const text = input.value.trim();
  if (!text) return;
  const msgs = document.getElementById('chatMessages');
  const div = document.createElement('div');
  div.className = 'msg msg-out';
  div.textContent = text;
  msgs.appendChild(div);
  input.value = '';
  msgs.scrollTop = msgs.scrollHeight;
  setTimeout(() => {
    const reply = document.createElement('div');
    reply.className = 'msg msg-in';
    reply.textContent = '¡Entendido! Te respondo en un momento 😊';
    msgs.appendChild(reply);
    msgs.scrollTop = msgs.scrollHeight;
  }, 1200);
}

// --- MODAL ---
function showModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
function selectCat(el, valor) {
  document.querySelectorAll('.cat-option').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('oferta_categoria').value = valor;
}
async function submitOferta(e) {
  e.preventDefault();

  const btn = document.getElementById('btnPublicar');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  const titulo      = document.getElementById('oferta_titulo').value.trim();
  const descripcion = document.getElementById('oferta_descripcion').value.trim();
  const categoria   = document.getElementById('oferta_categoria').value;
  const ciudad      = document.getElementById('oferta_ciudad').value;
  const valor       = document.getElementById('oferta_valor').value || 0;

  const datos = new FormData();
  datos.append('titulo',         titulo);
  datos.append('descripcion',    descripcion);
  datos.append('categoria',      categoria);
  datos.append('ciudad',         ciudad);
  datos.append('valor_estimado', valor);

  try {
    const respuesta = await fetch('guardar_oferta.php', {
      method: 'POST',
      body: datos
    });
    const resultado = await respuesta.json();

    if (resultado.ok) {
      closeModal('modalOferta');
      document.getElementById('formNuevaOferta').reset();
      document.querySelectorAll('.cat-option').forEach((c, i) => {
        c.classList.toggle('selected', i === 0);
      });
      document.getElementById('oferta_categoria').value = 'producto';
      showToast('🎉 ¡Oferta #' + resultado.id + ' publicada!');
      const statCards = document.querySelectorAll('.stat-card-num');
      if (statCards[0]) {
        statCards[0].textContent = parseInt(statCards[0].textContent) + 1;
      }
    } else {
      showToast('❌ ' + resultado.mensaje);
    }

  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Error de conexión. Revisa que XAMPP esté encendido.');

  } finally {
    btn.disabled = false;
    btn.textContent = 'PUBLICAR OFERTA';
  }
}

// --- TOAST ---
function showToast(msg) {
  const tc = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = 'toast success';
  t.textContent = msg;
  tc.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}
// =============================================
// MIS OFERTAS — Carga datos reales desde la BD
// =============================================
let todasMisOfertas = [];

async function cargarMisOfertas() {
  try {
    const respuesta = await fetch('mis_ofertas_ajax.php');
    const datos = await respuesta.json();
    todasMisOfertas = datos;
    document.getElementById('misOfertasTotal').textContent = datos.length;
    renderMisOfertas(todasMisOfertas);
  } catch (error) {
    console.error('Error:', error);
    document.getElementById('misOfertasGrid').innerHTML =
      '<div style="color:var(--rojo-tm); padding:20px;">❌ Error al cargar. ¿XAMPP encendido?</div>';
  }
}

function renderMisOfertas(lista) {
  const grid = document.getElementById('misOfertasGrid');
  if (lista.length === 0) {
    grid.innerHTML =
      '<div style="text-align:center; padding:40px; color:var(--gris-medio);">' +
      '<div style="font-size:48px; margin-bottom:16px;">📦</div>' +
      '<div style="font-size:16px;">Aún no tienes ofertas publicadas</div>' +
      '</div>';
    return;
  }
  const iconos    = { producto:'📦', servicio:'🛠️', conocimiento:'📚', experiencia:'🎭' };
  const badges    = { producto:'badge-red', servicio:'badge-blue', conocimiento:'badge-green', experiencia:'badge-yellow' };
  const etiquetas = { producto:'Producto', servicio:'Servicio', conocimiento:'Conocimiento', experiencia:'Experiencia' };
  const estadoColor = { activa:'badge-green', inactiva:'', intercambiada:'badge-blue' };
  const estadoLabel = { activa:'✅ Activa', inactiva:'⏸️ Inactiva', intercambiada:'🔄 Intercambiada' };

  grid.innerHTML = lista.map(function(o) {
    return '<div class="oc">' +
      '<div class="oc-img">' + (iconos[o.categoria] || '📦') +
      '<span class="badge ' + (badges[o.categoria] || '') + '" style="position:absolute;top:10px;left:10px;">' + (etiquetas[o.categoria] || o.categoria) + '</span>' +
      '<span class="badge ' + (estadoColor[o.estado] || '') + '" style="position:absolute;top:10px;right:10px;font-size:10px;">' + (estadoLabel[o.estado] || o.estado) + '</span>' +
      '</div>' +
      '<div class="oc-body">' +
      '<div class="oc-title">' + o.titulo + '</div>' +
      '<div class="oc-meta"><i class="fa fa-map-marker-alt"></i> ' + o.ciudad + ' &nbsp;·&nbsp; ' +
      (o.valor_estimado > 0 ? '$ ' + parseInt(o.valor_estimado).toLocaleString('es-CO') : 'Valor a convenir') +
      '</div></div>' +
      '<div class="oc-footer">' +
      '<button class="btn btn-ghost btn-sm" onclick="showToast(\'✏️ Pronto podrás editar\')"><i class="fa fa-edit"></i> Editar</button>' +
      '<button class="btn btn-secondary btn-sm" onclick="confirmarEliminar(' + o.id_oferta + ', \'' + o.titulo.replace(/'/g, "\\'") + '\')"><i class="fa fa-trash"></i> Eliminar</button>' +
      '</div></div>';
  }).join('');
}

function filtrarMisOfertas(boton, estado) {
  document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
  boton.classList.add('active');
  const filtradas = (estado === 'todos') ? todasMisOfertas : todasMisOfertas.filter(function(o) { return o.estado === estado; });
  renderMisOfertas(filtradas);
}

async function confirmarEliminar(id, titulo) {
  // confirm() muestra un popup de confirmación
  // Si el usuario cancela, no hacemos nada
  if (!confirm('¿Seguro que quieres eliminar "' + titulo + '"?\nEsta acción no se puede deshacer.')) {
    return; // El usuario canceló — salimos
  }

  try {
    // FormData es como un sobre donde metemos los datos
    // para enviarlos al servidor por POST
    const datos = new FormData();
    datos.append('id_oferta', id); // Metemos el ID al sobre

    // fetch() manda el sobre a eliminar_oferta.php
    // y espera la respuesta
    const respuesta = await fetch('eliminar_oferta.php', {
      method: 'POST',
      body: datos
    });

    // .json() abre el sobre de respuesta y lee el contenido
    const resultado = await respuesta.json();

    if (resultado.ok) {
      // Si funcionó mostramos toast de éxito
      showToast('🗑️ Oferta eliminada correctamente');
      // Recargamos la lista de mis ofertas
      // para que desaparezca la tarjeta eliminada
      cargarMisOfertas();
    } else {
      // Si algo salió mal mostramos el error
      showToast('❌ ' + resultado.mensaje);
    }

  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Error de conexión');
  }
}
// --- LOGOUT ---
function logout() {
  window.location.href = 'cerrar_sesion.php';
}
</script>
</body>
</html>
