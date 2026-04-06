<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'auth_guard.php';
require 'db.php';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title']   ?? '');
    $genre   = trim($_POST['genre']   ?? '');
    $episodes= trim($_POST['episodes']?? '');
    $status  = $_POST['status']       ?? '';
    $rating  = trim($_POST['rating']  ?? '');

    // ── Validation ───────────────────────────────────────
    if (empty($title))                                  $errors[] = 'Title is required.';
    if (empty($genre))                                  $errors[] = 'Genre is required.';
    if (empty($episodes) || !is_numeric($episodes) || (int)$episodes < 1)
                                                        $errors[] = 'Episodes must be a number greater than 0.';
    if (empty($status))                                 $errors[] = 'Status is required.';
    if (empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 10)
                                                        $errors[] = 'Rating must be between 1 and 10.';

    // ── Image Upload ─────────────────────────────────────
    $cover = 'default_cover.png';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $allowed  = ['image/jpeg','image/png','image/gif','image/webp'];
        $max_size = 2 * 1024 * 1024;
        if (!in_array($_FILES['cover_image']['type'], $allowed)) {
            $errors[] = 'Cover must be JPG, PNG, GIF or WEBP.';
        } elseif ($_FILES['cover_image']['size'] > $max_size) {
            $errors[] = 'Cover image must be under 2MB.';
        } else {
            $ext   = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $cover = uniqid('cover_', true) . '.' . $ext;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], 'uploads/' . $cover);
        }
    }

    // ── Insert ───────────────────────────────────────────
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO anime_watchlist
            (user_id, title, genre, episodes, status, rating, cover_image)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'], $title, $genre,
            (int)$episodes, $status, (float)$rating, $cover
        ]);
        $_SESSION['flash'] = "✅ '{$title}' added successfully!";
        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Anime — AniTrack</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body { background:#1a1a2e; color:#eee; font-family:'Segoe UI',sans-serif; padding-top:60px; }
        .navbar-custom { background:#16213e; border:none; border-bottom:2px solid #e94560; }
        .navbar-custom .navbar-brand { color:#e94560!important; font-weight:700; }
        .navbar-custom .nav>li>a { color:#ccc!important; }
        .navbar-custom .nav>li>a:hover { color:#e94560!important; background:transparent!important; }
        .panel-anime { background:#16213e; border:1px solid rgba(233,69,96,0.3);
                       border-radius:10px; margin-top:30px; }
        .panel-anime .panel-heading { background:linear-gradient(135deg,#0f3460,#16213e);
                                      border-bottom:1px solid #e94560; border-radius:10px 10px 0 0;
                                      padding:16px 24px; }
        .panel-anime .panel-title { color:#e94560; font-size:18px; font-weight:700; }
        .panel-anime .panel-body { padding:28px; }
        .form-control { background:rgba(255,255,255,0.07)!important;
                        border:1px solid rgba(255,255,255,0.15)!important;
                        color:#fff!important; border-radius:6px; }
        .form-control:focus { border-color:#e94560!important;
                              box-shadow:0 0 0 2px rgba(233,69,96,0.2)!important; }
        .form-control::placeholder { color:#555!important; }
        select.form-control option { background:#16213e; color:#eee; }
        label { color:#bbb; font-size:13px; font-weight:600; }
        .btn-save { background:#e94560; border:none; color:#fff; font-weight:600;
                    padding:10px 30px; border-radius:6px; font-size:15px; }
        .btn-save:hover { background:#c73652; color:#fff; }
        .btn-back { background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.15);
                    color:#ccc; padding:10px 20px; border-radius:6px; }
        .btn-back:hover { background:rgba(255,255,255,0.12); color:#fff; }
        .has-error .form-control { border-color:#e74c3c!important; }
        .has-error .help-block  { color:#e74c3c; font-size:12px; }
        .image-preview { width:100%; max-height:200px; object-fit:cover;
                         border-radius:6px; margin-top:10px;
                         border:1px solid rgba(255,255,255,0.1); display:none; }
        .rating-hint { color:#888; font-size:12px; margin-top:4px; }
        .navbar-toggle .icon-bar { background:#e94560; }
        .dropdown-menu { background:#16213e; border:1px solid #e94560; }
        .dropdown-menu>li>a { color:#ccc!important; }
        .dropdown-menu>li>a:hover { background:#e94560!important; color:#fff!important; }
        .avatar-sm { width:32px; height:32px; border-radius:50%; object-fit:cover;
                     border:2px solid #e94560; margin-right:6px; vertical-align:middle; }
    </style>
</head>
<body>

<nav class="navbar navbar-custom navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#nav">
        <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php">🎌 AniTrack</a>
    </div>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="nav navbar-nav">
        <li><a href="index.php"><span class="glyphicon glyphicon-home"></span> My List</a></li>
        <li><a href="browse.php"><span class="glyphicon glyphicon-film"></span> Browse</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="avatars/<?= htmlspecialchars($_SESSION['avatar'] ?? 'default.png') ?>"
                 class="avatar-sm"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=e94560&color=fff&size=32'">
            <?= htmlspecialchars($_SESSION['username']) ?> <span class="caret"></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-right">
            <li><a href="index.php"><span class="glyphicon glyphicon-list"></span> My Watchlist</a></li>
            <li class="divider"></li>
            <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">

      <div class="panel panel-anime">
        <div class="panel-heading">
          <h3 class="panel-title">
            <span class="glyphicon glyphicon-plus"></span> Add New Anime
          </h3>
        </div>
        <div class="panel-body">

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <strong><span class="glyphicon glyphicon-exclamation-sign"></span> Please fix the following:</strong>
              <ul style="margin:8px 0 0 16px">
                <?php foreach ($errors as $err): ?>
                  <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">

            <div class="row">
              <div class="col-sm-8">
                <!-- Title -->
                <div class="form-group <?= in_array('Title is required.', $errors) ? 'has-error' : '' ?>">
                  <label><span class="glyphicon glyphicon-film"></span> Anime Title *</label>
                  <input type="text" name="title" class="form-control"
                         placeholder="e.g. Attack on Titan"
                         value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>

                <!-- Genre -->
                <div class="form-group <?= in_array('Genre is required.', $errors) ? 'has-error' : '' ?>">
                  <label><span class="glyphicon glyphicon-tags"></span> Genre *</label>
                  <select name="genre" class="form-control">
                    <option value="">— Select Genre —</option>
                    <?php
                    $genres = ['Action','Adventure','Comedy','Drama','Fantasy','Horror',
                               'Mecha','Mystery','Romance','Sci-Fi','Slice of Life',
                               'Sports','Supernatural','Thriller'];
                    foreach ($genres as $g):
                      $sel = (($_POST['genre'] ?? '') === $g) ? 'selected' : '';
                    ?>
                      <option value="<?= $g ?>" <?= $sel ?>><?= $g ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="row">
                  <!-- Episodes -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label><span class="glyphicon glyphicon-list-alt"></span> Episodes *</label>
                      <input type="number" name="episodes" class="form-control"
                             placeholder="e.g. 24" min="1"
                             value="<?= htmlspecialchars($_POST['episodes'] ?? '') ?>">
                    </div>
                  </div>
                  <!-- Rating -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label><span class="glyphicon glyphicon-star"></span> Rating *</label>
                      <input type="number" name="rating" class="form-control"
                             placeholder="1 - 10" min="1" max="10" step="0.1"
                             value="<?= htmlspecialchars($_POST['rating'] ?? '') ?>">
                      <p class="rating-hint">Score from 1.0 to 10.0</p>
                    </div>
                  </div>
                </div>

                <!-- Status -->
                <div class="form-group <?= in_array('Status is required.', $errors) ? 'has-error' : '' ?>">
                  <label><span class="glyphicon glyphicon-signal"></span> Status *</label>
                  <select name="status" class="form-control">
                    <option value="">— Select Status —</option>
                    <?php
                    $statuses = ['Watching','Completed','Dropped','Plan to Watch'];
                    foreach ($statuses as $s):
                      $sel = (($_POST['status'] ?? '') === $s) ? 'selected' : '';
                    ?>
                      <option value="<?= $s ?>" <?= $sel ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Cover Image -->
              <div class="col-sm-4">
                <div class="form-group">
                  <label><span class="glyphicon glyphicon-picture"></span> Cover Image</label>
                  <input type="file" name="cover_image" class="form-control"
                         accept="image/*" id="coverInput">
                  <p class="rating-hint">JPG/PNG/GIF/WEBP, max 2MB</p>
                  <img id="imagePreview" class="image-preview" src="#" alt="Preview">
                </div>
              </div>
            </div>

            <hr style="border-color:rgba(255,255,255,0.08); margin:20px 0;">

            <div class="text-right">
              <a href="index.php" class="btn btn-back">
                <span class="glyphicon glyphicon-arrow-left"></span> Cancel
              </a>
              &nbsp;
              <button type="submit" class="btn btn-save">
                <span class="glyphicon glyphicon-floppy-disk"></span> Save Anime
              </button>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
// Image preview
document.getElementById('coverInput').addEventListener('change', function() {
    var file = this.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>