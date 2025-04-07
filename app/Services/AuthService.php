<?php

namespace App\Services;

use App\Models\User;
use App\Security\CSRF;

class AuthService {
    private $db;
    private $user;
    
    public function __construct() {
        $this->db = DatabaseService::getInstance();
        $this->user = new User($this->db);
    }
    
    public function login($email, $password) {
        $user = $this->user->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        $this->setSession($user);
        return true;
    }
    
    public function logout() {
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->user->findById($_SESSION['user_id']);
    }
    
    public function setSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['dark_mode'] = $user['dark_mode'] ?? false;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }
    
    public function generateResetToken($email) {
        $user = $this->user->findByEmail($email);
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->user->updateResetToken($user['id'], $token, $expires);
        return $token;
    }
    
    public function verifyResetToken($token) {
        return $this->user->findByResetToken($token);
    }
    
    public function resetPassword($token, $password) {
        $user = $this->user->findByResetToken($token);
        if (!$user) {
            return false;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->user->updatePassword($user['id'], $hashedPassword);
        $this->user->clearResetToken($user['id']);
        
        return true;
    }
} 