<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Panel de administración (solo desarrollo)</strong><br>
            Esta página solo está disponible cuando DEV_MODE = true
        </div>
        
        <h2 class="text-gradient mb-4">
            <i class="bi bi-people me-2"></i>Gestión de Usuarios
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-custom">
            <div class="card-body">
                <h5 class="card-title">Usuarios Registrados</h5>
                
                <?php if (!empty($users)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($user['username']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <?php if ($user['email_verified']): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Verificado
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>Pendiente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if (!$user['email_verified']): ?>
                                                <button class="btn btn-sm btn-success verify-user-btn" 
                                                        data-user-id="<?= $user['id'] ?>"
                                                        title="Verificar usuario manualmente">
                                                    <i class="bi bi-check-circle me-1"></i>Verificar
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="bi bi-check-circle me-1"></i>OK
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people display-4 text-muted"></i>
                        <p class="mt-3 text-muted">No hay usuarios registrados todavía</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-gear me-2"></i>Configuración de Desarrollo
                </h6>
                <p class="text-muted">
                    <strong>DEV_MODE:</strong> Activado - Los usuarios pueden iniciar sesión sin verificar email<br>
                    <strong>ENABLE_EMAIL:</strong> <?= Config::isEmailEnabled() ? 'Activado' : 'Desactivado' ?><br>
                </p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Importante:</strong> Recuerda cambiar DEV_MODE a false y ENABLE_EMAIL a true en producción.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    if (e.target.closest('.verify-user-btn')) {
        const button = e.target.closest('.verify-user-btn');
        const userId = button.dataset.userId;
        
        if (confirm('¿Verificar este usuario manualmente?')) {
            verifyUser(userId, button);
        }
    }
});

async function verifyUser(userId, button) {
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-arrow-clockwise spin me-1"></i>Verificando...';
    
    try {
        const response = await fetch(`/admin/verifyUser/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=<?= $csrf_token ?>`
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar UI
            const row = button.closest('tr');
            const statusCell = row.cells[3];
            statusCell.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Verificado</span>';
            
            const actionCell = row.cells[5];
            actionCell.innerHTML = '<span class="text-muted"><i class="bi bi-check-circle me-1"></i>OK</span>';
            
            // Mostrar mensaje de éxito
            showAlert('success', 'Usuario verificado exitosamente');
        } else {
            button.disabled = false;
            button.innerHTML = originalContent;
            showAlert('error', result.message || 'Error al verificar usuario');
        }
    } catch (error) {
        console.error('Error:', error);
        button.disabled = false;
        button.innerHTML = originalContent;
        showAlert('error', 'Error de conexión');
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="bi ${iconClass} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container').insertBefore(alert, document.querySelector('.row'));
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 3000);
}
</script>

<?php 
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>