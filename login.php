<?php
session_start();
require 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ── Validation ──────────────────────────────
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($password)) $errors[] = 'Password is required.';

    // ── Check credentials ───────────────────────
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid username or password.';
        } else {
            // ── Start session ────────────────────
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['avatar']   = $user['avatar'];
            header('Location: index.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Anime Watchlist</title>
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
        .login-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(233,69,96,0.3);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(10px);
        }
        .login-box h2 {
            color: #e94560;
            text-align: center;
            margin-bottom: 8px;
            font-weight: 700;
        }
        .login-box p.subtitle {
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
        .btn-login {
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
        .btn-login:hover { background: #c73652; color: #fff; }
        .register-link {
            text-align: center;
            margin-top: 18px;
            color: #aaa;
            font-size: 13px;
        }
        .register-link a { color: #e94560; }
        .emoji-title { font-size: 36px; text-align: center; display: block; margin-bottom: 6px; }
        .browse-link {
            text-align: center;
            margin-top: 10px;
            font-size: 13px;
        }
        .browse-link a { color: #7ec8e3; }
    </style>
</head>
<body>
<div class="login-box">
    <span class="emoji-title">🎌</span>
    <h2>Welcome Back!</h2>
    <p class="subtitle">Log in to manage your anime watchlist</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="list-unstyled" style="margin:0">
                <?php foreach ($errors as $e): ?>
                    <li><span class="glyphicon glyphicon-remove"></span> <?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label><span class="glyphicon glyphicon-user"></span> Username</label>
            <input type="text" name="username" class="form-control"
                   placeholder="Your username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label><span class="glyphicon glyphicon-lock"></span> Password</label>
            <input type="password" name="password" class="form-control"
                   placeholder="Your password">
        </div>
        <button type="submit" class="btn btn-login">
            <span class="glyphicon glyphicon-log-in"></span> Log In
        </button>
    </form>

    <div class="register-link">
        No account yet? <a href="register.php">Register here</a>
    </div>
    <div class="browse-link">
        <a href="browse.php"><span class="glyphicon glyphicon-film"></span> Browse anime without logging in</a>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>