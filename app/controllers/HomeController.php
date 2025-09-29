<?php

class HomeController extends Controller {
    
    public function index() {
        $imageModel = $this->model('Image');
        $likeModel = $this->model('Like');
        
        // Obtener parámetros de paginación
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * Config::getImagesPerPage();
        
        // Obtener imágenes con paginación
        $images = $imageModel->getAllImages(Config::getImagesPerPage(), $offset);
        
        // Añadir información de likes para el usuario actual (si está logueado)
        if ($this->isLoggedIn()) {
            foreach ($images as &$image) {
                $image['user_has_liked'] = $likeModel->hasUserLiked($_SESSION['user_id'], $image['id']);
            }
        }
        
        // Calcular información de paginación
        $totalImages = $imageModel->getTotalCount();
        $totalPages = ceil($totalImages / Config::getImagesPerPage());
        
        $data = [
            'title' => 'Galería Pública',
            'images' => $images,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'hasMore' => $page < $totalPages,
            'hasPrev' => $page > 1
        ];
        
        // Generar token CSRF si el usuario está logueado
        if ($this->isLoggedIn()) {
            $data['csrf_token'] = $this->generateCSRFToken();
        }
        
        $this->view('home/index', $data);
    }
    
    public function gallery() {
        // Alias para index
        $this->index();
    }
}
