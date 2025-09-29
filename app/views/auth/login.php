<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card form-card shadow-custom">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="text-gradient">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </h3>
                    <p class="text-muted">Accede a tu cuenta de Camagru</p>
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

                <form method="POST" action="/auth/login">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person me-1"></i>Usuario o Email
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($username ?? '') ?>"
                               required 
                               autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i>Contraseña
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </button>

                    <div class="text-center">
                        <a href="/auth/forgot-password" class="text-decoration-none">
                            <i class="bi bi-question-circle me-1"></i>¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-muted">
                ¿No tienes cuenta? 
                <a href="/auth/register" class="text-decoration-none">
                    <i class="bi bi-person-plus me-1"></i>Regístrate aquí
                </a>
            </p>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
