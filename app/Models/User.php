<?php

namespace App\Models;

use App\Services\DatabaseService;

class User {
    private $db;
    
    public function __construct(DatabaseService $db) {
        $this->db = $db;
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $result = $this->db->select($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $result = $this->db->select($sql, [$email]);
        return $result[0] ?? null;
    }
    
    public function findByResetToken($token) {
        $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()";
        $result = $this->db->select($sql, [$token]);
        return $result[0] ?? null;
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (email, password, first_name, last_name, dark_mode) 
                VALUES (?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['first_name'],
            $data['last_name'],
            $data['dark_mode'] ?? false
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE users SET 
                email = ?,
                first_name = ?,
                last_name = ?,
                dark_mode = ?
                WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['dark_mode'] ?? false,
            $id
        ]);
    }
    
    public function updatePassword($id, $password) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        return $this->db->query($sql, [
            password_hash($password, PASSWORD_DEFAULT),
            $id
        ]);
    }
    
    public function updateResetToken($id, $token, $expires) {
        $sql = "UPDATE users SET 
                reset_token = ?,
                reset_token_expires = ?
                WHERE id = ?";
        
        return $this->db->query($sql, [$token, $expires, $id]);
    }
    
    public function clearResetToken($id) {
        $sql = "UPDATE users SET 
                reset_token = NULL,
                reset_token_expires = NULL
                WHERE id = ?";
        
        return $this->db->query($sql, [$id]);
    }
    
    public function toggleDarkMode($id) {
        $sql = "UPDATE users SET dark_mode = NOT dark_mode WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
} 