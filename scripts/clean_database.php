<?php
require_once __DIR__ . '/../app/config/Config.php';
require_once __DIR__ . '/../app/core/Database.php';

// Solo permitir en modo desarrollo
if (!Config::isDevMode()) {
    die("Este script solo funciona en modo desarrollo");
}

try {
    $db = Database::getInstance();
    
    echo "🧹 Limpiando base de datos...\n\n";
    
    // Eliminar todas las imágenes y datos relacionados
    $db->execute("DELETE FROM likes");
    echo "✅ Likes eliminados\n";
    
    $db->execute("DELETE FROM comments");
    echo "✅ Comentarios eliminados\n";
    
    $db->execute("DELETE FROM images");
    echo "✅ Imágenes eliminadas\n";
    
    $db->execute("DELETE FROM users");
    echo "✅ Usuarios eliminados\n";
    
    // Eliminar archivos de imágenes físicas
    $uploadsDir = __DIR__ . '/../public/uploads/';
    if (is_dir($uploadsDir)) {
        $files = glob($uploadsDir . '*');
        foreach($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✅ Archivos de imágenes eliminados\n";
    }
    
    echo "\n🎉 Base de datos limpiada exitosamente!\n";
    echo "Ahora puedes registrarte nuevamente en: http://localhost:8080/auth/register\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>