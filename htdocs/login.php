<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db.php';
require 'helpers.php';

if (isset($_SESSION['user_id'])) { header('Location: mylist.php'); exit(); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']      ?? '';

    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($password)) $errors[] = 'Password is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid username or password.';
        } else {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['avatar']   = $user['avatar'];
            header('Location: mylist.php'); exit();
        }
    }
}

$active_page = 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — AniTrack</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <?php require 'styles.php'; ?>
    <style>
        body { display:flex; align-items:center; justify-content:center;
               min-height:100vh; padding-top:0!important; background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460); }
        .auth-box { background:rgba(255,255,255,0.04); border:1px solid rgba(233,69,96,0.3);
                    border-radius:12px; padding:38px; width:100%; max-width:420px;
                    backdrop-filter:blur(10px); }
        .auth-box h2 { color:#e94560; text-align:center; margin-bottom:6px; font-weight:700; }
        .auth-box .subtitle { color:#aaa; text-align:center; margin-bottom:26px; font-size:13px; }
        .emoji-title { font-size:34px; text-align:center; display:block; margin-bottom:6px; }
        .btn-auth { background:#e94560; border:none; color:#fff; width:100%; padding:10px;
                    border-radius:6px; font-size:15px; font-weight:600; margin-top:6px; }
        .btn-auth:hover { background:#c73652; color:#fff; }
        .auth-links { text-align:center; margin-top:16px; color:#aaa; font-size:13px; }
        .auth-links a { color:#e94560; }
        .browse-link { text-align:center; margin-top:8px; font-size:13px; }
        .browse-link a { color:#7ec8e3; }
    </style>
</head>
<body>
<?php require 'navbar.php'; ?>

<div class="auth-box">
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
        <button type="submit" class="btn btn-auth">
            <span class="glyphicon glyphicon-log-in"></span> Log In
        </button>
    </form>

    <div class="auth-links">
        No account yet? <a href="register.php">Register here</a>
    </div>
    <div class="browse-link">
        <a href="index.php"><span class="glyphicon glyphicon-film"></span> Browse without logging in</a>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>