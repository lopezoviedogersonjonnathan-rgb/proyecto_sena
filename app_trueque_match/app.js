/*
 * TRUEQUE MATCH — app.js
 * GA6-220501096-AA4-EV03
 * Gerson Jonnathan López Oviedo | Ficha 3186647 | SENA 2026
 */

// =============================================
// CONSTANTES GLOBALES
// =============================================
const TM = {
  version: '1.0.0',
  nombre: 'Trueque Match',
  empresa: 'G.L. Software Solutions',
  colores: {
    primario: '#C0392B',
    fondo: '#111111',
    texto: '#F5F0EB'
  }
};

// =============================================
// VALIDACIONES (RF01, RF02)
// =============================================
const Validar = {
  email: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
  password: (v) => v.length >= 8,
  coinciden: (a, b) => a === b,
  requerido: (v) => v.trim().length > 0,
  telefono: (v) => /^\d{7,11}$/.test(v.replace(/\s/g, ''))
};

// =============================================
// SESION (RF02)
// =============================================
const Session = {
  set: (user) => sessionStorage.setItem('tm_user', JSON.stringify(user)),
  get: () => { try { return JSON.parse(sessionStorage.getItem('tm_user')); } catch { return null; } },
  clear: () => sessionStorage.clear(),
  exists: () => !!sessionStorage.getItem('tm_user'),
  logout: () => { Session.clear(); window.location.href = 'login.html'; }
};

// =============================================
// TOAST NOTIFICATIONS (RF10)
// =============================================
const Toast = {
  show: (msg, tipo = 'info') => {
    let tc = document.getElementById('toastContainer');
    if (!tc) {
      tc = document.createElement('div');
      tc.id = 'toastContainer';
      tc.className = 'toast-container';
      document.body.appendChild(tc);
    }
    const t = document.createElement('div');
    t.className = `toast ${tipo}`;
    t.textContent = msg;
    tc.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
  },
  success: (msg) => Toast.show(msg, 'success'),
  error: (msg) => Toast.show(msg, 'error'),
  info: (msg) => Toast.show(msg, 'info')
};

// =============================================
// UTILIDADES DOM
// =============================================
const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => document.querySelectorAll(sel);

function showEl(id) { const el = document.getElementById(id); if (el) el.style.display = 'block'; }
function hideEl(id) { const el = document.getElementById(id); if (el) el.style.display = 'none'; }
function toggleEl(id) { const el = document.getElementById(id); if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none'; }

// =============================================
// FORMATEO
// =============================================
const Format = {
  moneda: (n) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(n),
  fecha: (d) => new Date(d).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: 'numeric' }),
  tiempo: (d) => {
    const diff = (Date.now() - new Date(d)) / 1000;
    if (diff < 3600) return `Hace ${Math.floor(diff/60)} min`;
    if (diff < 86400) return `Hace ${Math.floor(diff/3600)} h`;
    return `Hace ${Math.floor(diff/86400)} días`;
  }
};

// =============================================
// CATEGORIAS (RF05)
// =============================================
const Categorias = {
  producto:     { label: 'Producto',     icon: '📦', badge: 'badge-red' },
  servicio:     { label: 'Servicio',     icon: '🛠️', badge: 'badge-blue' },
  conocimiento: { label: 'Conocimiento', icon: '📚', badge: 'badge-green' },
  experiencia:  { label: 'Experiencia',  icon: '🎭', badge: 'badge-yellow' }
};

// =============================================
// ESTADOS DE TRUEQUE (RF07)
// =============================================
const EstadoTrueque = {
  pendiente:  { label: 'Pendiente',  badge: 'badge-yellow', icon: '⏳' },
  aceptado:   { label: 'Aceptado',   badge: 'badge-green',  icon: '✅' },
  completado: { label: 'Completado', badge: 'badge-blue',   icon: '🏆' },
  cancelado:  { label: 'Cancelado',  badge: 'badge-red',    icon: '❌' }
};

// =============================================
// MOCK DATA (para prototipo front-end)
// =============================================
const MockDB = {
  ofertas: [
    { id:1, icon:'📦', titulo:'Bicicleta montañera rodado 26', desc:'Bici en buen estado, frenos de disco, talla M.', categoria:'producto', ciudad:'Bogotá', usuario:'Carlos M.', avatar:'C', avColor:'#C0392B', tiempo:'2h' },
    { id:2, icon:'🎸', titulo:'Clases de guitarra (10 sesiones)', desc:'Músico profesional, 8 años de experiencia.', categoria:'servicio', ciudad:'Medellín', usuario:'Ana P.', avatar:'A', avColor:'#2980B9', tiempo:'3h' },
    { id:3, icon:'💻', titulo:'Curso Python completo', desc:'Material completo con ejercicios prácticos.', categoria:'conocimiento', ciudad:'Cali', usuario:'Luis R.', avatar:'L', avColor:'#27AE60', tiempo:'5h' },
    { id:4, icon:'🍳', titulo:'Clases de cocina italiana', desc:'5 sesiones de 2 horas. Pasta, risotto, tiramisú.', categoria:'experiencia', ciudad:'Bogotá', usuario:'María G.', avatar:'M', avColor:'#8E44AD', tiempo:'1d' },
    { id:5, icon:'🔧', titulo:'Reparación de computadores PC', desc:'Técnico en sistemas, 5 años de experiencia.', categoria:'servicio', ciudad:'Barranquilla', usuario:'Pedro L.', avatar:'P', avColor:'#E67E22', tiempo:'2d' },
    { id:6, icon:'📱', titulo:'iPhone 11 (128GB) seminuevo', desc:'Batería al 89%, con estuche y cargador original.', categoria:'producto', ciudad:'Medellín', usuario:'Sandra V.', avatar:'S', avColor:'#C0392B', tiempo:'2d' },
  ],
  trueques: [
    { id:1, oferta1:'🎸 Guitarra acústica', oferta2:'💻 Laptop i5', estado:'pendiente', usuario:'María G.', fecha:'12 Mar 2026' },
    { id:2, oferta1:'📚 Cursos Python', oferta2:'🎨 Ilustraciones', estado:'aceptado', usuario:'Pedro L.', fecha:'5 Mar 2026' },
    { id:3, oferta1:'🔧 Reparación PC', oferta2:'🍳 Clases cocina', estado:'completado', usuario:'Laura V.', fecha:'28 Feb 2026' },
    { id:4, oferta1:'📱 iPhone 11', oferta2:'🎮 PS4', estado:'completado', usuario:'Juan R.', fecha:'20 Feb 2026' },
  ],
  notificaciones: [
    { id:1, tipo:'trueque', icon:'🤝', titulo:'Nueva propuesta de trueque', msg:'María García quiere intercambiar su Laptop i5 por tu Guitarra.', tiempo:'2h', leida:false },
    { id:2, tipo:'evaluacion', icon:'⭐', titulo:'Nueva evaluación recibida', msg:'Laura Vargas te dio 5 estrellas.', tiempo:'1d', leida:false },
    { id:3, tipo:'chat', icon:'💬', titulo:'Nuevo mensaje de Pedro López', msg:'"Perfecto, acepto el trueque 🤝"', tiempo:'2d', leida:true },
    { id:4, tipo:'sistema', icon:'🎉', titulo:'¡Bienvenido a Trueque Match!', msg:'Tu cuenta fue creada. ¡Publica tu primera oferta!', tiempo:'5d', leida:true },
  ]
};

// =============================================
// INIT — Ejecuta al cargar cualquier página
// =============================================
document.addEventListener('DOMContentLoaded', () => {
  // Resaltar nav link activo
  const path = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-link').forEach(link => {
    if (link.getAttribute('href') === path) link.classList.add('active');
  });
  console.log(`%cTRUEQUE MATCH v${TM.version} — ${TM.empresa}`, 'color:#C0392B; font-weight:bold; font-size:14px;');
});
