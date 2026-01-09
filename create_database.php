<?php

// Create database using PDO
$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'tewoserpt';

try {
    // Connect without database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    
    echo "✓ Database '$dbname' created successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error creating database: " . $e->getMessage() . "\n";
    exit(1);
}
