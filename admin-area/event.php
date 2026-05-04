<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("event");
$user = new USER();

if (!$user->is_loggedin()) {
    $user->redirect("./index.php");
}

$search = isset($_GET["search"]) ? trim((string) $_GET["search"]) : "";
$page = isset($_GET["page"]) ? max(1, (int) $_GET["page"]) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$whereSql = "";
$params = array();
if ($search !== "") {
    $whereSql = " WHERE e.title LIKE :search";
    $params[":search"] = "%" . $search . "%";
}

$countSql = "SELECT COUNT(*) AS c FROM braveheart_events e" . $whereSql;
$baseFrom = " FROM braveheart_events e
            LEFT JOIN braveheart_categories c ON e.category_id = c.id";

$totalRows = 0;
try {
    $cst = $user->runQuery($countSql);
    $cst->execute($params);
    $totalRows = (int) $cst->fetchColumn();
} catch (Exception $e) {
    $totalRows = 0;
}

$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$query = "SELECT e.*, c.name AS category_name" . $baseFrom . $whereSql . " ORDER BY e.created_at DESC LIMIT " . (int) $perPage . " OFFSET " . (int) $offset;

try {
    $stmt = $user->runQuery($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $events = array();
}

$today = new DateTimeImmutable("today");
$showFrom = $totalRows === 0 ? 0 : $offset + 1;
$showTo = min($offset + count($events), $totalRows);
?>
<script>
    const adminSession = localStorage.getItem("admin_session");
    const sessionTime = localStorage.getItem("session_time");
    const currentTime = Math.floor(Date.now() / 1000);
    if (!adminSession || (currentTime - sessionTime > 1200)) {
        localStorage.removeItem("admin_session");
        window.location.href = "index.php?error=session_expired";
    }
</script>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <style>
    .edi-bh-events-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.25rem;
    }
    .edi-bh-events-title {
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      margin: 0;
    }
    .edi-bh-events-search-form.edi-admin-search-inline .form-control {
      max-width: 24rem;
    }
    .edi-bh-action-cell {
      white-space: nowrap;
    }
    .edi-bh-action-cell .form-check.form-switch {
      display: inline-flex;
      vertical-align: middle;
      margin-bottom: 0;
      margin-right: 0.75rem;
    }
    .edi-bh-edit-link {
      font-size: 0.875rem;
      font-weight: 600;
    }
    .status-pill-upcoming {
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      background-color: #ecfdf5;
      color: #16a34a;
    }
    .status-pill-completed {
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      background-color: #eff6ff;
      color: #1d4ed8;
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>

    <div class="container-fluid py-4">
      <div class="card">
        <div class="card-body p-4">
          <div class="edi-bh-events-toolbar">
            <h2 class="edi-bh-events-title text-uppercase text-danger">Brave Heart challenges</h2>
            <div class="d-flex flex-wrap align-items-end gap-2">
              <form class="edi-bh-events-search-form edi-admin-search-inline mb-0" method="get" action="event.php">
                <input type="hidden" name="page" value="1">
                <input
                  type="search"
                  name="search"
                  class="form-control"
                  placeholder="Document Title"
                  value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>"
                  autocomplete="off"
                >
                <button type="submit" class="btn btn-success mb-0">Search</button>
              </form>
              <a href="add-event.php" class="btn btn-success mb-0">Add new</a>
            </div>
          </div>

          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Category</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Document Title</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($events)): ?>
                  <tr>
                    <td colspan="4" class="text-center text-secondary text-sm py-4">No challenges found.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($events as $row): ?>
                    <?php
                      $deadlineStatus = "";
                      if (!empty($row["deadline_date"])) {
                          $deadline = new DateTimeImmutable($row["deadline_date"]);
                          $deadlineStatus = ($deadline >= $today) ? "Upcoming" : "Completed";
                      }
                      $statusClass = ($deadlineStatus === "Upcoming") ? "status-pill-upcoming" : "status-pill-completed";
                    ?>
                    <tr>
                      <td class="align-middle">
                        <span class="text-secondary text-sm"><?php echo htmlspecialchars($row["category_name"] ?? "—", ENT_QUOTES, "UTF-8"); ?></span>
                      </td>
                      <td class="align-middle">
                        <span class="text-secondary text-sm font-weight-bold"><?php echo htmlspecialchars($row["title"], ENT_QUOTES, "UTF-8"); ?></span>
                      </td>
                      <td class="align-middle">
                        <?php if ($deadlineStatus !== ""): ?>
                          <span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($deadlineStatus, ENT_QUOTES, "UTF-8"); ?></span>
                        <?php else: ?>
                          <span class="text-secondary text-sm">—</span>
                        <?php endif; ?>
                      </td>
                      <td class="align-middle text-center edi-bh-action-cell">
                        <div class="form-check form-switch justify-content-center d-inline-flex">
                          <input
                            class="form-check-input"
                            type="checkbox"
                            <?php echo ((int) $row["status"] === 1) ? "checked" : ""; ?>
                            onchange="toggleEventStatus(<?php echo (int) $row["id"]; ?>, this.checked)"
                            aria-label="Published"
                          >
                        </div>
                        <a href="edit-event.php?id=<?php echo (int) $row["id"]; ?>" class="edi-bh-edit-link text-success">Edit</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if ($totalRows > 0): ?>
            <p class="text-center text-secondary text-sm mt-3 mb-0">
              Showing <?php echo (int) $showFrom; ?> to <?php echo (int) $showTo; ?> of <?php echo (int) $totalRows; ?> challenges
            </p>
            <?php if ($totalPages > 1): ?>
              <nav class="d-flex justify-content-center mt-2" aria-label="Pagination">
                <ul class="pagination pagination-sm mb-0">
                  <?php
                  $qs = $search !== "" ? ("search=" . rawurlencode($search) . "&") : "";
                  for ($p = 1; $p <= $totalPages; $p++) {
                      $active = ($p === $page) ? " active" : "";
                      echo '<li class="page-item' . $active . '"><a class="page-link" href="event.php?' . $qs . "page=" . $p . '">' . $p . "</a></li>";
                  }
                  ?>
                </ul>
              </nav>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>

  <?php echo $adminHeader->printAdminFooterJS(); ?>

  <script>
    function toggleEventStatus(id, isChecked) {
      var status = isChecked ? 1 : 0;
      fetch("update-event-status.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + encodeURIComponent(id) + "&status=" + encodeURIComponent(status)
      }).then(function (res) {
        if (!res.ok) {
          alert("Failed to update status");
        }
      }).catch(function () {
        alert("Failed to update status");
      });
    }
  </script>
</body>
</html>
