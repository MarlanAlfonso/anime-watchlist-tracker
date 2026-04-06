<?php
require 'db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "<h2 style='color:green'>✅ Database connected successfully!</h2>";
    echo "<p>Users table found. Total users: " . $result['total'] . "</p>";
} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ Connection failed!</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>