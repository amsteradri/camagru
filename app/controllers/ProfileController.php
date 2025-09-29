<?php

class ProfileController extends Controller {
    
    public function index() {
        $this->requireLogin();
        
        $userModel = $this->model('User');
        $imageModel = $this->model('Image');
        
        $user = $userModel->find($_SESSION['user_id']);
        $userImages = $imageModel->getUserImages($_SESSION['user_id']);
        
        $data = [
            'title' => 'Mi Perfil',
            'user' => $user,
            'userImages' => $userImages,
            'csrf_token' => $this->generateCSRFToken()
        ];
        
        $this->view('profile/index', $data);
    }
    
    public function edit() {
        $this->requireLogin();
        
        if ($this->isPost()) {
            $username = $this->sanitize($_POST['username'] ?? '');
            $email = $this->sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $emailNotifications = isset($_POST['email_notifications']);
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $data['error'] = 'Token de seguridad inválido';
            } elseif (!empty($password) && $password !== $confirmPassword) {
                $data['error'] = 'Las contraseñas no coinciden';
            } elseif (!empty($password) && !$this->validatePasswordComplexity($password)) {
                $data['error'] = 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'Email inválido';
            } else {
                $userModel = $this->model('User');
                $result = $userModel->updateProfile(
                    $_SESSION['user_id'], 
                    $username, 
                    $email, 
                    !empty($password) ? $password : null,
                    $emailNotifications
                );
                
                if ($result['success']) {
                    $data['success'] = $result['message'];
                } else {
                    $data['error'] = $result['message'];
                }
            }
        }
        
        $userModel = $this->model('User');
        $user = $userModel->find($_SESSION['user_id']);
        
        $data['title'] = 'Editar Perfil';
        $data['user'] = $user;
        $data['csrf_token'] = $this->generateCSRFToken();
        
        $this->view('profile/edit', $data);
    }
    
    public function images() {
        $this->requireLogin();
        
        $imageModel = $this->model('Image');
        $userImages = $imageModel->getUserImages($_SESSION['user_id']);
        
        $data = [
            'title' => 'Mis Imágenes',
            'userImages' => $userImages,
            'csrf_token' => $this->generateCSRFToken()
        ];
        
        $this->view('profile/images', $data);
    }
    
    public function deleteImage($imageId = null) {
        $this->requireLogin();
        
        if (!$imageId) {
            $this->json(['success' => false, 'message' => 'ID de imagen requerido'], 400);
        }
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }
        
        $imageModel = $this->model('Image');
        $result = $imageModel->deleteImage($imageId, $_SESSION['user_id']);
        
        $this->json($result);
    }
}
