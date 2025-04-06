<?php
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
    // Connect to MySQL without selecting a database
    $pdo = new PDO(
        "mysql:host=$dbHost",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Read and execute the setup SQL file
    $sql = file_get_contents(__DIR__ . '/setup.sql');
    $pdo->exec($sql);

    echo "Database setup completed successfully!\n";
    echo "You can now log in with:\n";
    echo "Email: admin@mailzila.com\n";
    echo "Password: Admin@123\n";

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage() . "\n");
} 