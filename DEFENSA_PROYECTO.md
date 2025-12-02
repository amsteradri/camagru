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

Imagina que tu aplicación es un **Restaurante de Lujo**. He usado el patrón **MVC (Modelo-Vista-Controlador)** para que cada parte del código tenga una única responsabilidad. Así, si quiero cambiar el menú, no tengo que reformar la cocina entera.

### 🧠 Desglose Detallado (Analogía vs Realidad):

#### 1. El Cliente (El Navegador/Usuario)
*   **Analogía:** Es la persona hambrienta que entra y se sienta.
*   **Acción:** Escribe `http://localhost/gallery` en su navegador.
*   **Lo que no ve:** No sabe si hay PHP, Python o magia detrás. Solo quiere su "plato" (la página web).

#### 2. El Router (El Recepcionista - `app/core/Router.php`)
*   **Analogía:** Está en la puerta. Recibe a TODOS los clientes. No cocina, no sirve mesas, solo organiza.
*   **Su trabajo:**
    1.  Mira la URL: `/gallery/index`.
    2.  Dice: *"Ajá, buscas al `GalleryController` y quieres ejecutar la acción `index`"*.
    3.  Si pides algo que no existe (`/patata`), te manda al `ErrorController` (la mesa de quejas).
*   **Código Real:** Usa `explode('/', $url)` para separar la dirección y `call_user_func_array` para despertar al controlador adecuado.

#### 3. El Controlador (El Camarero Jefe - `app/controllers/`)
*   **Analogía:** Es el que coordina todo. Toma la nota del Recepcionista y se pone a trabajar.
*   **Su trabajo:**
    1.  Recibe el encargo: "Mostrar la galería".
    2.  Piensa: *"Para esto necesito fotos"*. -> **Llama al Modelo**.
    3.  Recibe las fotos de la cocina.
    4.  Piensa: *"Ahora necesito ponerlas bonitas"*. -> **Llama a la Vista**.
*   **Técnicamente:** Es una clase PHP (ej: `GalleryController`) que extiende de la clase base `Controller`.

#### 4. El Modelo (La Cocina y Despensa - `app/models/`)
*   **Analogía:** Aquí están los cocineros y los ingredientes. Es el único lugar donde se tocan los alimentos crudos (Datos).
*   **Su trabajo:**
    1.  El Camarero (Controlador) grita: *"¡Marchando 5 fotos recientes!"*.
    2.  El Cocinero (Modelo `Image`) abre la nevera (Base de Datos MySQL).
    3.  Ejecuta la consulta SQL: `SELECT * FROM images ORDER BY date DESC LIMIT 5`.
    4.  Devuelve los datos "crudos" (un array de información) al Camarero.
*   **Regla de Oro:** El Modelo NUNCA habla con la Vista. La cocina no sale al comedor.

#### 5. La Vista (El Emplatado - `app/views/`)
*   **Analogía:** Es la presentación final del plato.
*   **Su trabajo:**
    1.  Recibe los datos del Camarero.
    2.  Los mezcla con HTML y CSS.
    3.  No piensa, solo muestra. No hace cálculos ni consultas a la base de datos. Solo hace bucles (`foreach`) para mostrar las fotos que le han dado.
*   **Resultado:** El HTML final que ve el usuario en su navegador.

---

### 🔄 Ejemplo de Flujo: ¿Qué pasa cuando das "Login"?

Para que entiendas el viaje completo de un dato:

1.  **Usuario:** Rellena el formulario y pulsa "Entrar".
2.  **Router:** Ve que la URL es `/auth/login` (método POST). Llama a `AuthController`.
3.  **Controlador (`AuthController`):**
    *   Recibe los datos (`$_POST`).
    *   Llama al Modelo `User` -> `User::login($email, $password)`.
4.  **Modelo (`User`):**
    *   Busca en la BD: `SELECT * FROM users WHERE email = ...`.
    *   Comprueba la contraseña: `password_verify()`.
    *   Devuelve `true` o `false` al Controlador.
5.  **Controlador:**
    *   Si es `true`: Guarda la sesión (`$_SESSION['user']`) y redirige al `Home`.
    *   Si es `false`: Carga la **Vista** de Login otra vez, pero pasándole un mensaje de error ("Contraseña incorrecta").
6.  **Vista:** Muestra el formulario de nuevo con una alerta roja.

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

#### 📄 El archivo `docker-compose.yml` (El plano de obra)
Este archivo es el **Jefe de Obra**. Le dice a Docker qué "trabajadores" (servicios) necesita contratar y cómo deben comportarse.

1.  **`services:` (Los trabajadores)**
    *   **`web`:** Es tu servidor Apache+PHP (tu aplicación).
        *   `build: .`: Le dice "constrúyete usando las instrucciones del Dockerfile".
        *   `ports: "8080:80"`: Es un **túnel**. Si entras por el puerto 8080 de tu PC (`localhost:8080`), apareces mágicamente en el puerto 80 del contenedor.
        *   `volumes: .:/var/www/html`: Es un **espejo**. Lo que editas en tu carpeta de Windows se cambia al instante dentro del contenedor. Sin esto, tendrías que reiniciar el servidor con cada cambio de código.
        *   `depends_on: - db`: Le dice "Espera a que la base de datos esté lista antes de arrancar".
    *   **`db`:** Es la base de datos MySQL.
        *   `image: mysql:8.0`: Aquí no construimos nada, descargamos una imagen oficial de MySQL lista para usar.
        *   `environment`: Aquí le pasamos la contraseña (`rootpassword`) para que se configure sola al arrancar.
        *   `volumes`: Importante. Guarda los datos en un "disco virtual" (`db_data`) para que no se borren los usuarios si apagas el contenedor.
    *   **`phpmyadmin`:** Es una interfaz visual para ver la base de datos.
        *   Se conecta al servicio `db` usando las claves que le damos.

2.  **`networks:` (El WiFi privado)**
    *   Crea una red interna llamada `camagru_network`.
    *   Gracias a esto, el contenedor `web` puede conectarse a la base de datos usando el nombre `db` como si fuera una dirección web, sin necesidad de saber direcciones IP complicadas.

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

---

## 9. Mapa del Tesoro (Estructura de Carpetas)

Si te piden abrir un archivo, no entres en pánico. Aquí está qué es cada cosa:

*   `app/`: Todo el código PHP (Lógica).
    *   `config/`: Configuración de la base de datos.
    *   `controllers/`: Los "Camareros" (Admin, Auth, Gallery...).
    *   `core/`: El cerebro del framework (Router, Database, Model). **Aquí está la magia del MVC.**
    *   `models/`: Las clases que hablan con la BD (User, Image...).
    *   `views/`: Los archivos HTML/PHP (lo que se ve).
*   `public/`: Lo único que ve el navegador.
    *   `index.php`: La puerta de entrada.
    *   `assets/`: CSS, JS y las imágenes subidas (`uploads/`).
*   `docker/`: Configuración de los contenedores.

---

## 10. Accesos Rápidos (Chuleta de URLs y Claves)

Si te piden entrar a la base de datos para demostrar que los usuarios se guardan de verdad:

*   **Tu Web:** [http://localhost:8080](http://localhost:8080)
*   **phpMyAdmin (Gestor de BD):** [http://localhost:8081](http://localhost:8081)
    *   **Usuario:** `root`
    *   **Contraseña:** `rootpassword`
    *   (O también: Usuario `camagru_user` / Contraseña `camagru_pass`)

