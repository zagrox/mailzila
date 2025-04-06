<?php
require_once __DIR__ . '/config/init.php';

echo "<pre>";
echo "Session status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session data: ";
print_r($_SESSION);
echo "\nAPP_URL: " . APP_URL . "\n";
echo "Current path: " . $_SERVER['REQUEST_URI'] . "\n";
echo "isLoggedIn(): " . (isLoggedIn() ? 'true' : 'false') . "\n";
echo "</pre>"; 