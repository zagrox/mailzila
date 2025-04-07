<?php

namespace App\Routing;

class Router {
    private $routes = [];
    private $container;
    
    public function __construct($container) {
        $this->container = $container;
    }
    
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    private function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/mailzila', '', $path); // Remove base path
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                return $this->handleRoute($route['handler']);
            }
        }
        
        // No route found
        http_response_code(404);
        include __DIR__ . '/../../resources/views/errors/404.php';
    }
    
    private function matchPath($routePath, $requestPath) {
        // Convert route parameters to regex pattern
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<\1>[^/]+)', $routePath);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            // Remove numeric keys
            foreach ($matches as $key => $value) {
                if (is_numeric($key)) {
                    unset($matches[$key]);
                }
            }
            
            // Store parameters in request
            $_REQUEST = array_merge($_REQUEST, $matches);
            
            return true;
        }
        
        return false;
    }
    
    private function handleRoute($handler) {
        if (is_string($handler)) {
            // Controller@method format
            list($controller, $method) = explode('@', $handler);
            $controller = $this->container->make($controller);
            return $controller->$method();
        }
        
        // Closure
        return $handler();
    }
} 