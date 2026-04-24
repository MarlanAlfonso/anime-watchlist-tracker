<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db.php';
require 'helpers.php';

// ── Auth check for Add/Edit/Delete ───────────────────────────
if (!isset($_SESSION['user_id'])) {
    setFlash('❌ You must be logged in to perform this action.', 'danger');
    header('Location: index.php'); exit();
}

$action = $_REQUEST['action'] ?? '';

// ── ADD ──────────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateGeneralFields($_POST);

    $cover = 'default_cover.png';
    if (empty($errors) && isset($_FILES['cover_image'])) {
        $cover = handleImageUpload($_FILES['cover_image'], 'ga', $errors);
    }

    if (!empty($errors)) {
        setFlash('❌ ' . implode(' ', $errors), 'danger');
        header('Location: index.php'); exit();
    }

    $title       = trim($_POST['title']);
    $genre       = trim($_POST['genre']);
    $episodes    = (int)$_POST['episodes'];
    $status      = $_POST['status'];
    $description = trim($_POST['description'] ?? '');
    $added_by    = trim($_POST['added_by'] ?? $_SESSION['username']) ?: $_SESSION['username'];

    $pdo->prepare("INSERT INTO general_anime
        (title,genre,episodes,status,description,cover_image,added_by)
        VALUES (?,?,?,?,?,?,?)")
        ->execute([$title,$genre,$episodes,$status,$description,$cover,$added_by]);

    setFlash("✅ '{$title}' added to the general list!", 'success');
    header('Location: index.php'); exit();
}

// ── EDIT ─────────────────────────────────────────────────────
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $errors = validateGeneralFields($_POST);

    if (!$id) $errors[] = 'Invalid anime ID.';

    if (!empty($errors)) {
        setFlash('❌ ' . implode(' ', $errors), 'danger');
        header('Location: index.php'); exit();
    }

    $row = $pdo->prepare("SELECT cover_image FROM general_anime WHERE id = ?");
    $row->execute([$id]);
    $current   = $row->fetch();
    $old_cover = $current['cover_image'] ?? 'default_cover.png';

    $cover = handleImageReplace(
        $_FILES['cover_image'] ?? [],
        'ga',
        $old_cover,
        $errors
    );

    $title       = trim($_POST['title']);
    $genre       = trim($_POST['genre']);
    $episodes    = (int)$_POST['episodes'];
    $status      = $_POST['status'];
    $description = trim($_POST['description'] ?? '');

    $pdo->prepare("UPDATE general_anime
        SET title=?,genre=?,episodes=?,status=?,description=?,cover_image=?
        WHERE id=?")
        ->execute([$title,$genre,$episodes,$status,$description,$cover,$id]);

    setFlash("✅ '{$title}' updated successfully!", 'success');
    header('Location: index.php'); exit();
}

// ── DELETE ───────────────────────────────────────────────────
if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        $row = $pdo->prepare("SELECT cover_image, title FROM general_anime WHERE id = ?");
        $row->execute([$id]);
        $anime = $row->fetch();
        if ($anime) {
            deleteImageFile($anime['cover_image']);
            $pdo->prepare("DELETE FROM general_anime WHERE id = ?")->execute([$id]);
            setFlash("🗑️ '{$anime['title']}' deleted.", 'warning');
        }
    }
    header('Location: index.php'); exit();
}

header('Location: index.php'); exit();
?>