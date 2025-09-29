<?php

class AuthController extends Controller {
    
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('');
        }
        
        if ($this->isPost()) {
            $username = $this->sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $data['error'] = 'Token de seguridad inválido';
            } else {
                $userModel = $this->model('User');
                $result = $userModel->login($username, $password);
                
                if ($result['success']) {
                    // Redirigir a la página solicitada o al home
                    $redirect = $_SESSION['redirect_after_login'] ?? '';
                    unset($_SESSION['redirect_after_login']);
                    $this->redirect($redirect);
                } else {
                    $data['error'] = $result['message'];
                }
            }
        }
        
        $data['title'] = 'Iniciar Sesión';
        $data['csrf_token'] = $this->generateCSRFToken();
        $this->view('auth/login', $data);
    }
    
    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('');
        }
        
        if ($this->isPost()) {
            $username = $this->sanitize($_POST['username'] ?? '');
            $email = $this->sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            $data['username'] = $username;
            $data['email'] = $email;
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $data['error'] = 'Token de seguridad inválido';
            } elseif ($password !== $confirmPassword) {
                $data['error'] = 'Las contraseñas no coinciden';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'Email inválido';
            } elseif (!$this->validatePasswordComplexity($password)) {
                $data['error'] = 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números';
            } else {
                $userModel = $this->model('User');
                $result = $userModel->register($username, $email, $password);
                
                if ($result['success']) {
                    $data['success'] = $result['message'];
                    
                    // Si hay URL de verificación (emails deshabilitados), mostrarla
                    if (isset($result['verification_url'])) {
                        $data['verification_url'] = $result['verification_url'];
                        $data['show_verification'] = true;
                    }
                    
                    $data['username'] = '';
                    $data['email'] = '';
                } else {
                    $data['error'] = $result['message'];
                }
            }
        }
        
        $data['title'] = 'Registrarse';
        $data['csrf_token'] = $this->generateCSRFToken();
        $this->view('auth/register', $data);
    }
    
    public function logout() {
        $userModel = $this->model('User');
        $userModel->logout();
        $this->redirect('');
    }
    
    public function verify($token = null) {
        if (!$token) {
            $this->redirect('auth/login');
        }
        
        $userModel = $this->model('User');
        if ($userModel->verifyEmail($token)) {
            $data['success'] = 'Email verificado exitosamente. Ya puedes iniciar sesión.';
        } else {
            $data['error'] = 'Token de verificación inválido o expirado.';
        }
        
        $data['title'] = 'Verificar Email';
        $this->view('auth/verify', $data);
    }
    
    public function forgotPassword() {
        if ($this->isLoggedIn()) {
            $this->redirect('');
        }
        
        if ($this->isPost()) {
            $email = $this->sanitize($_POST['email'] ?? '');
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $data['error'] = 'Token de seguridad inválido';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['error'] = 'Email inválido';
            } else {
                $userModel = $this->model('User');
                if ($userModel->generateResetToken($email)) {
                    $data['success'] = 'Si el email existe en nuestro sistema, recibirás instrucciones para restablecer tu contraseña.';
                } else {
                    $data['success'] = 'Si el email existe en nuestro sistema, recibirás instrucciones para restablecer tu contraseña.';
                }
            }
        }
        
        $data['title'] = 'Olvidé mi Contraseña';
        $data['csrf_token'] = $this->generateCSRFToken();
        $this->view('auth/forgot-password', $data);
    }
    
    public function resetPassword($token = null) {
        if (!$token) {
            $this->redirect('auth/forgot-password');
        }
        
        if ($this->isPost()) {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? '';
            
            if (!$this->validateCSRFToken($csrfToken)) {
                $data['error'] = 'Token de seguridad inválido';
            } elseif ($password !== $confirmPassword) {
                $data['error'] = 'Las contraseñas no coinciden';
            } else {
                $userModel = $this->model('User');
                $result = $userModel->resetPassword($token, $password);
                
                if ($result['success']) {
                    $data['success'] = $result['message'];
                    $data['redirect_to_login'] = true;
                } else {
                    $data['error'] = $result['message'];
                }
            }
        }
        
        $data['title'] = 'Restablecer Contraseña';
        $data['token'] = $token;
        $data['csrf_token'] = $this->generateCSRFToken();
        $this->view('auth/reset-password', $data);
    }
}
