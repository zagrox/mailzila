<?php

namespace App\Controllers;

use App\Services\ViewService;
use App\Services\AuthService;

abstract class BaseController {
    protected $view;
    protected $auth;
    protected $db;
    
    public function __construct() {
        $this->view = new ViewService();
        $this->auth = new AuthService();
        $this->db = \App\Services\DatabaseService::getInstance();
    }
    
    protected function view($name, $data = []) {
        return $this->view->render($name, $data);
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    protected function validate($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            if (strpos($rule, 'required') !== false && empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
            // Add more validation rules as needed
        }
        return $errors;
    }
} 