<?php

// Configuración de errores y zona horaria
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Santiago');

// Compatibility para PHP < 8.0
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

// Iniciar sesión
session_start();

// Auto-carga de clases
spl_autoload_register(function ($className) {
    $paths = [
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/models/',
        __DIR__ . '/../app/core/',
        __DIR__ . '/../app/config/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Cargar configuración
require_once __DIR__ . '/../app/config/Config.php';

try {
    // Inicializar router
    $router = new Router();
} catch (Exception $e) {
    error_log("Application error: " . $e->getMessage());
    
    // Mostrar error 500 en producción
    http_response_code(500);
    echo "Error interno del servidor";
}
