<?php
// Helper functions
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        global $auth;
        return $auth->getCurrentUser();
    }
}

if (!function_exists('redirect')) {
    function redirect($path) {
        header('Location: ' . APP_URL . $path);
        exit();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $time);
        }
    }
}

if (!function_exists('getNotificationIcon')) {
    function getNotificationIcon($type) {
        switch ($type) {
            case 'campaign':
                return 'fas fa-paper-plane';
            case 'subscriber':
                return 'fas fa-user';
            case 'system':
                return 'fas fa-cog';
            default:
                return 'fas fa-bell';
        }
    }
} 