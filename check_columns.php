<?php

// Check table columns
$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'tewoserpt';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    $tables = ['transactions', 'purchase_lines'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "Columns in $table:\n";
            echo implode(", ", $columns) . "\n\n";
        } catch (PDOException $e) {
            echo "Table $table does not exist.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
