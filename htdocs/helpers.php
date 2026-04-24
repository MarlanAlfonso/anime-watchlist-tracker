<?php
// ============================================================
// SHARED HELPERS — used by both Home CRUD and My List CRUD
// ============================================================

// ── Constants ────────────────────────────────────────────────
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL',  'uploads/');

const GENRES = [
    'Action','Adventure','Comedy','Drama','Fantasy','Horror',
    'Mecha','Mystery','Romance','Sci-Fi','Slice of Life',
    'Sports','Supernatural','Thriller'
];

const WATCHLIST_STATUSES = [
    'Watching','Completed','Dropped','Plan to Watch'
];

const GENERAL_STATUSES = [
    'Ongoing','Completed','Upcoming'
];

// ── Image Upload ──────────────────────────────────────────────
function handleImageUpload(array $file, string $prefix, array &$errors): string {
    if (!isset($file) || $file['error'] !== 0) {
        return 'default_cover.png';
    }

    $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowed)) {
        $errors[] = 'Cover must be JPG, PNG, GIF or WEBP.';
        return 'default_cover.png';
    }

    if ($file['size'] > $max_size) {
        $errors[] = 'Cover image must be under 2MB.';
        return 'default_cover.png';
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid($prefix . '_', true) . '.' . $ext;
    move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filename);

    return $filename;
}

// ── Replace Image (edit) ─────────────────────────────────────
function handleImageReplace(array $file, string $prefix, string $old_filename, array &$errors): string {
    if (!isset($file) || $file['error'] !== 0) {
        return $old_filename; // keep existing
    }

    deleteImageFile($old_filename);

    return handleImageUpload($file, $prefix, $errors);
}

// ── Delete Image File ─────────────────────────────────────────
function deleteImageFile(string $filename): void {
    if (
        !empty($filename) &&
        $filename !== 'default_cover.png' &&
        $filename !== 'default.png' &&
        file_exists(UPLOAD_PATH . $filename)
    ) {
        unlink(UPLOAD_PATH . $filename);
    }
}

// ── Validate Watchlist Fields ─────────────────────────────────
function validateWatchlistFields(array $post): array {
    $errors = [];

    if (empty(trim($post['title'] ?? '')))
        $errors[] = 'Title is required.';

    if (empty(trim($post['genre'] ?? '')))
        $errors[] = 'Genre is required.';

    $eps = trim($post['episodes'] ?? '');
    if (empty($eps) || !is_numeric($eps) || (int)$eps < 1)
        $errors[] = 'Episodes must be a number greater than 0.';

    if (empty($post['status'] ?? ''))
        $errors[] = 'Status is required.';

    $rating = trim($post['rating'] ?? '');
    if (empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 10)
        $errors[] = 'Rating must be between 1 and 10.';

    return $errors;
}

// ── Validate General Anime Fields ────────────────────────────
function validateGeneralFields(array $post): array {
    $errors = [];

    if (empty(trim($post['title'] ?? '')))
        $errors[] = 'Title is required.';

    if (empty(trim($post['genre'] ?? '')))
        $errors[] = 'Genre is required.';

    $eps = trim($post['episodes'] ?? '');
    if (empty($eps) || !is_numeric($eps) || (int)$eps < 1)
        $errors[] = 'Episodes must be a number greater than 0.';

    if (empty($post['status'] ?? ''))
        $errors[] = 'Status is required.';

    return $errors;
}

// ── Cover URL Helper ──────────────────────────────────────────
function coverUrl(string $filename, int $w = 40, int $h = 54): string {
    if (!empty($filename) && $filename !== 'default_cover.png' && file_exists(UPLOAD_PATH . $filename)) {
        return UPLOAD_URL . htmlspecialchars($filename);
    }
    return "https://via.placeholder.com/{$w}x{$h}/1a1a2e/e94560?text=?";
}

// ── Flash Message Helpers ──────────────────────────────────────
function setFlash(string $message, string $type = 'success'): void {
    $_SESSION['flash']      = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlash(): array {
    $flash = [
        'message' => $_SESSION['flash']      ?? '',
        'type'    => $_SESSION['flash_type'] ?? 'success',
    ];
    unset($_SESSION['flash'], $_SESSION['flash_type']);
    return $flash;
}
?>