<?php
/*
 * =============================================
 * TRUEQUE MATCH — cerrar_sesion.php
 * Este archivo destruye la sesión activa del usuario
 * y lo redirige al login.
 * Es como el botón de "salir" de un edificio —
 * cuando lo presionas, te sacan y cierran la puerta.
 * Gerson Jonnathan López Oviedo | Ficha 3186647
 * =============================================
 */

// session_start() SIEMPRE va primero
// Sin esto PHP no puede encontrar la sesión activa
// Es como encender la luz antes de buscar algo en un cuarto oscuro
session_start();

// session_destroy() borra TODOS los datos de la sesión
// Es como vaciar completamente una mochila
// Después de esto $_SESSION queda vacío
session_destroy();

// header() redirige al usuario al login
// exit() detiene el código aquí — sin esto PHP seguiría corriendo
// Es como cerrar la puerta CON llave después de salir
header("Location: login.php");
exit();
?>