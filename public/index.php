<?php

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Load application bootstrap
require_once __DIR__ . '/../bootstrap/app.php'; 