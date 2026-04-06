<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'auth_guard.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browse.php');
    exit();
}

$anime_id = (int)($_POST['anime_id'] ?? 0);
if (!$anime_id) {
    header('Location: browse.php');
    exit();
}

// ── Fetch original anime ─────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM anime_watchlist WHERE id = ?");
$stmt->execute([$anime_id]);
$original = $stmt->fetch();

if (!$original) {
    $_SESSION['flash'] = "❌ Anime not found.";
    header('Location: browse.php');
    exit();
}

// ── Prevent copying your own entry ──────────────────────
if ((int)$original['user_id'] === (int)$_SESSION['user_id']) {
    $_SESSION['flash'] = "⚠️ That's already your own entry!";
    header('Location: browse.php');
    exit();
}

// ── Check if already copied ──────────────────────────────
$check = $pdo->prepare("SELECT id FROM anime_watchlist WHERE user_id = ? AND title = ?");
$check->execute([$_SESSION['user_id'], $original['title']]);
if ($check->fetch()) {
    $_SESSION['flash'] = "⚠️ '{$original['title']}' is already in your list!";
    header('Location: index.php');
    exit();
}

// ── Copy image file ──────────────────────────────────────
$new_cover = 'default_cover.png';
if ($original['cover_image'] !== 'default_cover.png' && file_exists('uploads/'.$original['cover_image'])) {
    $ext       = pathinfo($original['cover_image'], PATHINFO_EXTENSION);
    $new_cover = uniqid('cover_', true) . '.' . $ext;
    copy('uploads/'.$original['cover_image'], 'uploads/'.$new_cover);
}

// ── Insert copy into DB ──────────────────────────────────
$insert = $pdo->prepare("INSERT INTO anime_watchlist
    (user_id, title, genre, episodes, status, rating, cover_image)
    VALUES (?, ?, ?, ?, ?, ?, ?)");
$insert->execute([
    $_SESSION['user_id'],
    $original['title'],
    $original['genre'],
    $original['episodes'],
    $original['status'],
    $original['rating'],
    $new_cover
]);

$_SESSION['flash'] = "✅ '{$original['title']}' copied to your list!";
header('Location: index.php');
exit();
?>