<?php

class GalleryController extends Controller {
    
    public function index() {
        // Obtener imágenes con paginación
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * Config::getImagesPerPage();
        
        $imageModel = $this->model('Image');
        $likeModel = $this->model('Like');
        $commentModel = $this->model('Comment');
        
        $images = $imageModel->getAllImages(Config::getImagesPerPage(), $offset);
        
        // Añadir información adicional para cada imagen
        foreach ($images as &$image) {
            if ($this->isLoggedIn()) {
                $image['user_has_liked'] = $likeModel->hasUserLiked($_SESSION['user_id'], $image['id']);
            }
            $image['comments'] = $commentModel->getImageComments($image['id']);
        }
        
        // Calcular información de paginación
        $totalImages = $imageModel->getTotalCount();
        $totalPages = ceil($totalImages / Config::getImagesPerPage());
        
        $data = [
            'title' => 'Galería',
            'images' => $images,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'hasMore' => $page < $totalPages,
            'hasPrev' => $page > 1,
            'csrf_token' => $this->generateCSRFToken()
        ];
        
        $this->view('gallery/index', $data);
    }
    
    public function like($imageId = null) {
        $this->requireLogin();
        
        if (!$imageId) {
            $this->json(['success' => false, 'message' => 'ID de imagen requerido'], 400);
        }
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        // Debug logging
        error_log("LIKE DEBUG: Received token: " . $csrfToken);
        error_log("LIKE DEBUG: Session token: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));
        error_log("LIKE DEBUG: Token validation: " . ($this->validateCSRFToken($csrfToken) ? 'VALID' : 'INVALID'));
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }
        
        $likeModel = $this->model('Like');
        $result = $likeModel->toggleLike($_SESSION['user_id'], $imageId);
        
        $this->json($result);
    }
    
    public function comment($imageId = null) {
        $this->requireLogin();
        
        if (!$imageId) {
            $this->json(['success' => false, 'message' => 'ID de imagen requerido'], 400);
        }
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        // Debug logging
        error_log("COMMENT DEBUG: Received token: " . $csrfToken);
        error_log("COMMENT DEBUG: Session token: " . ($_SESSION['csrf_token'] ?? 'NOT SET'));
        error_log("COMMENT DEBUG: Token validation: " . ($this->validateCSRFToken($csrfToken) ? 'VALID' : 'INVALID'));
        
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }
        
        $comment = $this->sanitize($_POST['comment'] ?? '');
        
        $commentModel = $this->model('Comment');
        $result = $commentModel->addComment($_SESSION['user_id'], $imageId, $comment);
        
        if ($result['success']) {
            // Obtener el comentario recién creado con información del usuario
            $newComment = $this->model('Comment')->find($result['comment_id']);
            $userModel = $this->model('User');
            $user = $userModel->find($_SESSION['user_id']);
            
            $result['comment'] = [
                'id' => $newComment['id'],
                'comment' => $newComment['comment'],
                'created_at' => $newComment['created_at'],
                'username' => $user['username'],
                'user_id' => $user['id']
            ];
        }
        
        $this->json($result);
    }
    
    public function deleteComment($commentId = null) {
        $this->requireLogin();
        
        if (!$commentId) {
            $this->json(['success' => false, 'message' => 'ID de comentario requerido'], 400);
        }
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }
        
        $commentModel = $this->model('Comment');
        $result = $commentModel->deleteComment($commentId, $_SESSION['user_id']);
        
        $this->json($result);
    }
    
    public function getComments($imageId = null) {
        if (!$imageId) {
            $this->json(['success' => false, 'message' => 'ID de imagen requerido'], 400);
        }
        
        $commentModel = $this->model('Comment');
        $comments = $commentModel->getImageComments($imageId);
        
        $this->json(['success' => true, 'comments' => $comments]);
    }
    
    public function loadMore() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * Config::getImagesPerPage();
        
        $imageModel = $this->model('Image');
        $likeModel = $this->model('Like');
        $commentModel = $this->model('Comment');
        
        $images = $imageModel->getAllImages(Config::getImagesPerPage(), $offset);
        
        // Añadir información adicional para cada imagen
        foreach ($images as &$image) {
            if ($this->isLoggedIn()) {
                $image['user_has_liked'] = $likeModel->hasUserLiked($_SESSION['user_id'], $image['id']);
            }
            $image['comments'] = $commentModel->getImageComments($image['id']);
        }
        
        // Calcular si hay más páginas
        $totalImages = $imageModel->getTotalCount();
        $totalPages = ceil($totalImages / Config::getImagesPerPage());
        $hasMore = $page < $totalPages;
        
        $this->json([
            'success' => true,
            'images' => $images,
            'hasMore' => $hasMore,
            'nextPage' => $page + 1
        ]);
    }
}
