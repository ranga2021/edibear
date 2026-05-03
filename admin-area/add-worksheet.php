<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("add-worksheet");
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
      color: #f97316;
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
        Choose what to add. Coloring pages, books &amp; papers, and homeworks are grouped here as <strong>worksheets</strong> for the site.
      </p>
      <div class="row g-4">
        <div class="col-md-4">
          <a class="edi-ws-hub-card" href="./add-pdf">
            <i class="fas fa-palette" aria-hidden="true"></i>
            <h3>Add coloring page</h3>
            <p>Free printable PDF coloring sheets.</p>
          </a>
        </div>
        <div class="col-md-4">
          <a class="edi-ws-hub-card" href="./add-books">
            <i class="fas fa-book-open" aria-hidden="true"></i>
            <h3>Add book or paper</h3>
            <p>Books, past papers, and study materials.</p>
          </a>
        </div>
        <div class="col-md-4">
          <a class="edi-ws-hub-card" href="./add-homework">
            <i class="fas fa-edit" aria-hidden="true"></i>
            <h3>Add homework</h3>
            <p>Homework packs and assignments.</p>
          </a>
        </div>
      </div>
    </div>
  </main>
  <?php echo $adminHeader->printAdminFooter(); ?>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>
