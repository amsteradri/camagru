# 🎓 Guía de Defensa: Proyecto Camagru

Este documento sirve como guía técnica y funcional para explicar el desarrollo del proyecto Camagru. Aquí se detallan la arquitectura, las decisiones de diseño y el funcionamiento interno de las características clave.

---

## 1. Introducción y Objetivo
**¿Qué es Camagru?**
Es una aplicación web completa estilo "red social" (similar a un mini-Instagram) que permite a los usuarios registrarse, capturar fotos con su webcam, editarlas superponiendo stickers (imágenes PNG con transparencia) y compartirlas en una galería pública donde otros usuarios pueden dar "likes" y comentar.

**Objetivo Técnico:**
El objetivo principal fue construir una aplicación web desde cero **sin utilizar frameworks de Backend** (como Laravel o Symfony) y **sin frameworks de Frontend pesados** (como React o Angular), utilizando PHP nativo y JavaScript puro (Vanilla JS). Esto demuestra un dominio profundo de los fundamentos de la programación web.

---

## 2. Arquitectura del Sistema: MVC (Modelo-Vista-Controlador)

He implementado una arquitectura **MVC personalizada** para organizar el código de manera limpia y escalable.

### 🧠 ¿Cómo funciona mi MVC?

1.  **Entry Point (`public/index.php`):**
    *   Todo el tráfico pasa por aquí. Es el único archivo accesible directamente desde el navegador.
    *   Inicia la sesión, carga el entorno (`.env`) y llama al `Router`.

2.  **Router (`app/core/Router.php`):**
    *   Analiza la URL (ej: `/gallery/index`).
    *   Separa el **Controlador** (`GalleryController`) y el **Método** (`index`).
    *   Instancia el controlador y ejecuta la acción.

3.  **Controladores (`app/controllers/`):**
    *   Son el "cerebro". Reciben la petición del usuario, piden datos al Modelo y cargan una Vista.
    *   *Ejemplo:* El `EditorController` verifica si el usuario está logueado, recibe la imagen en Base64 y llama al modelo para guardarla.

4.  **Modelos (`app/models/`):**
    *   Gestionan la lógica de datos y la comunicación con la Base de Datos.
    *   Heredan de una clase base `Model` que maneja la conexión PDO.
    *   *Ejemplo:* `User.php` tiene métodos como `login()`, `register()`, `findByEmail()`.

5.  **Vistas (`app/views/`):**
    *   Es lo que ve el usuario (HTML + PHP para mostrar variables).
    *   Usan un sistema de "Layouts" (`layouts/main.php`) para no repetir el header y footer en cada página.

---

## 3. Tecnologías y Decisiones Técnicas

### 🐘 Backend (PHP)
*   **PDO (PHP Data Objects):** Utilizado para conectar a MySQL. Es vital porque permite usar *Prepared Statements*, lo que protege contra inyecciones SQL.
*   **GD Library:** Librería nativa de PHP usada para el procesamiento de imágenes (fusionar la foto del usuario con el sticker).
*   **Session Management:** Uso de `$_SESSION` nativo para mantener al usuario logueado.

### 🌐 Frontend (HTML/CSS/JS)
*   **Bootstrap 5:** Framework CSS utilizado para el sistema de rejilla (Grid) y componentes responsivos (que se vea bien en móvil y PC).
*   **Vanilla JavaScript:**
    *   **WebRTC (`navigator.mediaDevices.getUserMedia`):** API del navegador para acceder a la webcam sin plugins.
    *   **Canvas API:** Usada para dibujar el video en tiempo real y capturar el frame como una imagen.
    *   **Fetch API (AJAX):** Usada para dar likes, borrar fotos y enviar comentarios sin recargar la página, mejorando la experiencia de usuario (UX).

### 🐳 Infraestructura
*   **Docker & Docker Compose:**
    *   El proyecto está contenerizado. Tengo un contenedor para el servidor web (Apache+PHP) y otro para la base de datos (MySQL).
    *   Esto garantiza que el proyecto funcione igual en cualquier ordenador ("It works on my machine").

---

## 4. Explicación de Funcionalidades Clave (Lo difícil)

### 📸 A. El Editor y la Superposición (El Core)
Esta es la parte más compleja. Funciona en dos pasos:

1.  **Cliente (JS):**
    *   Capturo el stream de video en un `<video>`.
    *   Cuando el usuario hace clic, dibujo ese frame en un `<canvas>` oculto.
    *   Convierto ese canvas a una cadena Base64 (`data:image/png;base64...`) y la envío al servidor junto con las coordenadas de los stickers.

2.  **Servidor (PHP - `Image.php`):**
    *   Recibo el Base64 y lo decodifico a una imagen real.
    *   Uso `imagecreatefrompng` para cargar los stickers.
    *   Uso `imagecopyresampled` para pegar el sticker sobre la foto original, respetando el canal Alpha (transparencia).
    *   Guardo el resultado final en la carpeta `uploads/`.

### 🔐 B. Seguridad (Puntos Críticos)
Si te preguntan sobre seguridad, menciona estos 4 pilares:

1.  **SQL Injection:** Prevenida usando sentencias preparadas (`$stmt->prepare("SELECT * FROM users WHERE id = ?")`). Nunca concateno variables directamente en el SQL.
2.  **XSS (Cross-Site Scripting):** Todo lo que imprime el usuario (como comentarios) se pasa por `htmlspecialchars()` antes de mostrarse en pantalla. Esto convierte `<script>` en texto inofensivo.
3.  **CSRF (Cross-Site Request Forgery):** Cada formulario genera un token único oculto (`csrf_token`). Al enviar el formulario, verifico que el token coincida con el de la sesión. Esto evita que otros sitios envíen formularios en nombre del usuario.
4.  **Contraseñas:** Nunca se guardan en texto plano. Uso `password_hash()` (algoritmo Bcrypt) para guardarlas y `password_verify()` para comprobarlas.

### 📧 C. Sistema de Email
*   Uso un servidor SMTP (configurado en `.env`) para enviar correos reales.
*   **Verificación:** Al registrarse, genero un token aleatorio (`bin2hex(random_bytes(32))`), lo guardo en la BD y envío un link. El usuario no puede loguearse hasta hacer clic en ese link.

---

## 5. Base de Datos (Esquema)

Tengo 4 tablas principales relacionadas entre sí:

1.  **Users:** `id`, `username`, `email`, `password`, `token`.
2.  **Images:** `id`, `user_id` (FK), `filename`.
3.  **Likes:** `user_id` (FK), `image_id` (FK). *Nota: Tiene una clave única compuesta (user_id, image_id) para que un usuario no pueda dar like dos veces a la misma foto.*
4.  **Comments:** `id`, `user_id`, `image_id`, `text`.

---

## 6. Problemas Resueltos (Anécdotas para la defensa)

*   **El problema de la doble foto:** "Tuve un desafío interesante donde se generaban dos fotos al capturar. Descubrí que tenía dos archivos JS (`app.js` y `editor.js`) escuchando el mismo evento de clic. Lo solucioné implementando una detección en `app.js` para que se desactive si detecta que el editor avanzado está presente, y añadiendo 'flags' para evitar ejecuciones múltiples."
*   **Zonas Horarias:** "Al principio los tokens de recuperación expiraban inmediatamente. Aprendí que PHP y MySQL tenían zonas horarias diferentes. Lo arreglé haciendo que MySQL se encargue de calcular la expiración (`NOW() + INTERVAL 1 HOUR`) para mantener la consistencia."

---

## 7. Conclusión
Este proyecto me ha permitido entender cómo funcionan los frameworks modernos "por debajo del capó", gestionando manualmente el enrutamiento, la seguridad y la manipulación de archivos multimedia.
