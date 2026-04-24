<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db.php';
require 'src/helpers.php';

if (isset($_SESSION['user_id'])) { header('Location: mylist.php'); exit(); }

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username))                               $errors[] = 'Username is required.';
    if (empty($email))                                  $errors[] = 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))     $errors[] = 'Invalid email format.';
    if (empty($password))                               $errors[] = 'Password is required.';
    if (strlen($password) < 6)                          $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)                         $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) $errors[] = 'Username or email already exists.';
    }

    $avatar = 'default.png';
    if (empty($errors) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $avatar = handleImageUpload($_FILES['avatar'], 'avatar', $errors);
        if ($avatar === 'default_cover.png') $avatar = 'default.png';
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (username,email,password,avatar) VALUES (?,?,?,?)")
            ->execute([$username, $email, $hashed, $avatar]);
        $success = 'Account created! You can now <a href="login.php">log in</a>.';
    }
}

$active_page = 'register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — AniTrack</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <?php require 'src/styles.php'; ?>
    <style>
        body { display:flex; align-items:center; justify-content:center;
               min-height:100vh; padding-top:0!important; background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460); }
        .auth-box { background:rgba(255,255,255,0.04); border:1px solid rgba(233,69,96,0.3);
                    border-radius:12px; padding:38px; width:100%; max-width:480px;
                    backdrop-filter:blur(10px); }
        .auth-box h2 { color:#e94560; text-align:center; margin-bottom:6px; font-weight:700; }
        .auth-box .subtitle { color:#aaa; text-align:center; margin-bottom:26px; font-size:13px; }
        .emoji-title { font-size:34px; text-align:center; display:block; margin-bottom:6px; }
        .btn-auth { background:#e94560; border:none; color:#fff; width:100%; padding:10px;
                    border-radius:6px; font-size:15px; font-weight:600; margin-top:6px; }
        .btn-auth:hover { background:#c73652; color:#fff; }
        .auth-links { text-align:center; margin-top:16px; color:#aaa; font-size:13px; }
        .auth-links a { color:#e94560; }
        input[type="file"] { color:#ccc; }
    </style>
</head>
<body>
<?php require 'src/navbar.php'; ?>

<div class="auth-box">
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
        <button type="submit" class="btn btn-auth">
            <span class="glyphicon glyphicon-ok"></span> Create Account
        </button>
    </form>

    <div class="auth-links">
        Already have an account? <a href="login.php">Log in here</a>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>