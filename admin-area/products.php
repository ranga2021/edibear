<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  
  $adminHeader = new HEADER("products"); // Update your class.header.php to include this key
  $user = new USER();

  $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : '';

  
  

  // Fetch all products
  try {
      // 1. Fetch Categories for the dropdown
    $catStmt = $user->runQuery("SELECT * FROM product_categories ORDER BY name ASC");
    $catStmt->execute();
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Build the Product Query with Filters
    $query = "SELECT p.*, c.name as cat_name FROM products p 
              LEFT JOIN product_categories c ON p.category_id = c.id WHERE 1=1";
    $params = array();

    if ($category_filter != '') {
        $query .= " AND p.category_id = :cid";
        $params[':cid'] = $category_filter;
    }

    if ($status_filter != '') {
        // Assuming 1 = Active, 0 = Draft based on your status column
        $query .= " AND p.status = :status";
        $params[':status'] = ($status_filter == 'active') ? 1 : 0;
    }

    if ($stock_filter != '') {
        if ($stock_filter == 'in') {
            $query .= " AND p.stock > 0";
        } else {
            $query .= " AND p.stock = 0";
        }
    }

    $query .= " ORDER BY p.id DESC";
    $stmt = $user->runQuery($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $params = array();
  } catch (PDOException $e) {
      $products = [];
  }
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
</script><script>
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
  <style>
    /* Status Toggle Switch Style */
    .switch { position: relative; display: inline-block; width: 45px; height: 22px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #2dce89; }
    input:checked + .slider:before { transform: translateX(23px); }
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  
  <main class="main-content position-relative border-radius-lg ">
    <?php echo $adminHeader->printAdminNav2("Products List"); ?>
    
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="card card-body">
            <h6>Filters</h6>
            <form method="GET" action="products.php" id="filterForm">
            <div class="row">
              <div class="col-md-3">
                <select name="category" class="form-control" onchange="this.form.submit()">
              <option value="">All Categories</option>
              <?php foreach($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
              </div>
              <div class="col-md-4">
                <select name="status" class="form-control" onchange="this.form.submit()">
              <option value="">All Statuses</option>
              <option value="active" <?php echo ($status_filter == 'active') ? 'selected' : ''; ?>>Active</option>
              <option value="draft" <?php echo ($status_filter == 'draft') ? 'selected' : ''; ?>>Draft</option>
            </select>
              </div>
              <div class="col-md-4">
                <select name="stock" class="form-control" onchange="this.form.submit()">
              <option value="">All Stock</option>
              <option value="in" <?php echo ($stock_filter == 'in') ? 'selected' : ''; ?>>In ( > 0 )</option>
              <option value="out" <?php echo ($stock_filter == 'out') ? 'selected' : ''; ?>>Out ( = 0 )</option>
            </select>
              </div>
              
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product Name</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Brand</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Age</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Stock</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                      <th class="text-secondary opacity-7">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($products as $row): ?>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div><img src="../img/products/<?php echo $row['image']; ?>" class="avatar avatar-sm me-3"></div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($row['product_name']); ?></h6>
                          </div>
                        </div>
                      </td>
                      <td><p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($row['cat_name']); ?></p></td>
                      <td><p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($row['brand']); ?></p></td>
                      <td><p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($row['age_group']); ?></p></td>
                      <td><p class="text-xs font-weight-bold mb-0">LKR <?php echo number_format($row['price'], 2); ?></p></td>
                      <td><p class="text-xs font-weight-bold mb-0"><?php echo $row['stock']; ?></p></td>
                      <td class="align-middle text-center">
                        <label class="switch">
                          <input type="checkbox" <?php echo ($row['status'] == 1) ? 'checked' : ''; ?> 
                                 onchange="toggleStatus(<?php echo $row['id']; ?>, this.checked)">
                          <span class="slider"></span>
                        </label>
                      </td>
                      <td class="align-middle">
                        <a href="edit-product.php?id=<?php echo $row['id']; ?>" class="text-secondary font-weight-bold text-xs">Edit</a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>

  <script>
    function toggleStatus(id, isChecked) {
        const status = isChecked ? 1 : 0;
        fetch('update-status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&status=${status}`
        }).then(res => {
            if(!res.ok) alert("Status update failed");
        });
    }
  </script>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>