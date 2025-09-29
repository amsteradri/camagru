<?php

class User extends Model {
    protected $table = 'users';

    public function register($username, $email, $password) {
        // Validar si el usuario o email ya existe
        if ($this->findByUsername($username)) {
            return ['success' => false, 'message' => 'El nombre de usuario ya existe'];
        }

        if ($this->findByEmail($email)) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }

        // Validar contraseña
        if (!$this->isValidPassword($password)) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número'];
        }

        // Generar token de verificación
        $verificationToken = bin2hex(random_bytes(32));

        $data = [
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'verification_token' => $verificationToken,
            'email_verified' => 0
        ];

        $userId = $this->create($data);
        
        if ($userId) {
            // Enviar email de verificación solo si está habilitado
            if (Config::isEmailEnabled()) {
                $this->sendVerificationEmail($email, $username, $verificationToken);
                return ['success' => true, 'message' => 'Usuario registrado. Revisa tu email para verificar tu cuenta.'];
            } else {
                // Mostrar link de verificación cuando emails están deshabilitados
                $verificationUrl = Config::getAppUrl() . '/auth/verify/' . $verificationToken;
                return [
                    'success' => true, 
                    'message' => 'Usuario registrado exitosamente.',
                    'verification_url' => $verificationUrl,
                    'show_verification' => true
                ];
            }
        }

        return ['success' => false, 'message' => 'Error al registrar usuario'];
    }

    public function login($username, $password) {
        $user = $this->findByUsername($username);
        
        if (!$user) {
            $user = $this->findByEmail($username);
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            // En modo desarrollo, permitir login sin verificación
            if (!Config::isDevMode() && !$user['email_verified']) {
                return ['success' => false, 'message' => 'Debes verificar tu email antes de iniciar sesión'];
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            return ['success' => true, 'message' => 'Login exitoso'];
        }

        return ['success' => false, 'message' => 'Credenciales incorrectas'];
    }

    public function logout() {
        session_destroy();
    }

    public function findByUsername($username) {
        return $this->findBy('username', $username);
    }

    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }

    public function verifyEmail($token) {
        $sql = "UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$token]);
    }

    public function generateResetToken($email) {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$token, $expires, $email])) {
            if (Config::isEmailEnabled()) {
                $this->sendPasswordResetEmail($email, $user['username'], $token);
            }
            return true;
        }
        
        return false;
    }

    public function resetPassword($token, $newPassword) {
        $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()";
        $user = $this->db->fetch($sql, [$token]);

        if (!$user) {
            return ['success' => false, 'message' => 'Token inválido o expirado'];
        }

        if (!$this->isValidPassword($newPassword)) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número'];
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute([$passwordHash, $user['id']])) {
            return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar contraseña'];
    }

    public function updateProfile($userId, $username, $email, $password = null, $emailNotifications = true) {
        $user = $this->find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        // Verificar si el nuevo username ya existe (excepto para el usuario actual)
        $existingUser = $this->findByUsername($username);
        if ($existingUser && $existingUser['id'] != $userId) {
            return ['success' => false, 'message' => 'El nombre de usuario ya existe'];
        }

        // Verificar si el nuevo email ya existe (excepto para el usuario actual)
        $existingUser = $this->findByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }

        $updateData = [
            'username' => $username,
            'email' => $email,
            'email_notifications' => $emailNotifications ? 1 : 0
        ];

        if ($password && !empty($password)) {
            if (!$this->isValidPassword($password)) {
                return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número'];
            }
            $updateData['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($this->update($userId, $updateData)) {
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            return ['success' => true, 'message' => 'Perfil actualizado exitosamente'];
        }

        return ['success' => false, 'message' => 'Error al actualizar perfil'];
    }

    private function isValidPassword($password) {
        return strlen($password) >= 8 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }

    private function sendVerificationEmail($email, $username, $token) {
        $subject = 'Verificar tu cuenta en ' . Config::getAppName();
        $verificationUrl = Config::getAppUrl() . '/auth/verify/' . $token;
        
        $message = "
        <html>
        <head>
            <title>Verificación de cuenta</title>
        </head>
        <body>
            <h2>¡Hola {$username}!</h2>
            <p>Gracias por registrarte en " . Config::getAppName() . ".</p>
            <p>Para activar tu cuenta, haz clic en el siguiente enlace:</p>
            <p><a href='{$verificationUrl}'>Verificar mi cuenta</a></p>
            <p>Si no puedes hacer clic en el enlace, copia y pega esta URL en tu navegador:</p>
            <p>{$verificationUrl}</p>
            <p>Este enlace expirará en 24 horas.</p>
        </body>
        </html>
        ";

        $this->sendEmail($email, $subject, $message);
    }

    private function sendPasswordResetEmail($email, $username, $token) {
        $subject = 'Restablecer contraseña - ' . Config::getAppName();
        $resetUrl = Config::getAppUrl() . '/auth/reset-password/' . $token;
        
        $message = "
        <html>
        <head>
            <title>Restablecer contraseña</title>
        </head>
        <body>
            <h2>¡Hola {$username}!</h2>
            <p>Recibimos una solicitud para restablecer tu contraseña.</p>
            <p>Para crear una nueva contraseña, haz clic en el siguiente enlace:</p>
            <p><a href='{$resetUrl}'>Restablecer mi contraseña</a></p>
            <p>Si no puedes hacer clic en el enlace, copia y pega esta URL en tu navegador:</p>
            <p>{$resetUrl}</p>
            <p>Este enlace expirará en 1 hora.</p>
            <p>Si no solicitaste este cambio, ignora este email.</p>
        </body>
        </html>
        ";

        $this->sendEmail($email, $subject, $message);
    }

    private function sendEmail($to, $subject, $message) {
        require_once __DIR__ . '/../core/EmailService.php';
        return EmailService::sendWithFallback($to, $subject, $message);
    }
}
