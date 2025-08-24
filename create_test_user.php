<?php
// Run this script once to insert a test user into the players table
// Usage: php create_test_user.php

$host = '127.0.0.1';
$dbname = 'mcg_test';
$username = 'clyde';
$password = 'PurpleHorse@01';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $test_username = 'testuser';
    $test_password = 'TestPass123';
    $password_hash = password_hash($test_password, PASSWORD_DEFAULT);
    $display_name = 'Test User';
    $email = 'testuser@example.com';

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT player_id FROM players WHERE username = ?");
    $stmt->execute([$test_username]);
    if ($stmt->fetch()) {
        echo "User 'testuser' already exists.\n";
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO players (username, password_hash, display_name, email) VALUES (?, ?, ?, ?)");
    $stmt->execute([$test_username, $password_hash, $display_name, $email]);
    echo "Test user created: Username: testuser, Password: TestPass123, Email: testuser@example.com\n";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
