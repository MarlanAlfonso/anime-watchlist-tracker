<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'auth_guard.php';
require 'db.php';
require 'helpers.php';

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

$search        = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? 'All';
$valid_statuses = ['All','Watching','Completed','Dropped','Plan to Watch'];
if (!in_array($status_filter, $valid_statuses)) $status_filter = 'All';

$per_page = 8;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$where  = "WHERE user_id = ?";
$params = [$user_id];
if (!empty($search)) {
    $where   .= " AND (title LIKE ? OR genre LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status_filter !== 'All') {
    $where   .= " AND status = ?";
    $params[] = $status_filter;
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM anime_watchlist $where");
$count_stmt->execute($params);
$total       = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$params_paged = array_merge($params, [$per_page, $offset]);
$stmt = $pdo->prepare("SELECT * FROM anime_watchlist $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute($params_paged);
$animes = $stmt->fetchAll();

$stats_stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM anime_watchlist WHERE user_id = ? GROUP BY status");
$stats_stmt->execute([$user_id]);
$stats_raw = $stats_stmt->fetchAll();
$stats     = ['Watching'=>0,'Completed'=>0,'Dropped'=>0,'Plan to Watch'=>0];
foreach ($stats_raw as $s) $stats[$s['status']] = $s['cnt'];
$total_all = array_sum($stats);

$flash_data = getFlash();
$flash      = $flash_data['message'];
$flash_type = $flash_data['type'];

$active_page = 'mylist';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Watchlist — AniTrack</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <?php require 'styles.php'; ?>
    <style>
        .hero { background:linear-gradient(135deg,#16213e,#0f3460);
                padding:30px 0 20px; border-bottom:1px solid #e94560; }
        .hero h2 { color:#e94560; font-weight:700; margin:0 0 4px; }
        .hero p   { color:#aaa; margin:0; }
        .stat-box { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);
                    border-radius:8px; padding:14px 18px; text-align:center; margin-bottom:10px; }
        .stat-box .num { font-size:28px; font-weight:700; color:#e94560; }
        .stat-box .lbl { font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px; }
        .main-panel { background:#16213e; border-radius:10px; padding:24px; margin-top:24px;
                      border:1px solid rgba(255,255,255,0.07); }
        .search-bar .form-control { background:rgba(255,255,255,0.07);
                                    border:1px solid rgba(255,255,255,0.15);
                                    color:#fff; border-radius:6px 0 0 6px; }
        .search-bar .form-control::placeholder { color:#666; }
        .search-bar .btn { background:#e94560; border:none; color:#fff; border-radius:0 6px 6px 0; }
        .cover-thumb { width:45px; height:60px; object-fit:cover; border-radius:4px;
                       border:1px solid rgba(255,255,255,0.1); }
        .btn-edit { background:#3498db; border:none; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; }
        .btn-del  { background:#e74c3c; border:none; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; }
        .btn-edit:hover { background:#2980b9; color:#fff; }
        .btn-del:hover  { background:#c0392b; color:#fff; }
        .btn-add { background:#e94560; border:none; color:#fff; font-weight:600; padding:8px 20px; border-radius:6px; }
        .btn-add:hover { background:#c73652; color:#fff; }
        table { color:#eee!important; }
        .table>thead>tr>th { background:#0f3460; color:#e94560; border-bottom:2px solid #e94560!important;
                             font-size:12px; text-transform:uppercase; letter-spacing:1px; border-top:none!important; }
        .table>tbody>tr>td { border-top:1px solid rgba(255,255,255,0.06)!important; vertical-align:middle!important; }
        .table>tbody>tr:hover>td { background:rgba(233,69,96,0.06)!important; }
    </style>
</head>
<body>
<?php require 'navbar.php'; ?>

<div style="padding-top:0;">

<div class="hero">
  <div class="container">
    <div class="row">
      <div class="col-sm-6">
        <h2>🎌 <?= htmlspecialchars($username) ?>'s Watchlist</h2>
        <p>Track your anime journey</p>
      </div>
      <div class="col-sm-6 text-right" style="padding-top:8px;">
        <a href="create.php" class="btn btn-add">
          <span class="glyphicon glyphicon-plus"></span> Add Anime
        </a>
        &nbsp;
        <a href="browse.php" class="btn btn-secondary-custom">
          <span class="glyphicon glyphicon-globe"></span> Browse Others
        </a>
      </div>
    </div>
    <div class="row" style="margin-top:20px;">
      <div class="col-xs-6 col-sm-3">
        <div class="stat-box">
          <div class="num"><?= $total_all ?></div>
          <div class="lbl">Total</div>
        </div>
      </div>
      <div class="col-xs-6 col-sm-3">
        <div class="stat-box">
          <div class="num" style="color:#3498db"><?= $stats['Watching'] ?></div>
          <div class="lbl">Watching</div>
        </div>
      </div>
      <div class="col-xs-6 col-sm-3">
        <div class="stat-box">
          <div class="num" style="color:#27ae60"><?= $stats['Completed'] ?></div>
          <div class="lbl">Completed</div>
        </div>
      </div>
      <div class="col-xs-6 col-sm-3">
        <div class="stat-box">
          <div class="num" style="color:#f39c12"><?= $stats['Plan to Watch'] ?></div>
          <div class="lbl">Plan to Watch</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash_type ?> alert-dismissible" style="margin-top:16px;">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?= htmlspecialchars($flash) ?>
    </div>
  <?php endif; ?>

  <div class="main-panel">
    <ul class="nav nav-tabs">
      <?php foreach (['All','Watching','Completed','Dropped','Plan to Watch'] as $tab):
        $cnt = ($tab === 'All') ? $total_all : $stats[$tab];
        $url = '?status='.urlencode($tab).(!empty($search)?'&search='.urlencode($search):'');
      ?>
        <li class="<?= $status_filter===$tab?'active':'' ?>">
          <a href="<?= $url ?>">
            <?= $tab ?> <span class="badge" style="background:#e94560"><?= $cnt ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="search-bar" style="margin:18px 0 14px;">
      <form method="GET" class="input-group">
        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
        <input type="text" name="search" class="form-control"
               placeholder="Search by title or genre..."
               value="<?= htmlspecialchars($search) ?>">
        <span class="input-group-btn">
          <button class="btn" type="submit">
            <span class="glyphicon glyphicon-search"></span>
          </button>
        </span>
      </form>
    </div>

    <?php if (empty($animes)): ?>
      <div class="empty-state">
        <span class="glyphicon glyphicon-film"></span>
        <h4 style="color:#555">No anime found</h4>
        <p style="color:#444">
          <?= !empty($search) ? 'Try a different search term.' : 'Start by adding your first anime!' ?>
        </p>
        <?php if (empty($search)): ?>
          <a href="create.php" class="btn btn-add">
            <span class="glyphicon glyphicon-plus"></span> Add Anime
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Cover</th>
              <th>Title</th>
              <th class="hide-mobile">Genre</th>
              <th class="hide-mobile">Episodes</th>
              <th>Status</th>
              <th>Rating</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($animes as $i => $anime):
              $sc = match($anime['status']) {
                'Watching'  => 'badge-watching',
                'Completed' => 'badge-completed',
                'Dropped'   => 'badge-dropped',
                default     => 'badge-plan'
              };
              $cover = coverUrl($anime['cover_image'] ?? '', 45, 60);
            ?>
            <tr>
              <td style="color:#555"><?= $offset+$i+1 ?></td>
              <td>
                <img src="<?= $cover ?>" class="cover-thumb img-thumbnail"
                     onerror="this.src='https://via.placeholder.com/45x60/1a1a2e/e94560?text=?'">
              </td>
              <td>
                <strong><?= htmlspecialchars($anime['title']) ?></strong><br>
                <small style="color:#555"><?= date('M d, Y', strtotime($anime['created_at'])) ?></small>
              </td>
              <td class="hide-mobile">
                <span class="label label-default"><?= htmlspecialchars($anime['genre']) ?></span>
              </td>
              <td class="hide-mobile">
                <span class="badge"><?= $anime['episodes'] ?> eps</span>
              </td>
              <td>
                <span class="badge <?= $sc ?>"><?= $anime['status'] ?></span>
              </td>
              <td>
                <span class="rating-badge">⭐ <?= number_format($anime['rating'],1) ?></span>
              </td>
              <td>
                <div class="btn-group btn-group-xs">
                  <a href="edit.php?id=<?= $anime['id'] ?>" class="btn btn-edit"
                     data-toggle="tooltip" title="Edit">
                    <span class="glyphicon glyphicon-pencil"></span>
                  </a>
                  <button class="btn btn-del"
                          data-toggle="modal" data-target="#deleteModal"
                          data-id="<?= $anime['id'] ?>"
                          data-title="<?= htmlspecialchars($anime['title']) ?>"
                          title="Delete">
                    <span class="glyphicon glyphicon-trash"></span>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($total_pages > 1): ?>
        <nav style="text-align:center; margin-top:10px;">
          <ul class="pagination">
            <li class="<?= $page<=1?'disabled':'' ?>">
              <a href="?page=<?= $page-1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">&laquo; Prev</a>
            </li>
            <?php for ($p=1; $p<=$total_pages; $p++): ?>
              <li class="<?= $p==$page?'active':'' ?>">
                <a href="?page=<?= $p ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
            <li class="<?= $page>=$total_pages?'disabled':'' ?>">
              <a href="?page=<?= $page+1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">Next &raquo;</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dark">
    <div class="modal-content" style="background:#16213e;border:1px solid #e94560;color:#eee;border-radius:10px;">
      <div class="modal-header" style="border-bottom:1px solid rgba(233,69,96,0.3);">
        <button type="button" class="close" data-dismiss="modal" style="color:#eee;">&times;</button>
        <h4 class="modal-title" style="color:#e94560;">
          <span class="glyphicon glyphicon-trash"></span> Delete Anime
        </h4>
      </div>
      <div class="modal-body">
        <p>Delete <strong id="deleteTitle"></strong>?</p>
        <p style="color:#888; font-size:13px;">This cannot be undone.</p>
      </div>
      <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.1);">
        <button type="button" class="btn btn-secondary-custom" data-dismiss="modal">Cancel</button>
        <a href="#" id="deleteConfirmBtn" class="btn btn-danger-custom">
          <span class="glyphicon glyphicon-trash"></span> Delete
        </a>
      </div>
    </div>
  </div>
</div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
$(function() {
    $('[data-toggle="tooltip"]').tooltip();
    $('#deleteModal').on('show.bs.modal', function(e) {
        var b = $(e.relatedTarget);
        $('#deleteTitle').text(b.data('title'));
        $('#deleteConfirmBtn').attr('href', 'delete.php?id=' + b.data('id'));
    });
});
</script>
</body>
</html>