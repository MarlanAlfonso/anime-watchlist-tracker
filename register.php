<?php
session_start();
require 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ── Validation ──────────────────────────────
    if (empty($username))               $errors[] = 'Username is required.';
    if (empty($email))                  $errors[] = 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (empty($password))               $errors[] = 'Password is required.';
    if (strlen($password) < 6)          $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)         $errors[] = 'Passwords do not match.';

    // ── Check duplicate username/email ──────────
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already exists.';
        }
    }

    // ── Handle avatar upload ─────────────────────
    $avatar = 'default.png';
    if (!empty($errors) === false && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        if (!in_array($_FILES['avatar']['type'], $allowed)) {
            $errors[] = 'Avatar must be JPG, PNG, GIF, or WEBP.';
        } elseif ($_FILES['avatar']['size'] > $maxSize) {
            $errors[] = 'Avatar must be under 2MB.';
        } else {
            $ext    = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatar = uniqid('avatar_', true) . '.' . $ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], 'avatars/' . $avatar);
        }
    }

    // ── Insert into DB ───────────────────────────
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt   = $pdo->prepare("INSERT INTO users (username, email, password, avatar) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed, $avatar]);
        $success = 'Account created! You can now <a href="login.php">log in</a>.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Anime Watchlist</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .register-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(233,69,96,0.3);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
            backdrop-filter: blur(10px);
        }
        .register-box h2 {
            color: #e94560;
            text-align: center;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .register-box p.subtitle {
            color: #aaa;
            text-align: center;
            margin-bottom: 28px;
            font-size: 13px;
        }
        .form-control {
            background: rgba(255,255,255,0.08) !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
            color: #fff !important;
            border-radius: 6px;
        }
        .form-control:focus {
            border-color: #e94560 !important;
            box-shadow: 0 0 0 2px rgba(233,69,96,0.2) !important;
        }
        .form-control::placeholder { color: #888 !important; }
        label { color: #ccc; font-size: 13px; }
        .btn-register {
            background: #e94560;
            border: none;
            color: #fff;
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .btn-register:hover { background: #c73652; color: #fff; }
        .login-link {
            text-align: center;
            margin-top: 18px;
            color: #aaa;
            font-size: 13px;
        }
        .login-link a { color: #e94560; }
        .emoji-title { font-size: 36px; text-align: center; display: block; margin-bottom: 6px; }
        input[type="file"] { color: #ccc; }
    </style>
</head>
<body>
<div class="register-box">
    <span class="emoji-title">🎌</span>
    <h2>Create Account</h2>
    <p class="subtitle">Join and start tracking your anime!</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="list-unstyled" style="margin:0">
                <?php foreach ($errors as $e): ?>
                    <li><span class="glyphicon glyphicon-remove"></span> <?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <span class="glyphicon glyphicon-ok"></span> <?= $success ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label><span class="glyphicon glyphicon-user"></span> Username</label>
            <input type="text" name="username" class="form-control"
                   placeholder="Choose a username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label><span class="glyphicon glyphicon-envelope"></span> Email</label>
            <input type="email" name="email" class="form-control"
                   placeholder="your@email.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label><span class="glyphicon glyphicon-lock"></span> Password</label>
            <input type="password" name="password" class="form-control"
                   placeholder="At least 6 characters">
        </div>
        <div class="form-group">
            <label><span class="glyphicon glyphicon-lock"></span> Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control"
                   placeholder="Repeat your password">
        </div>
        <div class="form-group">
            <label><span class="glyphicon glyphicon-picture"></span> Avatar <small style="color:#888">(optional)</small></label>
            <input type="file" name="avatar" class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-register">
            <span class="glyphicon glyphicon-ok"></span> Create Account
        </button>
    </form>
    <div class="login-link">
        Already have an account? <a href="login.php">Log in here</a>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>