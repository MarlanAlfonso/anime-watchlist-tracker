<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'auth_guard.php';
require 'db.php';
require 'helpers.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: mylist.php'); exit(); }

$stmt = $pdo->prepare("SELECT * FROM anime_watchlist WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$anime = $stmt->fetch();
if (!$anime) {
    setFlash("❌ Anime not found or access denied.", 'danger');
    header('Location: mylist.php'); exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateWatchlistFields($_POST);

    $cover = handleImageReplace(
        $_FILES['cover_image'] ?? [],
        'cover',
        $anime['cover_image'],
        $errors
    );

    if (empty($errors)) {
        $pdo->prepare("UPDATE anime_watchlist
            SET title=?,genre=?,episodes=?,status=?,rating=?,cover_image=?
            WHERE id=? AND user_id=?")
            ->execute([
                trim($_POST['title']),
                trim($_POST['genre']),
                (int)$_POST['episodes'],
                $_POST['status'],
                (float)$_POST['rating'],
                $cover,
                $id,
                $_SESSION['user_id']
            ]);
        setFlash("✅ '" . trim($_POST['title']) . "' updated successfully!", 'success');
        header('Location: mylist.php'); exit();
    }
}

$val = fn($key) => htmlspecialchars($_POST[$key] ?? $anime[$key] ?? '');
$active_page = 'mylist';
$page_title  = 'Edit Anime — AniTrack';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <?php require 'styles.php'; ?>
    <style>
        .current-cover { width:100%; border-radius:6px; object-fit:cover;
                         border:1px solid rgba(255,255,255,0.1); margin-bottom:10px; }
    </style>
</head>
<body>
<?php require 'navbar.php'; ?>

<div class="container" style="padding-bottom:40px;">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
      <div class="panel panel-anime" style="margin-top:30px;">
        <div class="panel-heading">
          <h3 class="panel-title">
            <span class="glyphicon glyphicon-pencil"></span>
            Edit: <?= htmlspecialchars($anime['title']) ?>
          </h3>
        </div>
        <div class="panel-body">

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <strong><span class="glyphicon glyphicon-exclamation-sign"></span> Please fix:</strong>
              <ul style="margin:8px 0 0 16px">
                <?php foreach ($errors as $e): ?>
                  <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">
            <div class="row">
              <div class="col-sm-8">

                <div class="form-group">
                  <label><span class="glyphicon glyphicon-film"></span> Anime Title *</label>
                  <input type="text" name="title" class="form-control"
                         value="<?= $val('title') ?>">
                </div>

                <div class="form-group">
                  <label><span class="glyphicon glyphicon-tags"></span> Genre *</label>
                  <select name="genre" class="form-control">
                    <option value="">— Select Genre —</option>
                    <?php
                    $cur_genre = $_POST['genre'] ?? $anime['genre'];
                    foreach (GENRES as $g): ?>
                      <option value="<?= $g ?>"
                        <?= $cur_genre === $g ? 'selected' : '' ?>>
                        <?= $g ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label><span class="glyphicon glyphicon-list-alt"></span> Episodes *</label>
                      <input type="number" name="episodes" class="form-control"
                             min="1" value="<?= $val('episodes') ?>">
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label><span class="glyphicon glyphicon-star"></span> Rating *</label>
                      <input type="number" name="rating" class="form-control"
                             min="1" max="10" step="0.1"
                             value="<?= $val('rating') ?>">
                      <p class="rating-hint">Score from 1.0 to 10.0</p>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label><span class="glyphicon glyphicon-signal"></span> Status *</label>
                  <select name="status" class="form-control">
                    <option value="">— Select Status —</option>
                    <?php
                    $cur_status = $_POST['status'] ?? $anime['status'];
                    foreach (WATCHLIST_STATUSES as $s): ?>
                      <option value="<?= $s ?>"
                        <?= $cur_status === $s ? 'selected' : '' ?>>
                        <?= $s ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

              </div>
              <div class="col-sm-4">
                <div class="form-group">
                  <label><span class="glyphicon glyphicon-picture"></span> Cover Image</label>
                  <?php $cur_cover = $anime['cover_image'] !== 'default_cover.png'
                    ? 'uploads/' . htmlspecialchars($anime['cover_image'])
                    : 'https://via.placeholder.com/150x200/1a1a2e/e94560?text=No+Cover'; ?>
                  <img src="<?= $cur_cover ?>" id="imagePreview" class="current-cover"
                       onerror="this.src='https://via.placeholder.com/150x200/1a1a2e/e94560?text=No+Cover'">
                  <input type="file" name="cover_image" class="form-control"
                         accept="image/*" id="coverInput">
                  <p class="rating-hint">Upload new to replace current</p>
                </div>
              </div>
            </div>

            <hr style="border-color:rgba(255,255,255,0.08); margin:20px 0;">
            <div class="text-right">
              <a href="mylist.php" class="btn btn-secondary-custom">
                <span class="glyphicon glyphicon-arrow-left"></span> Cancel
              </a>
              &nbsp;
              <button type="submit" class="btn btn-success-custom">
                <span class="glyphicon glyphicon-floppy-disk"></span> Update Anime
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
document.getElementById('coverInput').addEventListener('change', function() {
    var file = this.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>