<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'auth_guard.php';
require 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// ── Search & Filter ──────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? 'All';
$valid_statuses = ['All','Watching','Completed','Dropped','Plan to Watch'];
if (!in_array($status_filter, $valid_statuses)) $status_filter = 'All';

// ── Pagination ───────────────────────────────────────────
$per_page = 8;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// ── Build Query ──────────────────────────────────────────
$where = "WHERE user_id = ?";
$params = [$user_id];

if (!empty($search)) {
    $where .= " AND (title LIKE ? OR genre LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status_filter !== 'All') {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}

// Count total
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM anime_watchlist $where");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Fetch entries
$params_paged = array_merge($params, [$per_page, $offset]);
$stmt = $pdo->prepare("SELECT * FROM anime_watchlist $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute($params_paged);
$animes = $stmt->fetchAll();

// ── Stats ────────────────────────────────────────────────
$stats_stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM anime_watchlist WHERE user_id = ? GROUP BY status");
$stats_stmt->execute([$user_id]);
$stats_raw = $stats_stmt->fetchAll();
$stats = ['Watching'=>0,'Completed'=>0,'Dropped'=>0,'Plan to Watch'=>0];
foreach ($stats_raw as $s) $stats[$s['status']] = $s['cnt'];
$total_all = array_sum($stats);

// ── Flash messages ───────────────────────────────────────
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Watchlist — Anime Tracker</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body { background:#1a1a2e; color:#eee; font-family:'Segoe UI',sans-serif; }
        .navbar-custom { background:#16213e; border:none; border-bottom:2px solid #e94560; margin-bottom:0; }
        .navbar-custom .navbar-brand { color:#e94560!important; font-weight:700; font-size:20px; }
        .navbar-custom .nav>li>a { color:#ccc!important; }
        .navbar-custom .nav>li>a:hover { color:#e94560!important; background:transparent!important; }
        .navbar-custom .nav>li.active>a { color:#e94560!important; background:transparent!important; }
        .navbar-toggle .icon-bar { background:#e94560; }
        .hero { background:linear-gradient(135deg,#16213e,#0f3460); padding:30px 0 20px; border-bottom:1px solid #e94560; }
        .hero h2 { color:#e94560; font-weight:700; margin:0 0 4px; }
        .hero p { color:#aaa; margin:0; }
        .stat-box { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);
                    border-radius:8px; padding:14px 18px; text-align:center; margin-bottom:10px; }
        .stat-box .num { font-size:28px; font-weight:700; color:#e94560; }
        .stat-box .lbl { font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px; }
        .main-panel { background:#16213e; border-radius:10px; padding:24px; margin-top:24px;
                      border:1px solid rgba(255,255,255,0.07); }
        .nav-tabs { border-bottom:2px solid #e94560; }
        .nav-tabs>li>a { color:#aaa!important; background:transparent!important;
                         border:none!important; border-radius:0!important; padding:10px 18px; }
        .nav-tabs>li.active>a { color:#fff!important; background:#e94560!important;
                                border-radius:6px 6px 0 0!important; }
        .nav-tabs>li>a:hover { color:#e94560!important; background:rgba(233,69,96,0.1)!important; }
        .search-bar { margin:18px 0 14px; }
        .search-bar .form-control { background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.15);
                                    color:#fff; border-radius:6px 0 0 6px; }
        .search-bar .form-control::placeholder { color:#666; }
        .search-bar .btn { background:#e94560; border:none; color:#fff; border-radius:0 6px 6px 0; }
        table { color:#eee!important; }
        .table>thead>tr>th { background:#0f3460; color:#e94560; border-bottom:2px solid #e94560!important;
                             font-size:12px; text-transform:uppercase; letter-spacing:1px; border-top:none!important; }
        .table>tbody>tr>td { border-top:1px solid rgba(255,255,255,0.06)!important;
                             vertical-align:middle!important; }
        .table>tbody>tr:hover>td { background:rgba(233,69,96,0.06)!important; }
        .cover-thumb { width:45px; height:60px; object-fit:cover; border-radius:4px;
                       border:1px solid rgba(255,255,255,0.1); }
        .badge-watching   { background:#3498db; }
        .badge-completed  { background:#27ae60; }
        .badge-dropped    { background:#e74c3c; }
        .badge-plan       { background:#f39c12; }
        .rating-badge { background:#e94560; color:#fff; padding:2px 8px; border-radius:12px;
                        font-size:12px; font-weight:700; }
        .btn-edit   { background:#3498db; border:none; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; }
        .btn-del    { background:#e74c3c; border:none; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; }
        .btn-edit:hover { background:#2980b9; color:#fff; }
        .btn-del:hover  { background:#c0392b; color:#fff; }
        .btn-add { background:#e94560; border:none; color:#fff; font-weight:600;
                   padding:8px 20px; border-radius:6px; }
        .btn-add:hover { background:#c73652; color:#fff; }
        .pagination>li>a { background:#16213e; border-color:#e94560; color:#e94560; }
        .pagination>li>a:hover { background:#e94560; color:#fff; border-color:#e94560; }
        .pagination>.active>a { background:#e94560; border-color:#e94560; color:#fff; }
        .empty-state { text-align:center; padding:60px 0; color:#555; }
        .empty-state .glyphicon { font-size:48px; margin-bottom:16px; }
        .alert-flash { margin-top:16px; }
        .dropdown-menu { background:#16213e; border:1px solid #e94560; }
        .dropdown-menu>li>a { color:#ccc!important; }
        .dropdown-menu>li>a:hover { background:#e94560!important; color:#fff!important; }
        .avatar-sm { width:32px; height:32px; border-radius:50%; object-fit:cover;
                     border:2px solid #e94560; margin-right:6px; vertical-align:middle; }
        @media(max-width:768px){ .hide-mobile{ display:none!important; } }
    </style>
</head>
<body>

<!-- ═══ NAVBAR ═══════════════════════════════════════════ -->
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
        <li class="active"><a href="index.php"><span class="glyphicon glyphicon-home"></span> My List</a></li>
        <li><a href="browse.php"><span class="glyphicon glyphicon-film"></span> Browse</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="avatars/<?= htmlspecialchars($_SESSION['avatar'] ?? 'default.png') ?>"
                 class="avatar-sm"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($username) ?>&background=e94560&color=fff&size=32'">
            <?= htmlspecialchars($username) ?> <span class="caret"></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-right">
            <li><a href="index.php"><span class="glyphicon glyphicon-list"></span> My Watchlist</a></li>
            <li><a href="browse.php"><span class="glyphicon glyphicon-globe"></span> Browse All</a></li>
            <li class="divider"></li>
            <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div style="padding-top:50px;">

<!-- ═══ HERO ══════════════════════════════════════════════ -->
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
        <a href="browse.php" class="btn btn-default">
          <span class="glyphicon glyphicon-globe"></span> Browse Others
        </a>
      </div>
    </div>

    <!-- Stats row -->
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

<!-- ═══ MAIN CONTENT ══════════════════════════════════════ -->
<div class="container">

  <?php if ($flash): ?>
    <div class="alert alert-<?= strpos($flash,'success') !== false ? 'success' : (strpos($flash,'deleted') !== false ? 'warning' : 'danger') ?> alert-dismissible alert-flash">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <span class="glyphicon glyphicon-<?= strpos($flash,'success') !== false ? 'ok' : 'info-sign' ?>"></span>
      <?= htmlspecialchars($flash) ?>
    </div>
  <?php endif; ?>

  <div class="main-panel">

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="statusTabs">
      <?php
      $tab_statuses = ['All','Watching','Completed','Dropped','Plan to Watch'];
      foreach ($tab_statuses as $tab):
        $active = ($status_filter === $tab) ? 'active' : '';
        $cnt = ($tab === 'All') ? $total_all : $stats[$tab];
        $url = '?status='.urlencode($tab).(!empty($search) ? '&search='.urlencode($search) : '');
      ?>
        <li class="<?= $active ?>">
          <a href="<?= $url ?>">
            <?= $tab ?> <span class="badge" style="background:#e94560"><?= $cnt ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- Search -->
    <div class="search-bar">
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

    <!-- Table -->
    <?php if (empty($animes)): ?>
      <div class="empty-state">
        <div><span class="glyphicon glyphicon-film"></span></div>
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
            <?php foreach ($animes as $i => $anime): ?>
            <?php
              $status_class = match($anime['status']) {
                'Watching'     => 'badge-watching',
                'Completed'    => 'badge-completed',
                'Dropped'      => 'badge-dropped',
                default        => 'badge-plan'
              };
              $cover = !empty($anime['cover_image']) && $anime['cover_image'] !== 'default_cover.png'
                ? 'uploads/' . htmlspecialchars($anime['cover_image'])
                : 'https://via.placeholder.com/45x60/1a1a2e/e94560?text=?';
            ?>
            <tr>
              <td style="color:#555"><?= $offset + $i + 1 ?></td>
              <td>
                <img src="<?= $cover ?>" class="cover-thumb img-thumbnail"
                     onerror="this.src='https://via.placeholder.com/45x60/1a1a2e/e94560?text=?'">
              </td>
              <td>
                <strong><?= htmlspecialchars($anime['title']) ?></strong>
                <br><small style="color:#555"><?= htmlspecialchars(date('M d, Y', strtotime($anime['created_at']))) ?></small>
              </td>
              <td class="hide-mobile">
                <span class="label label-default"><?= htmlspecialchars($anime['genre']) ?></span>
              </td>
              <td class="hide-mobile">
                <span class="badge"><?= $anime['episodes'] ?> eps</span>
              </td>
              <td>
                <span class="badge <?= $status_class ?>"><?= $anime['status'] ?></span>
              </td>
              <td>
                <span class="rating-badge">⭐ <?= number_format($anime['rating'], 1) ?></span>
              </td>
              <td>
                <div class="btn-group btn-group-xs">
                  <a href="edit.php?id=<?= $anime['id'] ?>" class="btn btn-edit"
                     data-toggle="tooltip" title="Edit">
                    <span class="glyphicon glyphicon-pencil"></span>
                  </a>
                  <button class="btn btn-del" data-toggle="modal" data-target="#deleteModal"
                          data-id="<?= $anime['id'] ?>" data-title="<?= htmlspecialchars($anime['title']) ?>"
                          data-toggle="tooltip" title="Delete">
                    <span class="glyphicon glyphicon-trash"></span>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <nav style="text-align:center; margin-top:10px;">
          <ul class="pagination">
            <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
              <a href="?page=<?= $page-1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">
                &laquo; Prev
              </a>
            </li>
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
              <li class="<?= $p == $page ? 'active' : '' ?>">
                <a href="?page=<?= $p ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">
                  <?= $p ?>
                </a>
              </li>
            <?php endfor; ?>
            <li class="<?= $page >= $total_pages ? 'disabled' : '' ?>">
              <a href="?page=<?= $page+1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">
                Next &raquo;
              </a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</div>

<!-- ═══ DELETE MODAL ═════════════════════════════════════ -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content" style="background:#16213e; border:1px solid #e94560; color:#eee;">
      <div class="modal-header" style="border-bottom:1px solid #e94560;">
        <button type="button" class="close" data-dismiss="modal" style="color:#eee;">&times;</button>
        <h4 class="modal-title" style="color:#e94560;">
          <span class="glyphicon glyphicon-trash"></span> Delete Anime
        </h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete <strong id="deleteTitle"></strong>?</p>
        <p style="color:#888; font-size:13px;">This action cannot be undone.</p>
      </div>
      <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.1);">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <a href="#" id="deleteConfirmBtn" class="btn btn-danger">
          <span class="glyphicon glyphicon-trash"></span> Delete
        </a>
      </div>
    </div>
  </div>
</div>

</div><!-- end padding-top div -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
// Tooltips
$('[data-toggle="tooltip"]').tooltip();

// Delete modal
$('#deleteModal').on('show.bs.modal', function(e) {
    var btn   = $(e.relatedTarget);
    var id    = btn.data('id');
    var title = btn.data('title');
    $('#deleteTitle').text(title);
    $('#deleteConfirmBtn').attr('href', 'delete.php?id=' + id);
});
</script>
</body>
</html>