<?php
  session_start();
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  require_once("../classes/edi_order_line_items.php");

  $adminHeader = new HEADER("orders");
  $user = new USER();
  
  // --- Handle Status Updates ---
  if (isset($_POST['update_type'])) {
      $orderID = (int)$_POST['order_id'];
      if ($_POST['update_type'] == 'payment') {
          $newStatus = $_POST['status']; // 'paid', 'pending', etc.
          $user->updateTable("orders", array("payment_status" => $newStatus), array("id" => $orderID));
      } else if ($_POST['update_type'] == 'order') {
          $newStatus = $_POST['status']; // 'Order Placed', 'Completed', etc.
          $user->updateTable("orders", array("order_status" => $newStatus), array("id" => $orderID));
      }
      echo "<script>alert('Status updated successfully'); location.href='order.php';</script>";
      exit;
  }

  // Summary counts from orders table
  try {
    $pdo = $user->getConnection();
    $summaryStmt = $pdo->query("
      SELECT 
        COUNT(*) AS total_orders,
        SUM(CASE WHEN order_status = 'Completed' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN order_status = 'Order Placed' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN order_status = 'Failed' THEN 1 ELSE 0 END) AS failed,
        SUM(CASE WHEN order_status = 'Return' THEN 1 ELSE 0 END) AS returned
      FROM orders
    ");
    $summaryRow = $summaryStmt ? $summaryStmt->fetch(PDO::FETCH_ASSOC) : array(
      'total_orders' => 0,
      'completed'    => 0,
      'pending'      => 0,
      'failed'       => 0,
      'returned'     => 0
    );
    $totalOrders    = (int) $summaryRow['total_orders'];
    $totalCompleted = (int) $summaryRow['completed'];
    $totalPending   = (int) $summaryRow['pending'];
    $totalFailed    = (int) $summaryRow['failed'];
    $totalReturn    = (int) $summaryRow['returned'];
  } catch (Exception $e) {
    $totalOrders = $totalCompleted = $totalPending = $totalFailed = $totalReturn = 0;
  }

  // Search filter
  $search = isset($_GET['search']) ? trim($_GET['search']) : "";

  $query = "SELECT 
              id,
              order_number,
              first_name,
              last_name,
              email,
              mobile,
              address_line,
              city,
              postal_code,
              district,
              payment_method,
              payment_status,
              order_status,
              subtotal,
              shipping,
              total,
              created_at
            FROM orders";

  $params = array();
  if ($search !== "") {
    $query .= " WHERE order_number LIKE :search
                OR email LIKE :search
                OR mobile LIKE :search
                OR first_name LIKE :search
                OR last_name LIKE :search
                OR DATE(created_at) LIKE :search";
    $params[':search'] = "%" . $search . "%";
  }

  $query .= " ORDER BY id DESC";

  $itemsByOrder = array();
  try {
    $stmt = $user->runQuery($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdo = $user->getConnection();
    $chk = $pdo->query("SHOW TABLES LIKE " . $pdo->quote("order_items"));
    if ($chk && $chk->rowCount() > 0 && !empty($orders)) {
      $ids = array();
      foreach ($orders as $r) {
        if (!empty($r["id"])) {
          $ids[] = (int) $r["id"];
        }
      }
      $ids = array_values(array_unique($ids));
      if (!empty($ids)) {
        $inList = implode(",", $ids);
        $iq = $pdo->query("SELECT * FROM order_items WHERE order_id IN (" . $inList . ") ORDER BY order_id ASC, id ASC");
        if ($iq) {
          foreach ($iq->fetchAll(PDO::FETCH_ASSOC) as $li) {
            $oid = (int) $li["order_id"];
            if (!isset($itemsByOrder[$oid])) {
              $itemsByOrder[$oid] = array();
            }
            $itemsByOrder[$oid][] = $li;
          }
        }
      }
    }
    if (!empty($itemsByOrder)) {
      EdiOrderLineItems::enrichItemsByOrder($pdo, $itemsByOrder);
    }
  } catch (Exception $e) {
    $orders = array();
    $itemsByOrder = array();
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <style>
    .orders-title {
      font-size: 1.4rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #f97316;
      margin-bottom: 1.5rem;
    }

    .orders-summary-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
      gap: 1.2rem;
      margin-bottom: 2rem;
    }

    .orders-summary-card {
      background-color: #ffffff;
      border-radius: 18px;
      border: 1px solid #e5e7eb;
      padding: 28px 16px;
      text-align: center;
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
      min-height: 140px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .orders-summary-value {
      font-size: 1.9rem;
      font-weight: 700;
      color: #111827;
      margin-bottom: 4px;
    }

    .orders-summary-label {
      font-size: 0.8rem;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: #9ca3af;
    }

    .orders-filter-row {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.25rem;
      gap: 0.75rem;
    }

    .orders-search-label {
      font-size: 0.8rem;
      text-transform: uppercase;
      color: #9ca3af;
      margin-right: 0.5rem;
    }

    .orders-search-input {
      max-width: 260px;
    }

    .orders-export-select {
      max-width: 100px;
    }

    .orders-table thead th {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.14em;
      color: #9ca3af;
      border-bottom: 1px solid #e5e7eb;
    }

    .orders-table tbody td {
      font-size: 0.85rem;
      vertical-align: middle;
    }

    .orders-status-pill {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      background-color: #eff6ff;
      color: #1d4ed8;
    }

    @media (max-width: 576px) {
      .orders-summary-card {
        padding: 22px 12px;
        min-height: 120px;
      }
      .orders-summary-value {
        font-size: 1.6rem;
      }
    }
    
    .cursor-pointer { cursor: pointer; }
    .view-btn:hover { color: #f97316 !important; text-decoration: underline; }
    .modal-detail-label { font-weight: bold; color: #9ca3af; text-transform: uppercase; font-size: 0.75rem; margin-bottom: 2px; }
    .modal-detail-value { color: #111827; margin-bottom: 1rem; font-size: 0.9rem; }

    .edi-order-line-thumb {
      width: 56px;
      height: 56px;
      object-fit: cover;
      border-radius: 8px;
      flex-shrink: 0;
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
    }
    .edi-order-line-meta {
      font-size: 0.72rem;
      color: #64748b;
      line-height: 1.35;
      max-width: 320px;
    }
    .edi-order-line-name {
      font-weight: 600;
      color: #111827;
      font-size: 0.88rem;
    }

  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg ">
    <?php echo $adminHeader->printAdminNav2("Orders"); ?>

    <div class="container-fluid py-4">
      <div class="orders-title">Orders</div>

      <div class="orders-summary-grid">
        <div class="orders-summary-card">
          <div class="orders-summary-value"><?php echo number_format($totalOrders); ?></div>
          <div class="orders-summary-label">Total Orders</div>
        </div>

        <div class="orders-summary-card">
          <div class="orders-summary-value"><?php echo number_format($totalCompleted); ?></div>
          <div class="orders-summary-label">Completed</div>
        </div>

        <div class="orders-summary-card">
          <div class="orders-summary-value"><?php echo number_format($totalPending); ?></div>
          <div class="orders-summary-label">Pending</div>
        </div>

        <div class="orders-summary-card">
          <div class="orders-summary-value"><?php echo number_format($totalFailed); ?></div>
          <div class="orders-summary-label">Failed</div>
        </div>

        <div class="orders-summary-card">
          <div class="orders-summary-value"><?php echo number_format($totalReturn); ?></div>
          <div class="orders-summary-label">Return</div>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <form method="GET" action="order.php">
            <div class="orders-filter-row">
              <div class="edi-admin-search-inline align-items-center">
                <span class="orders-search-label mb-0">Search</span>
                <input type="text" name="search" class="form-control orders-search-input" placeholder="Order Number / Date / Name" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-success mb-0">Search</button>
              </div>
              <div class="d-flex align-items-center gap-2">
                <span class="orders-search-label me-2">Export</span>
                <select class="form-control orders-export-select" disabled>
                  <option value="20">20</option>
                </select>
              </div>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table orders-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Date</th>
                  <th>Customer</th>
                  <th>Value</th>
                  <th>Method</th>
                  <th>Payment</th>
                  <th>Status</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($orders)): ?>
                  <tr><td colspan="8" class="text-center py-4">No orders found.</td></tr>
                <?php else: ?>
                  <?php foreach ($orders as $row): ?>
                    <?php
                      $rowForModal = $row;
                      $oid = (int) ($row["id"] ?? 0);
                      $rowForModal["line_items"] = isset($itemsByOrder[$oid]) ? $itemsByOrder[$oid] : array();
                      $orderNumber = $row['order_number'];
                      $customerName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                      if ($customerName === '') $customerName = 'Guest';
                      $customer = $customerName . (!empty($row['email']) ? ' (' . $row['email'] . ')' : '');

                      $value = (float)($row['total'] ?? 0);
                      $createdAt = $row['created_at'] ?? '';
                      $paymentMethod = !empty($row['payment_method']) ? strtoupper($row['payment_method']) : '-';
                      $paymentStatus = !empty($row['payment_status']) ? ucfirst($row['payment_status']) : '-';
                      $orderStatus = !empty($row['order_status']) ? $row['order_status'] : 'Order Placed';
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo $createdAt ? date('M d, Y H:i', strtotime($createdAt)) : '-'; ?></td>
                      <td><?php echo htmlspecialchars($customer, ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>LKR <?php echo number_format($value, 2); ?></td>
                      <td><?php echo htmlspecialchars($paymentMethod, ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><span class="orders-status-pill"><?php echo htmlspecialchars($orderStatus, ENT_QUOTES, 'UTF-8'); ?></span></td>
                      <td class="text-end">
                        <span class="text-primary text-xs cursor-pointer font-weight-bold view-btn" role="button" tabindex="0" data-order-b64="<?php echo htmlspecialchars(base64_encode(json_encode($rowForModal, JSON_UNESCAPED_UNICODE)), ENT_QUOTES, "UTF-8"); ?>" onclick="showOrderDetailsFromEl(this)">View</span>
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

  <div class="modal fade" id="orderViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content border-radius-lg">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Order: <span id="modal_order_number" class="text-warning"></span></h5>
          <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <p class="modal-detail-label">Customer Name</p>
              <p id="modal_customer_name" class="modal-detail-value"></p>
              <p class="modal-detail-label">Contact Details</p>
              <p id="modal_contact" class="modal-detail-value"></p>
              <p class="modal-detail-label">Shipping Address</p>
              <p id="modal_address" class="modal-detail-value"></p>
            </div>
            <div class="col-md-6">
              <p class="modal-detail-label">Payment Method</p>
              <p id="modal_payment_method" class="modal-detail-value"></p>
              <p class="modal-detail-label">Order Total</p>
              <h4 id="modal_total" class="text-dark font-weight-bold"></h4>
              <p class="text-xs text-secondary mt-1">(Subtotal: <span id="modal_subtotal"></span> + Shipping: <span id="modal_shipping"></span>)</p>
            </div>
          </div>
          <p class="modal-detail-label mt-3">Ordered products</p>
          <div id="modal_line_items" class="modal-detail-value mb-0"></div>
          <hr class="horizontal dark mt-4 mb-4">
          <div class="row">
            <div class="col-md-6 border-end">
                <form method="POST" action="">
                    <input type="hidden" name="order_id" id="payment_order_id">
                    <input type="hidden" name="update_type" value="payment">
                    <p class="small font-weight-bold mb-2">Update Payment</p>
                    <button type="submit" name="status" value="paid" class="btn btn-success btn-sm w-100">Mark as Paid</button>
                </form>
            </div>
            <div class="col-md-6">
                <form method="POST" action="">
                    <input type="hidden" name="order_id" id="status_order_id">
                    <input type="hidden" name="update_type" value="order">
                    <p class="small font-weight-bold mb-2">Change Order Status</p>
                    <div class="btn-group w-100" role="group">
                        <button type="submit" name="status" value="Completed" class="btn btn-outline-primary btn-sm">Complete</button>
                        <button type="submit" name="status" value="Return" class="btn btn-outline-info btn-sm">Return</button>
                        <button type="submit" name="status" value="Failed" class="btn btn-outline-danger btn-sm">Failed</button>
                    </div>
                </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script>
    function ediEscapeHtml(text) {
        const d = document.createElement("div");
        d.textContent = text == null ? "" : String(text);
        return d.innerHTML;
    }

    function ediOrderLineMetaParts(it) {
        const parts = [];
        if (it.edi_product_language) parts.push(String(it.edi_product_language));
        if (it.edi_product_grade) parts.push(String(it.edi_product_grade));
        if (it.edi_product_category) parts.push(String(it.edi_product_category));
        if (it.edi_product_subcategory) parts.push(String(it.edi_product_subcategory));
        return parts;
    }

    function renderOrderLineItems(data) {
        const wrap = document.getElementById("modal_line_items");
        if (!wrap) return;
        const items = data.line_items || [];
        if (!items.length) {
            wrap.innerHTML = "<p class=\"text-muted small mb-0\">No line items are stored for this order. Run <code>sql/migration_order_items.sql</code> and new checkouts will list products here.</p>";
            return;
        }
        let html = "<table class=\"table table-sm table-bordered mb-0\"><thead><tr><th>Product</th><th class=\"text-center\">Qty</th><th class=\"text-right\">Unit</th><th class=\"text-right\">Line</th></tr></thead><tbody>";
        items.forEach(function (it) {
            const unit = parseFloat(it.unit_price);
            const line = parseFloat(it.line_total);
            const imgFn = (it.edi_product_image != null && String(it.edi_product_image).trim() !== "") ? String(it.edi_product_image).trim() : "";
            const imgSrc = imgFn ? ("../img/products/" + encodeURIComponent(imgFn)) : "";
            const metaParts = ediOrderLineMetaParts(it);
            const metaHtml = metaParts.length
                ? "<div class=\"edi-order-line-meta mt-1\">" + metaParts.map(function (p) { return ediEscapeHtml(p); }).join(" <span class=\"text-muted\">·</span> ") + "</div>"
                : "<div class=\"edi-order-line-meta mt-1 text-muted\">—</div>";
            const thumb = imgSrc
                ? "<img class=\"edi-order-line-thumb\" src=\"" + imgSrc + "\" alt=\"\" onerror=\"this.style.visibility='hidden';\">"
                : "<div class=\"edi-order-line-thumb d-flex align-items-center justify-content-center text-muted small\">—</div>";
            html += "<tr><td><div class=\"d-flex align-items-start\" style=\"gap:10px;\">" + thumb + "<div><div class=\"edi-order-line-name\">" + ediEscapeHtml(it.product_name) + "</div>" + metaHtml + "</div></div></td>";
            html += "<td class=\"text-center align-middle\">" + ediEscapeHtml(it.quantity) + "</td>";
            html += "<td class=\"text-right align-middle\">LKR " + unit.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + "</td>";
            html += "<td class=\"text-right align-middle\">LKR " + line.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + "</td></tr>";
        });
        html += "</tbody></table>";
        wrap.innerHTML = html;
    }

    function showOrderDetailsFromEl(el) {
        var raw = el.getAttribute("data-order-b64") || "";
        try {
            var json = atob(raw);
            showOrderDetails(JSON.parse(json));
        } catch (e) {
            alert("Could not load order details.");
        }
    }

    function showOrderDetails(data) {
        document.getElementById("modal_order_number").innerText = data.order_number;
        document.getElementById("modal_customer_name").innerText = data.first_name + " " + data.last_name;
        document.getElementById("modal_contact").innerText = data.email + " / " + data.mobile;
        document.getElementById("modal_address").innerText = data.address_line + ", " + data.city + ", " + data.district + " (" + data.postal_code + ")";
        document.getElementById("modal_payment_method").innerText = (data.payment_method || "").toUpperCase();
        document.getElementById("modal_total").innerText = "LKR " + parseFloat(data.total).toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById("modal_subtotal").innerText = "LKR " + parseFloat(data.subtotal).toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById("modal_shipping").innerText = "LKR " + parseFloat(data.shipping).toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById("payment_order_id").value = data.id;
        document.getElementById("status_order_id").value = data.id;
        renderOrderLineItems(data);
        var myModal = new bootstrap.Modal(document.getElementById("orderViewModal"));
        myModal.show();
    }
  </script>
</body>
</html>