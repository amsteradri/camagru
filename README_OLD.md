# � Camagru - Mini Instagram con Editor de Fotos

Un proyecto web completo inspirado en Instagram que permite a los usuarios capturar fotos, aplicar stickers, crear una galería y interactuar socialmente con **sistema de emails real funcionando**.

## 🚀 Instalación y Configuración

### Prerrequisitos
- Docker y Docker Compose
- Cuenta de Gmail con contraseña de aplicación
- Navegador moderno con soporte para WebRTC (para webcam)

### 1. Clonar y Configurar
```bash
git clone <tu-repo>
cd camagru
```

### 2. Configurar Email (Gmail SMTP Funcional) ✅
```bash
# Editar app/config/Config.php
const SMTP_USERNAME = 'tu-email@gmail.com';        # Tu Gmail
const SMTP_PASSWORD = 'abcd efgh ijkl mnop';       # Contraseña de app (16 dígitos)
const FROM_EMAIL = 'tu-email@gmail.com';           # Mismo email
const ENABLE_EMAIL = true;                         # Emails funcionando al 100%
```

#### 📝 Obtener Contraseña de Aplicación Gmail:
1. Ve a [Google Account](https://myaccount.google.com/)
2. **Seguridad** → **Verificación en 2 pasos** (debe estar activada)
3. **Contraseñas de aplicaciones** → Generar nueva para "Correo"
4. Copia los 16 caracteres generados (ej: "abcd efgh ijkl mnop")

### 3. Levantar el Proyecto
```bash
# Construcción completa con stickers PNG y configuración optimizada
docker-compose up --build -d
```

### 4. Acceder a la Aplicación
- **Aplicación:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081 (camagru_user / camagru_pass)
- **Panel Admin:** http://localhost:8080/admin (solo desarrollo)

### � Sistema de Autenticación COMPLETO
- ✅ Registro con verificación por **email real** (Gmail SMTP)
- ✅ Login/logout seguro con tokens
- ✅ Recuperación de contraseña por email
- ✅ Perfil de usuario editable
- ✅ Notificaciones por email configurables

### 📷 Editor de Fotos Avanzado
- ✅ Recuperación de contraseña vía email
- ✅ Edición de perfil (username, email, password)
- ✅ Protección contra CSRF, XSS y SQL Injection
- ✅ Hash seguro de contraseñas

### 📸 Editor de Fotos
- ✅ Captura desde webcam en tiempo real
- ✅ Subida de imágenes desde PC (alternativa sin webcam)
- ✅ Selección y aplicación de stickers/superposiciones
- ✅ Procesamiento de imágenes en servidor (PHP GD)
- ✅ Botón de captura inactivo hasta seleccionar sticker
- ✅ Galería personal con miniaturas
- ✅ Eliminación de imágenes propias

### 🌍 Galería Pública
- ✅ Visualización de todas las imágenes públicas
- ✅ Ordenamiento por fecha (más recientes primero)
- ✅ Paginación (5 imágenes por página)
- ✅ Sistema de likes para usuarios logueados
- ✅ Sistema de comentarios
- ✅ Notificación por email al recibir comentarios
- ✅ Configuración de notificaciones por usuario

### 🛡️ Seguridad
- ✅ Validación y sanitización de todas las entradas
- ✅ Tokens CSRF en todos los formularios
- ✅ Protección XSS con htmlspecialchars
- ✅ Consultas preparadas (prevención SQL Injection)
- ✅ Hash seguro de contraseñas con password_hash()
- ✅ Validación de tipos de archivo
- ✅ Limitación de tamaño de archivos

### 🎨 Frontend Moderno
- ✅ Diseño responsive con Bootstrap 5
- ✅ JavaScript nativo (sin frameworks)
- ✅ Interfaz intuitiva y moderna
- ✅ Animaciones suaves
- ✅ Compatibilidad móvil
- ✅ Layout con header, main, footer

## 🏗️ Arquitectura Técnica

### Backend
- **PHP 8.1+** (solo librerías estándar)
- **MySQL 8.0** para persistencia
- **Patrón MVC** personalizado
- **PDO** para acceso a base de datos
- **GD/Imagick** para procesamiento de imágenes

### Frontend  
- **HTML5** semántico
- **CSS3** con variables personalizadas
- **JavaScript ES6+** nativo
- **Bootstrap 5** para responsive design
- **Bootstrap Icons** para iconografía

### DevOps
- **Docker & Docker Compose** para despliegue
- **Apache 2.4** como servidor web
- **phpMyAdmin** para administración de BD

## 🚀 Instalación y Despliegue

### Prerrequisitos
- Docker y Docker Compose instalados
- Git para clonar el repositorio

### 1. Clonar el repositorio
```bash
git clone <tu-repositorio-url>
cd camagru
```

### 2. Configurar variables de entorno (IMPORTANTE)
Edita el archivo `app/config/Config.php` y configura tu SMTP real para que funcionen los emails:

```php
// Configuración de email - CAMBIAR ESTOS VALORES
const SMTP_HOST = 'smtp.gmail.com';        // Tu servidor SMTP
const SMTP_USERNAME = 'tu-email@gmail.com'; // Tu email
const SMTP_PASSWORD = 'tu-app-password';    // Contraseña de aplicación
const FROM_EMAIL = 'noreply@tudominio.com'; // Email remitente
```

**Nota**: Para Gmail necesitas crear una "Contraseña de aplicación" en tu cuenta Google.

### 3. Desplegar con Docker
```bash
# Construir y levantar todos los servicios
docker-compose up -d --build

# Ver logs en tiempo real (opcional)
docker-compose logs -f
```

### 4. Verificar instalación
- **Aplicación principal**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Usuario: `root`
  - Contraseña: `rootpassword`

### 5. ¡Listo para usar! 🎉
La aplicación estará disponible en `http://localhost:8080`

**Credenciales de base de datos:**
- **Usuario BD**: `camagru_user` 
- **Contraseña BD**: `camagru_pass`
- **phpMyAdmin**: http://localhost:8081 (usuario: `root`, contraseña: `rootpassword`)

## 📱 Uso de la Aplicación

### Para Usuarios No Registrados
1. Visita la página principal para ver la galería pública
2. Regístrate con email válido
3. Confirma tu email desde el enlace enviado

### Para Usuarios Registrados
1. **Inicia sesión** con tus credenciales
2. **Crear fotos**: Ve al Editor → Permite acceso a webcam → Selecciona sticker → Captura
3. **Alternativa**: Sube imagen desde PC si no tienes webcam
4. **Interactúa**: Da likes y comenta en fotos de otros usuarios
5. **Gestiona**: Ve a "Mis Imágenes" para eliminar tus fotos

### Gestión de Perfil
- Edita username, email y contraseña
- Activa/desactiva notificaciones de comentarios
- Visualiza todas tus imágenes creadas

## 🗂️ Estructura del Proyecto

```
camagru/
├── app/
│   ├── config/          # Configuración de la app
│   ├── controllers/     # Controladores MVC
│   ├── models/         # Modelos de datos  
│   ├── views/          # Vistas HTML/PHP
│   └── core/           # Clases base (Router, Database, etc.)
├── public/
│   ├── index.php       # Punto de entrada
│   ├── assets/         # CSS, JS, imágenes
│   ├── uploads/        # Imágenes subidas por usuarios
│   └── stickers/       # Stickers disponibles
├── docker/             # Configuración Docker
├── sql/               # Scripts de base de datos
├── docker-compose.yml
└── README.md
```

## 🔧 Comandos Útiles de Docker

```bash
# Levantar servicios
docker-compose up -d

# Ver logs
docker-compose logs -f [servicio]

# Parar servicios  
docker-compose down

# Reconstruir tras cambios
docker-compose up -d --build

# Acceder al contenedor web
docker-compose exec web bash

# Backup de base de datos
docker-compose exec db mysqldump -u root -prootpassword camagru > backup.sql
```

## 🛠️ Desarrollo y Personalización

### Añadir Nuevos Stickers
1. Coloca archivos PNG/JPG/GIF en `public/stickers/`
2. Los stickers se detectan automáticamente
3. Recomendado: imágenes con transparencia (PNG)

### Modificar Estilos
- Edita `public/assets/css/style.css`
- Usa variables CSS personalizadas definidas en `:root`

### Extender Funcionalidad
- Añade nuevos controladores en `app/controllers/`
- Crea modelos correspondientes en `app/models/`
- Añade rutas en el sistema de routing automático

## 📧 Configuración de Email

Para que funcionen las notificaciones y verificaciones:

1. **Gmail/Google**:
   ```php
   const SMTP_HOST = 'smtp.gmail.com';
   const SMTP_PORT = 587;
   const SMTP_USERNAME = 'tu-email@gmail.com';
   const SMTP_PASSWORD = 'tu-app-password'; // No tu contraseña normal
   ```

2. **Otros proveedores**: Configura según tu proveedor SMTP

## ⚠️ Notas Importantes

- **Producción**: Cambia todas las contraseñas por defecto
- **SSL**: Configura HTTPS para producción  
- **Backup**: Haz backup regular de `uploads/` y base de datos
- **Logs**: Revisa logs de Apache/PHP para debugging
- **Permisos**: Asegura permisos correctos en `uploads/`

## 🐛 Troubleshooting

### Problema: "No se puede acceder a la webcam"
- **Solución**: Usa HTTPS o localhost, permite permisos en el navegador

### Problema: "Error de conexión a base de datos"  
- **Solución**: Verifica que el contenedor MySQL esté corriendo
```bash
docker-compose ps
docker-compose logs db
```

### Problema: "Imágenes no se muestran"
- **Solución**: Verifica permisos de la carpeta uploads/
```bash
docker-compose exec web chown -R www-data:www-data /var/www/html/public/uploads
```

### Problema: "Emails no se envían"
- **Solución**: Verifica configuración SMTP en Config.php

## 🏆 Cumplimiento de Requisitos

### ✅ Funcionalidades Obligatorias
- [x] Aplicación web MVC con PHP backend
- [x] Layout responsive (header, main, footer)
- [x] Registro con email válido y verificación
- [x] Login/Logout seguro  
- [x] Recuperación de contraseña
- [x] Edición de perfil
- [x] Seguridad (CSRF, XSS, SQLi protection)
- [x] Galería pública con paginación
- [x] Likes y comentarios para usuarios logueados
- [x] Notificaciones por email (configurables)
- [x] Editor de imágenes con webcam
- [x] Selección obligatoria de stickers
- [x] Procesamiento en servidor (PHP GD)
- [x] Alternativa de subida de archivos
- [x] Galería personal con miniaturas
- [x] Eliminación de imágenes propias

### ✅ Restricciones Técnicas
- [x] Backend: PHP puro (librerías estándar únicamente)
- [x] Frontend: HTML/CSS/JS nativo (sin frameworks JS)  
- [x] Framework CSS permitido: Bootstrap ✓
- [x] Despliegue: Docker/Docker-compose ✓

### 🚀 Extras Implementados
- [x] Interfaz moderna y responsive
- [x] Animaciones suaves
- [x] Manejo de errores robusto
- [x] Logging y debugging
- [x] Código bien estructurado y documentado
- [x] phpMyAdmin incluido para administración

## 📄 Licencia

Este proyecto fue desarrollado como parte del curriculum de 42. Libre para uso educativo.

---

**¿Problemas o sugerencias?** Abre un issue en el repositorio o contacta al desarrollador.

**¡Disfruta creando y compartiendo fotos con Camagru! 📸✨**
