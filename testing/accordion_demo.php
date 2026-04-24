<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'db.php';
$page_title = 'Bootstrap Components — AniTrack';
require 'navbar.php';
?>

<div class="container" style="padding-bottom:60px; margin-top:30px;">

  <!-- ── PAGE TITLE ───────────────────────────────────── -->
  <div class="row">
    <div class="col-md-12">
      <h2 style="color:#e94560; font-weight:700; border-bottom:2px solid #e94560; padding-bottom:10px;">
        <span class="glyphicon glyphicon-th-large"></span>
        Bootstrap 3.4.1 Components Showcase
      </h2>
      <p style="color:#888; margin-bottom:30px;">All Bootstrap components used in AniTrack</p>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">

      <!-- ── ALERTS ──────────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-bell"></span> Alerts</div>
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span class="glyphicon glyphicon-ok-circle"></span>
        <strong>Success!</strong> Anime added to your watchlist.
      </div>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span class="glyphicon glyphicon-remove-circle"></span>
        <strong>Error!</strong> Please fill in all required fields.
      </div>
      <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span class="glyphicon glyphicon-warning-sign"></span>
        <strong>Warning!</strong> That anime is already in your list.
      </div>
      <div class="alert alert-info alert-dismissible" style="margin-bottom:24px;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <span class="glyphicon glyphicon-info-sign"></span>
        <strong>Info!</strong> Browse public watchlists without logging in.
      </div>

      <!-- ── BUTTONS & BUTTON GROUPS ─────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-hand-up"></span> Buttons & Button Groups</div>
      <div style="margin-bottom:12px;">
        <button class="btn btn-primary-custom"><span class="glyphicon glyphicon-plus"></span> Add Anime</button>
        <button class="btn btn-success-custom"><span class="glyphicon glyphicon-floppy-disk"></span> Save</button>
        <button class="btn btn-danger-custom"><span class="glyphicon glyphicon-trash"></span> Delete</button>
        <button class="btn btn-secondary-custom"><span class="glyphicon glyphicon-arrow-left"></span> Cancel</button>
      </div>
      <div class="btn-group" style="margin-bottom:24px;">
        <button type="button" class="btn btn-default" style="background:rgba(255,255,255,0.07);color:#ccc;border-color:rgba(255,255,255,0.15);">
          <span class="glyphicon glyphicon-eye-open"></span> Watching
        </button>
        <button type="button" class="btn btn-default" style="background:rgba(255,255,255,0.07);color:#ccc;border-color:rgba(255,255,255,0.15);">
          <span class="glyphicon glyphicon-ok"></span> Completed
        </button>
        <button type="button" class="btn btn-default" style="background:rgba(255,255,255,0.07);color:#ccc;border-color:rgba(255,255,255,0.15);">
          <span class="glyphicon glyphicon-bookmark"></span> Plan to Watch
        </button>
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                style="background:rgba(255,255,255,0.07);color:#ccc;border-color:rgba(255,255,255,0.15);">
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <li><a href="#">Dropped</a></li>
          <li><a href="#">All Status</a></li>
        </ul>
      </div>

      <!-- ── TOOLTIPS & POPOVERS ──────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-info-sign"></span> Tooltips & Popovers</div>
      <div style="margin-bottom:24px;">
        <button class="btn btn-secondary-custom"
                data-toggle="tooltip" data-placement="top" title="This is a tooltip!">
          <span class="glyphicon glyphicon-question-sign"></span> Hover for Tooltip
        </button>
        &nbsp;
        <button class="btn btn-secondary-custom"
                data-toggle="popover" data-placement="top"
                data-trigger="click"
                title="One Piece Info"
                data-content="Genre: Adventure | Episodes: 1122 | Rating: 9.5 ⭐">
          <span class="glyphicon glyphicon-film"></span> Click for Popover
        </button>
        &nbsp;
        <span class="rating-badge"
              data-toggle="tooltip" data-placement="top" title="User rating: 9.5 / 10">
          ⭐ 9.5
        </span>
      </div>

      <!-- ── ACCORDION ────────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-menu-hamburger"></span> Accordion (FAQ)</div>
      <div class="panel-group accordion-dark" id="faqAccordion" style="margin-bottom:24px;">

        <div class="panel">
          <div class="panel-heading">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#faq1">
              <span class="glyphicon glyphicon-question-sign"></span>
              &nbsp; What is AniTrack?
            </a>
          </div>
          <div id="faq1" class="panel-collapse collapse in">
            <div class="panel-body">
              AniTrack is a free anime watchlist tracker where you can log every anime you've watched,
              are currently watching, plan to watch, or have dropped. You can also browse other users'
              lists and copy anime to your own list!
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-heading">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#faq2" class="collapsed">
              <span class="glyphicon glyphicon-user"></span>
              &nbsp; Do I need an account to browse?
            </a>
          </div>
          <div id="faq2" class="panel-collapse collapse">
            <div class="panel-body">
              No! You can browse all public watchlists without an account.
              However, you need to register and log in to add, edit, delete,
              or copy anime to your personal list.
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-heading">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#faq3" class="collapsed">
              <span class="glyphicon glyphicon-picture"></span>
              &nbsp; Can I upload cover images?
            </a>
          </div>
          <div id="faq3" class="panel-collapse collapse">
            <div class="panel-body">
              Yes! When adding or editing an anime you can upload a cover image in
              JPG, PNG, GIF or WEBP format. Max file size is 2MB.
              Images are stored securely in the uploads folder.
            </div>
          </div>
        </div>

        <div class="panel">
          <div class="panel-heading">
            <a data-toggle="collapse" data-parent="#faqAccordion" href="#faq4" class="collapsed">
              <span class="glyphicon glyphicon-copy"></span>
              &nbsp; How does Copy to My List work?
            </a>
          </div>
          <div id="faq4" class="panel-collapse collapse">
            <div class="panel-body">
              When browsing other users' watchlists you'll see a green Copy button
              next to each anime. Clicking it instantly copies that anime entry
              (including cover image) to your own personal watchlist.
              You can then edit it however you like!
            </div>
          </div>
        </div>

      </div>

      <!-- ── TABS ─────────────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-list-alt"></span> Tabs & Pills</div>
      <ul class="nav nav-tabs" id="demoTabs" style="border-bottom:2px solid #e94560; margin-bottom:0;">
        <li class="active"><a href="#tab-watching" data-toggle="tab">
          <span class="glyphicon glyphicon-eye-open"></span> Watching
          <span class="badge" style="background:#3498db">12</span>
        </a></li>
        <li><a href="#tab-completed" data-toggle="tab">
          <span class="glyphicon glyphicon-ok"></span> Completed
          <span class="badge" style="background:#27ae60">34</span>
        </a></li>
        <li><a href="#tab-dropped" data-toggle="tab">
          <span class="glyphicon glyphicon-remove"></span> Dropped
          <span class="badge" style="background:#e74c3c">3</span>
        </a></li>
        <li><a href="#tab-plan" data-toggle="tab">
          <span class="glyphicon glyphicon-bookmark"></span> Plan to Watch
          <span class="badge" style="background:#f39c12">8</span>
        </a></li>
      </ul>
      <div class="tab-content" style="background:#16213e; border:1px solid rgba(255,255,255,0.07); border-top:none; border-radius:0 0 8px 8px; padding:18px; margin-bottom:24px;">
        <div class="tab-pane active" id="tab-watching">
          <p style="color:#aaa; margin:0;">
            <span class="glyphicon glyphicon-eye-open" style="color:#3498db"></span>
            Currently watching <strong style="color:#3498db">12 anime</strong> right now.
            One Piece, Bleach TYBW, Demon Slayer and more!
          </p>
        </div>
        <div class="tab-pane" id="tab-completed">
          <p style="color:#aaa; margin:0;">
            <span class="glyphicon glyphicon-ok" style="color:#27ae60"></span>
            <strong style="color:#27ae60">34 anime</strong> completed and rated.
            Attack on Titan, Death Note, FMA Brotherhood top the list!
          </p>
        </div>
        <div class="tab-pane" id="tab-dropped">
          <p style="color:#aaa; margin:0;">
            <span class="glyphicon glyphicon-remove" style="color:#e74c3c"></span>
            <strong style="color:#e74c3c">3 anime</strong> dropped.
            Some just weren't for me!
          </p>
        </div>
        <div class="tab-pane" id="tab-plan">
          <p style="color:#aaa; margin:0;">
            <span class="glyphicon glyphicon-bookmark" style="color:#f39c12"></span>
            <strong style="color:#f39c12">8 anime</strong> on the backlog.
            Vinland Saga, Mushishi, and more waiting!
          </p>
        </div>
      </div>

      <!-- ── PILLS ─────────────────────────────────────── -->
      <ul class="nav nav-pills" style="margin-bottom:24px;">
        <li class="active"><a href="#" data-toggle="pill">All</a></li>
        <li><a href="#" data-toggle="pill">Action</a></li>
        <li><a href="#" data-toggle="pill">Romance</a></li>
        <li><a href="#" data-toggle="pill">Fantasy</a></li>
        <li><a href="#" data-toggle="pill">Sci-Fi</a></li>
      </ul>

      <!-- ── MODAL TRIGGER ─────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-modal-window"></span> Modal</div>
      <div style="margin-bottom:24px;">
        <button class="btn btn-danger-custom" data-toggle="modal" data-target="#demoModal">
          <span class="glyphicon glyphicon-trash"></span> Delete Demo (Modal)
        </button>
      </div>

      <!-- ── BADGES ────────────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-tag"></span> Badges & Labels</div>
      <div style="margin-bottom:24px;">
        <span class="badge badge-watching">Watching</span>&nbsp;
        <span class="badge badge-completed">Completed</span>&nbsp;
        <span class="badge badge-dropped">Dropped</span>&nbsp;
        <span class="badge badge-plan">Plan to Watch</span>&nbsp;
        <span class="rating-badge">⭐ 9.5</span>&nbsp;
        <span class="label label-default">Adventure</span>&nbsp;
        <span class="label label-primary" style="background:#e94560">Action</span>&nbsp;
        <span class="label label-success">Romance</span>
      </div>

      <!-- ── PAGINATION ────────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-th"></span> Pagination & Pager</div>
      <nav style="margin-bottom:10px;">
        <ul class="pagination">
          <li class="disabled"><a href="#">&laquo;</a></li>
          <li class="active"><a href="#">1</a></li>
          <li><a href="#">2</a></li>
          <li><a href="#">3</a></li>
          <li><a href="#">4</a></li>
          <li><a href="#">5</a></li>
          <li><a href="#">&raquo;</a></li>
        </ul>
      </nav>
      <ul class="pager" style="margin-bottom:24px;">
        <li class="previous disabled"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span> Previous User</a></li>
        <li class="next"><a href="#">Next User <span class="glyphicon glyphicon-chevron-right"></span></a></li>
      </ul>

    </div>

    <!-- ── SIDEBAR ───────────────────────────────────── -->
    <div class="col-md-4">

      <!-- ── LIST GROUP ──────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-list"></span> List Group</div>
      <ul class="list-group list-group-dark" style="margin-bottom:24px;">
        <li class="list-group-item active">
          <span class="glyphicon glyphicon-fire"></span> All Genres
          <span class="badge">57</span>
        </li>
        <li class="list-group-item">
          <span class="glyphicon glyphicon-tag"></span> Action
          <span class="badge">18</span>
        </li>
        <li class="list-group-item">
          <span class="glyphicon glyphicon-tag"></span> Adventure
          <span class="badge">12</span>
        </li>
        <li class="list-group-item">
          <span class="glyphicon glyphicon-tag"></span> Romance
          <span class="badge">9</span>
        </li>
        <li class="list-group-item">
          <span class="glyphicon glyphicon-tag"></span> Fantasy
          <span class="badge">8</span>
        </li>
        <li class="list-group-item">
          <span class="glyphicon glyphicon-tag"></span> Sci-Fi
          <span class="badge">6</span>
        </li>
      </ul>

      <!-- ── LIST GROUP COLLAPSE ─────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-collapse-down"></span> List Group Collapse</div>
      <div class="list-group list-group-dark" style="margin-bottom:24px;">
        <a href="#lgCollapse1" data-toggle="collapse"
           class="list-group-item"
           style="background:#0f3460; color:#e94560; font-weight:700;">
          <span class="glyphicon glyphicon-chevron-down"></span>
          &nbsp; Top Rated Anime
        </a>
        <div id="lgCollapse1" class="collapse in">
          <a href="#" class="list-group-item">
            <span class="rating-badge" style="font-size:11px;">⭐ 9.9</span>
            &nbsp; Fullmetal Alchemist: Brotherhood
          </a>
          <a href="#" class="list-group-item">
            <span class="rating-badge" style="font-size:11px;">⭐ 9.7</span>
            &nbsp; Steins;Gate
          </a>
          <a href="#" class="list-group-item">
            <span class="rating-badge" style="font-size:11px;">⭐ 9.5</span>
            &nbsp; One Piece
          </a>
        </div>

        <a href="#lgCollapse2" data-toggle="collapse"
           class="list-group-item collapsed"
           style="background:#0f3460; color:#e94560; font-weight:700; margin-top:4px;">
          <span class="glyphicon glyphicon-chevron-right"></span>
          &nbsp; Currently Airing
        </a>
        <div id="lgCollapse2" class="collapse">
          <a href="#" class="list-group-item">
            <span class="badge badge-watching">Watching</span>
            &nbsp; One Piece
          </a>
          <a href="#" class="list-group-item">
            <span class="badge badge-watching">Watching</span>
            &nbsp; Bleach: TYBW
          </a>
        </div>
      </div>

      <!-- ── PANEL ───────────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-list-alt"></span> Panel</div>
      <div class="panel panel-anime" style="margin-bottom:24px;">
        <div class="panel-heading">
          <h4 class="panel-title">
            <span class="glyphicon glyphicon-stats"></span> My Stats
          </h4>
        </div>
        <div class="panel-body" style="padding:16px;">
          <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span style="color:#888; font-size:13px;">Total Anime</span>
            <span class="badge" style="background:#e94560; font-size:13px;">57</span>
          </div>
          <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <span style="color:#888; font-size:13px;">Completed</span>
            <span class="badge badge-completed; font-size:13px;">34</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span style="color:#888; font-size:13px;">Average Rating</span>
            <span class="rating-badge">⭐ 8.4</span>
          </div>
        </div>
      </div>

      <!-- ── IMAGE ───────────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-picture"></span> Image</div>
      <img src="https://via.placeholder.com/300x160/0f3460/e94560?text=Cover+Image+Preview"
           class="img-thumbnail img-responsive"
           style="border-color:#e94560; background:#16213e; margin-bottom:24px;"
           data-toggle="tooltip" title="Anime cover image (.img-thumbnail)">

      <!-- ── TEXT STYLES ─────────────────────────────── -->
      <div class="section-title"><span class="glyphicon glyphicon-text-size"></span> Text Styles</div>
      <div style="margin-bottom:24px;">
        <p class="lead" style="color:#eee; font-size:16px;">Lead text — AniTrack</p>
        <p style="color:#eee;">Normal paragraph text for descriptions.</p>
        <p class="text-muted">Muted text for secondary info.</p>
        <p><small class="text-muted">Small muted — Added Apr 03, 2026</small></p>
        <p><strong style="color:#e94560">Bold accent text</strong></p>
        <p><em style="color:#888">Italic text for notes</em></p>
      </div>

    </div>
  </div>
</div>

<!-- ── DEMO MODAL ────────────────────────────────────── -->
<div class="modal fade" id="demoModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dark">
    <div class="modal-content" style="background:#16213e; border:1px solid #e94560; color:#eee; border-radius:10px;">
      <div class="modal-header" style="border-bottom:1px solid rgba(233,69,96,0.3);">
        <button type="button" class="close" data-dismiss="modal" style="color:#eee;">&times;</button>
        <h4 class="modal-title" style="color:#e94560;">
          <span class="glyphicon glyphicon-trash"></span> Confirm Delete
        </h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete <strong>One Piece</strong>?</p>
        <p style="color:#888; font-size:13px;">This action cannot be undone.</p>
      </div>
      <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.07);">
        <button type="button" class="btn btn-secondary-custom" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger-custom">
          <span class="glyphicon glyphicon-trash"></span> Delete
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script>
  $(function() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
  });
</script>
</body>
</html>