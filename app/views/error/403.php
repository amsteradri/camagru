<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="text-center py-5">
            <div class="text-warning mb-4">
                <i class="bi bi-shield-exclamation display-1"></i>
            </div>
            
            <h1 class="display-4 fw-bold text-warning mb-3">403</h1>
            <h2 class="h4 mb-3">Acceso denegado</h2>
            <p class="text-muted mb-4">
                No tienes permisos para acceder a esta página o recurso.
            </p>
            
            <div class="d-grid gap-2 d-md-block">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="/auth/login" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </a>
                    <a href="/auth/register" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-2"></i>Registrarse
                    </a>
                <?php else: ?>
                    <a href="/" class="btn btn-primary">
                        <i class="bi bi-house me-2"></i>Volver al Inicio
                    </a>
                    <a href="/profile" class="btn btn-outline-secondary">
                        <i class="bi bi-person me-2"></i>Mi Perfil
                    </a>
                <?php endif; ?>
            </div>
            
            <hr class="my-4">
            
            <div class="text-muted">
                <small>
                    Si crees que deberías tener acceso a esta página, 
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