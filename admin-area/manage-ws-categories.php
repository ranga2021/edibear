<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/edi_ws_taxonomy.php");

$adminHeader = new HEADER("manage-ws-categories");
$user = new USER();
$conn = $user->getConnection();

$deleteModalHtml = "";
$editMode = false;
$editId = 0;
$formName = "";
$formSort = 0;
$tableMissing = !EdiWsTaxonomy::tableExists($conn, "ws_categories");

$categories = array();
if (!$tableMissing) {
    try {
        $st = $user->runQuery("SELECT * FROM ws_categories ORDER BY sort_order ASC, name ASC");
        $st->execute();
        $categories = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $categories = array();
    }
}

if (isset($_GET["id"]) && ctype_digit((string) $_GET["id"]) && !$tableMissing) {
    $editId = (int) $_GET["id"];
    $row = $user->fetchAll(
        array("id", "name", "sort_order"),
        array("ws_categories"),
        array("id" => $editId)
    );
    if (!empty($row)) {
        $editMode = true;
        $formName = (string) $row[0]["name"];
        $formSort = (int) ($row[0]["sort_order"] ?? 0);
    } else {
        $user->redirect("./manage-ws-categories");
    }
}

if (isset($_POST["addWsCatSubmit"]) && !$tableMissing) {
    $name = trim((string) ($_POST["name"] ?? ""));
    $sort = (int) ($_POST["sort_order"] ?? 0);
    if ($name === "") {
        echo "<script>alert('Name is required.');location.href='./manage-ws-categories'</script>";
        exit;
    }
    $user->insertTable(
        "ws_categories",
        array(
            "name" => htmlspecialchars($name, ENT_QUOTES, "UTF-8"),
            "sort_order" => $sort,
        ),
        false
    );
    echo "<script>alert('Worksheet category added.');location.href='./manage-ws-categories'</script>";
    exit;
}

if (isset($_POST["updateWsCatSubmit"]) && !$tableMissing) {
    $id = (int) ($_POST["category_id"] ?? 0);
    $name = trim((string) ($_POST["name"] ?? ""));
    $sort = (int) ($_POST["sort_order"] ?? 0);
    if ($id < 1 || $name === "") {
        echo "<script>alert('Invalid data.');location.href='./manage-ws-categories'</script>";
        exit;
    }
    if (!$user->CountRows("ws_categories", array("id" => $id))) {
        $user->redirect("./manage-ws-categories");
    }
    $user->updateTable(
        "ws_categories",
        array(
            "name" => htmlspecialchars($name, ENT_QUOTES, "UTF-8"),
            "sort_order" => $sort,
        ),
        array("id" => $id)
    );
    echo "<script>alert('Updated.');location.href='./manage-ws-categories'</script>";
    exit;
}

if (isset($_POST["deleteWsCatSubmit"]) && !$tableMissing) {
    $id = (int) ($_POST["category_id"] ?? 0);
    $nm = htmlspecialchars((string) ($_POST["category_name"] ?? ""), ENT_QUOTES, "UTF-8");
    if ($id > 0) {
        $deleteModalHtml = $user->confirmDeleteModal(
            $id,
            $nm,
            "-",
            "Delete worksheet category",
            "manage-ws-categories"
        );
    }
}

if (isset($_POST["confirmDeleteSubmit"]) && !$tableMissing) {
    $id = (int) ($_POST["deleteNameID"] ?? 0);
    if ($id > 0) {
        $nProd = EdiWsTaxonomy::countWsProductsForCategory($conn, $id);
        if ($nProd > 0) {
            echo "<script>alert('Cannot delete: " . (int) $nProd . " row(s) in ws_products use this category (via subcategories). Remove or reassign them first.');location.href='./manage-ws-categories'</script>";
            exit;
        }
        $user->deleteTableRow("ws_categories", array("id" => $id));
    }
    echo "<script>alert('Deleted.');location.href='./manage-ws-categories'</script>";
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
    <?php echo $adminHeader->printAdminNav2("Worksheet categories"); ?>

    <div class="container-fluid py-4">
      <p class="text-sm text-muted mb-3">
        Separate taxonomy for worksheets (<code>ws_categories</code>). Used on <strong>Add worksheet</strong> forms.
        <a href="./manage-ws-subcategories" class="ms-2">Worksheet subcategories →</a>
      </p>

      <?php if ($tableMissing) { ?>
        <div class="alert alert-danger">
          Table <code>ws_categories</code> not found. Run your SQL migration, then reload.
        </div>
      <?php } else { ?>

      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6 class="mb-0">All worksheet categories</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-3">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sort</th>
                  <th class="text-secondary opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 0;
                foreach ($categories as $c) {
                    $i++;
                    echo "<tr>
                      <td class='align-middle text-sm'>" . (int) $i . "</td>
                      <td class='align-middle text-sm font-weight-bold'>" . htmlspecialchars((string) $c["name"]) . "</td>
                      <td class='align-middle text-sm'>" . (int) ($c["sort_order"] ?? 0) . "</td>
                      <td class='align-middle'>
                        <a href='manage-ws-categories?id=" . (int) $c["id"] . "' class='btn btn-sm btn-primary'>Edit</a>
                      </td>
                    </tr>";
                }
                if ($i === 0) {
                    echo "<tr><td colspan='4' class='text-sm text-muted px-3 py-4'>No categories yet.</td></tr>";
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
              <div class="col-md-8 mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required maxlength="191"
                       value="<?php echo htmlspecialchars($formName); ?>">
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Sort order</label>
                <input type="number" name="sort_order" class="form-control" value="<?php echo (int) $formSort; ?>">
              </div>
            </div>
            <div class="edi-admin-form-actions">
              <?php
              if ($editMode) {
                  echo "<button type='submit' name='updateWsCatSubmit' class='btn btn-primary'>Update</button>
                        <button type='submit' name='deleteWsCatSubmit' class='btn btn-danger'>Delete</button>
                        <a href='./manage-ws-categories' class='btn btn-outline-secondary'>Cancel</a>";
              } else {
                  echo "<button type='submit' name='addWsCatSubmit' class='btn btn-success'>Add category</button>";
              }
              ?>
            </div>
            <?php if ($editMode) { ?>
            <input type="hidden" name="category_name" value="<?php echo htmlspecialchars($formName); ?>">
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
