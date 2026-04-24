<?php
// Copy this file to db.php and fill in your credentials
$host   = 'your_db_host';
$dbname = 'your_db_name';
$user   = 'your_db_username';
$pass   = 'your_db_password';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}