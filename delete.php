<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'auth_guard.php';
require 'db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit();
}

// ── Ownership check ──────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM anime_watchlist WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$anime = $stmt->fetch();

if (!$anime) {
    $_SESSION['flash'] = "❌ Anime not found or access denied.";
    header('Location: index.php');
    exit();
}

// ── Delete image file ────────────────────────────────────
if ($anime['cover_image'] !== 'default_cover.png' && file_exists('uploads/' . $anime['cover_image'])) {
    unlink('uploads/' . $anime['cover_image']);
}

// ── Delete record ────────────────────────────────────────
$stmt = $pdo->prepare("DELETE FROM anime_watchlist WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

$_SESSION['flash'] = "🗑️ '{$anime['title']}' deleted successfully.";
header('Location: index.php');
exit();
?>