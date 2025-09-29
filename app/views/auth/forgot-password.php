<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card form-card shadow-custom">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="text-gradient">
                        <i class="bi bi-question-circle me-2"></i>Recuperar Contraseña
                    </h3>
                    <p class="text-muted">Te enviaremos un enlace para restablecer tu contraseña</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/auth/forgot-password">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i>Correo Electrónico
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               required 
                               autofocus
                               placeholder="tu-email@ejemplo.com">
                    </div>

                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-envelope-arrow-up me-2"></i>Enviar Enlace
                    </button>

                    <div class="text-center">
                        <a href="/auth/login" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Volver al Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>