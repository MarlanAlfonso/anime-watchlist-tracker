<?php
// ============================================
// AUTH GUARD - Protects pages from guests
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>