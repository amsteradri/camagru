<?php

class ErrorController extends Controller {
    
    public function notFound() {
        http_response_code(404);
        
        $data = [
            'title' => 'Página no encontrada',
            'error_code' => 404,
            'error_message' => 'La página que buscas no existe.'
        ];
        
        $this->view('error/404', $data);
    }
    
    public function serverError() {
        http_response_code(500);
        
        $data = [
            'title' => 'Error del servidor',
            'error_code' => 500,
            'error_message' => 'Ha ocurrido un error interno del servidor.'
        ];
        
        $this->view('error/500', $data);
    }
    
    public function forbidden() {
        http_response_code(403);
        
        $data = [
            'title' => 'Acceso denegado',
            'error_code' => 403,
            'error_message' => 'No tienes permisos para acceder a esta página.'
        ];
        
        $this->view('error/403', $data);
    }
}
