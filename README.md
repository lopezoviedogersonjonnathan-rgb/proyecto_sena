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
| App Móvil | React Native + Expo (Android) | ✅ APK en Redmi 9 |
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
| TIPO_USUARIO | Roles del sistema |
| USUARIO | Usuarios registrados |
| ADMINISTRADOR | Gestores del sistema |
| OFERTA | Publicaciones de intercambio |
| TRUEQUE | Intercambios formalizados |
| EVALUACION | Calificaciones 1-5 estrellas |
| SOLICITUD | Contacto previo al trueque |
| NOTIFICACION | Alertas del sistema |
| REPORTE | Denuncias de usuarios |
| USUARIO_OFERTA | Favoritos (tabla N:M) |

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
