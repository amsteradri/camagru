<?php

class AdminController extends Controller {
    
    public function index() {
        // Solo para desarrollo - verificar usuarios rápidamente
        if (!Config::isDevMode()) {
            $this->redirect('');
        }
        
        $userModel = $this->model('User');
        $db = Database::getInstance();
        
        $users = $db->fetchAll("SELECT id, username, email, email_verified, created_at FROM users ORDER BY created_at DESC");
        
        $data = [
            'title' => 'Admin - Usuarios',
            'users' => $users,
            'csrf_token' => $this->generateCSRFToken()
        ];
        
        $this->view('admin/users', $data);
    }
    
    public function verifyUser($userId = null) {
        if (!Config::isDevMode() || !$userId) {
            $this->json(['success' => false, 'message' => 'No disponible']);
        }
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }
        
        $db = Database::getInstance();
        $result = $db->execute("UPDATE users SET email_verified = 1 WHERE id = ?", [$userId]);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Usuario verificado']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al verificar usuario']);
        }
    }
}