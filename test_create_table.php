<?php

// Test running migrations manually
$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'tewoserpt';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating users table...\n";
    
    $sql = "CREATE TABLE `users` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `firstname` varchar(255) NOT NULL,
        `middlename` varchar(255) DEFAULT NULL,
        `lastname` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL UNIQUE,
        `username` varchar(255) DEFAULT NULL,
        `type` varchar(255) NOT NULL,
        `phone` varchar(255) DEFAULT NULL,
        `email_verified_at` timestamp NULL DEFAULT NULL,
        `phone_verified_at` timestamp NULL DEFAULT NULL,
        `password` varchar(255) NOT NULL,
        `avatar` varchar(255) DEFAULT NULL,
        `address` varchar(255) DEFAULT NULL,
        `country` varchar(255) DEFAULT NULL,
        `country_code` varchar(255) DEFAULT NULL,
        `dial_code` varchar(255) DEFAULT NULL,
        `created_by` int DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT '0',
        `is_online` tinyint(1) DEFAULT '0',
        `lang` varchar(255) DEFAULT NULL,
        `layout` varchar(255) DEFAULT NULL,
        `color_scheme` varchar(255) DEFAULT NULL,
        `layout_width` varchar(255) DEFAULT NULL,
        `layout_position` varchar(255) DEFAULT NULL,
        `topbar_color` varchar(255) DEFAULT NULL,
        `sidebar_size` varchar(255) DEFAULT NULL,
        `sidebar_view` varchar(255) DEFAULT NULL,
        `sidebar_color` varchar(255) DEFAULT NULL,
        `remember_token` varchar(100) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✓ Users table created successfully!\n";
    
    // Insert migration record
    $pdo->exec("INSERT INTO migrations (migration, batch) VALUES ('0001_01_01_000000_create_users_table', 1)");
    echo "✓ Migration record added!\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
