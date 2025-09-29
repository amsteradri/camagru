<?php

class Like extends Model {
    protected $table = 'likes';

    public function toggleLike($userId, $imageId) {
        // Verificar si ya existe el like
        $existingLike = $this->findLike($userId, $imageId);
        
        if ($existingLike) {
            // Si existe, eliminarlo (unlike)
            $this->removeLike($userId, $imageId);
            return ['success' => true, 'action' => 'unliked', 'likes_count' => $this->getImageLikesCount($imageId)];
        } else {
            // Si no existe, agregarlo
            $this->addLike($userId, $imageId);
            return ['success' => true, 'action' => 'liked', 'likes_count' => $this->getImageLikesCount($imageId)];
        }
    }

    public function addLike($userId, $imageId) {
        $data = [
            'user_id' => $userId,
            'image_id' => $imageId
        ];
        return $this->create($data);
    }

    public function removeLike($userId, $imageId) {
        $sql = "DELETE FROM likes WHERE user_id = ? AND image_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $imageId]);
    }

    public function findLike($userId, $imageId) {
        $sql = "SELECT * FROM likes WHERE user_id = ? AND image_id = ?";
        return $this->db->fetch($sql, [$userId, $imageId]);
    }

    public function hasUserLiked($userId, $imageId) {
        return $this->findLike($userId, $imageId) !== false;
    }

    public function getImageLikesCount($imageId) {
        $sql = "SELECT COUNT(*) as count FROM likes WHERE image_id = ?";
        $result = $this->db->fetch($sql, [$imageId]);
        return $result['count'];
    }

    public function getImageLikes($imageId) {
        $sql = "SELECT l.*, u.username 
                FROM likes l 
                JOIN users u ON l.user_id = u.id 
                WHERE l.image_id = ? 
                ORDER BY l.created_at DESC";
        return $this->db->fetchAll($sql, [$imageId]);
    }
}
