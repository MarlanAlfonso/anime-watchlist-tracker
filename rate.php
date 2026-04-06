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
$rating   = (float)($_POST['rating'] ?? 0);
$user_id  = (int)$_SESSION['user_id'];

if (!$anime_id || $rating < 1 || $rating > 10) {
    $_SESSION['flash']      = '❌ Invalid rating. Must be between 1 and 10.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: home.php'); exit();
}

// Upsert rating
$check = $pdo->prepare("SELECT id FROM anime_ratings WHERE anime_id = ? AND user_id = ?");
$check->execute([$anime_id, $user_id]);

if ($check->fetch()) {
    $pdo->prepare("UPDATE anime_ratings SET rating = ? WHERE anime_id = ? AND user_id = ?")->execute([$rating, $anime_id, $user_id]);
} else {
    $pdo->prepare("INSERT INTO anime_ratings (anime_id, user_id, rating) VALUES (?,?,?)")->execute([$anime_id, $user_id, $rating]);
}

$_SESSION['flash']      = "⭐ Rating of {$rating}/10 saved!";
$_SESSION['flash_type'] = 'success';
header('Location: home.php'); exit();
?>