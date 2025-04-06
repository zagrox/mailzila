<?php
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance();
    
    // Check if admin user exists
    $stmt = $db->query("SELECT * FROM users WHERE email = ?", ['admin@mailzila.com']);
    $admin = $stmt->fetch();

    if (!$admin) {
        // Create admin user
        $hashedPassword = password_hash('Admin@123', PASSWORD_DEFAULT);
        $db->query(
            "INSERT INTO users (email, password, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?)",
            ['admin@mailzila.com', $hashedPassword, 'Admin', 'User', 1]
        );
        echo "Admin user created successfully!\n";
    } else {
        // Update admin password
        $hashedPassword = password_hash('Admin@123', PASSWORD_DEFAULT);
        $db->query(
            "UPDATE users SET password = ?, is_active = 1 WHERE email = ?",
            [$hashedPassword, 'admin@mailzila.com']
        );
        echo "Admin user password updated successfully!\n";
    }

    echo "\nAdmin credentials:\n";
    echo "Email: admin@mailzila.com\n";
    echo "Password: Admin@123\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
} 