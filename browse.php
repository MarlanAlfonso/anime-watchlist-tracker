<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db.php';

$logged_in = isset($_SESSION['user_id']);
$current_user_id = $_SESSION['user_id'] ?? 0;

// ── Search & Filter ──────────────────────────────────────
$search      = trim($_GET['search'] ?? '');
$genre_filter= $_GET['genre'] ?? '';
$status_filter = $_GET['status'] ?? 'All';
$valid_statuses = ['All','Watching','Completed','Dropped','Plan to Watch'];
if (!in_array($status_filter, $valid_statuses)) $status_filter = 'All';

// ── Pagination ───────────────────────────────────────────
$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

// ── Build Query ──────────────────────────────────────────
$where  = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where   .= " AND (a.title LIKE ? OR a.genre LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($genre_filter)) {
    $where   .= " AND a.genre = ?";
    $params[] = $genre_filter;
}
if ($status_filter !== 'All') {
    $where   .= " AND a.status = ?";
    $params[] = $status_filter;
}

// Count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM anime_watchlist a JOIN users u ON a.user_id = u.id $where");
$count_stmt->execute($params);
$total       = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Fetch
$params_paged = array_merge($params, [$per_page, $offset]);
$stmt = $pdo->prepare("SELECT a.*, u.username, u.avatar FROM anime_watchlist a
    JOIN users u ON a.user_id = u.id
    $where ORDER BY a.created_at DESC LIMIT ? OFFSET ?");
$stmt->execute($params_paged);
$animes = $stmt->fetchAll();

// ── Featured (top rated) for Carousel ───────────────────
$featured_stmt = $pdo->query("SELECT a.*, u.username FROM anime_watchlist a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.rating DESC, a.created_at DESC LIMIT 5");
$featured = $featured_stmt->fetchAll();

// ── Genre list for sidebar ───────────────────────────────
$genre_stmt = $pdo->query("SELECT genre, COUNT(*) as cnt FROM anime_watchlist GROUP BY genre ORDER BY cnt DESC");
$genres = $genre_stmt->fetchAll();

// ── User list for sidebar ────────────────────────────────
$user_stmt = $pdo->query("SELECT u.id, u.username, u.avatar, COUNT(a.id) as total
    FROM users u LEFT JOIN anime_watchlist a ON u.id = a.user_id
    GROUP BY u.id ORDER BY total DESC LIMIT 8");
$top_users = $user_stmt->fetchAll();

// ── Stats ────────────────────────────────────────────────
$total_anime_stmt = $pdo->query("SELECT COUNT(*) FROM anime_watchlist");
$total_anime = $total_anime_stmt->fetchColumn();
$total_users_stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $total_users_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Anime — AniTrack</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body { background:#1a1a2e; color:#eee; font-family:'Segoe UI',sans-serif; }
        /* Navbar */
        .navbar-custom { background:#16213e; border:none; border-bottom:2px solid #e94560; margin-bottom:0; }
        .navbar-custom .navbar-brand { color:#e94560!important; font-weight:700; font-size:20px; }
        .navbar-custom .nav>li>a { color:#ccc!important; }
        .navbar-custom .nav>li>a:hover { color:#e94560!important; background:transparent!important; }
        .navbar-custom .nav>li.active>a { color:#e94560!important; background:transparent!important; }
        .navbar-toggle .icon-bar { background:#e94560; }
        .dropdown-menu { background:#16213e; border:1px solid #e94560; }
        .dropdown-menu>li>a { color:#ccc!important; }
        .dropdown-menu>li>a:hover { background:#e94560!important; color:#fff!important; }
        .avatar-sm { width:32px; height:32px; border-radius:50%; object-fit:cover;
                     border:2px solid #e94560; margin-right:6px; vertical-align:middle; }
        /* Carousel */
        .carousel-section { background:#0f3460; padding:0; border-bottom:2px solid #e94560; }
        .carousel-inner>.item>img { height:320px; object-fit:cover; width:100%; filter:brightness(0.45); }
        .carousel-caption { bottom:30px; }
        .carousel-caption h3 { font-size:28px; font-weight:700; color:#fff;
                               text-shadow:0 2px 8px rgba(0,0,0,0.8); }
        .carousel-caption p  { color:#ddd; font-size:14px; }
        .carousel-caption .badge-rating { background:#e94560; padding:4px 12px;
                                          border-radius:20px; font-size:14px; font-weight:700; }
        .carousel-indicators li { border-color:#e94560; }
        .carousel-indicators .active { background:#e94560; }
        .carousel-control { background:none!important; }
        /* Stats bar */
        .stats-bar { background:#16213e; padding:12px 0;
                     border-bottom:1px solid rgba(255,255,255,0.05); }
        .stats-bar .stat { display:inline-block; margin-right:24px; color:#aaa; font-size:13px; }
        .stats-bar .stat strong { color:#e94560; }
        /* Main layout */
        .main-content { padding-top:24px; padding-bottom:40px; }
        /* Sidebar */
        .sidebar-panel { background:#16213e; border:1px solid rgba(255,255,255,0.07);
                         border-radius:10px; margin-bottom:20px; overflow:hidden; }
        .sidebar-panel .panel-head { background:#0f3460; padding:12px 16px;
                                     border-bottom:1px solid #e94560; }
        .sidebar-panel .panel-head h5 { color:#e94560; margin:0; font-weight:700;
                                        font-size:13px; text-transform:uppercase; letter-spacing:1px; }
        /* List group */
        .list-group-item-custom { background:transparent; border:none;
                                  border-bottom:1px solid rgba(255,255,255,0.05);
                                  padding:9px 16px; cursor:pointer; display:block;
                                  color:#ccc; font-size:13px; transition:all 0.15s; }
        .list-group-item-custom:hover { background:rgba(233,69,96,0.1); color:#e94560;
                                        text-decoration:none; }
        .list-group-item-custom.active-genre { background:rgba(233,69,96,0.15);
                                               color:#e94560; border-left:3px solid #e94560; }
        .genre-badge { float:right; background:#0f3460; color:#aaa;
                       padding:1px 8px; border-radius:10px; font-size:11px; }
        /* User cards in sidebar */
        .user-item { padding:10px 16px; border-bottom:1px solid rgba(255,255,255,0.05);
                     display:flex; align-items:center; gap:10px; }
        .user-item:hover { background:rgba(233,69,96,0.07); }
        .user-item img { width:34px; height:34px; border-radius:50%; object-fit:cover;
                         border:2px solid #e94560; flex-shrink:0; }
        .user-item .uname { color:#eee; font-size:13px; font-weight:600; }
        .user-item .ucnt  { color:#888; font-size:11px; }
        /* Anime cards table */
        .browse-panel { background:#16213e; border:1px solid rgba(255,255,255,0.07);
                        border-radius:10px; padding:20px; }
        .filter-bar { margin-bottom:16px; }
        .filter-bar .form-control { background:rgba(255,255,255,0.07);
                                    border:1px solid rgba(255,255,255,0.15);
                                    color:#fff; border-radius:6px; }
        .filter-bar .form-control::placeholder { color:#555; }
        .filter-bar select.form-control option { background:#16213e; }
        .filter-bar .btn-search { background:#e94560; border:none; color:#fff; border-radius:6px; }
        .filter-bar .btn-clear  { background:rgba(255,255,255,0.07);
                                  border:1px solid rgba(255,255,255,0.15);
                                  color:#ccc; border-radius:6px; }
        table { color:#eee!important; }
        .table>thead>tr>th { background:#0f3460; color:#e94560;
                             border-bottom:2px solid #e94560!important;
                             font-size:11px; text-transform:uppercase;
                             letter-spacing:1px; border-top:none!important; }
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
        .btn-view-profile { background:rgba(255,255,255,0.07);
                            border:1px solid rgba(255,255,255,0.15);
                            color:#ccc; padding:3px 10px; border-radius:4px; font-size:11px; }
        .btn-view-profile:hover { color:#fff; background:rgba(255,255,255,0.12); }
        /* Pills */
        .nav-pills>li>a { color:#aaa; background:rgba(255,255,255,0.05);
                          border-radius:20px; margin-right:6px; font-size:12px;
                          padding:5px 14px; border:1px solid rgba(255,255,255,0.1); }
        .nav-pills>li>a:hover { background:rgba(233,69,96,0.15); color:#e94560; }
        .nav-pills>li.active>a { background:#e94560!important; color:#fff!important;
                                 border-color:#e94560!important; }
        /* Pagination */
        .pagination>li>a { background:#16213e; border-color:#e94560; color:#e94560; }
        .pagination>li>a:hover { background:#e94560; color:#fff; }
        .pagination>.active>a { background:#e94560; border-color:#e94560; color:#fff; }
        /* Collapse toggle */
        .collapse-toggle { background:#0f3460; border:none; color:#e94560;
                           width:100%; text-align:left; padding:10px 16px;
                           font-size:13px; font-weight:700; border-radius:0;
                           text-transform:uppercase; letter-spacing:1px; }
        .collapse-toggle:hover { background:#16213e; }
        /* Tooltip/Popover */
        .popover { background:#16213e; border:1px solid #e94560; color:#eee; max-width:220px; }
        .popover-title { background:#0f3460; color:#e94560; border-bottom:1px solid #e94560; }
        .popover.top>.arrow:after { border-top-color:#e94560; }
        .empty-state { text-align:center; padding:50px 0; color:#555; }
        @media(max-width:768px){ .hide-sm{ display:none!important; } }
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
        <li class="active"><a href="browse.php"><span class="glyphicon glyphicon-film"></span> Browse</a></li>
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

<div style="padding-top:50px;">

<!-- ═══ CAROUSEL ════════════════════════════════════════ -->
<?php if (!empty($featured)): ?>
<div class="carousel-section">
  <div id="featuredCarousel" class="carousel slide" data-ride="carousel" data-interval="4000">
    <ol class="carousel-indicators">
      <?php foreach ($featured as $fi => $f): ?>
        <li data-target="#featuredCarousel" data-slide-to="<?= $fi ?>"
            class="<?= $fi === 0 ? 'active' : '' ?>"></li>
      <?php endforeach; ?>
    </ol>
    <div class="carousel-inner">
      <?php foreach ($featured as $fi => $f): ?>
        <?php
          $fcover = (!empty($f['cover_image']) && $f['cover_image'] !== 'default_cover.png')
            ? 'uploads/' . htmlspecialchars($f['cover_image'])
            : 'https://via.placeholder.com/1200x320/0f3460/e94560?text=' . urlencode($f['title']);
        ?>
        <div class="item <?= $fi === 0 ? 'active' : '' ?>">
          <img src="<?= $fcover ?>" alt="<?= htmlspecialchars($f['title']) ?>"
               onerror="this.src='https://via.placeholder.com/1200x320/0f3460/e94560?text=<?= urlencode($f['title']) ?>'">
          <div class="carousel-caption">
            <h3><?= htmlspecialchars($f['title']) ?></h3>
            <p>
              <span class="label label-default"><?= htmlspecialchars($f['genre']) ?></span>
              &nbsp;
              <span class="badge-rating">⭐ <?= number_format($f['rating'],1) ?></span>
              &nbsp;
              <small>by <a href="profile.php?id=<?= $f['user_id'] ?>"
                           style="color:#e94560"><?= htmlspecialchars($f['username']) ?></a></small>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <a class="left carousel-control" href="#featuredCarousel" data-slide="prev">
      <span class="glyphicon glyphicon-chevron-left"></span>
    </a>
    <a class="right carousel-control" href="#featuredCarousel" data-slide="next">
      <span class="glyphicon glyphicon-chevron-right"></span>
    </a>
  </div>
</div>
<?php endif; ?>

<!-- ═══ STATS BAR ════════════════════════════════════════ -->
<div class="stats-bar">
  <div class="container">
    <span class="stat">
      <span class="glyphicon glyphicon-film"></span>
      <strong><?= $total_anime ?></strong> Anime Entries
    </span>
    <span class="stat">
      <span class="glyphicon glyphicon-user"></span>
      <strong><?= $total_users ?></strong> Users
    </span>
    <?php if ($logged_in): ?>
      <span class="stat">
        <span class="glyphicon glyphicon-info-sign"></span>
        Logged in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
      </span>
    <?php else: ?>
      <span class="stat">
        <a href="login.php" style="color:#e94560">Login</a> to add anime to your list!
      </span>
    <?php endif; ?>
  </div>
</div>

<!-- ═══ MAIN CONTENT ════════════════════════════════════ -->
<div class="container main-content">
  <div class="row">

    <!-- ── SIDEBAR ──────────────────────────────────── -->
    <div class="col-md-3 col-sm-4">

      <!-- Filter Collapse Panel -->
      <div class="sidebar-panel">
        <button class="collapse-toggle" data-toggle="collapse" data-target="#filterCollapse">
          <span class="glyphicon glyphicon-filter"></span> Filters
          <span class="glyphicon glyphicon-chevron-down pull-right"></span>
        </button>
        <div id="filterCollapse" class="collapse in">
          <form method="GET" style="padding:14px;">
            <div class="form-group" style="margin-bottom:10px;">
              <input type="text" name="search" class="form-control"
                     placeholder="Search anime, genre, user..."
                     value="<?= htmlspecialchars($search) ?>"
                     style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.15);color:#fff;">
            </div>
            <div class="form-group" style="margin-bottom:10px;">
              <select name="status" class="form-control"
                      style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.15);color:#fff;">
                <option value="All" <?= $status_filter==='All'?'selected':'' ?>>All Statuses</option>
                <?php foreach(['Watching','Completed','Dropped','Plan to Watch'] as $s): ?>
                  <option value="<?= $s ?>" <?= $status_filter===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <input type="hidden" name="genre" value="<?= htmlspecialchars($genre_filter) ?>">
            <div class="btn-group btn-group-sm" style="width:100%;">
              <button type="submit" class="btn btn-search"
                      style="background:#e94560;border:none;color:#fff;width:60%;border-radius:4px 0 0 4px;">
                <span class="glyphicon glyphicon-search"></span> Search
              </button>
              <a href="browse.php" class="btn btn-clear"
                 style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.15);color:#ccc;width:40%;border-radius:0 4px 4px 0;text-align:center;padding:5px;">
                Clear
              </a>
            </div>
          </form>
        </div>
      </div>

      <!-- Genre List Group -->
      <div class="sidebar-panel">
        <div class="panel-head">
          <h5><span class="glyphicon glyphicon-tags"></span> Genres</h5>
        </div>
        <div style="max-height:260px; overflow-y:auto;">
          <a href="browse.php?genre=&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>"
             class="list-group-item-custom <?= empty($genre_filter)?'active-genre':'' ?>">
            All Genres
            <span class="genre-badge"><?= $total_anime ?></span>
          </a>
          <?php foreach ($genres as $g): ?>
            <a href="browse.php?genre=<?= urlencode($g['genre']) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>"
               class="list-group-item-custom <?= $genre_filter===$g['genre']?'active-genre':'' ?>">
              <?= htmlspecialchars($g['genre']) ?>
              <span class="genre-badge"><?= $g['cnt'] ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Top Users List -->
      <div class="sidebar-panel">
        <div class="panel-head">
          <h5><span class="glyphicon glyphicon-star"></span> Top Users</h5>
        </div>
        <?php foreach ($top_users as $u): ?>
          <a href="profile.php?id=<?= $u['id'] ?>" style="text-decoration:none;">
            <div class="user-item">
              <img src="avatars/<?= htmlspecialchars($u['avatar'] ?? 'default.png') ?>"
                   onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['username']) ?>&background=e94560&color=fff&size=34'">
              <div>
                <div class="uname"><?= htmlspecialchars($u['username']) ?></div>
                <div class="ucnt"><?= $u['total'] ?> anime</div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- ── MAIN BROWSE PANEL ─────────────────────────── -->
    <div class="col-md-9 col-sm-8">
      <div class="browse-panel">

        <!-- Status Pills -->
        <ul class="nav nav-pills" style="margin-bottom:16px;">
          <?php
          $pill_statuses = ['All','Watching','Completed','Dropped','Plan to Watch'];
          foreach ($pill_statuses as $pill):
            $active = $status_filter === $pill ? 'active' : '';
            $url = '?status='.urlencode($pill)
                 . (!empty($search)       ? '&search='.urlencode($search)           : '')
                 . (!empty($genre_filter) ? '&genre='.urlencode($genre_filter)       : '');
          ?>
            <li class="<?= $active ?>">
              <a href="<?= $url ?>"><?= $pill ?></a>
            </li>
          <?php endforeach; ?>
        </ul>

        <!-- Results header -->
        <div style="margin-bottom:12px; color:#888; font-size:13px;">
          Showing <strong style="color:#e94560"><?= $total ?></strong> results
          <?php if (!empty($search)): ?>
            for "<strong style="color:#eee"><?= htmlspecialchars($search) ?></strong>"
          <?php endif; ?>
          <?php if (!empty($genre_filter)): ?>
            in <strong style="color:#eee"><?= htmlspecialchars($genre_filter) ?></strong>
          <?php endif; ?>
        </div>

        <!-- Table -->
        <?php if (empty($animes)): ?>
          <div class="empty-state">
            <span class="glyphicon glyphicon-film" style="font-size:48px;color:#333;display:block;margin-bottom:14px;"></span>
            <h4 style="color:#555">No anime found</h4>
            <p style="color:#444">Try different search terms or clear the filters.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Cover</th>
                  <th>Title</th>
                  <th class="hide-sm">Genre</th>
                  <th class="hide-sm">Eps</th>
                  <th>Status</th>
                  <th>Rating</th>
                  <th>User</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($animes as $anime): ?>
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
                  $is_own = ($current_user_id === (int)$anime['user_id']);
                ?>
                <tr>
                  <td>
                    <img src="<?= $cover ?>" class="cover-thumb"
                         onerror="this.src='https://via.placeholder.com/40x54/1a1a2e/e94560?text=?'">
                  </td>
                  <td>
                    <strong
                      data-toggle="popover"
                      data-trigger="hover"
                      data-placement="top"
                      title="<?= htmlspecialchars($anime['title']) ?>"
                      data-content="Genre: <?= htmlspecialchars($anime['genre']) ?> | Episodes: <?= $anime['episodes'] ?> | Added by: <?= htmlspecialchars($anime['username']) ?>">
                      <?= htmlspecialchars($anime['title']) ?>
                    </strong>
                  </td>
                  <td class="hide-sm">
                    <span class="label label-default"><?= htmlspecialchars($anime['genre']) ?></span>
                  </td>
                  <td class="hide-sm">
                    <span class="badge"><?= $anime['episodes'] ?></span>
                  </td>
                  <td>
                    <span class="badge <?= $status_class ?>"><?= $anime['status'] ?></span>
                  </td>
                  <td>
                    <span class="rating-star">⭐ <?= number_format($anime['rating'],1) ?></span>
                  </td>
                  <td>
                    <a href="profile.php?id=<?= $anime['user_id'] ?>"
                       class="btn btn-view-profile">
                      <span class="glyphicon glyphicon-user"></span>
                      <?= htmlspecialchars($anime['username']) ?>
                    </a>
                  </td>
                  <td>
                    <?php if ($logged_in && !$is_own): ?>
                      <form method="POST" action="copy.php" style="display:inline;">
                        <input type="hidden" name="anime_id" value="<?= $anime['id'] ?>">
                        <button type="submit" class="btn btn-copy"
                                data-toggle="tooltip" title="Copy to my list">
                          <span class="glyphicon glyphicon-plus"></span> Copy
                        </button>
                      </form>
                    <?php elseif ($is_own): ?>
                      <span style="color:#555; font-size:11px;">Your entry</span>
                    <?php else: ?>
                      <a href="login.php" class="btn btn-copy"
                         style="background:#555;" data-toggle="tooltip" title="Login to copy">
                        <span class="glyphicon glyphicon-lock"></span>
                      </a>
                    <?php endif; ?>
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
                <li class="<?= $page<=1?'disabled':'' ?>">
                  <a href="?page=<?= $page-1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>&genre=<?= urlencode($genre_filter) ?>">&laquo;</a>
                </li>
                <?php for ($p=1; $p<=$total_pages; $p++): ?>
                  <li class="<?= $p==$page?'active':'' ?>">
                    <a href="?page=<?= $p ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>&genre=<?= urlencode($genre_filter) ?>"><?= $p ?></a>
                  </li>
                <?php endfor; ?>
                <li class="<?= $page>=$total_pages?'disabled':'' ?>">
                  <a href="?page=<?= $page+1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>&genre=<?= urlencode($genre_filter) ?>">&raquo;</a>
                </li>
              </ul>
            </nav>
          <?php endif; ?>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

</div><!-- end padding-top -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
  $('[data-toggle="tooltip"]').tooltip();
  $('[data-toggle="popover"]').popover();
</script>
</body>
</html>