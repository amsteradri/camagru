<?php
// Generar stickers PNG para el editor

$stickersPath = __DIR__ . '/../public/stickers/';

function createTextSticker($text, $filename, $size = 50) {
    global $stickersPath;
    
    $width = 200;
    $height = 80;
    
    $image = imagecreatetruecolor($width, $height);
    
    // Hacer fondo transparente
    imagealphablending($image, false);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    // Color del texto
    $color = imagecolorallocate($image, 255, 255, 255);
    
    // Agregar texto
    imagestring($image, 5, 20, 20, $text, $color);
    
    // Guardar como PNG
    imagepng($image, $stickersPath . $filename);
    imagedestroy($image);
}

function createShapeSticker($shape, $filename, $color = [255, 0, 0]) {
    global $stickersPath;
    
    $size = 100;
    $image = imagecreatetruecolor($size, $size);
    
    // Hacer fondo transparente
    imagealphablending($image, false);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    // Color de la forma
    $shapeColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);
    
    switch($shape) {
        case 'heart':
            // Dibujar un corazón simple (rectángulo + círculos)
            imagefilledrectangle($image, 40, 50, 60, 80, $shapeColor);
            imagefilledellipse($image, 35, 45, 30, 30, $shapeColor);
            imagefilledellipse($image, 65, 45, 30, 30, $shapeColor);
            break;
            
        case 'star':
            // Dibujar una estrella simple
            $points = [
                50, 10,  // top
                60, 30,  // top right
                80, 30,  // right
                65, 50,  // bottom right
                70, 70,  // bottom
                50, 55,  // bottom center
                30, 70,  // bottom left
                35, 50,  // left
                20, 30,  // top left
                40, 30   // top center
            ];
            imagefilledpolygon($image, $points, 5, $shapeColor);
            break;
            
        case 'circle':
            imagefilledellipse($image, 50, 50, 80, 80, $shapeColor);
            break;
    }
    
    imagepng($image, $stickersPath . $filename);
    imagedestroy($image);
}

echo "🎨 Generando stickers PNG...\n\n";

// Crear stickers de texto
createTextSticker("COOL!", "cool_text.png");
createTextSticker("WOW!", "wow_text.png");
createTextSticker("AWESOME", "awesome_text.png");

// Crear stickers de formas
createShapeSticker("heart", "heart.png", [255, 20, 60]);
createShapeSticker("star", "star.png", [255, 215, 0]);
createShapeSticker("circle", "smile.png", [255, 165, 0]);

echo "✅ Stickers generados:\n";
echo "- cool_text.png\n";
echo "- wow_text.png\n";
echo "- awesome_text.png\n";
echo "- heart.png\n";
echo "- star.png\n";
echo "- smile.png\n";

echo "\n🧹 Limpiando stickers SVG antiguos...\n";
$oldStickers = ['cool_text.svg', 'happy_face.svg', 'heart.svg', 'sad_face.svg', 'star.svg', 'wow_bubble.svg'];
foreach($oldStickers as $old) {
    $oldPath = $stickersPath . $old;
    if (file_exists($oldPath)) {
        unlink($oldPath);
        echo "- Eliminado: $old\n";
    }
}

echo "\n✅ ¡Stickers PNG listos para usar!\n";
?>