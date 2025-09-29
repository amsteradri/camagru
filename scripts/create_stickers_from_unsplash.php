<?php
/**
 * Script para generar stickers PNG con transparencia desde Unsplash
 * Los stickers incluyen animales, objetos y comida
 */

// Configuración
$stickersPath = __DIR__ . '/../public/stickers/';
$tempPath = __DIR__ . '/../temp/';

// Crear directorios si no existen
if (!file_exists($stickersPath)) {
    mkdir($stickersPath, 0755, true);
}
if (!file_exists($tempPath)) {
    mkdir($tempPath, 0755, true);
}

// URLs de Unsplash para diferentes categorías (imágenes con fondos simples para fácil remoción)
$stickerSources = [
    // Animales
    'cat' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=200&h=200&fit=crop&crop=faces',
    'dog' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=200&h=200&fit=crop&crop=faces',
    'panda' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=200&h=200&fit=crop&crop=faces',
    'lion' => 'https://images.unsplash.com/photo-1546182990-dffeafbe841d?w=200&h=200&fit=crop&crop=faces',
    'elephant' => 'https://images.unsplash.com/photo-1564760055775-d63b17a55c44?w=200&h=200&fit=crop&crop=faces',
    'penguin' => 'https://images.unsplash.com/photo-1551986782-d0169b3f8fa7?w=200&h=200&fit=crop&crop=faces',
    
    // Comida
    'pizza' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=200&h=200&fit=crop&crop=center',
    'burger' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=200&h=200&fit=crop&crop=center',
    'donut' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?w=200&h=200&fit=crop&crop=center',
    'ice_cream' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=200&h=200&fit=crop&crop=center',
    'coffee' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=200&h=200&fit=crop&crop=center',
    'cake' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=200&h=200&fit=crop&crop=center',
    
    // Objetos
    'camera' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=200&h=200&fit=crop&crop=center',
    'sunglasses' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=200&h=200&fit=crop&crop=center',
    'guitar' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=200&h=200&fit=crop&crop=center',
    'balloon' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=200&h=200&fit=crop&crop=center',
    'crown' => 'https://images.unsplash.com/photo-1593476087123-36d1de271f08?w=200&h=200&fit=crop&crop=center',
    'gift' => 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=200&h=200&fit=crop&crop=center',
];

/**
 * Función para crear un círculo con transparencia y aplicar la imagen
 */
function createCircularSticker($imagePath, $outputPath, $size = 150) {
    // Crear imagen desde archivo
    $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($imagePath);
            break;
        case 'png':
            $source = imagecreatefrompng($imagePath);
            break;
        case 'gif':
            $source = imagecreatefromgif($imagePath);
            break;
        default:
            return false;
    }
    
    if (!$source) return false;
    
    $originalWidth = imagesx($source);
    $originalHeight = imagesy($source);
    
    // Crear imagen circular con transparencia
    $output = imagecreatetruecolor($size, $size);
    
    // Habilitar transparencia
    imagealphablending($output, false);
    imagesavealpha($output, true);
    
    // Fondo transparente
    $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
    imagefill($output, 0, 0, $transparent);
    
    // Calcular dimensiones para mantener aspecto
    $cropSize = min($originalWidth, $originalHeight);
    $cropX = ($originalWidth - $cropSize) / 2;
    $cropY = ($originalHeight - $cropSize) / 2;
    
    // Crear máscara circular
    $mask = imagecreatetruecolor($size, $size);
    $maskBg = imagecolorallocate($mask, 0, 0, 0);
    $maskFg = imagecolorallocate($mask, 255, 255, 255);
    imagefill($mask, 0, 0, $maskBg);
    
    // Dibujar círculo en la máscara
    imagefilledellipse($mask, $size/2, $size/2, $size-4, $size-4, $maskFg);
    
    // Redimensionar imagen fuente al tamaño del sticker
    $resized = imagecreatetruecolor($size, $size);
    imagecopyresampled($resized, $source, 0, 0, $cropX, $cropY, $size, $size, $cropSize, $cropSize);
    
    // Aplicar máscara circular
    for ($x = 0; $x < $size; $x++) {
        for ($y = 0; $y < $size; $y++) {
            $maskPixel = imagecolorat($mask, $x, $y);
            if (($maskPixel & 0xFF) > 0) { // Si el pixel de la máscara es blanco
                $sourcePixel = imagecolorat($resized, $x, $y);
                imagesetpixel($output, $x, $y, $sourcePixel);
            }
        }
    }
    
    // Guardar como PNG con transparencia
    $success = imagepng($output, $outputPath, 9);
    
    // Limpiar memoria
    imagedestroy($source);
    imagedestroy($output);
    imagedestroy($mask);
    imagedestroy($resized);
    
    return $success;
}

/**
 * Función para crear sticker con borde redondeado
 */
function createRoundedSticker($imagePath, $outputPath, $size = 150, $radius = 20) {
    $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($imagePath);
            break;
        case 'png':
            $source = imagecreatefrompng($imagePath);
            break;
        case 'gif':
            $source = imagecreatefromgif($imagePath);
            break;
        default:
            return false;
    }
    
    if (!$source) return false;
    
    $originalWidth = imagesx($source);
    $originalHeight = imagesy($source);
    
    // Crear imagen de salida con transparencia
    $output = imagecreatetruecolor($size, $size);
    imagealphablending($output, false);
    imagesavealpha($output, true);
    
    $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
    imagefill($output, 0, 0, $transparent);
    
    // Redimensionar imagen manteniendo proporción
    $cropSize = min($originalWidth, $originalHeight);
    $cropX = ($originalWidth - $cropSize) / 2;
    $cropY = ($originalHeight - $cropSize) / 2;
    
    $resized = imagecreatetruecolor($size, $size);
    imagecopyresampled($resized, $source, 0, 0, $cropX, $cropY, $size, $size, $cropSize, $cropSize);
    
    // Aplicar bordes redondeados
    imagealphablending($output, true);
    
    for ($x = 0; $x < $size; $x++) {
        for ($y = 0; $y < $size; $y++) {
            $pixel = imagecolorat($resized, $x, $y);
            
            // Verificar si está dentro del área redondeada
            $inArea = true;
            
            // Esquina superior izquierda
            if ($x < $radius && $y < $radius) {
                $distance = sqrt(pow($x - $radius, 2) + pow($y - $radius, 2));
                $inArea = $distance <= $radius;
            }
            // Esquina superior derecha
            elseif ($x >= $size - $radius && $y < $radius) {
                $distance = sqrt(pow($x - ($size - $radius), 2) + pow($y - $radius, 2));
                $inArea = $distance <= $radius;
            }
            // Esquina inferior izquierda
            elseif ($x < $radius && $y >= $size - $radius) {
                $distance = sqrt(pow($x - $radius, 2) + pow($y - ($size - $radius), 2));
                $inArea = $distance <= $radius;
            }
            // Esquina inferior derecha
            elseif ($x >= $size - $radius && $y >= $size - $radius) {
                $distance = sqrt(pow($x - ($size - $radius), 2) + pow($y - ($size - $radius), 2));
                $inArea = $distance <= $radius;
            }
            
            if ($inArea) {
                imagesetpixel($output, $x, $y, $pixel);
            }
        }
    }
    
    $success = imagepng($output, $outputPath, 9);
    
    imagedestroy($source);
    imagedestroy($output);
    imagedestroy($resized);
    
    return $success;
}

echo "🎨 Generador de Stickers PNG desde Unsplash\n";
echo "==========================================\n\n";

$successCount = 0;
$totalCount = count($stickerSources);

foreach ($stickerSources as $name => $url) {
    echo "📥 Descargando: $name... ";
    
    // Descargar imagen temporal
    $tempFile = $tempPath . $name . '.jpg';
    $imageData = file_get_contents($url);
    
    if ($imageData === false) {
        echo "❌ Error al descargar\n";
        continue;
    }
    
    file_put_contents($tempFile, $imageData);
    
    // Crear sticker circular
    $circularOutput = $stickersPath . $name . '_circle.png';
    $circularSuccess = createCircularSticker($tempFile, $circularOutput, 150);
    
    // Crear sticker con bordes redondeados
    $roundedOutput = $stickersPath . $name . '_rounded.png';
    $roundedSuccess = createRoundedSticker($tempFile, $roundedOutput, 150, 25);
    
    if ($circularSuccess && $roundedSuccess) {
        echo "✅ Creado (circular y redondeado)\n";
        $successCount++;
    } elseif ($circularSuccess || $roundedSuccess) {
        echo "⚠️ Parcialmente creado\n";
        $successCount++;
    } else {
        echo "❌ Error al procesar\n";
    }
    
    // Limpiar archivo temporal
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    // Pequeña pausa para no sobrecargar Unsplash
    usleep(500000); // 0.5 segundos
}

// Limpiar directorio temporal
if (is_dir($tempPath)) {
    rmdir($tempPath);
}

echo "\n🎉 Proceso completado!\n";
echo "📊 Stickers creados: $successCount/$totalCount\n";
echo "📁 Ubicación: $stickersPath\n\n";

// Listar todos los stickers creados
echo "📋 Stickers disponibles:\n";
$stickers = glob($stickersPath . '*.png');
foreach ($stickers as $sticker) {
    $filename = basename($sticker);
    $size = filesize($sticker);
    echo "   • $filename (" . round($size/1024, 1) . " KB)\n";
}

echo "\n💡 Los stickers están listos para usar en el editor!\n";
?>