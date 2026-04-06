<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db.php';

$logged_in       = isset($_SESSION['user_id']);
$current_user_id = $_SESSION['user_id'] ?? 0;

// ── Flash ────────────────────────────────────────────────
$flash      = $_SESSION['flash']      ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash'], $_SESSION['flash_type']);

// ── Search & Filter ──────────────────────────────────────
$search       = trim($_GET['search'] ?? '');
$genre_filter = $_GET['genre']        ?? '';
$status_filter= $_GET['status']       ?? 'All';
$valid_statuses = ['All','Ongoing','Completed','Upcoming'];
if (!in_array($status_filter, $valid_statuses)) $status_filter = 'All';

// ── Pagination ───────────────────────────────────────────
$per_page = 10;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

// ── Build Query ──────────────────────────────────────────
$where  = "WHERE 1=1";
$params = [];
if (!empty($search)) {
    $where   .= " AND (title LIKE ? OR genre LIKE ? OR added_by LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($genre_filter)) {
    $where   .= " AND genre = ?";
    $params[] = $genre_filter;
}
if ($status_filter !== 'All') {
    $where   .= " AND status = ?";
    $params[] = $status_filter;
}

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM general_anime $where");
$count_stmt->execute($params);
$total       = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$params_paged = array_merge($params, [$per_page, $offset]);
$stmt = $pdo->prepare("
    SELECT g.*,
        (SELECT COUNT(*) FROM anime_hearts   WHERE anime_id = g.id) AS heart_count,
        (SELECT AVG(rating) FROM anime_ratings WHERE anime_id = g.id) AS avg_rating,
        (SELECT COUNT(*) FROM anime_watchlist  WHERE title   = g.title) AS add_count
        " . ($logged_in ? ",
        (SELECT COUNT(*) FROM anime_hearts WHERE anime_id = g.id AND user_id = $current_user_id) AS user_hearted,
        (SELECT rating   FROM anime_ratings WHERE anime_id = g.id AND user_id = $current_user_id) AS user_rating
        " : "") . "
    FROM general_anime g $where
    ORDER BY g.created_at DESC LIMIT ? OFFSET ?
");
$stmt->execute($params_paged);
$animes = $stmt->fetchAll();

// ── Top Hearts ───────────────────────────────────────────
$top_hearts_stmt = $pdo->query("
    SELECT g.*, COUNT(h.id) as heart_count
    FROM general_anime g
    LEFT JOIN anime_hearts h ON g.id = h.anime_id
    GROUP BY g.id ORDER BY heart_count DESC LIMIT 5
");
$top_hearts = $top_hearts_stmt->fetchAll();

// ── Top Rated ────────────────────────────────────────────
$top_rated_stmt = $pdo->query("
    SELECT g.*, ROUND(AVG(r.rating),1) as avg_rating, COUNT(r.id) as rate_count
    FROM general_anime g
    LEFT JOIN anime_ratings r ON g.id = r.anime_id
    GROUP BY g.id HAVING rate_count > 0 ORDER BY avg_rating DESC LIMIT 5
");
$top_rated = $top_rated_stmt->fetchAll();

// ── Most Added ───────────────────────────────────────────
$most_added_stmt = $pdo->query("
    SELECT g.*, COUNT(w.id) as add_count
    FROM general_anime g
    LEFT JOIN anime_watchlist w ON w.title = g.title
    GROUP BY g.id ORDER BY add_count DESC LIMIT 5
");
$most_added = $most_added_stmt->fetchAll();

// ── Genre list ───────────────────────────────────────────
$genre_stmt = $pdo->query("SELECT genre, COUNT(*) as cnt FROM general_anime GROUP BY genre ORDER BY cnt DESC");
$genres     = $genre_stmt->fetchAll();

// ── Site stats ───────────────────────────────────────────
$stat_anime  = $pdo->query("SELECT COUNT(*) FROM general_anime")->fetchColumn();
$stat_hearts = $pdo->query("SELECT COUNT(*) FROM anime_hearts")->fetchColumn();
$stat_users  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home — AniTrack</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body { background:#1a1a2e; color:#eee; font-family:'Segoe UI',sans-serif; }
        /* Navbar */
        .navbar-custom { background:#16213e; border:none; border-bottom:2px solid #e94560; margin-bottom:0; }
        .navbar-custom .navbar-brand { color:#e94560!important; font-weight:700; font-size:20px; }
        .navbar-custom .nav>li>a { color:#ccc!important; }
        .navbar-custom .nav>li>a:hover,
        .navbar-custom .nav>li.active>a { color:#e94560!important; background:transparent!important; }
        .navbar-custom .nav>li>a .glyphicon { margin-right: 5px; } /* Revision: Icon Spacing */
        .navbar-toggle .icon-bar { background:#e94560; }
        .dropdown-menu { background:#16213e; border:1px solid #e94560; border-radius:6px; }
        .dropdown-menu>li>a { color:#ccc!important; }
        .dropdown-menu>li>a:hover { background:#e94560!important; color:#fff!important; }
        .dropdown-menu>.divider { background:rgba(255,255,255,0.08); }
        .avatar-sm { width:30px; height:30px; border-radius:50%; object-fit:cover;
                     border:2px solid #e94560; margin-right:6px; vertical-align:middle; }
        /* Hero */
        .hero { background:linear-gradient(135deg,#0f3460 0%,#16213e 50%,#1a1a2e 100%);
                padding:50px 0 36px; border-bottom:2px solid #e94560; }
        .hero h1 { color:#e94560; font-weight:800; font-size:36px; margin:0 0 8px; }
        .hero p  { color:#aaa; font-size:16px; margin:0 0 24px; }
        .hero-stat { display:inline-block; margin-right:28px; }
        .hero-stat .num { font-size:28px; font-weight:700; color:#e94560; display:block; }
        .hero-stat .lbl { font-size:12px; color:#777; text-transform:uppercase; letter-spacing:1px; }
        /* Section headers */
        .section-head { display:flex; align-items:center; justify-content:space-between;
                        margin:28px 0 14px; padding-bottom:10px;
                        border-bottom:2px solid rgba(233,69,96,0.25); }
        .section-head h4 { color:#e94560; font-weight:700; margin:0; font-size:16px;
                           text-transform:uppercase; letter-spacing:1px; }
        /* Top cards */
        .anime-card { background:#16213e; border:1px solid rgba(255,255,255,0.07);
                      border-radius:10px; overflow:hidden; transition:all .2s;
                      height:100%; }
        .anime-card:hover { border-color:#e94560; transform:translateY(-3px);
                            box-shadow:0 8px 24px rgba(233,69,96,0.15); }
        .anime-card img { width:100%; height:140px; object-fit:cover; }
        .anime-card .card-body { padding:12px; }
        .anime-card .card-title { color:#eee; font-weight:700; font-size:13px;
                                  margin:0 0 6px; white-space:nowrap;
                                  overflow:hidden; text-overflow:ellipsis; }
        .anime-card .card-meta  { color:#777; font-size:11px; }
        /* Main panel */
        .main-panel { background:#16213e; border:1px solid rgba(255,255,255,0.07);
                      border-radius:10px; padding:22px; margin-bottom:30px; }
        /* Pills */
        .nav-pills>li>a { color:#aaa; background:rgba(255,255,255,0.05);
                          border-radius:20px; margin-right:6px; font-size:12px;
                          padding:5px 14px; border:1px solid rgba(255,255,255,0.1); }
        .nav-pills>li>a:hover { background:rgba(233,69,96,0.15); color:#e94560; }
        .nav-pills>li.active>a { background:#e94560!important; color:#fff!important;
                                 border-color:#e94560!important; }
        /* Search */
        .search-row { margin-bottom:16px; }
        .search-row .form-control { background:rgba(255,255,255,0.06);
                                    border:1px solid rgba(255,255,255,0.12);
                                    color:#fff; border-radius:6px; }
        .search-row .form-control::placeholder { color:#555; }
        .search-row select.form-control option { background:#16213e; }
        .btn-search { background:#e94560; border:none; color:#fff; border-radius:6px; }
        .btn-search:hover { background:#c73652; color:#fff; }
        /* Table */
        table.anime-table { color:#eee!important; }
        .anime-table>thead>tr>th { background:#0f3460; color:#e94560;
                                   border-bottom:2px solid #e94560!important;
                                   font-size:11px; text-transform:uppercase;
                                   letter-spacing:1px; border-top:none!important; padding:12px 10px; }
        .anime-table>tbody>tr>td { border-top:1px solid rgba(255,255,255,0.05)!important;
                                   vertical-align:middle!important; padding:15px 10px; } /* Revision: Row Spacing */
        .anime-table>tbody>tr:hover>td { background:rgba(233,69,96,0.05)!important; }
        .cover-sm { width:40px; height:54px; object-fit:cover; border-radius:4px;
                    border:1px solid rgba(255,255,255,0.1); }
        /* Status badges */
        .badge-ongoing   { background:#3498db!important; }
        .badge-completed { background:#27ae60!important; }
        .badge-upcoming  { background:#9b59b6!important; }
        /* Heart button */
        .btn-heart { padding:3px 10px; border-radius:20px; font-size:12px;
                     font-weight:600; border:1px solid #e94560;
                     background:transparent; color:#e94560; transition:all .2s; }
        .btn-heart:hover,.btn-heart.hearted { background:#e94560; color:#fff; border-color:#e94560; }
        .btn-heart.hearted { background:#e94560; color:#fff; }
        /* Rating */
        .btn-rate { padding:3px 10px; border-radius:20px; font-size:12px;
                    font-weight:600; border:1px solid #f39c12;
                    background:transparent; color:#f39c12; transition:all .2s; }
        .btn-rate:hover { background:#f39c12; color:#fff; }
        .rating-display { background:#e94560; color:#fff; padding:2px 8px;
                          border-radius:12px; font-size:11px; font-weight:700; }
        /* CRUD buttons */
        .btn-edit-sm { background:#3498db; border:none; color:#fff;
                       padding:3px 9px; border-radius:4px; font-size:11px; }
        .btn-edit-sm:hover { background:#2980b9; color:#fff; }
        .btn-del-sm  { background:#e74c3c; border:none; color:#fff;
                       padding:3px 9px; border-radius:4px; font-size:11px; }
        .btn-del-sm:hover { background:#c0392b; color:#fff; }
        .btn-add-wl  { background:#27ae60; border:none; color:#fff;
                       padding:3px 9px; border-radius:4px; font-size:11px; font-weight:600; }
        .btn-add-wl:hover { background:#219a52; color:#fff; }
        /* Sidebar */
        .sidebar-box { background:#16213e; border:1px solid rgba(255,255,255,0.07);
                       border-radius:10px; overflow:hidden; margin-bottom:20px; }
        .sidebar-box .sb-head { background:#0f3460; padding:11px 15px;
                                border-bottom:1px solid #e94560; }
        .sidebar-box .sb-head h6 { color:#e94560; margin:0; font-weight:700;
                                   font-size:12px; text-transform:uppercase; letter-spacing:1px; }
        .genre-item { display:block; padding:8px 15px; color:#ccc; font-size:13px;
                      border-bottom:1px solid rgba(255,255,255,0.05); transition:all .15s; text-decoration:none; }
        .genre-item:hover,.genre-item.active-g { background:rgba(233,69,96,0.1);
                                                  color:#e94560; text-decoration:none; }
        .genre-item .gcnt { float:right; background:#0f3460; color:#888;
                            padding:1px 7px; border-radius:10px; font-size:11px; }
        /* Accordion */
        .accordion-dark .panel { background:#16213e; border:1px solid rgba(255,255,255,0.08);
                                  border-radius:8px!important; margin-bottom:5px; }
        .accordion-dark .panel-heading { background:#0f3460; border-radius:8px!important;
                                         padding:0; border-bottom:none; }
        .accordion-dark .panel-heading a { display: flex; justify-content: space-between; align-items: center; /* Revision: Alignment */
                                           padding:11px 15px; color:#e94560;
                                           font-weight:600; font-size:13px; text-decoration:none; }
        .accordion-dark .panel-heading a.collapsed { color:#aaa; }
        .accordion-dark .panel-body { color:#ccc; font-size:13px;
                                      border-top:1px solid rgba(233,69,96,0.2)!important; padding:13px 15px; }
        /* Modals */
        .modal-dark .modal-content { background:#16213e; border:1px solid #e94560;
                                     color:#eee; border-radius:10px; }
        .modal-dark .modal-header { border-bottom:1px solid rgba(233,69,96,0.3); }
        .modal-dark .modal-title  { color:#e94560; font-weight:700; }
        .modal-dark .modal-footer { border-top:1px solid rgba(255,255,255,0.06); }
        .modal-dark .close { color:#eee!important; }
        /* Forms */
        .form-control { background:rgba(255,255,255,0.06)!important;
                        border:1px solid rgba(255,255,255,0.12)!important;
                        color:#fff!important; border-radius:6px; }
        .form-control:focus { border-color:#e94560!important;
                              box-shadow:0 0 0 3px rgba(233,69,96,0.15)!important; }
        .form-control::placeholder { color:#555!important; }
        select.form-control option { background:#16213e; color:#eee; }
        label { color:#bbb; font-size:13px; font-weight:600; }
        /* Pagination */
        .pagination>li>a { background:#16213e; border-color:rgba(233,69,96,0.4); color:#e94560; }
        .pagination>li>a:hover { background:#e94560; color:#fff; }
        .pagination>.active>a { background:#e94560; border-color:#e94560; color:#fff; }
        .pagination>.disabled>a { background:#16213e; color:#444; }
        /* Alerts */
        .alert-success { background:rgba(39,174,96,0.15); border-left:4px solid #27ae60; color:#2ecc71; border-radius:8px; }
        .alert-danger  { background:rgba(231,76,60,0.15);  border-left:4px solid #e74c3c; color:#e74c3c; border-radius:8px; }
        .alert-warning { background:rgba(243,156,18,0.15); border-left:4px solid #f39c12; color:#f39c12; border-radius:8px; }
        .empty-state { text-align:center; padding:50px 0; color:#555; }
        img.preview-img { display:none; width:100%; border-radius:6px; margin-top:8px;
                          max-height:180px; object-fit:cover; border:1px solid rgba(255,255,255,0.1); }

        /* Revision: Popover Dark Mode Fix */
        .popover { background-color: #16213e !important; border: 1px solid #e94560 !important; }
        .popover-title { background-color: #0f3460 !important; color: #e94560 !important; border-bottom: 1px solid rgba(233,69,96,0.2) !important; }
        .popover-content { color: #ccc !important; }
        .popover.right>.arrow:after { border-right-color: #16213e !important; }

        @media(max-width:768px){ .hide-sm{ display:none!important; } }
    </style>
</head>
<body>

<nav class="navbar navbar-custom navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#mainNav">
        <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="home.php">🎌 AniTrack</a>
    </div>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="nav navbar-nav">
        <li class="active"><a href="home.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
        <?php if ($logged_in): ?>
          <li><a href="index.php"><span class="glyphicon glyphicon-list"></span> My List</a></li>
        <?php endif; ?>
        <li><a href="browse.php"><span class="glyphicon glyphicon-film"></span> Browse Users</a></li>
        <li><a href="accordion_demo.php"><span class="glyphicon glyphicon-th-large"></span> Components</a></li>
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
              <li class="dropdown-header" style="color:#888;font-size:11px;">ACCOUNT</li>
              <li><a href="index.php"><span class="glyphicon glyphicon-list"></span>&nbsp; My Watchlist</a></li>
              <li><a href="profile.php?id=<?= $_SESSION['user_id'] ?>"><span class="glyphicon glyphicon-user"></span>&nbsp; My Profile</a></li>
              <li class="divider"></li>
              <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span>&nbsp; Logout</a></li>
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

<div class="hero">
  <div class="container">
    <div class="row">
      <div class="col-md-7">
        <h1>🎌 Welcome to AniTrack</h1>
        <p>Discover, track and share your anime journey with the community!</p>
        <div>
          <span class="hero-stat">
            <span class="num"><?= $stat_anime ?></span>
            <span class="lbl">Anime Listed</span>
          </span>
          <span class="hero-stat">
            <span class="num"><?= $stat_hearts ?></span>
            <span class="lbl">Total Hearts</span>
          </span>
          <span class="hero-stat">
            <span class="num"><?= $stat_users ?></span>
            <span class="lbl">Users</span>
          </span>
        </div>
      </div>
      <div class="col-md-5 text-right" style="padding-top:12px;">
        <button class="btn btn-lg" data-toggle="modal" data-target="#addAnimeModal"
                style="background:#e94560;border:none;color:#fff;font-weight:700;border-radius:8px;">
          <span class="glyphicon glyphicon-plus"></span> Add Anime
        </button>
        <?php if (!$logged_in): ?>
          &nbsp;
          <a href="login.php" class="btn btn-lg btn-default"
             style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.2);color:#ccc;border-radius:8px;">
            <span class="glyphicon glyphicon-log-in"></span> Login to Heart & Rate
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="container">

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash_type ?> alert-dismissible" style="margin-top:18px;">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?= htmlspecialchars($flash) ?>
    </div>
  <?php endif; ?>

  <div class="section-head" style="margin-top:24px;">
    <h4><span class="glyphicon glyphicon-fire"></span> Anime Highlights</h4>
    <small style="color:#666">Click to expand each section</small>
  </div>

  <div class="panel-group accordion-dark" id="highlightAccordion">

    <div class="panel">
      <div class="panel-heading">
        <a data-toggle="collapse" data-parent="#highlightAccordion" href="#topHearts">
          ❤️ &nbsp; Top Hearted Anime
          <span class="badge" style="background:#e94560;"><?= count($top_hearts) ?></span>
        </a>
      </div>
      <div id="topHearts" class="panel-collapse collapse">
        <div class="panel-body" style="padding:14px;">
          <?php if (empty($top_hearts)): ?>
            <p style="color:#555; margin:0;">No hearts yet — be the first to heart an anime!</p>
          <?php else: ?>
            <div class="row">
              <?php foreach ($top_hearts as $th): ?>
                <?php $tc = (!empty($th['cover_image']) && $th['cover_image'] !== 'default_cover.png')
                    ? 'uploads/'.htmlspecialchars($th['cover_image'])
                    : 'https://via.placeholder.com/120x160/0f3460/e94560?text='.urlencode($th['title']); ?>
                <div class="col-xs-6 col-sm-4 col-md-2" style="margin-bottom:12px;">
                  <div class="anime-card">
                    <img src="<?= $tc ?>" alt="<?= htmlspecialchars($th['title']) ?>"
                         onerror="this.src='https://via.placeholder.com/120x160/0f3460/e94560?text=?'">
                    <div class="card-body">
                      <div class="card-title"
                           data-toggle="tooltip" title="<?= htmlspecialchars($th['title']) ?>">
                        <?= htmlspecialchars($th['title']) ?>
                      </div>
                      <div class="card-meta">❤️ <?= $th['heart_count'] ?> hearts</div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-heading">
        <a data-toggle="collapse" data-parent="#highlightAccordion" href="#topRated" class="collapsed">
          ⭐ &nbsp; Top Rated Anime
          <span class="badge" style="background:#f39c12;"><?= count($top_rated) ?></span>
        </a>
      </div>
      <div id="topRated" class="panel-collapse collapse">
        <div class="panel-body" style="padding:14px;">
          <?php if (empty($top_rated)): ?>
            <p style="color:#555; margin:0;">No ratings yet — be the first to rate!</p>
          <?php else: ?>
            <div class="row">
              <?php foreach ($top_rated as $tr): ?>
                <?php $trc = (!empty($tr['cover_image']) && $tr['cover_image'] !== 'default_cover.png')
                    ? 'uploads/'.htmlspecialchars($tr['cover_image'])
                    : 'https://via.placeholder.com/120x160/0f3460/e94560?text='.urlencode($tr['title']); ?>
                <div class="col-xs-6 col-sm-4 col-md-2" style="margin-bottom:12px;">
                  <div class="anime-card">
                    <img src="<?= $trc ?>" alt="<?= htmlspecialchars($tr['title']) ?>"
                         onerror="this.src='https://via.placeholder.com/120x160/0f3460/e94560?text=?'">
                    <div class="card-body">
                      <div class="card-title"><?= htmlspecialchars($tr['title']) ?></div>
                      <div class="card-meta">
                        <span class="rating-display">⭐ <?= $tr['avg_rating'] ?></span>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-heading">
        <a data-toggle="collapse" data-parent="#highlightAccordion" href="#mostAdded" class="collapsed">
          📋 &nbsp; Most Added to Watchlists
          <span class="badge" style="background:#27ae60;"><?= count($most_added) ?></span>
        </a>
      </div>
      <div id="mostAdded" class="panel-collapse collapse">
        <div class="panel-body" style="padding:14px;">
          <?php if (empty($most_added)): ?>
            <p style="color:#555; margin:0;">No watchlist adds yet!</p>
          <?php else: ?>
            <div class="row">
              <?php foreach ($most_added as $ma): ?>
                <?php $mac = (!empty($ma['cover_image']) && $ma['cover_image'] !== 'default_cover.png')
                    ? 'uploads/'.htmlspecialchars($ma['cover_image'])
                    : 'https://via.placeholder.com/120x160/0f3460/e94560?text='.urlencode($ma['title']); ?>
                <div class="col-xs-6 col-sm-4 col-md-2" style="margin-bottom:12px;">
                  <div class="anime-card">
                    <img src="<?= $mac ?>" alt="<?= htmlspecialchars($ma['title']) ?>"
                         onerror="this.src='https://via.placeholder.com/120x160/0f3460/e94560?text=?'">
                    <div class="card-body">
                      <div class="card-title"><?= htmlspecialchars($ma['title']) ?></div>
                      <div class="card-meta">📋 <?= $ma['add_count'] ?> added</div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div><div class="row" style="margin-top:10px;">
    <div class="col-md-12">
      <div class="main-panel">

        <ul class="nav nav-pills" style="margin-bottom:14px;">
          <?php foreach (['All','Ongoing','Completed','Upcoming'] as $pill): ?>
            <li class="<?= $status_filter===$pill?'active':'' ?>">
              <a href="home.php?status=<?= urlencode($pill) ?>&genre=<?= urlencode($genre_filter) ?>&search=<?= urlencode($search) ?>">
                <?= $pill ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="row search-row">
          <form method="GET" class="col-sm-12">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <input type="hidden" name="genre"  value="<?= htmlspecialchars($genre_filter) ?>">
            <div class="input-group">
              <input type="text" name="search" class="form-control"
                     placeholder="Search by title, genre or who added it..."
                     value="<?= htmlspecialchars($search) ?>"
                     style="border-top-right-radius: 0; border-bottom-right-radius: 0;"> <span class="input-group-btn">
                <button class="btn btn-search" type="submit" style="border-top-left-radius: 0; border-bottom-left-radius: 0; height: 34px;"> <span class="glyphicon glyphicon-search"></span> Search
                </button>
              </span>
            </div>
          </form>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <span style="color:#666; font-size:13px;">
            <strong style="color:#e94560"><?= $total ?></strong> anime found
          </span>
          <button class="btn btn-sm" data-toggle="modal" data-target="#addAnimeModal"
                  style="background:#e94560;border:none;color:#fff;border-radius:6px;font-weight:600;">
            <span class="glyphicon glyphicon-plus"></span> Add Anime
          </button>
        </div>

        <?php if (empty($animes)): ?>
          <div class="empty-state">
            <span class="glyphicon glyphicon-film" style="font-size:48px; display:block; margin-bottom:14px;"></span>
            <h4 style="color:#555">No anime found</h4>
            <p style="color:#444">Be the first to add one!</p>
            <button class="btn" data-toggle="modal" data-target="#addAnimeModal"
                    style="background:#e94560;border:none;color:#fff;border-radius:6px;">
              <span class="glyphicon glyphicon-plus"></span> Add Anime
            </button>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover anime-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Cover</th>
                  <th>Title</th>
                  <th class="hide-sm">Genre</th>
                  <th class="hide-sm">Eps</th>
                  <th>Status</th>
                  <th>❤️</th>
                  <th>⭐</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($animes as $i => $a): ?>
                <?php
                  $sc = match($a['status']) {
                    'Ongoing'   => 'badge-ongoing',
                    'Completed' => 'badge-completed',
                    default     => 'badge-upcoming'
                  };
                  $cover = (!empty($a['cover_image']) && $a['cover_image'] !== 'default_cover.png')
                    ? 'uploads/'.htmlspecialchars($a['cover_image'])
                    : 'https://via.placeholder.com/40x54/1a1a2e/e94560?text=?';
                  $hearts    = (int)$a['heart_count'];
                  $avg_r     = $a['avg_rating'] ? number_format($a['avg_rating'],1) : '—';
                  $u_hearted = $logged_in && !empty($a['user_hearted']);
                  $u_rating  = $logged_in ? ($a['user_rating'] ?? null) : null;
                ?>
                <tr>
                  <td style="color:#555"><?= $offset+$i+1 ?></td>
                  <td>
                    <img src="<?= $cover ?>" class="cover-sm img-thumbnail"
                         onerror="this.src='https://via.placeholder.com/40x54/1a1a2e/e94560?text=?'"
                         data-toggle="popover" data-trigger="hover" data-placement="right"
                         title="<?= htmlspecialchars($a['title']) ?>"
                         data-content="<?= htmlspecialchars($a['description'] ? substr($a['description'],0,100).'...' : 'No description.') ?>">
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($a['title']) ?></strong><br>
                    <small style="color:#555">by <?= htmlspecialchars($a['added_by']) ?></small>
                  </td>
                  <td class="hide-sm">
                    <span class="label label-default"><?= htmlspecialchars($a['genre']) ?></span>
                  </td>
                  <td class="hide-sm">
                    <span class="badge"><?= $a['episodes'] ?></span>
                  </td>
                  <td>
                    <span class="badge <?= $sc ?>"><?= $a['status'] ?></span>
                  </td>
                  <td>
                    <?php if ($logged_in): ?>
                      <form method="POST" action="heart.php" style="display:inline;">
                        <input type="hidden" name="anime_id" value="<?= $a['id'] ?>">
                        <button type="submit"
                                class="btn-heart <?= $u_hearted?'hearted':'' ?>"
                                data-toggle="tooltip"
                                title="<?= $u_hearted?'Remove heart':'Heart this anime' ?>">
                          ❤️ <?= $hearts ?>
                        </button>
                      </form>
                    <?php else: ?>
                      <a href="login.php" class="btn-heart"
                         data-toggle="tooltip" title="Login to heart">
                        🤍 <?= $hearts ?>
                      </a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($logged_in): ?>
                      <button class="btn-rate"
                              data-toggle="modal"
                              data-target="#rateModal"
                              data-id="<?= $a['id'] ?>"
                              data-title="<?= htmlspecialchars($a['title']) ?>"
                              data-myrating="<?= $u_rating ?? '' ?>"
                              data-toggle2="tooltip"
                              title="<?= $u_rating ? 'Your rating: '.$u_rating : 'Rate this anime' ?>">
                        <?= $avg_r !== '—' ? '⭐ '.$avg_r : '⭐ Rate' ?>
                      </button>
                    <?php else: ?>
                      <span class="rating-display">
                        <?= $avg_r !== '—' ? '⭐ '.$avg_r : '—' ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="btn-group btn-group-xs">
                      <?php if ($logged_in): ?>
                        <form method="POST" action="add_to_watchlist.php" style="display:inline;">
                          <input type="hidden" name="anime_id" value="<?= $a['id'] ?>">
                          <button type="submit" class="btn-add-wl"
                                  data-toggle="tooltip" title="Add to my watchlist">
                            <span class="glyphicon glyphicon-plus"></span>
                          </button>
                        </form>
                      <?php endif; ?>
                      <button class="btn-edit-sm" data-toggle="modal"
                              data-target="#editAnimeModal"
                              data-id="<?= $a['id'] ?>"
                              data-title="<?= htmlspecialchars($a['title']) ?>"
                              data-genre="<?= htmlspecialchars($a['genre']) ?>"
                              data-episodes="<?= $a['episodes'] ?>"
                              data-status="<?= $a['status'] ?>"
                              data-description="<?= htmlspecialchars($a['description'] ?? '') ?>"
                              data-toggle2="tooltip" title="Edit">
                        <span class="glyphicon glyphicon-pencil"></span>
                      </button>
                      <button class="btn-del-sm" data-toggle="modal"
                              data-target="#deleteModal"
                              data-id="<?= $a['id'] ?>"
                              data-title="<?= htmlspecialchars($a['title']) ?>"
                              data-toggle2="tooltip" title="Delete">
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

<div class="modal fade" id="addAnimeModal" tabindex="-1">
  <div class="modal-dialog modal-dark">
    <div class="modal-content" style="background:#16213e;border:1px solid #e94560;color:#eee;border-radius:10px;">
      <div class="modal-header" style="border-bottom:1px solid rgba(233,69,96,0.3);">
        <button type="button" class="close" data-dismiss="modal" style="color:#eee;">&times;</button>
        <h4 class="modal-title" style="color:#e94560;">
          <span class="glyphicon glyphicon-plus"></span> Add Anime to General List
        </h4>
      </div>
      <form method="POST" action="home_crud.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <div class="row">
            <div class="col-sm-8">
              <div class="form-group">
                <label>Anime Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Naruto" required>
              </div>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Genre *</label>
                    <select name="genre" class="form-control" required>
                      <option value="">— Select —</option>
                      <?php foreach(['Action','Adventure','Comedy','Drama','Fantasy','Horror','Mecha','Mystery','Romance','Sci-Fi','Slice of Life','Sports','Supernatural','Thriller'] as $g): ?>
                        <option value="<?= $g ?>"><?= $g ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Episodes *</label>
                    <input type="number" name="episodes" class="form-control" placeholder="e.g. 24" min="1" required>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Status *</label>
                <select name="status" class="form-control" required>
                  <option value="">— Select —</option>
                  <option value="Ongoing">Ongoing</option>
                  <option value="Completed">Completed</option>
                  <option value="Upcoming">Upcoming</option>
                </select>
              </div>
              <div class="form-group">
                <label>Your Name / Nickname</label>
                <input type="text" name="added_by" class="form-control"
                       placeholder="e.g. Guest, John..."
                       value="<?= $logged_in ? htmlspecialchars($_SESSION['username']) : '' ?>">
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Cover Image</label>
                <input type="file" name="cover_image" class="form-control" accept="image/*" id="addCoverInput">
                <img id="addPreview" class="preview-img">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Description / Synopsis</label>
            <textarea name="description" class="form-control" rows="3"
                      placeholder="Brief description of the anime..."></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.07);">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn" style="background:#e94560;border:none;color:#fff;font-weight:600;">
            <span class="glyphicon glyphicon-floppy-disk"></span> Save Anime
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editAnimeModal" tabindex="-1">
  <div class="modal-dialog modal-dark">
    <div class="modal-content" style="background:#16213e;border:1px solid #3498db;color:#eee;border-radius:10px;">
      <div class="modal-header" style="border-bottom:1px solid rgba(52,152,219,0.3);">
        <button type="button" class="close" data-dismiss="modal" style="color:#eee;">&times;</button>
        <h4 class="modal-title" style="color:#3498db;">
          <span class="glyphicon glyphicon-pencil"></span> Edit Anime
        </h4>
      </div>
      <form method="POST" action="home_crud.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="editId">
        <div class="modal-body">
          <div class="form-group">
            <label>Anime Title *</label>
            <input type="text" name="title" id="editTitle" class="form-control" required>
          </div>
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label>Genre *</label>
                <select name="genre" id="editGenre" class="form-control" required>
                  <?php foreach(['Action','Adventure','Comedy','Drama','Fantasy','Horror','Mecha','Mystery','Romance','Sci-Fi','Slice of Life','Sports','Supernatural','Thriller'] as $g): ?>
                    <option value="<?= $g ?>"><?= $g ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Episodes *</label>
                <input type="number" name="episodes" id="editEpisodes" class="form-control" min="1" required>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label>Status *</label>
                <select name="status" id="editStatus" class="form-control" required>
                  <option value="Ongoing">Ongoing</option>
                  <option value="Completed">Completed</option>
                  <option value="Upcoming">Upcoming</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label>Replace Cover Image</label>
            <input type="file" name="cover_image" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.07);">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn" style="background:#3498db;border:none;color:#fff;font-weight:600;">
            <span class="glyphicon glyphicon-floppy-disk"></span> Update Anime
          </button>
        </div>
      </form>
    </div>
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
        <p>Delete <strong id="deleteTitle"></strong> from the general list?</p>
        <p style="color:#888;font-size:13px;">This cannot be undone.</p>
      </div>
      <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.07);">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <a href="#" id="deleteConfirmBtn" class="btn btn-danger">
          <span class="glyphicon glyphicon-trash"></span> Delete
        </a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="rateModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dark">
    <div class="modal-content" style="background:#16213e;border:1px solid #f39c12;color:#eee;border-radius:10px;">
      <div class="modal-header" style="border-bottom:1px solid rgba(243,156,18,0.3);">
        <button type="button" class="close" data-dismiss="modal" style="color:#eee;">&times;</button>
        <h4 class="modal-title" style="color:#f39c12;">
          ⭐ Rate: <span id="rateTitle"></span>
        </h4>
      </div>
      <form method="POST" action="rate.php">
        <input type="hidden" name="anime_id" id="rateAnimeId">
        <div class="modal-body" style="text-align:center;">
          <p style="color:#aaa; font-size:13px;">Your current rating: <strong id="currentRating" style="color:#f39c12;"></strong></p>
          <div class="form-group">
            <label style="font-size:15px;">Select Rating (1–10)</label>
            <input type="number" name="rating" id="rateInput" class="form-control"
                   min="1" max="10" step="0.5" placeholder="e.g. 8.5"
                   style="text-align:center; font-size:24px; font-weight:700; color:#f39c12;">
          </div>
          <div style="margin-top:8px;">
            <?php for ($star=1; $star<=10; $star++): ?>
              <button type="button" class="star-btn" data-val="<?= $star ?>"
                      style="background:none;border:none;font-size:20px;cursor:pointer;color:#444;padding:2px;">★</button>
            <?php endfor; ?>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.07);">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn" style="background:#f39c12;border:none;color:#fff;font-weight:600;">
            ⭐ Submit Rating
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

</div><script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
$(function() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    // Edit modal fill
    $('#editAnimeModal').on('show.bs.modal', function(e) {
        var b = $(e.relatedTarget);
        $('#editId').val(b.data('id'));
        $('#editTitle').val(b.data('title'));
        $('#editGenre').val(b.data('genre'));
        $('#editEpisodes').val(b.data('episodes'));
        $('#editStatus').val(b.data('status'));
        $('#editDescription').val(b.data('description'));
    });

    // Delete modal fill
    $('#deleteModal').on('show.bs.modal', function(e) {
        var b = $(e.relatedTarget);
        $('#deleteTitle').text(b.data('title'));
        $('#deleteConfirmBtn').attr('href', 'home_crud.php?action=delete&id=' + b.data('id'));
    });

    // Rate modal fill
    $('#rateModal').on('show.bs.modal', function(e) {
        var b = $(e.relatedTarget);
        $('#rateAnimeId').val(b.data('id'));
        $('#rateTitle').text(b.data('title'));
        var mr = b.data('myrating');
        $('#currentRating').text(mr ? mr + ' / 10' : 'Not rated yet');
        $('#rateInput').val(mr || '');
        updateStars(mr || 0);
    });

    // Star buttons
    $(document).on('click', '.star-btn', function() {
        var val = $(this).data('val');
        $('#rateInput').val(val);
        updateStars(val);
    });

    function updateStars(val) {
        $('.star-btn').each(function() {
            $(this).css('color', $(this).data('val') <= val ? '#f39c12' : '#444');
        });
    }

    $('#rateInput').on('input', function() {
        updateStars($(this).val());
    });

    // Add cover preview
    $('#addCoverInput').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#addPreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
</body>
</html>