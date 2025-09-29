<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card form-card shadow-custom text-center">
            <div class="card-body p-4">
                <?php if (isset($success)): ?>
                    <div class="text-success mb-4">
                        <i class="bi bi-check-circle display-4"></i>
                    </div>
                    <h3 class="text-success mb-3">¡Email Verificado!</h3>
                    <p class="text-muted mb-4"><?= htmlspecialchars($success) ?></p>
                    <a href="/auth/login" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </a>
                <?php else: ?>
                    <div class="text-danger mb-4">
                        <i class="bi bi-exclamation-triangle display-4"></i>
                    </div>
                    <h3 class="text-danger mb-3">Error de Verificación</h3>
                    <p class="text-muted mb-4"><?= htmlspecialchars($error) ?></p>
                    <div class="d-grid gap-2">
                        <a href="/auth/register" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus me-2"></i>Registrarse Nuevamente
                        </a>
                        <a href="/" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-2"></i>Volver al Inicio
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