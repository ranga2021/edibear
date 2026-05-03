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
    .edi-ws-hub-page-title {
      font-size: 1.35rem;
      font-weight: 700;
      color: #1e293b;
      margin: 0 0 0.35rem;
      letter-spacing: 0.02em;
    }
    .edi-ws-hub-intro {
      color: #64748b;
      font-size: 0.95rem;
      max-width: 48rem;
      margin-bottom: 1.25rem;
      line-height: 1.5;
    }
  </style>
</head>
<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>
    <div class="container-fluid py-4">
      <h1 class="edi-ws-hub-page-title">Worksheet</h1>
      <p class="edi-ws-hub-intro">
        All coloring pages, books &amp; papers, and homework items in one list. Use Search to filter by document title. Edit opens the correct form for each row.
      </p>
      <div class="row">
        <div class="col-12 mb-4">
          <?php require __DIR__ . '/partials/edi_worksheet_list_unified_card.php'; ?>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>
