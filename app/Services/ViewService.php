<?php

namespace App\Services;

class ViewService {
    private $layout = 'layouts/app';
    private $viewPath;
    
    public function __construct() {
        $this->viewPath = __DIR__ . '/../../resources/views/';
    }
    
    public function setLayout($layout) {
        $this->layout = $layout;
    }
    
    public function render($name, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = $this->viewPath . $name . '.php';
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
        }
        
        include $viewFile;
        
        // Get the contents and clean the buffer
        $content = ob_get_clean();
        
        // If no layout is set, return the content directly
        if (!$this->layout) {
            return $content;
        }
        
        // Include the layout
        $layoutFile = $this->viewPath . $this->layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file not found: {$layoutFile}");
        }
        
        include $layoutFile;
    }
    
    public function partial($name, $data = []) {
        extract($data);
        
        $partialFile = $this->viewPath . 'components/' . $name . '.php';
        if (!file_exists($partialFile)) {
            throw new \Exception("Partial file not found: {$partialFile}");
        }
        
        include $partialFile;
    }
    
    public function escape($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    public function asset($path) {
        return APP_URL . '/assets/' . ltrim($path, '/');
    }
    
    public function url($path) {
        return APP_URL . '/' . ltrim($path, '/');
    }
    
    public function csrf_token() {
        return CSRF::generateToken();
    }
    
    public function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . $this->csrf_token() . '">';
    }
} 