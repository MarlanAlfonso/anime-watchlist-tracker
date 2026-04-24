<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'auth_guard.php';
require 'db.php';
require 'helpers.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: mylist.php'); exit(); }

$stmt = $pdo->prepare("SELECT * FROM anime_watchlist WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$anime = $stmt->fetch();

if (!$anime) {
    setFlash("❌ Anime not found or access denied.", 'danger');
    header('Location: mylist.php'); exit();
}

deleteImageFile($anime['cover_image']);

$pdo->prepare("DELETE FROM anime_watchlist WHERE id = ? AND user_id = ?")
    ->execute([$id, $_SESSION['user_id']]);

setFlash("🗑️ '{$anime['title']}' deleted successfully.", 'warning');
header('Location: mylist.php'); exit();
?>