<?php ob_start(); ?>

<div class="hero-section bg-primary text-white py-5 mb-5 rounded">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">
            <i class="bi bi-camera-fill me-3"></i>Camagru
        </h1>
        <p class="lead mb-4">Crea, edita y comparte tus fotos con stickers únicos</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div>
                <a href="/auth/register" class="btn btn-light btn-lg me-3">
                    <i class="bi bi-person-plus me-2"></i>Únete ahora
                </a>
                <a href="/auth/login" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </a>
            </div>
        <?php else: ?>
            <a href="/editor" class="btn btn-light btn-lg">
                <i class="bi bi-camera2 me-2"></i>Crear nueva foto
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Galería Pública -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-images me-2"></i>Galería Pública</h2>
            <small class="text-muted">
                <?= count($images) ?> imágenes en esta página
            </small>
        </div>
        
        <?php if (empty($images)): ?>
            <div class="text-center py-5">
                <i class="bi bi-image display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">No hay imágenes todavía</h3>
                <p class="text-muted">¡Sé el primero en subir una foto!</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/editor" class="btn btn-primary">
                        <i class="bi bi-camera2 me-2"></i>Crear mi primera foto
                    </a>
                <?php else: ?>
                    <a href="/auth/register" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Registrarse para empezar
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Grid de imágenes -->
            <div class="row" id="images-grid">
                <?php foreach ($images as $image): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card image-card">
                            <div class="card-img-top-wrapper position-relative">
                                <img src="/uploads/<?= htmlspecialchars($image['filename']) ?>" 
                                     class="card-img-top" 
                                     alt="Foto de <?= htmlspecialchars($image['username']) ?>"
                                     style="height: 400px; object-fit: contain; background: #f8f9fa;">
                            </div>
                            
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">
                                        <i class="bi bi-person-circle me-1"></i>
                                        <?= htmlspecialchars($image['username']) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($image['created_at'])) ?>
                                    </small>
                                </div>
                                
                                <!-- Botones de interacción -->
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="d-flex gap-2 mb-3">
                                        <button class="btn btn-sm <?= $image['user_has_liked'] ? 'btn-danger' : 'btn-outline-danger' ?> like-btn" 
                                                data-image-id="<?= $image['id'] ?>">
                                            <i class="bi bi-heart<?= $image['user_has_liked'] ? '-fill' : '' ?>"></i>
                                            <span class="likes-count"><?= $image['likes_count'] ?></span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary comment-toggle-btn" 
                                                data-image-id="<?= $image['id'] ?>">
                                            <i class="bi bi-chat-dots"></i>
                                            <span class="comments-count"><?= $image['comments_count'] ?></span>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex gap-2 mb-3">
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-heart me-1"></i><?= $image['likes_count'] ?> likes
                                        </span>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-chat-dots me-1"></i><?= $image['comments_count'] ?> comentarios
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Sección de comentarios -->
                                <div class="comments-section" id="comments-<?= $image['id'] ?>" style="display: none;">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form class="comment-form mb-3" data-image-id="<?= $image['id'] ?>">
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm comment-input" 
                                                       placeholder="Escribe un comentario..." required>
                                                <button class="btn btn-primary btn-sm" type="submit">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        </form>
                                    <?php endif; ?>
                                    
                                    <div class="comments-list" id="comments-list-<?= $image['id'] ?>">
                                        <!-- Los comentarios se cargarán dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegación de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($hasPrev): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
                                    <i class="bi bi-chevron-left"></i> Anterior
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($hasMore): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
                                    Siguiente <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
