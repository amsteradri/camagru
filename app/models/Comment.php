<?php

class Comment extends Model {
    protected $table = 'comments';

    public function addComment($userId, $imageId, $comment) {
        if (empty(trim($comment))) {
            return ['success' => false, 'message' => 'El comentario no puede estar vacío'];
        }

        $data = [
            'user_id' => $userId,
            'image_id' => $imageId,
            'comment' => trim($comment)
        ];

        $commentId = $this->create($data);
        if ($commentId) {
            // Enviar notificación por email solo si está habilitado
            if (Config::isEmailEnabled()) {
                $this->sendCommentNotification($imageId, $comment);
            }
            
            return [
                'success' => true, 
                'comment_id' => $commentId,
                'comments_count' => $this->getImageCommentsCount($imageId)
            ];
        }

        return ['success' => false, 'message' => 'Error al agregar comentario'];
    }

    public function getImageComments($imageId) {
        $sql = "SELECT c.*, u.username 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.image_id = ? 
                ORDER BY c.created_at ASC";
        return $this->db->fetchAll($sql, [$imageId]);
    }

    public function getImageCommentsCount($imageId) {
        $sql = "SELECT COUNT(*) as count FROM comments WHERE image_id = ?";
        $result = $this->db->fetch($sql, [$imageId]);
        return $result['count'];
    }

    public function deleteComment($commentId, $userId) {
        // Verificar que el comentario pertenece al usuario o que es el dueño de la imagen
        $sql = "SELECT c.*, i.user_id as image_owner 
                FROM comments c 
                JOIN images i ON c.image_id = i.id 
                WHERE c.id = ?";
        $comment = $this->db->fetch($sql, [$commentId]);

        if (!$comment) {
            return ['success' => false, 'message' => 'Comentario no encontrado'];
        }

        // Solo el autor del comentario o el dueño de la imagen pueden eliminarlo
        if ($comment['user_id'] != $userId && $comment['image_owner'] != $userId) {
            return ['success' => false, 'message' => 'No tienes permisos para eliminar este comentario'];
        }

        if ($this->delete($commentId)) {
            return [
                'success' => true, 
                'message' => 'Comentario eliminado',
                'comments_count' => $this->getImageCommentsCount($comment['image_id'])
            ];
        }

        return ['success' => false, 'message' => 'Error al eliminar comentario'];
    }

    private function sendCommentNotification($imageId, $comment) {
        // Obtener información de la imagen y su autor
        $sql = "SELECT i.*, u.username, u.email, u.email_notifications 
                FROM images i 
                JOIN users u ON i.user_id = u.id 
                WHERE i.id = ?";
        $imageData = $this->db->fetch($sql, [$imageId]);

        if (!$imageData || !$imageData['email_notifications']) {
            return; // No enviar notificación si está desactivada
        }

        // Obtener información del usuario que comentó
        $commenterInfo = $this->db->fetch("SELECT username FROM users WHERE id = ?", [$_SESSION['user_id']]);

        $subject = 'Nuevo comentario en tu foto - ' . Config::getAppName();
        $imageUrl = Config::getAppUrl() . '/gallery#image-' . $imageId;
        
        $message = "
        <html>
        <head>
            <title>Nuevo comentario</title>
        </head>
        <body>
            <h2>¡Hola {$imageData['username']}!</h2>
            <p><strong>{$commenterInfo['username']}</strong> ha comentado en tu foto:</p>
            <blockquote style='border-left: 3px solid #ccc; margin: 0; padding-left: 20px;'>
                " . htmlspecialchars($comment) . "
            </blockquote>
            <p><a href='{$imageUrl}'>Ver foto y comentarios</a></p>
            <hr>
            <p style='font-size: 12px; color: #666;'>
                Si no quieres recibir estas notificaciones, puedes desactivarlas en tu perfil.
            </p>
        </body>
        </html>
        ";

        $this->sendEmail($imageData['email'], $subject, $message);
    }

    private function sendEmail($to, $subject, $message) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . Config::getFromName() . ' <' . Config::getFromEmail() . '>',
            'Reply-To: ' . Config::getFromEmail(),
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}
