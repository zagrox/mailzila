<?php

class Auth {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function login($email, $password) {
        try {
            $sql = "SELECT * FROM users WHERE email = ?";
            $result = $this->db->select($sql, [$email]);

            if (!empty($result)) {
                $user = $result[0];
                if (password_verify($password, $user['password'])) {
                    $this->setSession($user);
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function register($data) {
        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (email, password, first_name, last_name) VALUES (?, ?, ?, ?)";
            $result = $this->db->query($sql, [
                $data['email'],
                $hashedPassword,
                $data['first_name'],
                $data['last_name']
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }

    public function socialLogin($provider, $providerData) {
        try {
            // Check if user exists
            $stmt = $this->db->query(
                "SELECT * FROM users WHERE provider = ? AND provider_id = ?",
                [$provider, $providerData['id']]
            );
            $user = $stmt->fetch();

            if (!$user) {
                // Create new user
                $this->db->query(
                    "INSERT INTO users (email, first_name, last_name, avatar_url, provider, provider_id, access_token, refresh_token, token_expires_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $providerData['email'],
                        $providerData['firstName'],
                        $providerData['lastName'],
                        $providerData['avatar'],
                        $provider,
                        $providerData['id'],
                        $providerData['accessToken'],
                        $providerData['refreshToken'] ?? null,
                        $providerData['expiresAt'] ?? null
                    ]
                );
                $user = $this->db->query(
                    "SELECT * FROM users WHERE provider = ? AND provider_id = ?",
                    [$provider, $providerData['id']]
                )->fetch();
            } else {
                // Update existing user's tokens
                $this->db->query(
                    "UPDATE users SET access_token = ?, refresh_token = ?, token_expires_at = ? WHERE id = ?",
                    [
                        $providerData['accessToken'],
                        $providerData['refreshToken'] ?? $user['refresh_token'],
                        $providerData['expiresAt'] ?? $user['token_expires_at'],
                        $user['id']
                    ]
                );
            }

            $this->setSession($user);
            return true;
        } catch (PDOException $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Social login error: " . $e->getMessage());
            }
            return false;
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        return true;
    }

    public function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            $result = $this->db->select($sql, [$_SESSION['user_id']]);
            
            if (!empty($result)) {
                return $result[0];
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error getting current user: " . $e->getMessage());
            return null;
        }
    }

    public function updateProfile($userId, $firstName, $lastName) {
        try {
            $this->db->query(
                "UPDATE users SET first_name = ?, last_name = ? WHERE id = ?",
                [$firstName, $lastName, $userId]
            );
            return true;
        } catch (PDOException $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Update profile error: " . $e->getMessage());
            }
            return false;
        }
    }

    public function updatePassword($userId, $currentPassword, $newPassword) {
        try {
            // Verify current password
            $stmt = $this->db->query(
                "SELECT password FROM users WHERE id = ?",
                [$userId]
            );
            $user = $stmt->fetch();

            if ($user && password_verify($currentPassword, $user['password'])) {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $this->db->query(
                    "UPDATE users SET password = ? WHERE id = ?",
                    [$hashedPassword, $userId]
                );
                return true;
            }

            return false;
        } catch (PDOException $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log("Update password error: " . $e->getMessage());
            }
            return false;
        }
    }

    private function setSession($user) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    }
} 