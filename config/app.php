<?php

return [
    'name' => 'Mailzila',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8888/mailzila',
    'timezone' => 'UTC',
    'locale' => 'en',
    'key' => $_ENV['APP_KEY'] ?? 'your-secret-key-here',
    'cipher' => 'AES-256-CBC',
    'providers' => [
        // Add your service providers here
    ],
    'aliases' => [
        'App' => 'App\Helpers\App',
        'Auth' => 'App\Services\AuthService',
        'Config' => 'App\Helpers\Config',
        'DB' => 'App\Services\DatabaseService',
        'View' => 'App\Helpers\View',
        'Route' => 'App\Helpers\Route',
    ],
]; 