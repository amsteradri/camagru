<?php

class Controller {
    protected function model($model) {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model();
    }

    protected function view($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            http_response_code(404);
            echo "Vista no encontrada: " . $view;
        }
    }

    protected function redirect($url = '') {
        if (empty($url)) {
            $url = '/';
        }
        
        if (!str_starts_with($url, 'http')) {
            $url = Config::getAppUrl() . '/' . ltrim($url, '/');
        }
        
        header('Location: ' . $url);
        exit;
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect('auth/login');
        }
    }

    protected function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    protected function sanitize($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = $this->sanitize($value);
            }
        } else {
            $input = trim($input);
            $input = stripslashes($input);
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }
    
    protected function validatePasswordComplexity($password) {
        // Mínimo 8 caracteres
        if (strlen($password) < 8) {
            return false;
        }
        
        // Debe contener al menos una mayúscula
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Debe contener al menos una minúscula
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Debe contener al menos un número
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
}
