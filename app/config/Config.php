<?php

// Cargar variables de entorno
require_once __DIR__ . '/../core/EnvLoader.php';
EnvLoader::load(__DIR__ . '/../../.env');

class Config {
    // Configuración de base de datos
    public static function getDbHost() { return EnvLoader::get('DB_HOST', 'db'); }
    public static function getDbName() { return EnvLoader::get('DB_NAME', 'camagru'); }
    public static function getDbUser() { return EnvLoader::get('DB_USER', 'camagru_user'); }
    public static function getDbPass() { return EnvLoader::get('DB_PASS', 'camagru_pass'); }
    public static function getDbCharset() { return EnvLoader::get('DB_CHARSET', 'utf8mb4'); }

    // Configuración de la aplicación
    public static function getAppName() { return EnvLoader::get('APP_NAME', 'Camagru'); }
    public static function getAppUrl() { return EnvLoader::get('APP_URL', 'http://localhost:8080'); }
    
    // Modo desarrollo
    public static function isDevMode() { return EnvLoader::get('DEV_MODE', 'true') === 'true'; }
    
    // Configuración de email
    public static function getSmtpHost() { return EnvLoader::get('SMTP_HOST', 'smtp.gmail.com'); }
    public static function getSmtpPort() { return (int)EnvLoader::get('SMTP_PORT', '587'); }
    public static function getSmtpUsername() { return EnvLoader::get('SMTP_USERNAME', ''); }
    public static function getSmtpPassword() { return EnvLoader::get('SMTP_PASSWORD', ''); }
    public static function getFromEmail() { return EnvLoader::get('FROM_EMAIL', ''); }
    public static function getFromName() { return EnvLoader::get('FROM_NAME', 'Camagru'); }
    public static function isEmailEnabled() { return EnvLoader::get('ENABLE_EMAIL', 'false') === 'true'; }

    // Configuración de seguridad
    public static function getCsrfTokenName() { return EnvLoader::get('CSRF_TOKEN_NAME', 'csrf_token'); }
    public static function getSessionName() { return EnvLoader::get('SESSION_NAME', 'camagru_session'); }
    public static function getSecretKey() { return EnvLoader::get('SECRET_KEY', 'change-this-key-in-production'); }
    
    // Configuración de imágenes
    public static function getMaxImageSize() { return (int)EnvLoader::get('MAX_IMAGE_SIZE', 5242880); }
    public static function getAllowedImageTypes() { 
        $types = EnvLoader::get('ALLOWED_IMAGE_TYPES', 'image/jpeg,image/png,image/gif');
        return explode(',', $types);
    }
    public static function getImageWidth() { return (int)EnvLoader::get('IMAGE_WIDTH', 640); }
    public static function getImageHeight() { return (int)EnvLoader::get('IMAGE_HEIGHT', 480); }
    
    // Rutas de archivos
    public static function getUploadPath() { 
        $path = EnvLoader::get('UPLOAD_PATH', 'public/uploads/');
        return __DIR__ . '/../../' . trim($path, '/') . '/';
    }
    public static function getStickersPath() { 
        $path = EnvLoader::get('STICKERS_PATH', 'public/stickers/');
        return __DIR__ . '/../../' . trim($path, '/') . '/';
    }
    
    // Configuración de paginación
    public static function getImagesPerPage() { return (int)EnvLoader::get('IMAGES_PER_PAGE', 5); }
    
    public static function getDSN() {
        return 'mysql:host=' . self::getDbHost() . ';dbname=' . self::getDbName() . ';charset=' . self::getDbCharset();
    }
}
