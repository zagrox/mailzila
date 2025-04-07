<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class LoginController extends BaseController {
    public function showLoginForm() {
        if ($this->auth->isLoggedIn()) {
            return $this->redirect('/dashboard');
        }
        return $this->view('auth.login');
    }
    
    public function login() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $errors = $this->validate([
            'email' => $email,
            'password' => $password
        ], [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            return $this->view('auth.login', [
                'errors' => $errors,
                'email' => $email
            ]);
        }
        
        try {
            if ($this->auth->login($email, $password)) {
                return $this->redirect('/dashboard');
            }
            
            return $this->view('auth.login', [
                'error' => 'Invalid credentials',
                'email' => $email
            ]);
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return $this->view('auth.login', [
                'error' => 'An error occurred during login',
                'email' => $email
            ]);
        }
    }
    
    public function logout() {
        $this->auth->logout();
        return $this->redirect('/login');
    }
} 