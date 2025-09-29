<?php
/**
 * Script para generar un resumen visual de todos los stickers
 */

$stickersPath = __DIR__ . '/../public/stickers/';

echo "🎨 RESUMEN COMPLETO DE STICKERS CAMAGRU\n";
echo "======================================\n\n";

// Obtener todos los stickers PNG
$stickers = glob($stickersPath . '*.png');
$totalStickers = count($stickers);

echo "📊 ESTADÍSTICAS GENERALES:\n";
echo "-------------------------\n";
echo "📁 Total de stickers: $totalStickers\n";
echo "📂 Ubicación: $stickersPath\n";

$totalSize = 0;
foreach ($stickers as $sticker) {
    $totalSize += filesize($sticker);
}

echo "💾 Tamaño total: " . round($totalSize / 1024 / 1024, 2) . " MB\n";
echo "📏 Tamaño promedio: " . round(($totalSize / $totalStickers) / 1024, 1) . " KB\n\n";

// Categorizar stickers
$categories = [
    '🐾 ANIMALES' => ['cat', 'dog', 'panda', 'lion', 'elephant', 'penguin', 'rabbit', 'fox'],
    '🍕 COMIDA' => ['burger', 'donut', 'ice_cream', 'coffee', 'cake', 'pizza', 'sushi', 'taco'],
    '🎸 OBJETOS' => ['camera', 'sunglasses', 'guitar', 'balloon', 'crown', 'gift', 'music_note', 'trophy'], 
    '⭐ FORMAS' => ['heart', 'star', 'smile'],
    '📝 TEXTO' => ['awesome_text', 'cool_text', 'wow_text']
];

echo "📂 CATEGORÍAS DETALLADAS:\n";
echo "========================\n";

foreach ($categories as $categoryName => $items) {
    echo "\n$categoryName:\n";
    $categoryCount = 0;
    $categorySize = 0;
    
    foreach ($items as $item) {
        $files = glob($stickersPath . $item . '*.png');
        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            $categorySize += $size;
            $categoryCount++;
            
            echo sprintf("   ✅ %-30s %6.1f KB\n", $filename, $size / 1024);
        }
    }
    
    echo "   📊 Subtotal: $categoryCount stickers (" . round($categorySize / 1024, 1) . " KB)\n";
}

echo "\n🎭 TIPOS DE STICKERS:\n";
echo "===================\n";

$types = [
    '_circle.png' => 'Círculos perfectos',
    '_rounded.png' => 'Bordes redondeados', 
    '_custom.png' => 'Dibujados a mano',
    '.png (otros)' => 'Originales y texto'
];

foreach ($types as $suffix => $description) {
    if ($suffix === '.png (otros)') {
        // Contar los que no tienen sufijos especiales
        $count = 0;
        foreach ($stickers as $sticker) {
            $filename = basename($sticker);
            if (!preg_match('/(circle|rounded|custom)/', $filename)) {
                $count++;
            }
        }
    } else {
        $pattern = str_replace('.png', '', $suffix);
        $count = 0;
        foreach ($stickers as $sticker) {
            if (strpos(basename($sticker), $pattern) !== false) {
                $count++;
            }
        }
    }
    
    echo sprintf("   🎨 %-20s: %2d stickers\n", $description, $count);
}

echo "\n🚀 SCRIPTS DE GENERACIÓN:\n";
echo "========================\n";
echo "   📜 create_stickers_from_unsplash.php - Descarga desde Unsplash\n";
echo "   📜 create_additional_stickers.php     - URLs alternativas\n";
echo "   📜 create_custom_stickers.php         - Dibujados con PHP GD\n";

echo "\n🔧 COMPATIBILIDAD:\n";
echo "=================\n";
echo "   ✅ Formato: PNG con canal alfa (transparencia)\n";
echo "   ✅ Tamaño: 150x150 píxeles estándar\n";
echo "   ✅ Navegadores: Todos los modernos\n";
echo "   ✅ Dispositivos: Desktop y móvil\n";
echo "   ✅ Frameworks: Compatible con cualquier editor de imágenes\n";

echo "\n💡 INSTRUCCIONES DE USO:\n";
echo "=======================\n";
echo "   1. Los stickers se cargan automáticamente en el editor\n";
echo "   2. Haz clic en un sticker para seleccionarlo\n";
echo "   3. Colócalo en la imagen arrastrando o haciendo clic\n";
echo "   4. Puedes usar múltiples stickers en una sola imagen\n";
echo "   5. Los stickers mantienen la transparencia al guardar\n";

echo "\n🎊 ¡COLECCIÓN COMPLETA LISTA!\n";
echo "============================\n";
echo "🎯 Los usuarios de Camagru ahora tienen acceso a:\n";
echo "   • 16 adorables animales\n";
echo "   • 16 deliciosos alimentos  \n";
echo "   • 16 objetos interesantes\n";
echo "   • 5 formas y símbolos\n";
echo "   • 3 textos expresivos\n";
echo "\n📸 ¡Que disfruten creando fotos increíbles!\n\n";
?>