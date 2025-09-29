<?php
/**
 * Script para crear stickers adicionales con URLs alternativas
 */

$stickersPath = __DIR__ . '/../public/stickers/';
$tempPath = __DIR__ . '/../temp/';

if (!file_exists($tempPath)) {
    mkdir($tempPath, 0755, true);
}

// URLs alternativas para stickers adicionales
$additionalStickers = [
    // Comida adicional
    'pizza' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=200&h=200&fit=crop',
    'taco' => 'https://images.unsplash.com/photo-1565299585323-38174c7a7208?w=200&h=200&fit=crop',
    'sushi' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=200&h=200&fit=crop',
    
    // Animales adicionales
    'rabbit' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?w=200&h=200&fit=crop',
    'fox' => 'https://images.unsplash.com/photo-1474511320723-9a56873867b5?w=200&h=200&fit=crop',
    
    // Objetos adicionales
    'music_note' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=200&h=200&fit=crop',
    'trophy' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=200&h=200&fit=crop',
];

// Reutilizar las funciones del script anterior
function createCircularSticker($imagePath, $outputPath, $size = 150) {
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
    
    $output = imagecreatetruecolor($size, $size);
    imagealphablending($output, false);
    imagesavealpha($output, true);
    
    $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
    imagefill($output, 0, 0, $transparent);
    
    $cropSize = min($originalWidth, $originalHeight);
    $cropX = ($originalWidth - $cropSize) / 2;
    $cropY = ($originalHeight - $cropSize) / 2;
    
    $mask = imagecreatetruecolor($size, $size);
    $maskBg = imagecolorallocate($mask, 0, 0, 0);
    $maskFg = imagecolorallocate($mask, 255, 255, 255);
    imagefill($mask, 0, 0, $maskBg);
    
    imagefilledellipse($mask, $size/2, $size/2, $size-4, $size-4, $maskFg);
    
    $resized = imagecreatetruecolor($size, $size);
    imagecopyresampled($resized, $source, 0, 0, $cropX, $cropY, $size, $size, $cropSize, $cropSize);
    
    for ($x = 0; $x < $size; $x++) {
        for ($y = 0; $y < $size; $y++) {
            $maskPixel = imagecolorat($mask, $x, $y);
            if (($maskPixel & 0xFF) > 0) {
                $sourcePixel = imagecolorat($resized, $x, $y);
                imagesetpixel($output, $x, $y, $sourcePixel);
            }
        }
    }
    
    $success = imagepng($output, $outputPath, 9);
    
    imagedestroy($source);
    imagedestroy($output);
    imagedestroy($mask);
    imagedestroy($resized);
    
    return $success;
}

function createRoundedSticker($imagePath, $outputPath, $size = 150, $radius = 25) {
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
    
    $output = imagecreatetruecolor($size, $size);
    imagealphablending($output, false);
    imagesavealpha($output, true);
    
    $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
    imagefill($output, 0, 0, $transparent);
    
    $cropSize = min($originalWidth, $originalHeight);
    $cropX = ($originalWidth - $cropSize) / 2;
    $cropY = ($originalHeight - $cropSize) / 2;
    
    $resized = imagecreatetruecolor($size, $size);
    imagecopyresampled($resized, $source, 0, 0, $cropX, $cropY, $size, $size, $cropSize, $cropSize);
    
    imagealphablending($output, true);
    
    for ($x = 0; $x < $size; $x++) {
        for ($y = 0; $y < $size; $y++) {
            $pixel = imagecolorat($resized, $x, $y);
            $inArea = true;
            
            if ($x < $radius && $y < $radius) {
                $distance = sqrt(pow($x - $radius, 2) + pow($y - $radius, 2));
                $inArea = $distance <= $radius;
            }
            elseif ($x >= $size - $radius && $y < $radius) {
                $distance = sqrt(pow($x - ($size - $radius), 2) + pow($y - $radius, 2));
                $inArea = $distance <= $radius;
            }
            elseif ($x < $radius && $y >= $size - $radius) {
                $distance = sqrt(pow($x - $radius, 2) + pow($y - ($size - $radius), 2));
                $inArea = $distance <= $radius;
            }
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

echo "🎨 Creando stickers adicionales...\n";
echo "=================================\n\n";

$successCount = 0;
$totalCount = count($additionalStickers);

foreach ($additionalStickers as $name => $url) {
    echo "📥 Descargando: $name... ";
    
    $tempFile = $tempPath . $name . '.jpg';
    
    // Usar cURL para mayor compatibilidad
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Camagru/1.0)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($imageData === false || $httpCode !== 200) {
        echo "❌ Error al descargar (HTTP: $httpCode)\n";
        continue;
    }
    
    file_put_contents($tempFile, $imageData);
    
    $circularOutput = $stickersPath . $name . '_circle.png';
    $roundedOutput = $stickersPath . $name . '_rounded.png';
    
    $circularSuccess = createCircularSticker($tempFile, $circularOutput, 150);
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
    
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    usleep(500000);
}

if (is_dir($tempPath)) {
    rmdir($tempPath);
}

echo "\n🎉 Stickers adicionales completados!\n";
echo "📊 Nuevos stickers: $successCount/$totalCount\n\n";

// Contar todos los stickers
$allStickers = glob($stickersPath . '*.png');
$totalStickers = count($allStickers);

echo "📋 Total de stickers disponibles: $totalStickers\n";
echo "📁 Ubicación: $stickersPath\n";
echo "\n💡 ¡Ahora tienes una gran variedad de stickers para el editor!\n";
?>