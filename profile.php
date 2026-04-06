<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db.php';

$logged_in       = isset($_SESSION['user_id']);
$current_user_id = $_SESSION['user_id'] ?? 0;

// ── Get profile user ─────────────────────────────────────
$profile_id = (int)($_GET['id'] ?? 0);
if (!$profile_id) { header('Location: browse.php'); exit(); }

$u_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$u_stmt->execute([$profile_id]);
$profile_user = $u_stmt->fetch();
if (!$profile_user) { header('Location: browse.php'); exit(); }

// ── Filter & Pagination ──────────────────────────────────
$status_filter = $_GET['status'] ?? 'All';
$valid_statuses = ['All','Watching','Completed','Dropped','Plan to Watch'];
if (!in_array($status_filter, $valid_statuses)) $status_filter = 'All';

$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$where  = "WHERE user_id = ?";
$params = [$profile_id];
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

// ── Stats ────────────────────────────────────────────────
$stats_stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM anime_watchlist WHERE user_id = ? GROUP BY status");
$stats_stmt->execute([$profile_id]);
$stats_raw = $stats_stmt->fetchAll();
$stats = ['Watching'=>0,'Completed'=>0,'Dropped'=>0,'Plan to Watch'=>0];
foreach ($stats_raw as $s) $stats[$s['status']] = $s['cnt'];
$total_all = array_sum($stats);

// ── Pager: prev/next user ────────────────────────────────
$prev_stmt = $pdo->prepare("SELECT id FROM users WHERE id < ? ORDER BY id DESC LIMIT 1");
$prev_stmt->execute([$profile_id]);
$prev_user = $prev_stmt->fetch();

$next_stmt = $pdo->prepare("SELECT id FROM users WHERE id > ? ORDER BY id ASC LIMIT 1");
$next_stmt->execute([$profile_id]);
$next_user = $next_stmt->fetch();

// ── Flash ────────────────────────────────────────────────
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$is_own = ($current_user_id === $profile_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profile_user['username']) ?>'s List — AniTrack</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body { background:#1a1a2e; color:#eee; font-family:'Segoe UI',sans-serif; padding-top:60px; }
        .navbar-custom { background:#16213e; border:none; border-bottom:2px solid #e94560; }
        .navbar-custom .navbar-brand { color:#e94560!important; font-weight:700; }
        .navbar-custom .nav>li>a { color:#ccc!important; }
        .navbar-custom .nav>li>a:hover { color:#e94560!important; background:transparent!important; }
        .navbar-toggle .icon-bar { background:#e94560; }
        .dropdown-menu { background:#16213e; border:1px solid #e94560; }
        .dropdown-menu>li>a { color:#ccc!important; }
        .dropdown-menu>li>a:hover { background:#e94560!important; color:#fff!important; }
        .avatar-sm { width:32px; height:32px; border-radius:50%; object-fit:cover;
                     border:2px solid #e94560; margin-right:6px; vertical-align:middle; }
        /* Profile hero */
        .profile-hero { background:linear-gradient(135deg,#16213e,#0f3460);
                        padding:30px 0 24px; border-bottom:1px solid #e94560; margin-bottom:24px; }
        .profile-avatar { width:80px; height:80px; border-radius:50%; object-fit:cover;
                          border:3px solid #e94560; }
        .profile-hero h2 { color:#e94560; font-weight:700; margin:0 0 4px; }
        .profile-hero p  { color:#888; margin:0; font-size:13px; }
        /* Stat boxes */
        .stat-box { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08);
                    border-radius:8px; padding:12px 14px; text-align:center; }
        .stat-box .num { font-size:24px; font-weight:700; color:#e94560; }
        .stat-box .lbl { font-size:11px; color:#777; text-transform:uppercase; letter-spacing:1px; }
        /* Main panel */
        .main-panel { background:#16213e; border:1px solid rgba(255,255,255,0.07);
                      border-radius:10px; padding:22px; }
        /* Tabs */
        .nav-tabs { border-bottom:2px solid #e94560; }
        .nav-tabs>li>a { color:#aaa!important; background:transparent!important;
                         border:none!important; border-radius:0!important; padding:9px 16px; }
        .nav-tabs>li.active>a { color:#fff!important; background:#e94560!important;
                                border-radius:6px 6px 0 0!important; }
        .nav-tabs>li>a:hover { color:#e94560!important; background:rgba(233,69,96,0.1)!important; }
        table { color:#eee!important; }
        .table>thead>tr>th { background:#0f3460; color:#e94560;
                             border-bottom:2px solid #e94560!important;
                             font-size:11px; text-transform:uppercase; letter-spacing:1px;
                             border-top:none!important; }
        .table>tbody>tr>td { border-top:1px solid rgba(255,255,255,0.05)!important;
                             vertical-align:middle!important; }
        .table>tbody>tr:hover>td { background:rgba(233,69,96,0.05)!important; }
        .cover-thumb { width:40px; height:54px; object-fit:cover;
                       border-radius:4px; border:1px solid rgba(255,255,255,0.1); }
        .badge-watching  { background:#3498db; }
        .badge-completed { background:#27ae60; }
        .badge-dropped   { background:#e74c3c; }
        .badge-plan      { background:#f39c12; }
        .rating-star { background:#e94560; color:#fff; padding:2px 8px;
                       border-radius:12px; font-size:11px; font-weight:700; }
        .btn-copy { background:#27ae60; border:none; color:#fff;
                    padding:3px 10px; border-radius:4px; font-size:11px; font-weight:600; }
        .btn-copy:hover { background:#219a52; color:#fff; }
        /* Pager */
        .pager>li>a { background:#16213e; border-color:#e94560; color:#e94560; border-radius:20px; }
        .pager>li>a:hover { background:#e94560; color:#fff; }
        /* Pagination */
        .pagination>li>a { background:#16213e; border-color:#e94560; color:#e94560; }
        .pagination>li>a:hover { background:#e94560; color:#fff; }
        .pagination>.active>a { background:#e94560; border-color:#e94560; color:#fff; }
        .empty-state { text-align:center; padding:50px; color:#555; }
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
      <a class="navbar-brand" href="<?= $logged_in ? 'index.php' : 'browse.php' ?>">🎌 AniTrack</a>
    </div>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="nav navbar-nav">
        <?php if ($logged_in): ?>
          <li><a href="index.php"><span class="glyphicon glyphicon-home"></span> My List</a></li>
        <?php endif; ?>
        <li><a href="browse.php"><span class="glyphicon glyphicon-film"></span> Browse</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php if ($logged_in): ?>
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
        <?php else: ?>
          <li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
          <li><a href="register.php"><span class="glyphicon glyphicon-user"></span> Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- ═══ PROFILE HERO ════════════════════════════════════ -->
<div class="profile-hero">
  <div class="container">
    <div class="row">
      <div class="col-sm-8">
        <div style="display:flex; align-items:center; gap:18px;">
          <img src="avatars/<?= htmlspecialchars($profile_user['avatar'] ?? 'default.png') ?>"
               class="profile-avatar"
               onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($profile_user['username']) ?>&background=e94560&color=fff&size=80'">
          <div>
            <h2><?= htmlspecialchars($profile_user['username']) ?>'s List</h2>
            <p>Member since <?= date('F Y', strtotime($profile_user['created_at'])) ?></p>
            <?php if ($is_own): ?>
              <span class="label label-danger" style="font-size:11px;">Your Profile</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-sm-4 text-right" style="padding-top:8px;">
        <a href="browse.php" class="btn btn-default btn-sm">
          <span class="glyphicon glyphicon-arrow-left"></span> Back to Browse
        </a>
      </div>
    </div>

    <!-- Stats -->
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

  <!-- Pager: prev/next user -->
  <ul class="pager" style="margin:10px 0 20px;">
    <li class="previous <?= !$prev_user?'disabled':'' ?>">
      <a href="<?= $prev_user ? 'profile.php?id='.$prev_user['id'] : '#' ?>">
        <span class="glyphicon glyphicon-chevron-left"></span> Previous User
      </a>
    </li>
    <li class="next <?= !$next_user?'disabled':'' ?>">
      <a href="<?= $next_user ? 'profile.php?id='.$next_user['id'] : '#' ?>">
        Next User <span class="glyphicon glyphicon-chevron-right"></span>
      </a>
    </li>
  </ul>

  <?php if ($flash): ?>
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?= htmlspecialchars($flash) ?>
    </div>
  <?php endif; ?>

  <div class="main-panel">

    <!-- Tabs -->
    <ul class="nav nav-tabs" style="margin-bottom:16px;">
      <?php
      $tab_statuses = ['All','Watching','Completed','Dropped','Plan to Watch'];
      foreach ($tab_statuses as $tab):
        $active = ($status_filter === $tab) ? 'active' : '';
        $cnt    = ($tab === 'All') ? $total_all : $stats[$tab];
        $url    = '?id='.$profile_id.'&status='.urlencode($tab);
      ?>
        <li class="<?= $active ?>">
          <a href="<?= $url ?>">
            <?= $tab ?> <span class="badge" style="background:#e94560"><?= $cnt ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- Table -->
    <?php if (empty($animes)): ?>
      <div class="empty-state">
        <span class="glyphicon glyphicon-film" style="font-size:48px;color:#333;display:block;margin-bottom:14px;"></span>
        <h4 style="color:#555">No anime in this category</h4>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Cover</th>
              <th>Title</th>
              <th>Genre</th>
              <th>Eps</th>
              <th>Status</th>
              <th>Rating</th>
              <?php if ($logged_in && !$is_own): ?>
                <th>Action</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($animes as $i => $anime): ?>
            <?php
              $status_class = match($anime['status']) {
                'Watching'    => 'badge-watching',
                'Completed'   => 'badge-completed',
                'Dropped'     => 'badge-dropped',
                default       => 'badge-plan'
              };
              $cover = (!empty($anime['cover_image']) && $anime['cover_image'] !== 'default_cover.png')
                ? 'uploads/'.htmlspecialchars($anime['cover_image'])
                : 'https://via.placeholder.com/40x54/1a1a2e/e94560?text=?';
            ?>
            <tr>
              <td style="color:#555"><?= $offset + $i + 1 ?></td>
              <td>
                <img src="<?= $cover ?>" class="cover-thumb"
                     onerror="this.src='https://via.placeholder.com/40x54/1a1a2e/e94560?text=?'">
              </td>
              <td><strong><?= htmlspecialchars($anime['title']) ?></strong></td>
              <td><span class="label label-default"><?= htmlspecialchars($anime['genre']) ?></span></td>
              <td><span class="badge"><?= $anime['episodes'] ?> eps</span></td>
              <td><span class="badge <?= $status_class ?>"><?= $anime['status'] ?></span></td>
              <td><span class="rating-star">⭐ <?= number_format($anime['rating'],1) ?></span></td>
              <?php if ($logged_in && !$is_own): ?>
                <td>
                  <form method="POST" action="copy.php" style="display:inline;">
                    <input type="hidden" name="anime_id" value="<?= $anime['id'] ?>">
                    <button type="submit" class="btn btn-copy"
                            data-toggle="tooltip" title="Copy to my list">
                      <span class="glyphicon glyphicon-plus"></span> Copy
                    </button>
                  </form>
                </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <nav style="text-align:center; margin-top:10px;">
          <ul class="pagination">
            <li class="<?= $page<=1?'disabled':'' ?>">
              <a href="?id=<?= $profile_id ?>&page=<?= $page-1 ?>&status=<?= urlencode($status_filter) ?>">&laquo;</a>
            </li>
            <?php for ($p=1; $p<=$total_pages; $p++): ?>
              <li class="<?= $p==$page?'active':'' ?>">
                <a href="?id=<?= $profile_id ?>&page=<?= $p ?>&status=<?= urlencode($status_filter) ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
            <li class="<?= $page>=$total_pages?'disabled':'' ?>">
              <a href="?id=<?= $profile_id ?>&page=<?= $page+1 ?>&status=<?= urlencode($status_filter) ?>">&raquo;</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
  $('[data-toggle="tooltip"]').tooltip();
</script>
</body>
</html>