<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-gradient">
                <i class="bi bi-images me-2"></i>Mis Imágenes
                <small class="text-muted">(<?= count($userImages) ?> fotos)</small>
            </h2>
            <a href="/editor" class="btn btn-primary">
                <i class="bi bi-camera2 me-2"></i>Crear Nueva
            </a>
        </div>
    </div>
</div>

<?php if (!empty($userImages)): ?>
    <div class="row" id="user-images-grid">
        <?php foreach ($userImages as $image): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4 user-image-col" data-image-id="<?= $image['id'] ?>">
                <div class="card image-card user-image-card">
                    <div class="position-relative">
                        <img src="/uploads/<?= htmlspecialchars($image['filename']) ?>" 
                             class="card-img-top" 
                             alt="Mi foto"
                             style="height: 250px; object-fit: contain; background: #f8f9fa;">
                        <div class="position-absolute top-0 end-0 p-2">
                            <button class="btn btn-sm btn-danger delete-image-btn" 
                                    data-image-id="<?= $image['id'] ?>"
                                    title="Eliminar imagen">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($image['created_at'])) ?>
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                <?= date('H:i', strtotime($image['created_at'])) ?>
                            </small>
                        </div>
                        
                        <?php if (isset($image['original_filename']) && $image['original_filename']): ?>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-file-earmark me-1"></i>
                                    <?= htmlspecialchars($image['original_filename']) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="card-title">
                        <i class="bi bi-info-circle me-2"></i>Gestión de Imágenes
                    </h6>
                    <p class="text-muted mb-3">
                        Puedes eliminar cualquiera de tus imágenes haciendo clic en el botón de eliminar.
                        Una vez eliminadas, no se pueden recuperar.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="/editor" class="btn btn-primary">
                            <i class="bi bi-camera2 me-2"></i>Crear Nueva Foto
                        </a>
                        <a href="/profile" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver al Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-camera2 display-1 text-muted"></i>
                    <h4 class="mt-4 text-muted">No tienes imágenes todavía</h4>
                    <p class="text-muted mb-4">
                        ¡Ve al editor y crea tu primera foto con stickers únicos!
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="/editor" class="btn btn-primary btn-lg">
                            <i class="bi bi-camera2 me-2"></i>Crear mi Primera Foto
                        </a>
                        <a href="/profile" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver al Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>