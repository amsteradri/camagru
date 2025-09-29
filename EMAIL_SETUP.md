# Configuración de Email para Camagru

## 📧 Cómo configurar Gmail para enviar emails

### Paso 1: Generar contraseña de aplicación
1. Ve a tu cuenta de Google: https://myaccount.google.com/
2. Ve a "Seguridad" → "Verificación en 2 pasos" (debe estar activada)
3. Busca "Contraseñas de aplicaciones"
4. Genera una nueva contraseña para "Correo"
5. Copia la contraseña generada (16 caracteres)

### Paso 2: Actualizar Config.php
Abre `app/config/Config.php` y cambia estas líneas:

```php
// Reemplaza con tu información real
const SMTP_USERNAME = 'tu-email@gmail.com';        // Tu email de Gmail
const SMTP_PASSWORD = 'abcd efgh ijkl mnop';       // La contraseña de app (16 caracteres)
const FROM_EMAIL = 'tu-email@gmail.com';           // Mismo email
```

### Paso 3: Ejemplo de configuración completa
```php
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587;
const SMTP_USERNAME = 'amsteradri@gmail.com';      // TU EMAIL AQUÍ
const SMTP_PASSWORD = 'abcd efgh ijkl mnop';       // TU CONTRASEÑA DE APP AQUÍ
const FROM_EMAIL = 'amsteradri@gmail.com';         // TU EMAIL AQUÍ
const FROM_NAME = 'Camagru';
const ENABLE_EMAIL = true;
```

### 🔧 Para probar rápidamente (opcional)
Si quieres probar sin configurar email real, puedes usar el simulador:

```php
// En Config.php - para desarrollo/testing
const ENABLE_EMAIL = false;    // No envía emails reales
const DEV_MODE = true;         // Permite login sin verificar
```

### 📝 Notas importantes:
- La contraseña de aplicación es diferente a tu contraseña de Gmail
- Necesitas tener verificación en 2 pasos activada
- El email se enviará desde tu cuenta de Gmail
- Los usuarios recibirán un link único para verificar su cuenta

¿Necesitas ayuda configurando las credenciales de Gmail?