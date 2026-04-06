<?php
// ── Reusable Navbar + Bootstrap includes ────────────────
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'AniTrack' ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body { background:#1a1a2e; color:#eee; font-family:'Segoe UI',sans-serif; padding-top:50px; }
        /* ── Navbar ── */
        .navbar-custom { background:#16213e; border:none; border-bottom:2px solid #e94560; min-height:50px; }
        .navbar-custom .navbar-brand { color:#e94560!important; font-weight:700; font-size:20px; padding:14px 15px; }
        .navbar-custom .nav>li>a { color:#ccc!important; transition:color .2s; }
        .navbar-custom .nav>li>a:hover,
        .navbar-custom .nav>li.active>a { color:#e94560!important; background:transparent!important; }
        .navbar-toggle .icon-bar { background:#e94560; }
        .navbar-toggle { border-color:#e94560; }
        /* ── Dropdown ── */
        .dropdown-menu { background:#16213e; border:1px solid #e94560; border-radius:6px; box-shadow:0 4px 20px rgba(0,0,0,0.4); }
        .dropdown-menu>li>a { color:#ccc!important; padding:8px 16px; }
        .dropdown-menu>li>a:hover { background:#e94560!important; color:#fff!important; }
        .dropdown-menu>.divider { background:rgba(255,255,255,0.08); }
        /* ── Avatar ── */
        .avatar-sm { width:30px; height:30px; border-radius:50%; object-fit:cover; border:2px solid #e94560; margin-right:6px; vertical-align:middle; }
        /* ── Alerts ── */
        .alert { border-radius:8px; border:none; }
        .alert-success { background:rgba(39,174,96,0.15); border-left:4px solid #27ae60!important; color:#2ecc71; }
        .alert-danger  { background:rgba(231,76,60,0.15);  border-left:4px solid #e74c3c!important; color:#e74c3c; }
        .alert-warning { background:rgba(243,156,18,0.15); border-left:4px solid #f39c12!important; color:#f39c12; }
        .alert-info    { background:rgba(52,152,219,0.15); border-left:4px solid #3498db!important; color:#3498db; }
        /* ── Panels ── */
        .panel-anime { background:#16213e; border:1px solid rgba(233,69,96,0.25); border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.3); }
        .panel-anime>.panel-heading { background:linear-gradient(135deg,#0f3460,#16213e); border-bottom:2px solid #e94560; border-radius:10px 10px 0 0; padding:14px 22px; }
        .panel-anime>.panel-heading .panel-title { color:#e94560; font-size:17px; font-weight:700; }
        .panel-anime>.panel-body { padding:26px; }
        /* ── Forms ── */
        .form-control { background:rgba(255,255,255,0.06)!important; border:1px solid rgba(255,255,255,0.12)!important; color:#fff!important; border-radius:6px; transition:border .2s,box-shadow .2s; }
        .form-control:focus { border-color:#e94560!important; box-shadow:0 0 0 3px rgba(233,69,96,0.15)!important; outline:none!important; }
        .form-control::placeholder { color:#555!important; }
        select.form-control option { background:#16213e; color:#eee; }
        .form-group label { color:#bbb; font-size:13px; font-weight:600; margin-bottom:6px; }
        .has-error .form-control { border-color:#e74c3c!important; }
        .has-error .help-block { color:#e74c3c; font-size:12px; margin-top:4px; }
        .has-success .form-control { border-color:#27ae60!important; }
        /* ── Buttons ── */
        .btn { border-radius:6px; font-weight:600; transition:all .2s; }
        .btn-primary-custom { background:#e94560; border:none; color:#fff; }
        .btn-primary-custom:hover { background:#c73652; color:#fff; transform:translateY(-1px); }
        .btn-secondary-custom { background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.15); color:#ccc; }
        .btn-secondary-custom:hover { background:rgba(255,255,255,0.12); color:#fff; }
        .btn-success-custom { background:#27ae60; border:none; color:#fff; }
        .btn-success-custom:hover { background:#219a52; color:#fff; }
        .btn-danger-custom  { background:#e74c3c; border:none; color:#fff; }
        .btn-danger-custom:hover  { background:#c0392b; color:#fff; }
        /* ── Tables ── */
        .table-dark { color:#eee!important; }
        .table-dark>thead>tr>th { background:#0f3460; color:#e94560; border-bottom:2px solid #e94560!important; font-size:11px; text-transform:uppercase; letter-spacing:1px; border-top:none!important; padding:12px; }
        .table-dark>tbody>tr>td { border-top:1px solid rgba(255,255,255,0.05)!important; vertical-align:middle!important; padding:10px; }
        .table-dark>tbody>tr:hover>td { background:rgba(233,69,96,0.06)!important; }
        /* ── Badges & Labels ── */
        .badge-watching  { background:#3498db!important; }
        .badge-completed { background:#27ae60!important; }
        .badge-dropped   { background:#e74c3c!important; }
        .badge-plan      { background:#f39c12!important; }
        .rating-badge { background:#e94560; color:#fff; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700; }
        /* ── Modals ── */
        .modal-dark .modal-content { background:#16213e; border:1px solid #e94560; color:#eee; border-radius:10px; }
        .modal-dark .modal-header { border-bottom:1px solid rgba(233,69,96,0.3); padding:16px 22px; }
        .modal-dark .modal-title  { color:#e94560; font-weight:700; }
        .modal-dark .modal-footer { border-top:1px solid rgba(255,255,255,0.06); }
        .modal-dark .close { color:#eee!important; opacity:.7; }
        .modal-dark .close:hover { opacity:1; }
        /* ── Tooltips & Popovers ── */
        .tooltip-inner { background:#e94560; border-radius:4px; font-size:12px; }
        .tooltip.top .tooltip-arrow { border-top-color:#e94560; }
        .tooltip.bottom .tooltip-arrow { border-bottom-color:#e94560; }
        .popover { background:#16213e; border:1px solid #e94560; color:#eee; border-radius:8px; max-width:240px; }
        .popover-title { background:#0f3460; color:#e94560; border-bottom:1px solid rgba(233,69,96,0.3); font-weight:700; }
        .popover.top>.arrow:after    { border-top-color:#e94560; }
        .popover.bottom>.arrow:after { border-bottom-color:#e94560; }
        /* ── Pagination & Pager ── */
        .pagination>li>a,.pagination>li>span { background:#16213e; border-color:rgba(233,69,96,0.4); color:#e94560; }
        .pagination>li>a:hover { background:#e94560; color:#fff; border-color:#e94560; }
        .pagination>.active>a,.pagination>.active>span { background:#e94560; border-color:#e94560; color:#fff; }
        .pagination>.disabled>a { background:#16213e; color:#444; border-color:rgba(255,255,255,0.08); }
        .pager>li>a { background:#16213e; border-color:#e94560; color:#e94560; border-radius:20px; padding:6px 18px; }
        .pager>li>a:hover { background:#e94560; color:#fff; }
        /* ── List Groups ── */
        .list-group-dark .list-group-item { background:#16213e; border-color:rgba(255,255,255,0.07); color:#ccc; transition:all .15s; }
        .list-group-dark .list-group-item:hover { background:rgba(233,69,96,0.1); color:#e94560; }
        .list-group-dark .list-group-item.active { background:#e94560; border-color:#e94560; color:#fff; }
        /* ── Accordion ── */
        .accordion-dark .panel { background:#16213e; border:1px solid rgba(255,255,255,0.08); border-radius:8px!important; margin-bottom:6px; }
        .accordion-dark .panel-heading { background:#0f3460; border-radius:8px!important; padding:0; border-bottom:none; }
        .accordion-dark .panel-heading a { display:block; padding:12px 16px; color:#e94560; font-weight:600; font-size:14px; text-decoration:none; }
        .accordion-dark .panel-heading a:hover { color:#fff; }
        .accordion-dark .panel-heading a.collapsed { color:#aaa; }
        .accordion-dark .panel-body { color:#ccc; font-size:13px; border-top:1px solid rgba(233,69,96,0.2)!important; padding:14px 16px; }
        /* ── Misc ── */
        .cover-thumb { width:42px; height:56px; object-fit:cover; border-radius:5px; border:1px solid rgba(255,255,255,0.1); }
        .section-title { color:#e94560; font-weight:700; font-size:15px; text-transform:uppercase; letter-spacing:1px; margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid rgba(233,69,96,0.2); }
        ::-webkit-scrollbar { width:6px; }
        ::-webkit-scrollbar-track { background:#1a1a2e; }
        ::-webkit-scrollbar-thumb { background:#e94560; border-radius:3px; }
        @media(max-width:768px){ .hide-mobile{ display:none!important; } }
    </style>
</head>
<body>
<nav class="navbar navbar-custom navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#mainNav">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="home.php">
        🎌 AniTrack
      </a>
    </div>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="nav navbar-nav">
        <li class="<?= $current_page==='home.php'?'active':'' ?>">
          <a href="home.php"><span class="glyphicon glyphicon-home"></span> Home</a>
        </li>
        <li class="<?= $current_page==='browse.php'?'active':'' ?>">
          <a href="browse.php"><span class="glyphicon glyphicon-film"></span> Browse</a>
        </li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="<?= $current_page==='index.php'?'active':'' ?>">
            <a href="index.php"><span class="glyphicon glyphicon-list"></span> My List</a>
          </li>
        <?php endif; ?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
              <img src="avatars/<?= htmlspecialchars($_SESSION['avatar'] ?? 'default.png') ?>"
                   class="avatar-sm"
                   onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'U') ?>&background=e94560&color=fff&size=32'">
              <?= htmlspecialchars($_SESSION['username'] ?? '') ?>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
              <li class="dropdown-header" style="color:#888; font-size:11px;">ACCOUNT</li>
              <li><a href="index.php"><span class="glyphicon glyphicon-list"></span>&nbsp; My Watchlist</a></li>
              <li><a href="profile.php?id=<?= $_SESSION['user_id'] ?>"><span class="glyphicon glyphicon-user"></span>&nbsp; My Profile</a></li>
              <li class="divider"></li>
              <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span>&nbsp; Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="<?= $current_page==='login.php'?'active':'' ?>">
            <a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a>
          </li>
          <li class="<?= $current_page==='register.php'?'active':'' ?>">
            <a href="register.php"><span class="glyphicon glyphicon-user"></span> Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>