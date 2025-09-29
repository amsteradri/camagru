<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card form-card shadow-custom">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="text-gradient">
                        <i class="bi bi-person-plus me-2"></i>Crear Cuenta
                    </h3>
                    <p class="text-muted">Únete a la comunidad de Camagru</p>
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
                        
                        <?php if (isset($show_verification) && $show_verification && isset($verification_url)): ?>
                            <hr>
                            <div class="mt-3">
                                <strong><i class="bi bi-envelope me-2"></i>Email deshabilitado en desarrollo</strong><br>
                                <small class="text-muted">Haz clic en este enlace para verificar tu cuenta:</small><br>
                                <a href="<?= htmlspecialchars($verification_url) ?>" class="btn btn-sm btn-outline-success mt-2">
                                    <i class="bi bi-check-circle me-1"></i>Verificar Cuenta
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/auth/register">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person me-1"></i>Nombre de Usuario
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($username ?? '') ?>"
                               required 
                               autofocus
                               pattern="[a-zA-Z0-9_]{3,20}"
                               title="3-20 caracteres, solo letras, números y guiones bajos">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i>Correo Electrónico
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($email ?? '') ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i>Contraseña
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
                        <i class="bi bi-person-plus me-2"></i>Crear Cuenta
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <p class="text-muted">
                ¿Ya tienes cuenta? 
                <a href="/auth/login" class="text-decoration-none">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Inicia sesión aquí
                </a>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
});
</script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
