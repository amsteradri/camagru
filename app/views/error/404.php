<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="text-center py-5">
            <div class="text-danger mb-4">
                <i class="bi bi-exclamation-triangle display-1"></i>
            </div>
            
            <h1 class="display-4 fw-bold text-danger mb-3">404</h1>
            <h2 class="h4 mb-3">Página no encontrada</h2>
            <p class="text-muted mb-4">
                Lo sentimos, la página que buscas no existe o ha sido movida.
            </p>
            
            <div class="d-grid gap-2 d-md-block">
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-house me-2"></i>Volver al Inicio
                </a>
                <a href="/gallery" class="btn btn-outline-secondary">
                    <i class="bi bi-images me-2"></i>Ver Galería
                </a>
            </div>
            
            <hr class="my-4">
            
            <div class="text-muted">
                <small>
                    Si crees que esto es un error, 
                    <a href="mailto:admin@camagru.com" class="text-decoration-none">contacta al administrador</a>
                </small>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>