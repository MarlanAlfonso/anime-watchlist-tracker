<?php
// ============================================================
// SHARED NAVBAR — included at top of every page
// Set $active_page before including:
//   'home', 'mylist', 'browse', 'login', 'register'
// ============================================================
$active_page   = $active_page ?? '';
$logged_in_nav = isset($_SESSION['user_id']);
?>
<nav class="navbar navbar-custom navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed"
              data-toggle="collapse" data-target="#mainNav">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php">🎌 AniTrack</a>
    </div>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="nav navbar-nav">
        <li class="<?= $active_page === 'home'   ? 'active' : '' ?>">
          <a href="index.php">
            <span class="glyphicon glyphicon-home"></span> Home
          </a>
        </li>
        <li class="<?= $active_page === 'browse' ? 'active' : '' ?>">
          <a href="browse.php">
            <span class="glyphicon glyphicon-film"></span> Browse
          </a>
        </li>
        <?php if ($logged_in_nav): ?>
          <li class="<?= $active_page === 'mylist' ? 'active' : '' ?>">
            <a href="mylist.php">
              <span class="glyphicon glyphicon-list"></span> My List
            </a>
          </li>
        <?php endif; ?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <?php if ($logged_in_nav): ?>
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
              <li>
                <a href="mylist.php">
                  <span class="glyphicon glyphicon-list"></span>&nbsp; My Watchlist
                </a>
              </li>
              <li>
                <a href="profile.php?id=<?= (int)$_SESSION['user_id'] ?>">
                  <span class="glyphicon glyphicon-user"></span>&nbsp; My Profile
                </a>
              </li>
              <li class="divider"></li>
              <li>
                <a href="logout.php">
                  <span class="glyphicon glyphicon-log-out"></span>&nbsp; Logout
                </a>
              </li>
            </ul>
          </li>
        <?php else: ?>
          <li class="<?= $active_page === 'login'    ? 'active' : '' ?>">
            <a href="login.php">
              <span class="glyphicon glyphicon-log-in"></span> Login
            </a>
          </li>
          <li class="<?= $active_page === 'register' ? 'active' : '' ?>">
            <a href="register.php">
              <span class="glyphicon glyphicon-user"></span> Register
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>