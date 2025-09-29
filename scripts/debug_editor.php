<?php
// Script de debugging para el editor
require_once __DIR__ . '/../app/config/Config.php';

echo "🔧 Debugging del Editor de Camagru\n\n";

// Verificar configuración
echo "📁 Configuración de paths:\n";
echo "- UPLOAD_PATH: " . Config::getUploadPath() . "\n";
echo "- STICKERS_PATH: " . Config::getStickersPath() . "\n";
echo "- MAX_IMAGE_SIZE: " . (Config::getMaxImageSize() / (1024*1024)) . "MB\n";
echo "- ALLOWED_TYPES: " . implode(', ', Config::getAllowedImageTypes()) . "\n\n";

// Verificar directorios
echo "📂 Estado de directorios:\n";
$uploadPath = Config::getUploadPath();
$stickersPath = Config::getStickersPath();

echo "- Uploads exists: " . (is_dir($uploadPath) ? "✅ Sí" : "❌ No") . "\n";
echo "- Uploads writable: " . (is_writable($uploadPath) ? "✅ Sí" : "❌ No") . "\n";
echo "- Stickers exists: " . (is_dir($stickersPath) ? "✅ Sí" : "❌ No") . "\n";
echo "- Stickers readable: " . (is_readable($stickersPath) ? "✅ Sí" : "❌ No") . "\n\n";

// Listar stickers disponibles
echo "🎨 Stickers disponibles:\n";
if (is_dir($stickersPath)) {
    $files = scandir($stickersPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $fullPath = $stickersPath . $file;
            echo "- $file (" . filesize($fullPath) . " bytes)\n";
        }
    }
} else {
    echo "❌ Directorio de stickers no encontrado\n";
}

echo "\n";

// Verificar extensiones PHP
echo "🔧 Extensiones PHP:\n";
echo "- GD: " . (extension_loaded('gd') ? "✅ Sí" : "❌ No") . "\n";
echo "- Upload files: " . (ini_get('file_uploads') ? "✅ Sí" : "❌ No") . "\n";
echo "- Max upload size: " . ini_get('upload_max_filesize') . "\n";
echo "- Max post size: " . ini_get('post_max_size') . "\n\n";

echo "✅ Debugging completado.\n";
?>