<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="text-center py-5">
            <div class="text-danger mb-4">
                <i class="bi bi-exclamation-octagon display-1"></i>
            </div>
            
            <h1 class="display-4 fw-bold text-danger mb-3">500</h1>
            <h2 class="h4 mb-3">Error interno del servidor</h2>
            <p class="text-muted mb-4">
                Lo sentimos, ha ocurrido un error en nuestro servidor. 
                Nuestro equipo técnico ha sido notificado y está trabajando para solucionarlo.
            </p>
            
            <div class="d-grid gap-2 d-md-block">
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-house me-2"></i>Volver al Inicio
                </a>
                <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Intentar de nuevo
                </button>
            </div>
            
            <hr class="my-4">
            
            <div class="text-muted">
                <small>
                    Si el problema persiste, 
                    <a href="mailto:admin@camagru.com" class="text-decoration-none">contacta al soporte técnico</a>
                </small>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>