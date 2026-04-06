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
if (!$anime_id) { header('Location: home.php'); exit(); }

// Get anime from general list
$stmt = $pdo->prepare("SELECT * FROM general_anime WHERE id = ?");
$stmt->execute([$anime_id]);
$anime = $stmt->fetch();

if (!$anime) {
    $_SESSION['flash']      = '❌ Anime not found.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: home.php'); exit();
}

// Check duplicate
$check = $pdo->prepare("SELECT id FROM anime_watchlist WHERE user_id = ? AND title = ?");
$check->execute([$_SESSION['user_id'], $anime['title']]);
if ($check->fetch()) {
    $_SESSION['flash']      = "⚠️ '{$anime['title']}' is already in your watchlist!";
    $_SESSION['flash_type'] = 'warning';
    header('Location: home.php'); exit();
}

// Copy cover image
$new_cover = 'default_cover.png';
if ($anime['cover_image'] !== 'default_cover.png' && file_exists('uploads/'.$anime['cover_image'])) {
    $ext       = pathinfo($anime['cover_image'], PATHINFO_EXTENSION);
    $new_cover = uniqid('cover_', true) . '.' . $ext;
    copy('uploads/'.$anime['cover_image'], 'uploads/'.$new_cover);
}

// Insert into watchlist
$insert = $pdo->prepare("INSERT INTO anime_watchlist (user_id,title,genre,episodes,status,rating,cover_image) VALUES (?,?,?,?,'Plan to Watch',0,?)");
$insert->execute([$_SESSION['user_id'], $anime['title'], $anime['genre'], $anime['episodes'], $new_cover]);

$_SESSION['flash']      = "✅ '{$anime['title']}' added to your watchlist! Go set your status and rating.";
$_SESSION['flash_type'] = 'success';
header('Location: index.php'); exit();
?>