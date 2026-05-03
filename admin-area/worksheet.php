<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("worksheet");
$user = new USER();
?>
<script>
    const adminSession = localStorage.getItem('admin_session');
    const sessionTime = localStorage.getItem('session_time');
    const currentTime = Math.floor(Date.now() / 1000);
    if (!adminSession || (currentTime - sessionTime > 1200)) {
        localStorage.removeItem('admin_session');
        window.location.href = 'index.php?error=session_expired';
    }
</script>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <style>
    .edi-ws-hub-intro { color: #64748b; font-size: 0.95rem; max-width: 42rem; margin-bottom: 1.5rem; }
    .edi-ws-hub-card {
      display: block;
      height: 100%;
      text-decoration: none;
      color: inherit;
      border-radius: 14px;
      border: 1px solid #e2e8f0;
      background: #fff;
      padding: 1.5rem 1.25rem;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .edi-ws-hub-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 16px 36px rgba(15, 23, 42, 0.1);
      border-color: #fdba74;
      color: inherit;
    }
    .edi-ws-hub-card:focus { outline: 2px solid #f97316; outline-offset: 3px; }
    .edi-ws-hub-card i {
      font-size: 2rem;
      color: #e74c3c;
      margin-bottom: 0.75rem;
    }
    .edi-ws-hub-card h3 { font-size: 1.05rem; font-weight: 700; color: #1e293b; margin: 0 0 0.35rem; }
    .edi-ws-hub-card p { font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.45; }
  </style>
</head>
<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>
    <div class="container-fluid py-4">
      <p class="edi-ws-hub-intro mb-4">
        Open the list you need. All worksheet-type content is managed from these screens.
      </p>
      <div class="row g-4">
        <div class="col-md-4">
          <a class="edi-ws-hub-card" href="./pdf">
            <i class="fas fa-images" aria-hidden="true"></i>
            <h3>Coloring pages</h3>
            <p>View, edit, and manage PDF coloring listings.</p>
          </a>
        </div>
        <div class="col-md-4">
          <a class="edi-ws-hub-card" href="./books">
            <i class="fas fa-book" aria-hidden="true"></i>
            <h3>Books &amp; papers</h3>
            <p>Manage books, past papers, and related items.</p>
          </a>
        </div>
        <div class="col-md-4">
          <a class="edi-ws-hub-card" href="./homework">
            <i class="fas fa-tasks" aria-hidden="true"></i>
            <h3>Homeworks</h3>
            <p>Manage homework packs and assignments.</p>
          </a>
        </div>
      </div>
    </div>
  </main>
  <?php echo $adminHeader->printAdminFooter(); ?>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>
