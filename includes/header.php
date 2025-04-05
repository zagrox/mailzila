<?php
session_start();

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Define application constants
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8888/mailzila');
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Mailzila');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? true);

// Load ElasticEmail API wrapper
require_once __DIR__ . '/ElasticEmailAPI.php';
$api = new ElasticEmailAPI($_ENV['ELASTICEMAIL_API_KEY']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,.2);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .table th {
            border-top: none;
            background-color: #f8f9fa;
        }
        .badge {
            padding: 0.5em 0.75em;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="text-center mb-4">
                    <h4><?php echo APP_NAME; ?></h4>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/dashboard.php') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/pages/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/campaigns/') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/pages/campaigns/list.php">
                        <i class="fas fa-envelope"></i> Campaigns
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/subscribers/') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/pages/subscribers/list.php">
                        <i class="fas fa-users"></i> Subscribers
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/templates/') !== false ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/pages/templates/list.php">
                        <i class="fas fa-file-alt"></i> Templates
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content"> 