<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("manage-product-categories");
$user = new USER();

$deleteModalHtml = "";
$editMode = false;
$editId = 0;
$formName = "";
$formDesc = "";
$formStatus = 1;

try {
    $st = $user->runQuery("SELECT * FROM product_categories ORDER BY name ASC");
    $st->execute();
    $categories = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

if (isset($_GET['id']) && ctype_digit((string) $_GET['id'])) {
    $editId = (int) $_GET['id'];
    $row = $user->fetchAll(
        ["id", "name", "description", "status"],
        ["product_categories"],
        ["id" => $editId]
    );
    if (!empty($row)) {
        $editMode = true;
        $formName = $row[0]["name"];
        $formDesc = (string) ($row[0]["description"] ?? "");
        $formStatus = (int) $row[0]["status"] === 1 ? 1 : 0;
    } else {
        $user->redirect("./manage-product-categories");
    }
}

if (isset($_POST["addCategorySubmit"])) {
    $name = trim((string) ($_POST["name"] ?? ""));
    $desc = trim((string) ($_POST["description"] ?? ""));
    $status = isset($_POST["status_active"]) ? 1 : 0;
    if ($name === "") {
        echo "<script>alert('Category name is required.');location.href='./manage-product-categories'</script>";
        exit;
    }
    $user->insertTable(
        "product_categories",
        [
            "name" => htmlspecialchars($name, ENT_QUOTES, "UTF-8"),
            "description" => $desc !== "" ? htmlspecialchars($desc, ENT_QUOTES, "UTF-8") : "",
            "status" => $status,
        ],
        false
    );
    echo "<script>alert('Category added.');location.href='./manage-product-categories'</script>";
    exit;
}

if (isset($_POST["updateCategorySubmit"])) {
    $id = (int) ($_POST["category_id"] ?? 0);
    $name = trim((string) ($_POST["name"] ?? ""));
    $desc = trim((string) ($_POST["description"] ?? ""));
    $status = isset($_POST["status_active"]) ? 1 : 0;
    if ($id < 1 || $name === "") {
        echo "<script>alert('Invalid data.');location.href='./manage-product-categories'</script>";
        exit;
    }
    if (!$user->CountRows("product_categories", ["id" => $id])) {
        $user->redirect("./manage-product-categories");
    }
    $user->updateTable(
        "product_categories",
        [
            "name" => htmlspecialchars($name, ENT_QUOTES, "UTF-8"),
            "description" => $desc !== "" ? htmlspecialchars($desc, ENT_QUOTES, "UTF-8") : "",
            "status" => $status,
        ],
        ["id" => $id]
    );
    echo "<script>alert('Category updated.');location.href='./manage-product-categories'</script>";
    exit;
}

if (isset($_POST["deleteCategorySubmit"])) {
    $id = (int) ($_POST["category_id"] ?? 0);
    $nm = htmlspecialchars((string) ($_POST["category_name"] ?? ""), ENT_QUOTES, "UTF-8");
    if ($id > 0) {
        $deleteModalHtml = $user->confirmDeleteModal(
            $id,
            $nm,
            "-",
            "Delete product category",
            "manage-product-categories"
        );
    }
}

if (isset($_POST["confirmDeleteSubmit"])) {
    $id = (int) ($_POST["deleteNameID"] ?? 0);
    if ($id > 0) {
        $n = (int) $user->CountRows("products", ["category_id" => $id]);
        if ($n > 0) {
            echo "<script>alert('Cannot delete: " . $n . " product(s) are assigned to this category. Reassign or remove those products first.');location.href='./manage-product-categories'</script>";
            exit;
        }
        $user->deleteTableRow("product_categories", ["id" => $id]);
    }
    echo "<script>alert('Category deleted.');location.href='./manage-product-categories'</script>";
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
    <?php echo $adminHeader->printAdminNav2("Product categories"); ?>

    <div class="container-fluid py-4">
      <p class="text-sm text-muted mb-3">
        These names appear in the <strong>Add Product</strong> category dropdown and on the shop filters.
        <a href="./manage-product-subcategories" class="ms-2">Manage subcategories →</a>
      </p>

      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6 class="mb-0">All categories</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-3">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                  <th class="text-secondary opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 0;
                foreach ($categories as $c) {
                    $i++;
                    $stLabel = ((int) $c["status"] === 1) ? "Active" : "Hidden";
                    $stClass = ((int) $c["status"] === 1) ? "text-success" : "text-secondary";
                    echo "<tr>
                      <td class='align-middle text-sm'>" . (int) $i . "</td>
                      <td class='align-middle text-sm font-weight-bold'>" . htmlspecialchars($c["name"]) . "</td>
                      <td class='align-middle text-sm $stClass'>" . htmlspecialchars($stLabel) . "</td>
                      <td class='align-middle'>
                        <a href='manage-product-categories?id=" . (int) $c["id"] . "' class='btn btn-sm btn-primary'>Edit</a>
                      </td>
                    </tr>";
                }
                if ($i === 0) {
                    echo "<tr><td colspan='4' class='text-sm text-muted px-3 py-4'>No categories yet. Add one using the form below.</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6><?php echo $editMode ? "Edit category" : "Add category"; ?></h6>
        </div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="category_id" value="<?php echo (int) $editId; ?>">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required maxlength="100"
                       value="<?php echo htmlspecialchars($formName); ?>">
              </div>
              <div class="col-md-6 mb-3 d-flex align-items-end">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="status_active" id="status_active"
                    <?php echo $formStatus === 1 ? "checked" : ""; ?>>
                  <label class="form-check-label" for="status_active">Active (visible in shop filters)</label>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3" maxlength="2000"><?php echo htmlspecialchars($formDesc); ?></textarea>
            </div>
            <div class="edi-admin-form-actions">
              <?php
              if ($editMode) {
                  echo "<button type='submit' name='updateCategorySubmit' class='btn btn-primary'>Update</button>
                        <button type='submit' name='deleteCategorySubmit' class='btn btn-danger'>Delete</button>
                        <a href='./manage-product-categories' class='btn btn-outline-secondary'>Cancel</a>";
              } else {
                  echo "<button type='submit' name='addCategorySubmit' class='btn btn-success'>Add category</button>";
              }
              ?>
            </div>
            <?php if ($editMode) { ?>
            <input type="hidden" name="category_name" value="<?php echo htmlspecialchars($formName); ?>">
            <?php } ?>
          </form>
        </div>
      </div>

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
