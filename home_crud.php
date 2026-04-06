<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db.php';

$action = $_REQUEST['action'] ?? '';

// ── ADD ──────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $genre       = trim($_POST['genre']       ?? '');
    $episodes    = trim($_POST['episodes']    ?? '');
    $status      = $_POST['status']           ?? '';
    $description = trim($_POST['description'] ?? '');
    $added_by    = trim($_POST['added_by']    ?? 'Guest');

    if (empty($title) || empty($genre) || empty($episodes) || empty($status)) {
        $_SESSION['flash']      = '❌ Please fill in all required fields.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: home.php'); exit();
    }

    $cover = 'default_cover.png';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $allowed  = ['image/jpeg','image/png','image/gif','image/webp'];
        $max_size = 2 * 1024 * 1024;
        if (in_array($_FILES['cover_image']['type'], $allowed) && $_FILES['cover_image']['size'] <= $max_size) {
            $ext   = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $cover = uniqid('ga_', true) . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], 'uploads/' . $cover);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO general_anime (title,genre,episodes,status,description,cover_image,added_by) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$title, $genre, (int)$episodes, $status, $description, $cover, $added_by ?: 'Guest']);

    $_SESSION['flash']      = "✅ '{$title}' added successfully!";
    $_SESSION['flash_type'] = 'success';
    header('Location: home.php'); exit();
}

// ── EDIT ─────────────────────────────────────────────────
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id']          ?? 0);
    $title       = trim($_POST['title']        ?? '');
    $genre       = trim($_POST['genre']        ?? '');
    $episodes    = trim($_POST['episodes']     ?? '');
    $status      = $_POST['status']            ?? '';
    $description = trim($_POST['description']  ?? '');

    if (!$id || empty($title) || empty($genre) || empty($episodes) || empty($status)) {
        $_SESSION['flash']      = '❌ Please fill in all required fields.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: home.php'); exit();
    }

    // Get current cover
    $cur = $pdo->prepare("SELECT cover_image FROM general_anime WHERE id = ?");
    $cur->execute([$id]);
    $row   = $cur->fetch();
    $cover = $row['cover_image'] ?? 'default_cover.png';

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $allowed  = ['image/jpeg','image/png','image/gif','image/webp'];
        $max_size = 2 * 1024 * 1024;
        if (in_array($_FILES['cover_image']['type'], $allowed) && $_FILES['cover_image']['size'] <= $max_size) {
            if ($cover !== 'default_cover.png' && file_exists('uploads/'.$cover)) unlink('uploads/'.$cover);
            $ext   = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $cover = uniqid('ga_', true) . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], 'uploads/' . $cover);
        }
    }

    $stmt = $pdo->prepare("UPDATE general_anime SET title=?,genre=?,episodes=?,status=?,description=?,cover_image=? WHERE id=?");
    $stmt->execute([$title, $genre, (int)$episodes, $status, $description, $cover, $id]);

    $_SESSION['flash']      = "✅ '{$title}' updated successfully!";
    $_SESSION['flash_type'] = 'success';
    header('Location: home.php'); exit();
}

// ── DELETE ───────────────────────────────────────────────
if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        $cur = $pdo->prepare("SELECT cover_image, title FROM general_anime WHERE id = ?");
        $cur->execute([$id]);
        $row = $cur->fetch();
        if ($row) {
            if ($row['cover_image'] !== 'default_cover.png' && file_exists('uploads/'.$row['cover_image'])) {
                unlink('uploads/' . $row['cover_image']);
            }
            $pdo->prepare("DELETE FROM general_anime WHERE id = ?")->execute([$id]);
            $_SESSION['flash']      = "🗑️ '{$row['title']}' deleted.";
            $_SESSION['flash_type'] = 'warning';
        }
    }
    header('Location: home.php'); exit();
}

header('Location: home.php'); exit();
?>