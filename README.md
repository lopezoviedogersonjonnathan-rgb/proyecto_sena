# 🤝 TRUEQUE MATCH
### Plataforma Colombiana de Intercambio Sin Dinero

**Estudiante:** Gerson Jonnathan López Oviedo  
**Ficha:** 3186647  
**Instructor:** Arnaldo Alfonso Montiel Brun  
**Programa:** Tecnólogo en Análisis y Desarrollo de Software  
**Empresa:** G.L. Software Solutions  
**SENA 2026**

🌍 **En producción:** http://truequematch-gerson.great-site.net/truequematch/app_trueque_match/

---

## 🌐 Descripción

Trueque Match es una aplicación web y móvil que permite a los colombianos intercambiar productos, servicios, conocimientos y experiencias **sin necesidad de dinero**. Promueve la economía colaborativa conectando personas que tienen algo que ofrecer con quienes lo necesitan.

---

## 🚀 Stack Tecnológico

| Capa | Tecnología | Estado |
|------|-----------|--------|
| Backend Web | PHP 8.2 + MariaDB | ✅ Funcional |
| Frontend Web | HTML5 + CSS3 + JavaScript | ✅ Funcional |
| Base de Datos | MariaDB via XAMPP puerto 3307 | ✅ Funcional |
| App Móvil | React Native + Expo (Android) | ✅ Probada en Redmi 9 (Gerson) y Redmi 10C (Sandra) |
| APIs REST | 11 endpoints PHP | ✅ Todas probadas en Postman con datos reales |
| Control de versiones | Git + GitHub | ✅ Funcional |
| IDE | Visual Studio Code | ✅ Configurado |
| Hosting / Producción | InfinityFree | ✅ En línea desde septiembre 2026 |

---

## 📋 Evidencias SENA

| Evidencia | Descripción | Estado |
|-----------|-------------|--------|
| GA4-220501095-AA1-EV02 | Modelo conceptual y lógico del proyecto | ✅ |
| GA4-220501095-AA2-EV02 | Informe de entregables para el proyecto | ✅ |
| GA6-220501096-AA1-EV01 | Modelo Relacional + Normalización 1FN/2FN/3FN | ✅ |
| GA6-220501096-AA1-EV02 | Modelo Entidad Relación (MySQL Workbench) | ✅ |
| GA6-220501096-AA1-EV03 y EV04 | Creación y elaboración de base de datos NoSQL (MongoDB) | ✅ |
| GA6-220501096-AA2-EV01 | Informe técnico: sentencias DDL y DML de SQL | ✅ |
| GA6-220501096-AA4-EV03 | Front-End HTML/CSS/JS + PHP + MariaDB + GitHub | ✅ |
| GA7-220501096-AA4-EV02 y EV03 | Componentes Front-End | ✅ |
| GA7-220501096-AA3-EV01 y EV02 | Video de sustentación | ✅ |
| GA7-220501096-AA5-EV02, EV03 y EV04 | APIs REST probadas en Postman (video) | ✅ |
| GA10-220501097-AA5-EV01 y AA6-EV01 | Despliegue a producción (InfinityFree) | ✅ |

---

## 🗄️ Base de Datos

- **Motor:** MariaDB 10.x via XAMPP | **Puerto:** 3307
- **BD:** `trueque_match_db` | **Tablas:** 10 normalizadas (1FN, 2FN, 3FN)

| Tabla | Descripción |
|-------|-------------|
| tipo_usuario | Roles del sistema |
| usuario | Usuarios registrados |
| administrador | Gestores del sistema |
| oferta | Publicaciones de intercambio |
| trueque | Intercambios formalizados |
| evaluacion | Calificaciones 1-5 estrellas |
| solicitud | Contacto previo al trueque |
| notificacion | Alertas del sistema |
| reporte | Denuncias de usuarios |
| usuario_oferta | Favoritos (tabla N:M) |

---

## 🔌 APIs REST (11 endpoints — todas probadas en Postman)

| API | Recurso que maneja |
|-----|--------------------|
| `api_login.php` | Autenticación de usuarios |
| `api_registro.php` | Registro de usuarios nuevos |
| `api_ofertas.php` | Ofertas: crear (POST), listar (GET), editar (PUT), eliminar (DELETE) |
| `api_solicitudes.php` | Solicitudes de contacto previo al trueque |
| `api_trueques.php` | Trueques formalizados |
| `api_evaluaciones.php` | Evaluaciones y reputación de usuarios |
| `api_favoritos.php` | Ofertas favoritas de cada usuario |
| `api_notificaciones.php` | Notificaciones del sistema |
| `api_reportes.php` | Reportes/denuncias de usuarios |

> Nota: `api_ofertas.php` es el único endpoint oficial para editar y eliminar ofertas (PUT/DELETE). Los antiguos `api_editar_oferta.php` y `api_eliminar_oferta.php` fueron retirados para evitar duplicidad de código.

---

## 📁 Estructura del Proyecto
```
proyecto_sena/
├── README.md
├── conexion.php
├── agregar_oferta.php
├── ofertas.php
├── editar_oferta.php
├── trueque_match_db.sql
└── app_trueque_match/
    ├── index.html
    ├── login.php
    ├── registro.php
    ├── dashboard.php
    ├── mis_ofertas_ajax.php
    ├── guardar_oferta.php
    ├── eliminar_oferta.php
    ├── cerrar_sesion.php
    ├── como-funciona.html
    ├── api_login.php
    ├── api_registro.php
    ├── api_ofertas.php
    ├── api_solicitudes.php
    ├── api_trueques.php
    ├── api_evaluaciones.php
    ├── api_favoritos.php
    ├── api_notificaciones.php
    ├── api_reportes.php
    ├── styles.css
    ├── app.js
    └── LOGO_FINAL.png
```
---
## 🎥 Videos de Evidencias

### GA7-AA3-EV01 y EV02 — Video de sustentación

📹 https://youtu.be/Kis8zE8BeT4?si=l61Aqz02DDFPFNM7

En este video se presenta la explicación y desarrollo de las evidencias GA7-220501096-AA3-EV01 y GA7-220501096-AA3-EV02 correspondientes al proyecto app TRUEQUE MATCH.

### GA7-AA5-EV02, EV03 y EV04 — Pruebas de APIs en Postman

📹 https://youtu.be/Klnpu9v9o8Y?si=G9hOcj40Blfm2daB

---

## 🌍 Despliegue en producción

Trueque Match ya está funcionando en internet, de punta a punta, no solo en el entorno local de XAMPP.

- **URL en vivo:** http://truequematch-gerson.great-site.net/truequematch/app_trueque_match/
- **Hosting:** InfinityFree (Linux + MySQL/MariaDB), subido manualmente vía File Manager
- **Verificado en producción:**
  - ✅ Login real con credenciales de la base de datos (`login.php`)
  - ✅ Lectura de ofertas (`mis_ofertas_ajax.php`)
  - ✅ Lectura de trueques, evaluaciones y notificaciones (`dashboard.php`)
  - ✅ Escritura real: publicación de una oferta nueva de prueba, guardada correctamente en la base de datos de producción (`guardar_oferta.php`)

> Nota técnica: como el hosting de producción usa Linux (sensible a mayúsculas/minúsculas en nombres de tabla) y no está conectado automáticamente a GitHub, cada actualización de código debe subirse manualmente al hosting después de cada `git push` para que el cambio se vea reflejado en la URL en vivo.

---

## 📌 Estado del proyecto

Las 11 APIs REST están confirmadas funcionando tanto por revisión de código como por ejecución real en Postman con datos reales, y el proyecto completo ya está desplegado y verificado en producción (ver sección "Despliegue en producción"). El proyecto sigue en fase de pulido de detalles menores (manual de usuario, exportación de diagramas EER en HD) antes de la recopilación final de evidencias.

