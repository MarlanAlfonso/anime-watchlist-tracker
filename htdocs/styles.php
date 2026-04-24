<?php
// ============================================================
// SHARED STYLES — included in <head> of every page
// Each page can add its own <style> block AFTER this include
// ============================================================
?>
<style>
  /* ── Base ── */
  body { background:#1a1a2e; color:#eee; font-family:'Segoe UI',sans-serif; padding-top:50px; }

  /* ── Navbar ── */
  .navbar-custom { background:#16213e; border:none; border-bottom:2px solid #e94560; min-height:50px; margin-bottom:0; }
  .navbar-custom .navbar-brand { color:#e94560!important; font-weight:700; font-size:20px; padding:14px 15px; }
  .navbar-custom .nav>li>a { color:#ccc!important; transition:color .2s; }
  .navbar-custom .nav>li>a .glyphicon { margin-right:4px; }
  .navbar-custom .nav>li>a:hover,
  .navbar-custom .nav>li.active>a { color:#e94560!important; background:transparent!important; }
  .navbar-toggle { border-color:#e94560; }
  .navbar-toggle .icon-bar { background:#e94560; }

  /* ── Dropdown ── */
  .dropdown-menu { background:#16213e; border:1px solid #e94560; border-radius:6px; box-shadow:0 4px 20px rgba(0,0,0,0.4); }
  .dropdown-menu>li>a { color:#ccc!important; padding:8px 16px; }
  .dropdown-menu>li>a:hover { background:#e94560!important; color:#fff!important; }
  .dropdown-menu>.divider { background:rgba(255,255,255,0.08); }
  .dropdown-header { color:#888!important; font-size:11px!important; }

  /* ── Avatar ── */
  .avatar-sm { width:30px; height:30px; border-radius:50%; object-fit:cover; border:2px solid #e94560; margin-right:6px; vertical-align:middle; }

  /* ── Alerts ── */
  .alert { border-radius:8px; border:none; }
  .alert-success { background:rgba(39,174,96,0.15);  border-left:4px solid #27ae60!important; color:#2ecc71; }
  .alert-danger  { background:rgba(231,76,60,0.15);   border-left:4px solid #e74c3c!important; color:#e74c3c; }
  .alert-warning { background:rgba(243,156,18,0.15);  border-left:4px solid #f39c12!important; color:#f39c12; }
  .alert-info    { background:rgba(52,152,219,0.15);  border-left:4px solid #3498db!important; color:#3498db; }

  /* ── Forms ── */
  .form-control { background:rgba(255,255,255,0.06)!important; border:1px solid rgba(255,255,255,0.12)!important; color:#fff!important; border-radius:6px; transition:border .2s, box-shadow .2s; }
  .form-control:focus { border-color:#e94560!important; box-shadow:0 0 0 3px rgba(233,69,96,0.15)!important; outline:none!important; }
  .form-control::placeholder { color:#555!important; }
  select.form-control option { background:#16213e; color:#eee; }
  label { color:#bbb; font-size:13px; font-weight:600; margin-bottom:6px; display:block; }
  .has-error .form-control { border-color:#e74c3c!important; }
  .has-error .help-block { color:#e74c3c; font-size:12px; margin-top:4px; }
  .rating-hint { color:#888; font-size:12px; margin-top:4px; }

  /* ── Buttons ── */
  .btn { border-radius:6px; font-weight:600; transition:all .2s; }
  .btn-primary-custom  { background:#e94560; border:none; color:#fff; }
  .btn-primary-custom:hover  { background:#c73652; color:#fff; }
  .btn-success-custom  { background:#27ae60; border:none; color:#fff; }
  .btn-success-custom:hover  { background:#219a52; color:#fff; }
  .btn-danger-custom   { background:#e74c3c; border:none; color:#fff; }
  .btn-danger-custom:hover   { background:#c0392b; color:#fff; }
  .btn-secondary-custom { background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.15); color:#ccc; }
  .btn-secondary-custom:hover { background:rgba(255,255,255,0.12); color:#fff; }

  /* ── Tables ── */
  .table-dark { color:#eee!important; }
  .table-dark>thead>tr>th { background:#0f3460; color:#e94560; border-bottom:2px solid #e94560!important; font-size:11px; text-transform:uppercase; letter-spacing:1px; border-top:none!important; padding:12px 10px; }
  .table-dark>tbody>tr>td { border-top:1px solid rgba(255,255,255,0.05)!important; vertical-align:middle!important; padding:10px; }
  .table-dark>tbody>tr:hover>td { background:rgba(233,69,96,0.06)!important; }

  /* ── Badges ── */
  .badge-watching  { background:#3498db!important; }
  .badge-completed { background:#27ae60!important; }
  .badge-dropped   { background:#e74c3c!important; }
  .badge-plan      { background:#f39c12!important; }
  .badge-ongoing   { background:#3498db!important; }
  .badge-upcoming  { background:#9b59b6!important; }
  .rating-badge    { background:#e94560; color:#fff; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:700; }

  /* ── Cover image ── */
  .cover-thumb { width:42px; height:56px; object-fit:cover; border-radius:5px; border:1px solid rgba(255,255,255,0.1); }
  img.preview-img { display:none; width:100%; border-radius:6px; margin-top:8px; max-height:180px; object-fit:cover; border:1px solid rgba(255,255,255,0.1); }

  /* ── Panels ── */
  .panel-anime { background:#16213e; border:1px solid rgba(233,69,96,0.25); border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.3); }
  .panel-anime>.panel-heading { background:linear-gradient(135deg,#0f3460,#16213e); border-bottom:2px solid #e94560; border-radius:10px 10px 0 0; padding:14px 22px; }
  .panel-anime>.panel-heading .panel-title { color:#e94560; font-size:17px; font-weight:700; }
  .panel-anime>.panel-body { padding:26px; }

  /* ── Modals ── */
  .modal-dark .modal-content { background:#16213e; border:1px solid #e94560; color:#eee; border-radius:10px; }
  .modal-dark .modal-header  { border-bottom:1px solid rgba(233,69,96,0.3); padding:16px 22px; }
  .modal-dark .modal-title   { color:#e94560; font-weight:700; }
  .modal-dark .modal-footer  { border-top:1px solid rgba(255,255,255,0.06); }
  .modal-dark .close { color:#eee!important; opacity:.7; }
  .modal-dark .close:hover { opacity:1; }

  /* ── Tabs ── */
  .nav-tabs { border-bottom:2px solid #e94560; }
  .nav-tabs>li>a { color:#aaa!important; background:transparent!important; border:none!important; border-radius:0!important; padding:9px 16px; }
  .nav-tabs>li.active>a { color:#fff!important; background:#e94560!important; border-radius:6px 6px 0 0!important; }
  .nav-tabs>li>a:hover { color:#e94560!important; background:rgba(233,69,96,0.1)!important; }

  /* ── Pills ── */
  .nav-pills>li>a { color:#aaa; background:rgba(255,255,255,0.05); border-radius:20px; margin-right:6px; font-size:12px; padding:5px 14px; border:1px solid rgba(255,255,255,0.1); }
  .nav-pills>li>a:hover { background:rgba(233,69,96,0.15); color:#e94560; }
  .nav-pills>li.active>a { background:#e94560!important; color:#fff!important; border-color:#e94560!important; }

  /* ── Pagination ── */
  .pagination>li>a,.pagination>li>span { background:#16213e; border-color:rgba(233,69,96,0.4); color:#e94560; }
  .pagination>li>a:hover { background:#e94560; color:#fff; border-color:#e94560; }
  .pagination>.active>a,.pagination>.active>span { background:#e94560; border-color:#e94560; color:#fff; }
  .pagination>.disabled>a { background:#16213e; color:#444; border-color:rgba(255,255,255,0.08); }

  /* ── Pager ── */
  .pager>li>a { background:#16213e; border-color:#e94560; color:#e94560; border-radius:20px; padding:6px 18px; }
  .pager>li>a:hover { background:#e94560; color:#fff; }

  /* ── List Group ── */
  .list-group-dark .list-group-item { background:#16213e; border-color:rgba(255,255,255,0.07); color:#ccc; transition:all .15s; }
  .list-group-dark .list-group-item:hover { background:rgba(233,69,96,0.1); color:#e94560; }
  .list-group-dark .list-group-item.active { background:#e94560; border-color:#e94560; color:#fff; }

  /* ── Accordion ── */
  .accordion-dark .panel { background:#16213e; border:1px solid rgba(255,255,255,0.08); border-radius:8px!important; margin-bottom:5px; }
  .accordion-dark .panel-heading { background:#0f3460; border-radius:8px!important; padding:0; border-bottom:none; }
  .accordion-dark .panel-heading a { display:flex; justify-content:space-between; align-items:center; padding:11px 15px; color:#e94560; font-weight:600; font-size:13px; text-decoration:none; }
  .accordion-dark .panel-heading a.collapsed { color:#aaa; }
  .accordion-dark .panel-body { color:#ccc; font-size:13px; border-top:1px solid rgba(233,69,96,0.2)!important; padding:13px 15px; }

  /* ── Tooltips & Popovers ── */
  .tooltip-inner { background:#e94560; border-radius:4px; font-size:12px; }
  .tooltip.top .tooltip-arrow    { border-top-color:#e94560; }
  .tooltip.bottom .tooltip-arrow { border-bottom-color:#e94560; }
  .popover { background:#16213e!important; border:1px solid #e94560!important; color:#eee; border-radius:8px; max-width:240px; }
  .popover-title   { background:#0f3460!important; color:#e94560!important; border-bottom:1px solid rgba(233,69,96,0.3)!important; font-weight:700; }
  .popover-content { color:#ccc!important; }
  .popover.top>.arrow:after    { border-top-color:#e94560!important; }
  .popover.right>.arrow:after  { border-right-color:#16213e!important; }
  .popover.bottom>.arrow:after { border-bottom-color:#e94560!important; }

  /* ── Misc ── */
  .empty-state { text-align:center; padding:50px 0; color:#555; }
  .empty-state .glyphicon { font-size:48px; margin-bottom:16px; display:block; }
  .section-title { color:#e94560; font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:1px; margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid rgba(233,69,96,0.2); }
  ::-webkit-scrollbar { width:6px; }
  ::-webkit-scrollbar-track { background:#1a1a2e; }
  ::-webkit-scrollbar-thumb { background:#e94560; border-radius:3px; }
  @media(max-width:768px){ .hide-mobile,.hide-sm{ display:none!important; } }
</style>