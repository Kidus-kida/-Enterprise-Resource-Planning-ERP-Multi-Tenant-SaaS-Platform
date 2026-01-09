<?php

// Test database connection
$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'tewoserpt';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Successfully connected to database '$dbname'!\n";
    
    // Check if migrations table exists
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nExisting tables (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
    exit(1);
}
