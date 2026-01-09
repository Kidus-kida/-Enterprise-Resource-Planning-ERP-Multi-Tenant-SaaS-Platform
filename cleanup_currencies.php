<?php

// Drop 'currencies' table which is causing migration to fail
$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'tewoserpt';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $pdo->exec("DROP TABLE IF EXISTS `currencies`");
    echo "Dropped currencies table\n";
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Done.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
