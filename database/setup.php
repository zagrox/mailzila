<?php
require_once __DIR__ . '/../config/init.php';

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
} else {
    die('Environment file not found. Please create a .env file.');
}

// Database credentials
$dbHost = $_ENV['DB_HOST'] ?? 'localhost:8889'; // MAMP default MySQL port
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? 'root';
$dbName = $_ENV['DB_NAME'] ?? 'mailzila';

try {
    // Get database connection
    $db = Database::getInstance(
        $_ENV['DB_HOST'],
        $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );

    // Read and execute SQL files
    $sqlFiles = [
        __DIR__ . '/create_database.sql',
        __DIR__ . '/setup.sql',
        __DIR__ . '/users.sql',
        __DIR__ . '/../sql/notifications.sql',
        __DIR__ . '/../sql/update_settings.sql'
    ];

    foreach ($sqlFiles as $file) {
        if (file_exists($file)) {
            $sql = file_get_contents($file);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $db->query($statement);
                    } catch (Exception $e) {
                        // Log error but continue with other statements
                        error_log("Error executing SQL: " . $e->getMessage());
                        error_log("Statement: " . $statement);
                    }
                }
            }
        }
    }

    // Create admin user if it doesn't exist
    $adminEmail = 'admin@mailzila.com';
    $result = $db->select("SELECT id FROM users WHERE email = ?", [$adminEmail]);
    
    if (empty($result)) {
        $hashedPassword = password_hash('Admin@123', PASSWORD_DEFAULT);
        $db->query(
            "INSERT INTO users (email, password, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?)",
            [$adminEmail, $hashedPassword, 'Admin', 'User', 1]
        );
        echo "Admin user created successfully!\n";
    } else {
        echo "Admin user already exists.\n";
    }

    echo "Database setup completed successfully!\n";
    echo "You can now log in with:\n";
    echo "Email: admin@mailzila.com\n";
    echo "Password: Admin@123\n";

} catch (Exception $e) {
    error_log("Database setup error: " . $e->getMessage());
    echo "Error during database setup: " . $e->getMessage() . "\n";
    exit(1);
} 