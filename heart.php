<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php'); exit();
}

$anime_id = (int)($_POST['anime_id'] ?? 0);
$user_id  = (int)$_SESSION['user_id'];

if (!$anime_id) { header('Location: home.php'); exit(); }

// Toggle heart
$check = $pdo->prepare("SELECT id FROM anime_hearts WHERE anime_id = ? AND user_id = ?");
$check->execute([$anime_id, $user_id]);

if ($check->fetch()) {
    $pdo->prepare("DELETE FROM anime_hearts WHERE anime_id = ? AND user_id = ?")->execute([$anime_id, $user_id]);
    $_SESSION['flash']      = '💔 Removed from hearts.';
    $_SESSION['flash_type'] = 'warning';
} else {
    $pdo->prepare("INSERT INTO anime_hearts (anime_id, user_id) VALUES (?,?)")->execute([$anime_id, $user_id]);
    $_SESSION['flash']      = '❤️ Added to hearts!';
    $_SESSION['flash_type'] = 'success';
}

header('Location: home.php'); exit();
?>