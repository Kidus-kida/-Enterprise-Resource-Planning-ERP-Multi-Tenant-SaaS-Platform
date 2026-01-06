<?php

// Check migrations table
$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'tewoserpt';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    $stmt = $pdo->query("SELECT migration, batch FROM migrations ORDER BY id DESC LIMIT 5");
    $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Last 5 migrations:\n";
    foreach ($migrations as $m) {
        echo " - " . $m['migration'] . " (Batch " . $m['batch'] . ")\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
