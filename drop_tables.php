<?php

// Drop all tables
$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'tewoserpt';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Dropping " . count($tables) . " tables...\n";
    
    foreach ($tables as $table) {
        echo "  Dropping $table...";
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo " ✓\n";
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n✓ All tables dropped successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
