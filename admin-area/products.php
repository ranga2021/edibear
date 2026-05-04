<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");

  $adminHeader = new HEADER("products");
  $user = new USER();

  $search = isset($_GET["search"]) ? trim((string) $_GET["search"]) : "";
  $category_filter = isset($_GET["category"]) ? trim((string) $_GET["category"]) : "";
  $status_filter = isset($_GET["status"]) ? trim((string) $_GET["status"]) : "";
  $stock_filter = isset($_GET["stock"]) ? trim((string) $_GET["stock"]) : "";

  $categories = array();
  $products = array();

  try {
      $catStmt = $user->runQuery("SELECT * FROM product_categories ORDER BY name ASC");
      $catStmt->execute();
      $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

      $query = "SELECT p.*, c.name AS cat_name FROM products p
                LEFT JOIN product_categories c ON p.category_id = c.id WHERE 1=1";
      $params = array();

      if ($search !== "") {
          $query .= " AND p.product_name LIKE :q";
          $params[":q"] = "%" . $search . "%";
      }

      if ($category_filter !== "") {
          $query .= " AND p.category_id = :cid";
          $params[":cid"] = (int) $category_filter;
      }

      if ($status_filter !== "") {
          $query .= " AND p.status = :status";
          $params[":status"] = ($status_filter === "active") ? 1 : 0;
      }

      if ($stock_filter !== "") {
          if ($stock_filter === "in") {
              $query .= " AND p.stock > 0";
          } else {
              $query .= " AND p.stock = 0";
          }
      }

      $query .= " ORDER BY p.id DESC";
      $stmt = $user->runQuery($query);
      $stmt->execute($params);
      $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      $categories = array();
      $products = array();
  }
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
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg ">
    <?php echo $adminHeader->printAdminNav2("Products"); ?>

    <div class="container-fluid py-4">
      <form method="get" action="products.php" class="mb-0">
        <div class="edi-products-toolbar">
          <h1 class="edi-products-title text-uppercase">Products</h1>
          <div class="edi-products-search edi-admin-search-inline">
            <input type="search" name="search" class="form-control" placeholder="Product Name" value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>">
            <button type="submit" class="btn btn-success mb-0">Search</button>
          </div>
        </div>

        <div class="edi-products-filters">
          <div class="edi-products-filters-heading">Filters</div>
          <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
              <label for="edi-filter-category">Category</label>
              <select name="category" id="edi-filter-category" class="form-control" onchange="this.form.submit()">
                <option value="">All categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?php echo (int) $cat["id"]; ?>" <?php echo ((string) $category_filter === (string) $cat["id"]) ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($cat["name"], ENT_QUOTES, "UTF-8"); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
              <label for="edi-filter-status">Status (active / draft)</label>
              <select name="status" id="edi-filter-status" class="form-control" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="active" <?php echo ($status_filter === "active") ? "selected" : ""; ?>>Active</option>
                <option value="draft" <?php echo ($status_filter === "draft") ? "selected" : ""; ?>>Draft</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="edi-filter-stock">Stock (in stock / out of stock)</label>
              <select name="stock" id="edi-filter-stock" class="form-control" onchange="this.form.submit()">
                <option value="">All stock</option>
                <option value="in" <?php echo ($stock_filter === "in") ? "selected" : ""; ?>>In stock</option>
                <option value="out" <?php echo ($stock_filter === "out") ? "selected" : ""; ?>>Out of stock</option>
              </select>
            </div>
          </div>
        </div>
      </form>

      <div class="edi-products-table-wrap">
        <div class="table-responsive">
          <table class="table align-items-center">
            <thead>
              <tr>
                <th class="ps-4">Product name</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Age</th>
                <th>Price</th>
                <th>Available</th>
                <th>Stock</th>
                <th class="text-center">Status</th>
                <th class="pe-4">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $row): ?>
                <?php
                $stockQty = (int) ($row["stock"] ?? 0);
                $stockLabel = $stockQty > 0 ? "In stock" : "Out of stock";
                ?>
                <tr>
                  <td class="ps-4">
                    <span class="edi-product-name-cell"><?php echo htmlspecialchars((string) ($row["product_name"] ?? ""), ENT_QUOTES, "UTF-8"); ?></span>
                  </td>
                  <td><?php echo htmlspecialchars((string) ($row["cat_name"] ?? "—"), ENT_QUOTES, "UTF-8"); ?></td>
                  <td><?php echo htmlspecialchars((string) ($row["brand"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
                  <td><?php echo htmlspecialchars((string) ($row["age_group"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
                  <td><?php echo htmlspecialchars(number_format((float) ($row["price"] ?? 0), 2, ".", ""), ENT_QUOTES, "UTF-8"); ?></td>
                  <td><?php echo (int) $stockQty; ?></td>
                  <td><?php echo htmlspecialchars($stockLabel, ENT_QUOTES, "UTF-8"); ?></td>
                  <td class="text-center">
                    <label class="edi-product-status-switch mb-0">
                      <input type="checkbox" <?php echo ((int) ($row["status"] ?? 0) === 1) ? "checked" : ""; ?>
                        onchange="toggleStatus(<?php echo (int) $row["id"]; ?>, this.checked)">
                      <span class="edi-slider"></span>
                    </label>
                  </td>
                  <td class="pe-4">
                    <a href="edit-product.php?id=<?php echo (int) $row["id"]; ?>" class="text-secondary font-weight-bold" style="font-size:0.8125rem;">Edit</a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (count($products) === 0): ?>
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">No products match your filters.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>

  <script>
    function toggleStatus(id, isChecked) {
        const status = isChecked ? 1 : 0;
        fetch("update-status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + encodeURIComponent(id) + "&status=" + encodeURIComponent(status)
        }).then(function (res) {
            if (!res.ok) {
                alert("Status update failed");
            }
        });
    }
  </script>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>
