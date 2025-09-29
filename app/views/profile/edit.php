<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card form-card shadow-custom">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h3 class="text-gradient">
                        <i class="bi bi-person-gear me-2"></i>Editar Perfil
                    </h3>
                    <p class="text-muted">Actualiza tu información personal</p>
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

                <form method="POST" action="/profile/edit">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person me-1"></i>Nombre de Usuario
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($user['username']) ?>"
                               required
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
                               value="<?= htmlspecialchars($user['email']) ?>"
                               required>
                    </div>

                    <hr class="my-4">
                    
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-lock me-2"></i>Cambiar Contraseña (opcional)
                    </h6>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Nueva Contraseña
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password"
                               minlength="8"
                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
                               title="Mínimo 8 caracteres, incluir mayúscula, minúscula y número">
                        <div class="form-text">
                            <small>Deja en blanco si no quieres cambiar la contraseña</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            Confirmar Nueva Contraseña
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password">
                    </div>

                    <hr class="my-4">

                    <h6 class="text-muted mb-3">
                        <i class="bi bi-gear me-2"></i>Configuración
                    </h6>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="email_notifications" 
                                   name="email_notifications"
                                   <?= $user['email_notifications'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="email_notifications">
                                <strong>Recibir notificaciones por email</strong>
                                <div class="text-muted small">
                                    Te enviaremos un email cuando alguien comente en tus fotos
                                </div>
                            </label>
                        </div>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                        </button>
                        <a href="/profile" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver al Perfil
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password.value && confirmPassword.value) {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmPassword.setCustomValidity('');
            }
        } else if (!password.value && !confirmPassword.value) {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);
});
</script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>