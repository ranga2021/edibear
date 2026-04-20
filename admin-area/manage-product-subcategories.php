<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("manage-product-subcategories");
$user = new USER();

$deleteModalHtml = "";
$editMode = false;
$editId = 0;
$formProductCatId = 0;
$formTitle = "";
$formDesc = "";
$tableMissing = false;

try {
    $pst = $user->runQuery("SELECT id, name FROM product_categories WHERE status = 1 ORDER BY name ASC");
    $pst->execute();
    $productCategories = $pst->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $productCategories = [];
}

try {
    $st = $user->runQuery(
        "SELECT ps.*, pc.name AS category_name
         FROM product_subcategories ps
         INNER JOIN product_categories pc ON pc.id = ps.product_category_id
         ORDER BY pc.name ASC, ps.title ASC"
    );
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rows = [];
    $em = $e->getMessage();
    if (stripos($em, "product_subcategories") !== false
        && (stripos($em, "doesn't exist") !== false || stripos($em, "Unknown table") !== false)) {
        $tableMissing = true;
    }
}

if (isset($_GET["id"]) && ctype_digit((string) $_GET["id"]) && !$tableMissing) {
    $editId = (int) $_GET["id"];
    $found = $user->fetchAll(
        ["id", "product_category_id", "title", "description"],
        ["product_subcategories"],
        ["id" => $editId]
    );
    if (!empty($found)) {
        $editMode = true;
        $formProductCatId = (int) ($found[0]["product_category_id"] ?? 0);
        $formTitle = (string) ($found[0]["title"] ?? "");
        $formDesc = (string) ($found[0]["description"] ?? "");
    } else {
        $user->redirect("./manage-product-subcategories");
    }
}

if (isset($_POST["addSubSubmit"]) && !$tableMissing) {
    $pcid = (int) ($_POST["product_category_id"] ?? 0);
    $title = trim((string) ($_POST["title"] ?? ""));
    $desc = trim((string) ($_POST["description"] ?? ""));
    if ($pcid < 1 || $title === "") {
        echo "<script>alert('Product category and title are required.');location.href='./manage-product-subcategories'</script>";
        exit;
    }
    if (!$user->CountRows("product_categories", ["id" => $pcid])) {
        echo "<script>alert('Invalid product category.');location.href='./manage-product-subcategories'</script>";
        exit;
    }
    $user->insertTable(
        "product_subcategories",
        [
            "product_category_id" => $pcid,
            "title" => htmlspecialchars($title, ENT_QUOTES, "UTF-8"),
            "description" => $desc !== "" ? htmlspecialchars($desc, ENT_QUOTES, "UTF-8") : "",
        ],
        false
    );
    echo "<script>alert('Subcategory added.');location.href='./manage-product-subcategories'</script>";
    exit;
}

if (isset($_POST["updateSubSubmit"]) && !$tableMissing) {
    $id = (int) ($_POST["sub_id"] ?? 0);
    $pcid = (int) ($_POST["product_category_id"] ?? 0);
    $title = trim((string) ($_POST["title"] ?? ""));
    $desc = trim((string) ($_POST["description"] ?? ""));
    if ($id < 1 || $pcid < 1 || $title === "") {
        echo "<script>alert('Invalid data.');location.href='./manage-product-subcategories'</script>";
        exit;
    }
    if (!$user->CountRows("product_subcategories", ["id" => $id]) || !$user->CountRows("product_categories", ["id" => $pcid])) {
        $user->redirect("./manage-product-subcategories");
    }
    $user->updateTable(
        "product_subcategories",
        [
            "product_category_id" => $pcid,
            "title" => htmlspecialchars($title, ENT_QUOTES, "UTF-8"),
            "description" => $desc !== "" ? htmlspecialchars($desc, ENT_QUOTES, "UTF-8") : "",
        ],
        ["id" => $id]
    );
    echo "<script>alert('Subcategory updated.');location.href='./manage-product-subcategories'</script>";
    exit;
}

if (isset($_POST["deleteSubSubmit"]) && !$tableMissing) {
    $id = (int) ($_POST["sub_id"] ?? 0);
    $ttl = htmlspecialchars((string) ($_POST["sub_title"] ?? ""), ENT_QUOTES, "UTF-8");
    if ($id > 0) {
        $deleteModalHtml = $user->confirmDeleteModal(
            $id,
            $ttl,
            "-",
            "Delete product subcategory",
            "manage-product-subcategories"
        );
    }
}

if (isset($_POST["confirmDeleteSubmit"]) && !$tableMissing) {
    $id = (int) ($_POST["deleteNameID"] ?? 0);
    if ($id > 0) {
        $user->deleteTableRow("product_subcategories", ["id" => $id]);
    }
    echo "<script>alert('Subcategory deleted.');location.href='./manage-product-subcategories'</script>";
    exit;
}
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
</head>
<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2("Product subcategories"); ?>

    <div class="container-fluid py-4">
      <p class="text-sm text-muted mb-3">
        Subcategories are tied to <strong>product categories</strong> and appear in <strong>Add Product</strong> after you pick a category.
        <a href="./manage-product-categories" class="ms-2">← Product categories</a>
      </p>

      <?php if ($tableMissing) { ?>
        <div class="alert alert-danger">
          The <code>product_subcategories</code> table is missing. Run
          <code>sql/add_product_subcategories.sql</code> on your database, then reload this page.
        </div>
      <?php } elseif (empty($productCategories)) { ?>
        <div class="alert alert-warning">Add at least one <a href="./manage-product-categories">product category</a> before creating subcategories.</div>
      <?php } ?>

      <?php if (!$tableMissing) { ?>
      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6 class="mb-0">All product subcategories</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-3">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product category</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                  <th class="text-secondary opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 0;
                foreach ($rows as $r) {
                    $i++;
                    echo "<tr>
                      <td class='align-middle text-sm'>" . (int) $i . "</td>
                      <td class='align-middle text-sm'>" . htmlspecialchars((string) ($r["category_name"] ?? "")) . "</td>
                      <td class='align-middle text-sm font-weight-bold'>" . htmlspecialchars((string) ($r["title"] ?? "")) . "</td>
                      <td class='align-middle'>
                        <a href='manage-product-subcategories?id=" . (int) $r["id"] . "' class='btn btn-sm btn-primary'>Edit</a>
                      </td>
                    </tr>";
                }
                if ($i === 0) {
                    echo "<tr><td colspan='4' class='text-sm text-muted px-3 py-4'>No subcategories yet.</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6><?php echo $editMode ? "Edit subcategory" : "Add subcategory"; ?></h6>
        </div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="sub_id" value="<?php echo (int) $editId; ?>">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Product category <span class="text-danger">*</span></label>
                <select name="product_category_id" class="form-control" required <?php echo empty($productCategories) ? "disabled" : ""; ?>>
                  <option value="">Select category</option>
                  <?php foreach ($productCategories as $m) { ?>
                    <option value="<?php echo (int) $m["id"]; ?>"
                      <?php echo ((int) $m["id"] === $formProductCatId) ? "selected" : ""; ?>>
                      <?php echo htmlspecialchars($m["name"] ?? ""); ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required maxlength="100"
                       value="<?php echo htmlspecialchars($formTitle); ?>">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <input type="text" name="description" class="form-control" maxlength="200"
                     value="<?php echo htmlspecialchars($formDesc); ?>">
            </div>
            <div class="d-flex flex-wrap">
              <?php
              if ($editMode) {
                  echo "<button type='submit' name='updateSubSubmit' class='btn btn-primary me-2 mb-2'>Update</button>
                        <button type='submit' name='deleteSubSubmit' class='btn btn-danger me-2 mb-2'>Delete</button>
                        <a href='./manage-product-subcategories' class='btn btn-outline-secondary mb-2'>Cancel</a>";
              } else {
                  echo "<button type='submit' name='addSubSubmit' class='btn btn-success'" . (empty($productCategories) ? " disabled" : "") . ">Add subcategory</button>";
              }
              ?>
            </div>
            <?php if ($editMode) { ?>
            <input type="hidden" name="sub_title" value="<?php echo htmlspecialchars($formTitle); ?>">
            <?php } ?>
          </form>
        </div>
      </div>
      <?php } ?>

      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>

  <?php
  if ($deleteModalHtml !== "") {
      echo $deleteModalHtml;
  }
  ?>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>
