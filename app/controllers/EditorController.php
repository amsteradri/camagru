<?php

class EditorController extends Controller {
    
    public function index() {
        $this->requireLogin();
        
        $imageModel = $this->model('Image');
        $userImages = $imageModel->getUserImages($_SESSION['user_id'], 10);
        
        // Obtener lista de stickers disponibles
        $stickers = $this->getAvailableStickers();
        
        $data = [
            'title' => 'Editor de Fotos',
            'userImages' => $userImages,
            'stickers' => $stickers,
            'csrf_token' => $this->generateCSRFToken()
        ];
        
        $this->view('editor/index', $data);
    }
    
    public function capture() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        // Protección contra double-submit usando hash de contenido
        $imageData = $_POST['image_data'] ?? '';
        $stickersData = $_POST['stickers_data'] ?? '';
        
        $requestHash = md5($imageData . $stickersData . $_SESSION['user_id']);
        $currentTime = microtime(true);
        $lastRequestHash = $_SESSION['last_capture_hash'] ?? '';
        $lastRequestTime = $_SESSION['last_capture_time'] ?? 0;
        
        // Bloquear si es el mismo contenido en menos de 5 segundos
        if ($requestHash === $lastRequestHash && ($currentTime - $lastRequestTime) < 5) {
            error_log("CAPTURE DEBUG: Duplicate request detected based on content hash and time");
            $this->json(['success' => false, 'message' => 'Solicitud duplicada detectada']);
        }
        
        $_SESSION['last_capture_hash'] = $requestHash;
        $_SESSION['last_capture_time'] = $currentTime;
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }
        
        error_log("CAPTURE DEBUG: image_data length: " . strlen($imageData));
        error_log("CAPTURE DEBUG: stickers_data: " . $stickersData);
        
        if (empty($imageData)) {
            $this->json(['success' => false, 'message' => 'Datos de imagen requeridos']);
        }
        
        // Permitir captura sin stickers - stickersData puede estar vacío
        if (empty($stickersData)) {
            $stickersData = '[]';
        }
        
        // Decodificar stickers data
        $stickers = json_decode($stickersData, true);
        if (!is_array($stickers)) {
            $this->json(['success' => false, 'message' => 'Datos de stickers inválidos']);
        }
        
        // Decodificar imagen base64
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
        if (!$imageData) {
            error_log("CAPTURE DEBUG: Failed to decode base64 image");
            $this->json(['success' => false, 'message' => 'Datos de imagen inválidos']);
        }
        
        error_log("CAPTURE DEBUG: decoded image size: " . strlen($imageData));
        
        // Procesar imagen con múltiples stickers
        $imageModel = $this->model('Image');
        
        error_log("CAPTURE DEBUG: About to call processImageWithMultipleStickers");
        $processedImageData = $imageModel->processImageWithMultipleStickers($imageData, $stickers);
        
        error_log("CAPTURE DEBUG: processImageWithMultipleStickers returned, data size: " . strlen($processedImageData ?: ''));
        
        if (!$processedImageData) {
            error_log("CAPTURE DEBUG: processImageWithMultipleStickers failed");
            $this->json(['success' => false, 'message' => 'Error al procesar la imagen']);
        }
        
        error_log("CAPTURE DEBUG: processed image size: " . strlen($processedImageData));
        
        // Guardar imagen procesada
        error_log("CAPTURE DEBUG: About to call uploadImage");
        $result = $imageModel->uploadImage($_SESSION['user_id'], $processedImageData);
        
        error_log("CAPTURE DEBUG: upload result: " . json_encode($result));
        
        if ($result['success']) {
            $this->json([
                'success' => true,
                'message' => 'Imagen guardada exitosamente',
                'image_id' => $result['image_id'],
                'filename' => $result['filename'],
                'image_url' => '/uploads/' . $result['filename']
            ]);
        } else {
            $this->json(['success' => false, 'message' => $result['message']]);
        }
    }
    
    public function upload() {
        $this->requireLogin();
        
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!$this->validateCSRFToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Token de seguridad inválido'], 403);
        }
        
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'Error al subir archivo']);
        }
        
        $file = $_FILES['image'];
        $stickersData = $_POST['stickers_data'] ?? '';
        
        error_log("UPLOAD DEBUG: file info: " . json_encode([
            'name' => $file['name'],
            'size' => $file['size'],
            'type' => $file['type'],
            'error' => $file['error']
        ]));
        error_log("UPLOAD DEBUG: stickers_data: " . $stickersData);
        
        // Permitir subida sin stickers - stickersData puede estar vacío
        if (empty($stickersData)) {
            $stickersData = '[]';
        }
        
        // Decodificar stickers data
        $stickers = json_decode($stickersData, true);
        if (!is_array($stickers)) {
            error_log("UPLOAD DEBUG: json_decode failed, stickers_data was: " . $stickersData);
            $this->json(['success' => false, 'message' => 'Datos de stickers inválidos']);
        }
        
        error_log("UPLOAD DEBUG: decoded stickers: " . json_encode($stickers));
        
        // Validar archivo
        if ($file['size'] > Config::getMaxImageSize()) {
            $this->json(['success' => false, 'message' => 'El archivo es muy grande']);
        }
        
        if (!in_array($file['type'], Config::getAllowedImageTypes())) {
            $this->json(['success' => false, 'message' => 'Tipo de archivo no permitido']);
        }
        
        // Leer datos del archivo
        $imageData = file_get_contents($file['tmp_name']);
        if (!$imageData) {
            $this->json(['success' => false, 'message' => 'Error al leer el archivo']);
        }
        
        // Procesar imagen con múltiples stickers
        $imageModel = $this->model('Image');
        
        $processedImageData = $imageModel->processImageWithMultipleStickers($imageData, $stickers);
        
        if (!$processedImageData) {
            $this->json(['success' => false, 'message' => 'Error al procesar la imagen']);
        }
        
        // Guardar imagen procesada
        $result = $imageModel->uploadImage($_SESSION['user_id'], $processedImageData, $file['name']);
        
        if ($result['success']) {
            $this->json([
                'success' => true,
                'message' => 'Imagen guardada exitosamente',
                'image_id' => $result['image_id'],
                'filename' => $result['filename'],
                'image_url' => '/uploads/' . $result['filename']
            ]);
        } else {
            $this->json(['success' => false, 'message' => $result['message']]);
        }
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
    
    private function getAvailableStickers() {
        $stickers = [];
        $stickerPath = Config::getStickersPath();
        
        if (is_dir($stickerPath)) {
            $files = scandir($stickerPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && 
                    in_array(pathinfo($file, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif'])) {
                    $stickers[] = [
                        'filename' => $file,
                        'path' => '/stickers/' . $file,
                        'name' => pathinfo($file, PATHINFO_FILENAME)
                    ];
                }
            }
        }
        
        return $stickers;
    }
}
