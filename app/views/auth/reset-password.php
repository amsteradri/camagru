<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card form-card shadow-custom">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="text-gradient">
                        <i class="bi bi-key me-2"></i>Nueva Contraseña
                    </h3>
                    <p class="text-muted">Ingresa tu nueva contraseña</p>
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
                    
                    <?php if (isset($redirect_to_login)): ?>
                        <script>
                            setTimeout(function() {
                                window.location.href = '/auth/login';
                            }, 3000);
                        </script>
                        <p class="text-center text-muted">
                            <small>Serás redirigido al login en 3 segundos...</small>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!isset($success)): ?>
                <form method="POST" action="/auth/reset-password/<?= htmlspecialchars($token) ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i>Nueva Contraseña
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required
                               minlength="8"
                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
                               title="Mínimo 8 caracteres, incluir mayúscula, minúscula y número">
                        <div class="form-text">
                            <small>Mínimo 8 caracteres con al menos una mayúscula, minúscula y número</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="bi bi-lock-fill me-1"></i>Confirmar Contraseña
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-check-circle me-2"></i>Actualizar Contraseña
                    </button>
                </form>
                <?php endif; ?>

                <div class="text-center">
                    <a href="/auth/login" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Volver al Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password && confirmPassword) {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }
    
    if (password && confirmPassword) {
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    }
});
</script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>