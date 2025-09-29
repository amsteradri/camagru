<?php
/**
 * Script para crear stickers dibujados con código (formas geométricas y emojis simples)
 */

$stickersPath = __DIR__ . '/../public/stickers/';

/**
 * Crear sticker de taco dibujado
 */
function createTacoSticker($outputPath, $size = 150) {
    $image = imagecreatetruecolor($size, $size);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    // Colores
    $yellow = imagecolorallocate($image, 255, 220, 100);     // Tortilla
    $green = imagecolorallocate($image, 100, 180, 100);      // Lechuga
    $red = imagecolorallocate($image, 220, 100, 100);        // Tomate
    $brown = imagecolorallocate($image, 150, 100, 80);       // Carne
    $white = imagecolorallocate($image, 255, 255, 255);      // Crema
    
    imagealphablending($image, true);
    
    // Dibujar tortilla (semicírculo)
    $center_x = $size / 2;
    $center_y = $size / 2 + 20;
    $radius = $size / 2 - 20;
    
    imagefilledarc($image, $center_x, $center_y, $radius * 2, $radius * 2, 0, 180, $yellow, IMG_ARC_PIE);
    
    // Dibujar relleno
    $fill_y = $center_y - 15;
    imagefilledrectangle($image, $center_x - $radius/2, $fill_y - 5, $center_x + $radius/2, $fill_y + 5, $brown);
    imagefilledrectangle($image, $center_x - $radius/2 + 10, $fill_y - 8, $center_x + $radius/2 - 10, $fill_y - 3, $green);
    
    // Puntos de tomate
    for ($i = 0; $i < 8; $i++) {
        $x = $center_x - $radius/2 + ($i * ($radius/4));
        $y = $fill_y + rand(-3, 3);
        imagefilledellipse($image, $x, $y, 6, 6, $red);
    }
    
    return imagepng($image, $outputPath, 9);
}

/**
 * Crear sticker de pizza dibujado
 */
function createPizzaSliceSticker($outputPath, $size = 150) {
    $image = imagecreatetruecolor($size, $size);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    // Colores
    $crust = imagecolorallocate($image, 200, 150, 100);      // Corteza
    $cheese = imagecolorallocate($image, 255, 220, 150);     // Queso
    $pepperoni = imagecolorallocate($image, 180, 50, 50);    // Pepperoni
    $sauce = imagecolorallocate($image, 200, 80, 80);        // Salsa
    
    imagealphablending($image, true);
    
    $center_x = $size / 2;
    $center_y = $size - 20;
    $width = $size - 40;
    $height = $size - 40;
    
    // Dibujar triángulo de pizza
    $points = array(
        $center_x, 20,                    // Punta
        $center_x - $width/2, $center_y,  // Esquina izquierda
        $center_x + $width/2, $center_y   // Esquina derecha
    );
    
    imagefilledpolygon($image, $points, 3, $crust);
    
    // Añadir queso (triángulo más pequeño)
    $cheese_points = array(
        $center_x, 30,
        $center_x - $width/2 + 10, $center_y - 10,
        $center_x + $width/2 - 10, $center_y - 10
    );
    
    imagefilledpolygon($image, $cheese_points, 3, $cheese);
    
    // Añadir pepperoni
    for ($i = 0; $i < 5; $i++) {
        $x = $center_x + rand(-40, 40);
        $y = 50 + ($i * 20) + rand(-10, 10);
        if ($y < $center_y - 15) {
            imagefilledellipse($image, $x, $y, 12, 12, $pepperoni);
        }
    }
    
    return imagepng($image, $outputPath, 9);
}

/**
 * Crear sticker de estrella brillante
 */
function createStarSticker($outputPath, $size = 150) {
    $image = imagecreatetruecolor($size, $size);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    $gold = imagecolorallocate($image, 255, 215, 0);
    $yellow = imagecolorallocate($image, 255, 255, 100);
    
    imagealphablending($image, true);
    
    $center_x = $size / 2;
    $center_y = $size / 2;
    $radius = $size / 2 - 20;
    
    // Dibujar estrella de 5 puntas
    $points = array();
    for ($i = 0; $i < 10; $i++) {
        $angle = ($i * M_PI) / 5;
        $r = ($i % 2 == 0) ? $radius : $radius / 2.5;
        $x = $center_x + $r * cos($angle - M_PI/2);
        $y = $center_y + $r * sin($angle - M_PI/2);
        $points[] = $x;
        $points[] = $y;
    }
    
    imagefilledpolygon($image, $points, 10, $gold);
    
    // Añadir brillo
    imagefilledellipse($image, $center_x - 15, $center_y - 15, 20, 20, $yellow);
    
    return imagepng($image, $outputPath, 9);
}

/**
 * Crear sticker de corazón grande
 */
function createBigHeartSticker($outputPath, $size = 150) {
    $image = imagecreatetruecolor($size, $size);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    $red = imagecolorallocate($image, 220, 20, 60);
    $pink = imagecolorallocate($image, 255, 105, 180);
    
    imagealphablending($image, true);
    
    $center_x = $size / 2;
    $center_y = $size / 2;
    
    // Dibujar corazón con dos círculos y un triángulo
    $radius = 30;
    
    // Círculo izquierdo
    imagefilledellipse($image, $center_x - $radius/2, $center_y - 10, $radius, $radius, $red);
    // Círculo derecho
    imagefilledellipse($image, $center_x + $radius/2, $center_y - 10, $radius, $radius, $red);
    
    // Triángulo inferior
    $points = array(
        $center_x - $radius, $center_y,
        $center_x + $radius, $center_y,
        $center_x, $center_y + $radius + 10
    );
    
    imagefilledpolygon($image, $points, 3, $red);
    
    // Brillo
    imagefilledellipse($image, $center_x - 8, $center_y - 15, 12, 8, $pink);
    
    return imagepng($image, $outputPath, 9);
}

echo "🎨 Creando stickers dibujados con código...\n";
echo "==========================================\n\n";

$customStickers = [
    'taco_custom' => 'createTacoSticker',
    'pizza_custom' => 'createPizzaSliceSticker', 
    'star_custom' => 'createStarSticker',
    'heart_custom' => 'createBigHeartSticker'
];

$successCount = 0;

foreach ($customStickers as $name => $function) {
    echo "🎨 Creando: $name... ";
    
    $outputPath = $stickersPath . $name . '.png';
    
    if (function_exists($function)) {
        $success = $function($outputPath, 150);
        
        if ($success) {
            echo "✅ Creado\n";
            $successCount++;
        } else {
            echo "❌ Error\n";
        }
    } else {
        echo "❌ Función no encontrada\n";
    }
}

echo "\n🎉 Stickers personalizados completados!\n";
echo "📊 Stickers creados: $successCount/" . count($customStickers) . "\n";

// Contar todos los stickers finales
$allStickers = glob($stickersPath . '*.png');
$totalStickers = count($allStickers);

echo "📋 Total de stickers disponibles: $totalStickers\n";
echo "\n💡 ¡Colección de stickers completada!\n";

// Crear un resumen por categorías
$categories = [
    'Animales' => ['cat', 'dog', 'panda', 'lion', 'elephant', 'penguin', 'rabbit', 'fox'],
    'Comida' => ['burger', 'donut', 'ice_cream', 'coffee', 'cake', 'pizza', 'sushi', 'taco'],
    'Objetos' => ['camera', 'sunglasses', 'guitar', 'balloon', 'crown', 'gift', 'music_note', 'trophy'],
    'Formas' => ['heart', 'star', 'smile'],
    'Texto' => ['awesome_text', 'cool_text', 'wow_text']
];

echo "\n📂 Categorías de stickers:\n";
foreach ($categories as $category => $items) {
    $count = 0;
    foreach ($items as $item) {
        $files = glob($stickersPath . $item . '*.png');
        $count += count($files);
    }
    echo "   $category: $count stickers\n";
}

echo "\n🎊 ¡Los stickers están listos para usar en Camagru!\n";
?>