<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-gradient">
                <i class="bi bi-person-circle me-2"></i>Mi Perfil
            </h2>
            <a href="/profile/edit" class="btn btn-outline-primary">
                <i class="bi bi-gear me-2"></i>Editar Perfil
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información del usuario -->
    <div class="col-lg-4">
        <div class="card shadow-custom mb-4">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-person-circle display-1 text-primary"></i>
                </div>
                <h4><?= htmlspecialchars($user['username']) ?></h4>
                <p class="text-muted mb-3">
                    <i class="bi bi-envelope me-1"></i>
                    <?= htmlspecialchars($user['email']) ?>
                </p>
                <div class="row text-center">
                    <div class="col">
                        <strong><?= count($userImages) ?></strong>
                        <div class="text-muted small">Fotos</div>
                    </div>
                    <div class="col">
                        <strong><?= date('M Y', strtotime($user['created_at'])) ?></strong>
                        <div class="text-muted small">Miembro desde</div>
                    </div>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <a href="/profile/edit" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Editar Perfil
                    </a>
                    <a href="/profile/images" class="btn btn-outline-secondary">
                        <i class="bi bi-images me-2"></i>Ver Todas Mis Fotos
                    </a>
                </div>
            </div>
        </div>

        <!-- Configuraciones -->
        <div class="card shadow-custom">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-gear me-2"></i>Configuración
                </h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Notificaciones por Email</strong>
                        <div class="text-muted small">
                            Recibir notificaciones cuando comenten tus fotos
                        </div>
                    </div>
                    <div>
                        <?php if ($user['email_notifications']): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Activado
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">
                                <i class="bi bi-x-circle me-1"></i>Desactivado
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Galería de imágenes -->
    <div class="col-lg-8">
        <div class="card shadow-custom">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-images me-2"></i>Mis Fotos Recientes
                    </h5>
                    <?php if (count($userImages) > 6): ?>
                        <a href="/profile/images" class="btn btn-sm btn-outline-primary">
                            Ver todas
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($userImages)): ?>
                    <div class="row">
                        <?php foreach (array_slice($userImages, 0, 6) as $image): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="position-relative">
                                        <img src="/uploads/<?= htmlspecialchars($image['filename']) ?>" 
                                             class="card-img-top" 
                                             alt="Mi foto"
                                             style="height: 200px; object-fit: contain; background: #f8f9fa;">
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <button class="btn btn-sm btn-danger delete-image-btn" 
                                                    data-image-id="<?= $image['id'] ?>"
                                                    title="Eliminar imagen">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-2">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($image['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($userImages) > 6): ?>
                        <div class="text-center">
                            <a href="/profile/images" class="btn btn-primary">
                                <i class="bi bi-images me-2"></i>Ver todas mis fotos (<?= count($userImages) ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-camera2 display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No tienes fotos todavía</h5>
                        <p class="text-muted">¡Ve al editor y crea tu primera foto!</p>
                        <a href="/editor" class="btn btn-primary">
                            <i class="bi bi-camera2 me-2"></i>Crear mi primera foto
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>