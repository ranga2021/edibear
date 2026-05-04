<?php
  session_start();
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");

  $adminHeader = new HEADER("add-event");
  $user = new USER();

  // Search filter by title
  $search = isset($_GET['search']) ? trim($_GET['search']) : "";

  $query = "SELECT e.*, c.name AS category_name
            FROM braveheart_events e
            LEFT JOIN braveheart_categories c ON e.category_id = c.id";

  $params = array();
  if ($search !== "") {
    $query .= " WHERE e.title LIKE :search";
    $params[':search'] = "%" . $search . "%";
  }

  $query .= " ORDER BY e.created_at DESC";

  try {
    $stmt = $user->runQuery($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    $events = array();
  }

  $today = new DateTimeImmutable('today');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <style>
    .events-search-wrapper {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 1rem;
    }

    .events-search-input {
      max-width: 280px;
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

    .toggle-switch {
      position: relative;
      display: inline-block;
      width: 40px;
      height: 20px;
    }

    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .toggle-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #e5e7eb;
      transition: .3s;
      border-radius: 999px;
    }

    .toggle-slider:before {
      position: absolute;
      content: "";
      height: 14px;
      width: 14px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .3s;
      border-radius: 50%;
    }

    .toggle-switch input:checked + .toggle-slider {
      background-color: #22c55e;
    }

    .toggle-switch input:checked + .toggle-slider:before {
      transform: translateX(20px);
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg ">
    <?php echo $adminHeader->printAdminNav2("Brave Heart Events"); ?>

    <div class="container-fluid py-4">
      <div class="card">
        <div class="card-body">
          <form method="get" action="event.php">
            <div class="events-search-wrapper edi-admin-search-inline">
              <input
                type="text"
                name="search"
                class="form-control events-search-input"
                placeholder="Document Title"
                value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
              >
              <button type="submit" class="btn btn-success mb-0">Search</button>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Document Title</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($events)): ?>
                  <tr>
                    <td colspan="4" class="text-center py-4">No events found.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($events as $row): ?>
                    <?php
                      $deadlineStatus = '';
                      if (!empty($row['deadline_date'])) {
                        $deadline = new DateTimeImmutable($row['deadline_date']);
                        $deadlineStatus = ($deadline >= $today) ? 'Upcoming' : 'Completed';
                      }
                      $statusClass = ($deadlineStatus === 'Upcoming') ? 'status-pill-upcoming' : 'status-pill-completed';
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['category_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <?php if ($deadlineStatus): ?>
                          <span class="<?php echo $statusClass; ?>">
                            <?php echo $deadlineStatus; ?>
                          </span>
                        <?php else: ?>
                          -
                        <?php endif; ?>
                      </td>
                      <td>
                        <label class="toggle-switch me-3">
                          <input
                            type="checkbox"
                            <?php echo ((int)$row['status'] === 1) ? 'checked' : ''; ?>
                            onchange="toggleEventStatus(<?php echo (int)$row['id']; ?>, this.checked)"
                          >
                          <span class="toggle-slider"></span>
                        </label>
                        <a href="edit-event.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-link text-sm p-0">Edit</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>

  <?php echo $adminHeader->printAdminFooterJS(); ?>

  <script>
    function toggleEventStatus(id, isChecked) {
      var status = isChecked ? 1 : 0;
      fetch('update-event-status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id) + '&status=' + encodeURIComponent(status)
      }).then(function (res) {
        if (!res.ok) {
          alert('Failed to update status');
        }
      }).catch(function () {
        alert('Failed to update status');
      });
    }
  </script>
</body>
</html>

