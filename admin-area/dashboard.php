<?php
require_once("../classes/session_config.php");

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("dashboard");
$user = new USER();
$pdo = $user->getConnection();

// Per-type resource counts (for stat validation + charts)
$blogCount = (int) $user->CountRows("blog_details", array("status" => 1));
$booksCount = (int) $user->CountRows("books_details", array("status" => 1));
$hwCount = (int) $user->CountRows("homework_details", array("status" => 1));
$pdfCount = (int) $user->CountRows("pdf_details", array("status" => 1));

// Dashboard statistics (data-only, no side effects)
$totalResources = $blogCount + $booksCount + $hwCount + $pdfCount;

$totalProducts = (int) $user->CountRows("products", array("status" => 1));

// Total sales
$totalSales = 0;
try {
    $orderStmt = $pdo->query("SELECT COALESCE(SUM(total), 0) AS total_sales FROM orders");
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

    $downloadStmt = $pdo->query($downloadQuery);
    $downloadRow = $downloadStmt->fetch(PDO::FETCH_ASSOC);
    $totalDownloads = (int) $downloadRow['grand_total'];
} catch (Exception $e) {
    $totalDownloads = 0;
}

// Total members
$totalMembers = (int) $user->CountRows("tourists", array());

// Order KPIs
$orderCount = 0;
$ordersPending = 0;
$ordersThisMonth = 0;
try {
    $orderCount = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $ordersPending = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'pending'")->fetchColumn();
    $ordersThisMonth = (int) $pdo->query(
        "SELECT COUNT(*) FROM orders WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"
    )->fetchColumn();
} catch (Exception $e) {
    $orderCount = 0;
    $ordersPending = 0;
    $ordersThisMonth = 0;
}

// Revenue last 14 days (for line chart)
$salesDayMap = array();
try {
    $st = $pdo->query(
        "SELECT DATE(created_at) AS d, COALESCE(SUM(total), 0) AS rev FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(created_at)"
    );
    if ($st) {
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $salesDayMap[(string) $row['d']] = (float) $row['rev'];
        }
    }
} catch (Exception $e) {
    $salesDayMap = array();
}
$labels14 = array();
$data14 = array();
for ($i = 13; $i >= 0; $i--) {
    $d = date("Y-m-d", strtotime("-{$i} days"));
    $labels14[] = date("M j", strtotime($d));
    $data14[] = isset($salesDayMap[$d]) ? round($salesDayMap[$d], 2) : 0;
}

// Payment method revenue split (doughnut)
$payLabels = array();
$payData = array();
$payColors = array();
try {
    $st = $pdo->query("SELECT payment_method, COALESCE(SUM(total), 0) AS tot FROM orders GROUP BY payment_method");
    if ($st) {
        $palette = array(
            "cod" => array("label" => "Cash on delivery", "color" => "#33a675"),
            "bank_transfer" => array("label" => "Bank transfer", "color" => "#5b8def"),
            "card" => array("label" => "Card", "color" => "#f97316"),
        );
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $pm = (string) ($row["payment_method"] ?? "");
            $tot = (float) ($row["tot"] ?? 0);
            if ($tot <= 0) {
                continue;
            }
            $meta = isset($palette[$pm]) ? $palette[$pm] : array("label" => ucfirst(str_replace("_", " ", $pm)), "color" => "#94a3b8");
            $payLabels[] = $meta["label"];
            $payData[] = round($tot, 2);
            $payColors[] = $meta["color"];
        }
    }
} catch (Exception $e) {
    $payLabels = array();
    $payData = array();
    $payColors = array();
}

// New members last 14 days
$memberDayMap = array();
try {
    $st = $pdo->query(
        "SELECT DATE(`timestamp`) AS d, COUNT(*) AS c FROM tourists WHERE `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(`timestamp`)"
    );
    if ($st) {
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $memberDayMap[(string) $row["d"]] = (int) $row["c"];
        }
    }
} catch (Exception $e) {
    $memberDayMap = array();
}
$memberLabels14 = array();
$memberData14 = array();
for ($i = 13; $i >= 0; $i--) {
    $d = date("Y-m-d", strtotime("-{$i} days"));
    $memberLabels14[] = date("M j", strtotime($d));
    $memberData14[] = isset($memberDayMap[$d]) ? (int) $memberDayMap[$d] : 0;
}

$hasPaymentChart = $payLabels !== array() && array_sum($payData) > 0;

$chartPayload = array(
    "revenueLabels" => $labels14,
    "revenueData" => $data14,
    "resourceLabels" => array("Blogs", "Coloring PDFs", "Books", "Homework"),
    "resourceData" => array($blogCount, $pdfCount, $booksCount, $hwCount),
    "resourceColors" => array("#f97316", "#8b5cf6", "#33a675", "#0ea5e9"),
    "paymentLabels" => $payLabels,
    "paymentData" => $payData,
    "paymentColors" => $payColors,
    "memberLabels" => $memberLabels14,
    "memberData" => $memberData14,
    "hasPaymentChart" => $hasPaymentChart,
);
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

    /* Shared width + column tracks so stat cards and KPI row line up */
    .dashboard-metrics-shell {
      max-width: 1280px;
      margin-left: auto;
      margin-right: auto;
      padding-left: 0.25rem;
      padding-right: 0.25rem;
    }

    .stats-title {
      font-size: 1.4rem;
      font-weight: 700;
      text-transform: uppercase;
      color: #f97316;
      margin-bottom: 1.5rem;
    }

    .stats-title--sub {
      margin-top: 2rem;
    }

    .stats-card-grid {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 1.2rem;
      margin-bottom: 1.2rem;
    }

    .stats-card {
      background-color: #ffffff;
      border-radius: 18px;
      border: 1px solid #e5e7eb;
      padding: 28px 16px 24px;
      text-align: center;
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .stats-card--link {
      display: block;
      text-decoration: none;
      color: inherit;
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .stats-card--link:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 44px rgba(15, 23, 42, 0.12);
      border-color: #fdba74;
    }

    .stats-card--link:focus {
      outline: 2px solid #f97316;
      outline-offset: 3px;
    }

    .stats-card-icon {
      font-size: 1.65rem;
      margin-bottom: 0.65rem;
      opacity: 0.92;
      color: #f97316;
    }

    .stats-card-value {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
    }

    .stats-card-label {
      font-size: 0.8rem;
      color: #64748b;
      margin-top: 0.35rem;
      line-height: 1.35;
    }

    .stats-card-hint {
      font-size: 0.65rem;
      color: #94a3b8;
      margin-top: 0.5rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .dashboard-kpi-row {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 1.2rem;
      margin-bottom: 2rem;
      align-items: stretch;
    }

    .dashboard-kpi {
      background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      padding: 1rem 1.15rem;
      min-height: 5.25rem;
      height: 100%;
      display: flex;
      align-items: center;
      gap: 0.85rem;
      min-width: 0;
    }

    .dashboard-kpi i {
      font-size: 1.35rem;
      color: #64748b;
    }

    .dashboard-kpi strong {
      display: block;
      font-size: 1.25rem;
      color: #0f172a;
    }

    .dashboard-kpi span {
      font-size: 0.75rem;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    .dashboard-charts-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 1.25rem;
      margin-bottom: 1.25rem;
    }

    @media (max-width: 1199.98px) {
      .stats-card-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .dashboard-kpi-row {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }

    @media (max-width: 767.98px) {
      .stats-card-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .dashboard-kpi-row {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 991.98px) {
      .dashboard-charts-grid {
        grid-template-columns: 1fr;
      }
    }

    .chart-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 18px;
      padding: 1.25rem 1.35rem 1rem;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    .chart-card-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: #334155;
      margin: 0 0 0.25rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .chart-card-desc {
      font-size: 0.75rem;
      color: #94a3b8;
      margin-bottom: 0.75rem;
    }

    .chart-canvas-wrap {
      position: relative;
      height: 260px;
    }

    .chart-canvas-wrap--short {
      height: 220px;
    }
  </style>
</head>

<body class="bg-gray-100">

<?php echo $adminHeader->printAdminNav(); ?>

<main class="main-content">

<?php echo $adminHeader->printAdminNav2("Dashboard"); ?>

<div class="container-fluid py-4">

  <div class="dashboard-metrics-shell">

  <h2 class="stats-title">Statistics</h2>

  <div class="stats-card-grid">

    <a href="./blogs" class="stats-card stats-card--link" title="Blogs (worksheet lists: sidebar → Worksheet)">
      <div class="stats-card-icon"><i class="fas fa-layer-group" aria-hidden="true"></i></div>
      <div class="stats-card-value"><?php echo number_format($totalResources); ?></div>
      <div class="stats-card-label">Total Resources</div>
      <div class="stats-card-hint">Open blogs</div>
    </a>

    <a href="./worksheet" class="stats-card stats-card--link" title="Worksheets: coloring pages, books, homework">
      <div class="stats-card-icon"><i class="fas fa-cloud-download-alt" aria-hidden="true"></i></div>
      <div class="stats-card-value"><?php echo number_format($totalDownloads); ?></div>
      <div class="stats-card-label">Total Downloads</div>
      <div class="stats-card-hint">Coloring pages</div>
    </a>

    <a href="./products" class="stats-card stats-card--link" title="Honey Market products">
      <div class="stats-card-icon"><i class="fas fa-box-open" aria-hidden="true"></i></div>
      <div class="stats-card-value"><?php echo number_format($totalProducts); ?></div>
      <div class="stats-card-label">Total Products</div>
      <div class="stats-card-hint">Shop inventory</div>
    </a>

    <a href="./manage-users" class="stats-card stats-card--link" title="Registered members">
      <div class="stats-card-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
      <div class="stats-card-value"><?php echo number_format($totalMembers); ?></div>
      <div class="stats-card-label">Total Members</div>
      <div class="stats-card-hint">User accounts</div>
    </a>

    <a href="./order" class="stats-card stats-card--link" title="Orders &amp; revenue detail">
      <div class="stats-card-icon"><i class="fas fa-coins" aria-hidden="true"></i></div>
      <div class="stats-card-value"><?php echo number_format($totalSales); ?></div>
      <div class="stats-card-label">Total Sales (LKR)</div>
      <div class="stats-card-hint">Orders</div>
    </a>

  </div>

  <div class="dashboard-kpi-row" aria-label="Order summary">
    <div class="dashboard-kpi">
      <i class="fas fa-shopping-bag" aria-hidden="true"></i>
      <div>
        <strong><?php echo number_format($orderCount); ?></strong>
        <span>All orders</span>
      </div>
    </div>
    <div class="dashboard-kpi">
      <i class="fas fa-hourglass-half" aria-hidden="true"></i>
      <div>
        <strong><?php echo number_format($ordersPending); ?></strong>
        <span>Pending payment</span>
      </div>
    </div>
    <div class="dashboard-kpi">
      <i class="fas fa-calendar-check" aria-hidden="true"></i>
      <div>
        <strong><?php echo number_format($ordersThisMonth); ?></strong>
        <span>Orders this month</span>
      </div>
    </div>
  </div>

  <h3 class="stats-title stats-title--sub" style="font-size:1.1rem;">Insights</h3>

  <div class="dashboard-charts-grid">
    <div class="chart-card">
      <h4 class="chart-card-title">Revenue — last 14 days</h4>
      <p class="chart-card-desc">Sum of order totals per day (LKR).</p>
      <div class="chart-canvas-wrap">
        <canvas id="ediChartRevenue14"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h4 class="chart-card-title">Published content mix</h4>
      <p class="chart-card-desc">Active blogs, PDFs, books &amp; homework items.</p>
      <div class="chart-canvas-wrap">
        <canvas id="ediChartResources"></canvas>
      </div>
    </div>
  </div>

  <div class="dashboard-charts-grid">
    <div class="chart-card">
      <h4 class="chart-card-title">Sales by payment method</h4>
      <p class="chart-card-desc">Share of lifetime revenue (orders with total &gt; 0).</p>
      <?php if (!$hasPaymentChart): ?>
      <p class="text-muted text-sm mb-0 py-4 text-center">No order revenue to chart yet.</p>
      <?php else: ?>
      <div class="chart-canvas-wrap chart-canvas-wrap--short">
        <canvas id="ediChartPayment"></canvas>
      </div>
      <?php endif; ?>
    </div>
    <div class="chart-card">
      <h4 class="chart-card-title">New members — last 14 days</h4>
      <p class="chart-card-desc">Registrations per day (account created).</p>
      <div class="chart-canvas-wrap">
        <canvas id="ediChartMembers14"></canvas>
      </div>
    </div>
  </div>

  </div><!-- .dashboard-metrics-shell -->

</div>

</main>

<?php echo $adminHeader->printAdminFooter(); ?>
<?php echo $adminHeader->printAdminFooterJS(); ?>

<script>
(function () {
  var C = typeof Chart !== 'undefined' ? Chart : null;
  if (!C) return;

  var payload = <?php echo json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

  var commonOpts = {
    maintainAspectRatio: false,
    responsive: true,
    legend: { display: true, position: 'bottom', labels: { boxWidth: 10, fontColor: '#64748b' } },
    tooltips: {
      backgroundColor: 'rgba(15,23,42,0.9)',
      titleFontColor: '#fff',
      bodyFontColor: '#e2e8f0',
      cornerRadius: 8
    }
  };

  // Line: revenue
  var revCtx = document.getElementById('ediChartRevenue14');
  if (revCtx && payload.revenueLabels && payload.revenueLabels.length) {
    new C(revCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: payload.revenueLabels,
        datasets: [{
          label: 'Revenue (LKR)',
          data: payload.revenueData,
          borderColor: '#f97316',
          backgroundColor: 'rgba(249,115,22,0.12)',
          borderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 5,
          fill: true,
          lineTension: 0.25
        }]
      },
      options: Object.assign({}, commonOpts, {
        legend: { display: false },
        scales: {
          xAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#64748b', maxRotation: 45, minRotation: 0 } }],
          yAxes: [{
            ticks: {
              beginAtZero: true,
              fontColor: '#64748b',
              callback: function (v) { return Number(v).toLocaleString(); }
            },
            gridLines: { color: 'rgba(148,163,184,0.2)' }
          }]
        },
        tooltips: {
          callbacks: {
            label: function (item, data) {
              return 'Rs ' + Number(item.yLabel).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
          }
        }
      })
    });
  }

  // Bar: resources
  var resCtx = document.getElementById('ediChartResources');
  if (resCtx && payload.resourceLabels && payload.resourceLabels.length) {
    new C(resCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: payload.resourceLabels,
        datasets: [{
          label: 'Items',
          data: payload.resourceData,
          backgroundColor: payload.resourceColors || ['#f97316', '#8b5cf6', '#33a675', '#0ea5e9'],
          borderWidth: 0
        }]
      },
      options: Object.assign({}, commonOpts, {
        legend: { display: false },
        scales: {
          xAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#64748b' } }],
          yAxes: [{
            ticks: { beginAtZero: true, stepSize: 1, fontColor: '#64748b' },
            gridLines: { color: 'rgba(148,163,184,0.2)' }
          }]
        }
      })
    });
  }

  // Doughnut: payment
  var payCtx = document.getElementById('ediChartPayment');
  if (payCtx && payload.hasPaymentChart && payload.paymentLabels && payload.paymentLabels.length) {
    new C(payCtx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: payload.paymentLabels,
        datasets: [{
          data: payload.paymentData,
          backgroundColor: payload.paymentColors && payload.paymentColors.length ? payload.paymentColors : ['#33a675', '#5b8def', '#f97316'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: Object.assign({}, commonOpts, {
        tooltips: {
          callbacks: {
            label: function (item, data) {
              var v = data.datasets[item.datasetIndex].data[item.index];
              return data.labels[item.index] + ': Rs ' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
          }
        }
      })
    });
  }

  // Line: members
  var memCtx = document.getElementById('ediChartMembers14');
  if (memCtx && payload.memberLabels && payload.memberLabels.length) {
    new C(memCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: payload.memberLabels,
        datasets: [{
          label: 'New signups',
          data: payload.memberData,
          borderColor: '#0ea5e9',
          backgroundColor: 'rgba(14,165,233,0.1)',
          borderWidth: 2,
          pointRadius: 3,
          fill: true,
          lineTension: 0.25
        }]
      },
      options: Object.assign({}, commonOpts, {
        legend: { display: false },
        scales: {
          xAxes: [{ gridLines: { display: false }, ticks: { fontColor: '#64748b', maxRotation: 45, minRotation: 0 } }],
          yAxes: [{
            ticks: { beginAtZero: true, stepSize: 1, fontColor: '#64748b' },
            gridLines: { color: 'rgba(148,163,184,0.2)' }
          }]
        }
      })
    });
  }
})();
</script>

</body>
</html>
