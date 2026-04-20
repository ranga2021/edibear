<?php
require_once("../classes/session_config.php");

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("dashboard");
$user = new USER();



// Dashboard statistics (data-only, no side effects)
$totalResources =
    (int) $user->CountRows("blog_details", array("status" => 1)) +
    (int) $user->CountRows("books_details", array("status" => 1)) +
    (int) $user->CountRows("homework_details", array("status" => 1)) +
    (int) $user->CountRows("pdf_details", array("status" => 1));

$totalProducts = (int) $user->CountRows("products", array("status" => 1));

// Total sales
$totalSales = 0;
try {
    $orderStmt = $user->getConnection()->query("SELECT COALESCE(SUM(total), 0) AS total_sales FROM orders");
    $orderRow = $orderStmt ? $orderStmt->fetch(PDO::FETCH_ASSOC) : null;
    if ($orderRow && isset($orderRow['total_sales'])) {
        $totalSales = (float) $orderRow['total_sales'];
    }
} catch (Exception $e) {
    $totalSales = 0;
}

// Total downloads
$totalDownloads = 0;
try {
    $downloadQuery = "
        SELECT 
            (SELECT COALESCE(SUM(download_count), 0) FROM pdf_details) +
            (SELECT COALESCE(SUM(download_count), 0) FROM books_details) +
            (SELECT COALESCE(SUM(download_count), 0) FROM homework_details) 
        AS grand_total";

    $downloadStmt = $user->getConnection()->query($downloadQuery);
    $downloadRow = $downloadStmt->fetch(PDO::FETCH_ASSOC);
    $totalDownloads = (int)$downloadRow['grand_total'];
} catch (Exception $e) {
    $totalDownloads = 0;
}

// Total members
$totalMembers = (int)$user->CountRows("tourists", array());
?>
<script>
    // 1. Check if the localStorage item exists
    const adminSession = localStorage.getItem('admin_session');
    const sessionTime = localStorage.getItem('session_time');
    const currentTime = Math.floor(Date.now() / 1000);

    // 2. If missing OR older than 20 minutes (1200 seconds), kick them out
    if (!adminSession || (currentTime - sessionTime > 1200)) {
        localStorage.removeItem('admin_session');
        window.location.href = 'index.php?error=session_expired';
    }
</script>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo $adminHeader->printAdminHeader(); ?>

  <script src="./assets/js/plugins/chartjs.min.js"></script>

  <style>
    .stats-section {
      margin-bottom: 2rem;
    }

    .stats-title {
      font-size: 1.4rem;
      font-weight: 700;
      text-transform: uppercase;
      color: #f97316;
      margin-bottom: 1.5rem;
    }

    .stats-card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 1.2rem;
    }

    .stats-card {
      background-color: #ffffff;
      border-radius: 18px;
      border: 1px solid #e5e7eb;
      padding: 32px 16px;
      text-align: center;
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .stats-card-value {
      font-size: 2rem;
      font-weight: 700;
    }

    .stats-card-label {
      font-size: 0.8rem;
      color: #9ca3af;
    }
  </style>
</head>

<body class="bg-gray-100">

<?php echo $adminHeader->printAdminNav(); ?>

<main class="main-content">

<?php echo $adminHeader->printAdminNav2("Dashboard"); ?>

<div class="container-fluid py-4">

  <h2 class="stats-title">Statistics</h2>

  <div class="stats-card-grid">

    <div class="stats-card">
      <div class="stats-card-value"><?php echo number_format($totalResources); ?></div>
      <div class="stats-card-label">Total Resources</div>
    </div>

    <div class="stats-card">
      <div class="stats-card-value"><?php echo number_format($totalDownloads); ?></div>
      <div class="stats-card-label">Total Downloads</div>
    </div>

    <div class="stats-card">
      <div class="stats-card-value"><?php echo number_format($totalProducts); ?></div>
      <div class="stats-card-label">Total Products</div>
    </div>

    <div class="stats-card">
      <div class="stats-card-value"><?php echo number_format($totalMembers); ?></div>
      <div class="stats-card-label">Total Members</div>
    </div>

    <div class="stats-card">
      <div class="stats-card-value"><?php echo number_format($totalSales); ?></div>
      <div class="stats-card-label">Total Sales</div>
    </div>

  </div>

</div>

</main>

<?php echo $adminHeader->printAdminFooter(); ?>
<?php echo $adminHeader->printAdminFooterJS(); ?>


</body>
</html>