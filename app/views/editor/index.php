<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <h2 class="text-gradient mb-4">
            <i class="bi bi-camera2 me-2"></i>Editor de Fotos
        </h2>
    </div>
</div>

<div class="row">
    <!-- Columna izquierda - Webcam y captura -->
    <div class="col-lg-8">
        <div class="card shadow-custom mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-camera-video me-2"></i>Cámara Web
                </h5>
                
                <div class="webcam-container mb-3 position-relative">
                    <video id="webcam-video" autoplay muted style="width: 100%; max-width: 640px; height: auto; background: #000; border-radius: 10px;"></video>
                    <canvas id="webcam-canvas" style="display: none;"></canvas>
                    
                    <!-- Overlay para preview de stickers -->
                    <div class="preview-overlay" id="preview-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                        <!-- Los stickers de preview se mostrarán aquí -->
                    </div>
                    
                    <!-- Canvas interactivo para posicionamiento -->
                    <canvas id="interactive-canvas" 
                            style="position: absolute; top: 0; left: 0; cursor: crosshair; border-radius: 10px;"
                            width="640" height="480">
                    </canvas>
                </div>

                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button id="capture-btn" class="btn btn-primary capture-btn" disabled>
                        <i class="bi bi-camera me-2"></i>Capturar Foto
                    </button>
                    <button id="process-btn" class="btn btn-success process-btn" style="display: none;" disabled>
                        <i class="bi bi-check-circle me-2"></i>Procesar Imagen
                    </button>
                    <label for="image-upload" class="btn btn-outline-secondary" id="upload-label">
                        <i class="bi bi-upload me-2"></i>Subir Archivo
                    </label>
                    <input type="file" id="image-upload" name="image" accept="image/*" style="display: none;">
                    <button id="clear-stickers" class="btn btn-outline-warning" style="display: none;">
                        <i class="bi bi-trash me-2"></i>Limpiar
                    </button>
                </div>
                
                <!-- Instrucciones -->
                <div class="alert alert-info mt-3" id="instructions" style="display: none;">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>¡Sticker seleccionado!</strong> Haz clic en la imagen para posicionarlo. Puedes agregar varios stickers y arrastrarlos para moverlos.
                </div>
                
                <!-- Token CSRF oculto para uploads -->
                <input type="hidden" id="csrf-token" value="<?= $csrf_token ?>">
            </div>
        </div>
    </div>

    <!-- Columna derecha - Stickers y galería -->
    <div class="col-lg-4">
        <!-- Panel de stickers -->
        <div class="card shadow-custom mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-emoji-smile me-2"></i>Stickers
                    <small class="text-muted">(selecciona uno)</small>
                </h5>
                
                <div class="stickers-panel">
                    <?php if (!empty($stickers)): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($stickers as $sticker): ?>
                                <img src="<?= $sticker['path'] ?>" 
                                     alt="<?= $sticker['name'] ?>"
                                     class="sticker-option" 
                                     data-sticker="<?= $sticker['filename'] ?>"
                                     title="<?= $sticker['name'] ?>">
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay stickers disponibles.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Galería personal -->
        <div class="card shadow-custom">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-images me-2"></i>Mis Fotos
                    <small class="text-muted">(<?= count($userImages) ?>)</small>
                </h5>
                
                <div id="user-gallery" class="user-images-grid">
                    <?php if (!empty($userImages)): ?>
                        <?php foreach ($userImages as $image): ?>
                            <div class="user-image-card">
                                <img src="/uploads/<?= htmlspecialchars($image['filename']) ?>" 
                                     alt="Mi imagen"
                                     loading="lazy">
                                <div class="user-image-overlay">
                                    <button class="btn btn-danger btn-sm delete-image-btn" 
                                            data-image-id="<?= $image['id'] ?>"
                                            title="Eliminar imagen">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-camera2 display-6"></i>
                            <p class="mt-2">Aún no tienes fotos.<br>¡Captura tu primera imagen!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir JavaScript específico del editor -->
<script src="/assets/js/editor.js"></script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>