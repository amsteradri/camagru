# 🎓 Guía Definitiva de Defensa: Proyecto Camagru (Versión "For Dummies")

Este documento es tu "chuleta" maestra. Está escrito en lenguaje sencillo para que entiendas cada pieza del puzzle y puedas explicarlo con confianza, incluso si te pones nervioso.

---

## 1. ¿Qué es esto y por qué lo he hecho?

**La frase clave:**
> "Camagru es una red social de fotografía, similar a un Instagram básico, donde puedes hacerte fotos con la webcam, ponerles stickers divertidos y compartirlas."

**El objetivo oculto (lo que el profesor quiere oír):**
No se trata solo de hacer una web bonita. El reto era **hacerlo todo "a mano"**.
*   ❌ Sin frameworks que te lo dan todo hecho (como Laravel, React, Symfony).
*   ✅ Usando solo PHP puro, JavaScript nativo y SQL.
*   **¿Por qué?** Para demostrar que entiendo cómo funciona la web por debajo: cómo viajan los datos, cómo se protege la información y cómo se estructura una aplicación real.

---

## 2. La Estructura: MVC (El esqueleto)

Imagina que tu aplicación es un **Restaurante**. He usado el patrón **MVC (Modelo-Vista-Controlador)** para organizarlo.

### 🧠 Explicación con analogía:

1.  **El Cliente (El Navegador/Usuario):**
    *   Llega y pide: *"Quiero ver la galería"* (Escribe la URL `/gallery`).

2.  **El Router (El Recepcionista - `app/core/Router.php`):**
    *   Es el primero que recibe al cliente.
    *   Mira la URL y dice: *"Ah, quieres la galería. ¡Avisaré al camarero encargado de la galería!"*.
    *   **Técnicamente:** Analiza la URL `url.com/controlador/metodo` y decide qué código ejecutar.

3.  **El Controlador (El Camarero - `app/controllers/`):**
    *   Es el jefe de la operación. Recibe la orden del Router.
    *   Dice: *"Vale, el cliente quiere ver fotos. Voy a pedirlas a la cocina (Modelo) y luego las pondré bonitas en el plato (Vista)"*.
    *   **Técnicamente:** Es el intermediario. Pide datos y carga la página.

4.  **El Modelo (La Cocina/Almacén - `app/models/`):**
    *   Aquí están los ingredientes (Datos). El cocinero sabe dónde está todo en la despensa (Base de Datos).
    *   El Controlador le dice: *"Dame las últimas 5 fotos"*. El Modelo hace la consulta SQL (`SELECT * FROM images...`) y se las devuelve.
    *   **Técnicamente:** Gestiona la lógica de datos y habla con MySQL.

5.  **La Vista (El Plato Presentado - `app/views/`):**
    *   Es lo que llega a la mesa. El Controlador coge los datos crudos del Modelo y los pone en una plantilla HTML bonita para que el usuario los vea.
    *   **Técnicamente:** Archivos HTML/PHP que muestran la interfaz.

---

## 3. Las "Tripas" del Proyecto (Tecnologías)

### 🐘 Backend (El motor - PHP)
*   **¿Por qué PHP?** Es el lenguaje del servidor. Procesa los formularios, guarda las fotos y decide qué mostrar.
*   **PDO:** Es la herramienta que uso para hablar con la base de datos de forma segura. Es como usar un traductor certificado en lugar de gritarle a la base de datos.
*   **GD Library:** Es la "herramienta de Photoshop" de PHP. La uso para pegar el sticker encima de tu foto.

### 🌐 Frontend (La cara - HTML/JS/CSS)
*   **Vanilla JS (JavaScript puro):** No usé librerías pesadas.
    *   **AJAX (Fetch):** Permite dar "Like" sin que la página se recargue. Es como levantar la mano para pedir algo sin tener que salir y volver a entrar al restaurante.
    *   **Webcam:** Uso código nativo del navegador para encender la cámara.
*   **Bootstrap 5:** Para que la web sea bonita y se adapte al móvil sin tener que escribir mil líneas de CSS.

### 🐳 Docker (El contenedor)
*   Imagina que mi proyecto es una casa amueblada. Docker me permite meter la casa entera en una caja mágica.
*   Tú te descargas la caja, la abres (`docker-compose up`) y la casa aparece montada exactamente igual que en mi ordenador. No tienes que instalar PHP ni MySQL por tu cuenta.

---

## 4. La Magia: ¿Cómo funciona el Editor de Fotos?

Esta es la parte más difícil. Si te preguntan "¿Cómo se guardan las fotos?", responde esto:

**Paso 1: En el navegador (Tu ordenador)**
1.  El navegador pide permiso para usar la cámara (`getUserMedia`).
2.  El video se muestra en una etiqueta `<video>`.
3.  Cuando pulsas "Capturar", copio lo que se ve en el video a un `<canvas>` (un lienzo digital invisible).
4.  Ese lienzo se convierte en un texto larguísimo (Base64) que representa la imagen.
5.  Envío ese texto y la posición de los stickers al servidor.

**Paso 2: En el servidor (PHP)**
1.  Recibo el texto Base64 y lo convierto de nuevo en un archivo de imagen.
2.  Cargo la imagen del sticker (que es un PNG transparente).
3.  Uso matemáticas para calcular dónde pegarlo (coordenadas X e Y).
4.  **Fusión:** Pego el sticker sobre la foto original.
5.  Guardo el resultado final en la carpeta `uploads/` y registro la foto en la base de datos.

---

## 5. Seguridad: Los 4 Jinetes del Apocalipsis (y cómo los vencí)

Si te preguntan "¿Es segura tu web?", di que te has protegido contra los 4 ataques más comunes:

1.  **Inyección SQL (El ataque del espía):**
    *   *El ataque:* Alguien escribe código en el login para engañar a la base de datos.
    *   *Mi defensa:* Uso **Sentencias Preparadas (PDO)**. Separo los datos del código. Es como si alguien intenta colar una orden en una carta, pero yo leo la carta como texto, no como órdenes.

2.  **XSS (El ataque del grafitero):**
    *   *El ataque:* Alguien pone un comentario con código JavaScript malicioso (`<script>alert('Hacked')</script>`).
    *   *Mi defensa:* Uso `htmlspecialchars()`. Convierte esos símbolos en texto inofensivo. El navegador lo muestra, pero no lo ejecuta.

3.  **CSRF (El ataque del impostor):**
    *   *El ataque:* Una web maliciosa intenta enviar un formulario en tu nombre sin que lo sepas.
    *   *Mi defensa:* **Tokens CSRF**. Cada vez que te doy un formulario, te doy un código secreto único. Si me envías el formulario sin ese código, sé que no fuiste tú (o que no lo hiciste desde mi web).

4.  **Contraseñas (El candado):**
    *   *El problema:* Guardar contraseñas tal cual ("123456") es peligroso.
    *   *Mi defensa:* Las guardo "hasheadas" (`password_hash`). Las convierto en un garabato ininteligible. Ni yo mismo puedo saber cuál es tu contraseña real, solo puedo comprobar si la que escribes coincide con el garabato.

---

## 6. Base de Datos: ¿Dónde guardo las cosas?

Tengo 4 cajones (tablas):

1.  **Users:** Datos de la gente (nombre, email, contraseña encriptada).
2.  **Images:** Las fotos (quién la subió y cómo se llama el archivo).
3.  **Likes:** Quién dio like a qué foto. (Tiene un truco: una "clave única" para que no puedas dar like 50 veces a la misma foto).
4.  **Comments:** Qué dijeron, quién lo dijo y en qué foto.

---

## 7. Anécdotas para brillar (Storytelling)

Si te preguntan si tuviste problemas, cuenta esto para parecer un experto que sabe resolver crisis:

*   **"El misterio de la doble foto":**
    *   *"Al principio, cuando hacía una foto, se guardaban dos veces. Me volví loco buscando el error. Resulta que tenía dos archivos JavaScript distintos escuchando el clic del mismo botón. Aprendí a depurar el código y a controlar mejor los eventos del navegador."*

*   **"El token que caducaba al instante":**
    *   *"El enlace de 'recuperar contraseña' me decía siempre que había expirado. Descubrí que PHP (mi código) y MySQL (la base de datos) tenían horas diferentes (zonas horarias). Lo arreglé haciendo que la base de datos se encargue de todos los cálculos de tiempo para que sea consistente."*

---

## 8. Resumen en 3 frases
1.  Es una arquitectura MVC hecha a mano, sin magia de frameworks.
2.  Es segura, protegiendo datos y usuarios contra ataques comunes.
3.  Es funcional, combinando tecnologías de frontend (Webcam/Canvas) y backend (PHP GD) para crear algo divertido.
