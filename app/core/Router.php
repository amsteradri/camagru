<?php

class Router {
    private $routes = [];
    private $currentController = 'HomeController';
    private $currentMethod = 'index';
    private $params = [];

    public function __construct() {
        $this->parseUrl();
        $this->loadController();
        $this->callMethod();
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);

            // Controller
            if (isset($url[0]) && !empty($url[0])) {
                $this->currentController = ucfirst(strtolower($url[0])) . 'Controller';
                unset($url[0]);
            }

            // Method
            if (isset($url[1]) && !empty($url[1])) {
                $this->currentMethod = strtolower($url[1]);
                unset($url[1]);
            }

            // Parameters
            $this->params = $url ? array_values($url) : [];
        }
    }

    private function loadController() {
        $controllerPath = __DIR__ . '/../controllers/' . $this->currentController . '.php';
        
        if (file_exists($controllerPath)) {
            require_once $controllerPath;
            $this->currentController = new $this->currentController();
        } else {
            // Redirect to 404 page
            require_once __DIR__ . '/../controllers/ErrorController.php';
            $this->currentController = new ErrorController();
            $this->currentMethod = 'notFound';
        }
    }

    private function callMethod() {
        if (method_exists($this->currentController, $this->currentMethod)) {
            call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
        } else {
            // Method not found, call 404
            if (method_exists($this->currentController, 'notFound')) {
                $this->currentController->notFound();
            } else {
                echo "Method not found";
            }
        }
    }
}
