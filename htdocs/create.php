<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'auth_guard.php';
require 'db.php';
require 'helpers.php';

$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateWatchlistFields($_POST);

    $cover = 'default_cover.png';
    if (isset($_FILES['cover_image'])) {
        $cover = handleImageUpload($_FILES['cover_image'], 'cover', $errors);
    }

    if (empty($errors)) {
        $pdo->prepare("INSERT INTO anime_watchlist
            (user_id,title,genre,episodes,status,rating,cover_image)
            VALUES (?,?,?,?,?,?,?)")
            ->execute([
                $_SESSION['user_id'],
                trim($_POST['title']),
                trim($_POST['genre']),
                (int)$_POST['episodes'],
                $_POST['status'],
                (float)$_POST['rating'],
                $cover
            ]);
        setFlash("✅ '" . trim($_POST['title']) . "' added successfully!", 'success');
        header('Location: mylist.php'); exit();
    }
}

$active_page = 'mylist';
$page_title  = 'Add Anime — AniTrack';
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
        .image-preview { width:100%; max-height:200px; object-fit:cover;
                         border-radius:6px; margin-top:10px; display:none;
                         border:1px solid rgba(255,255,255,0.1); }
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
            <span class="glyphicon glyphicon-plus"></span> Add New Anime to My List
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

                <div class="form-group <?= in_array('Title is required.',$errors)?'has-error':'' ?>">
                  <label><span class="glyphicon glyphicon-film"></span> Anime Title *</label>
                  <input type="text" name="title" class="form-control"
                         placeholder="e.g. Attack on Titan"
                         value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>

                <div class="form-group <?= in_array('Genre is required.',$errors)?'has-error':'' ?>">
                  <label><span class="glyphicon glyphicon-tags"></span> Genre *</label>
                  <select name="genre" class="form-control">
                    <option value="">— Select Genre —</option>
                    <?php foreach (GENRES as $g): ?>
                      <option value="<?= $g ?>"
                        <?= (($_POST['genre'] ?? '') === $g) ? 'selected' : '' ?>>
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
                             placeholder="e.g. 24" min="1"
                             value="<?= htmlspecialchars($_POST['episodes'] ?? '') ?>">
                    </div>
                  </div>
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

                <div class="form-group <?= in_array('Status is required.',$errors)?'has-error':'' ?>">
                  <label><span class="glyphicon glyphicon-signal"></span> Status *</label>
                  <select name="status" class="form-control">
                    <option value="">— Select Status —</option>
                    <?php foreach (WATCHLIST_STATUSES as $s): ?>
                      <option value="<?= $s ?>"
                        <?= (($_POST['status'] ?? '') === $s) ? 'selected' : '' ?>>
                        <?= $s ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

              </div>
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
              <a href="mylist.php" class="btn btn-secondary-custom">
                <span class="glyphicon glyphicon-arrow-left"></span> Cancel
              </a>
              &nbsp;
              <button type="submit" class="btn btn-primary-custom">
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
document.getElementById('coverInput').addEventListener('change', function() {
    var file = this.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var p = document.getElementById('imagePreview');
            p.src = e.target.result;
            p.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>