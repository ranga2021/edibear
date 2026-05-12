<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/edi_ws_taxonomy.php");

$adminHeader = new HEADER("manage-ws-subcategories");
$user = new USER();
$conn = $user->getConnection();

$deleteModalHtml = "";
$editMode = false;
$editId = 0;
$formWsCatId = 0;
$formName = "";
$formSort = 0;
$tableMissing = !EdiWsTaxonomy::tableExists($conn, "ws_subcategories")
    || !EdiWsTaxonomy::tableExists($conn, "ws_categories");

$wsCategories = EdiWsTaxonomy::loadCategories($conn);

$rows = array();
if (!$tableMissing) {
    try {
        $st = $user->runQuery(
            "SELECT s.*, c.name AS category_name
             FROM ws_subcategories s
             INNER JOIN ws_categories c ON c.id = s.category_id
             ORDER BY c.sort_order ASC, c.name ASC, s.sort_order ASC, s.name ASC"
        );
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $rows = array();
    }
}

if (isset($_GET["id"]) && ctype_digit((string) $_GET["id"]) && !$tableMissing) {
    $editId = (int) $_GET["id"];
    $found = $user->fetchAll(
        array("id", "category_id", "name", "sort_order"),
        array("ws_subcategories"),
        array("id" => $editId)
    );
    if (!empty($found)) {
        $editMode = true;
        $formWsCatId = (int) ($found[0]["category_id"] ?? 0);
        $formName = (string) ($found[0]["name"] ?? "");
        $formSort = (int) ($found[0]["sort_order"] ?? 0);
    } else {
        $user->redirect("./manage-ws-subcategories");
    }
}

if (isset($_POST["addWsSubSubmit"]) && !$tableMissing) {
    $cid = (int) ($_POST["ws_category_id"] ?? 0);
    $name = trim((string) ($_POST["name"] ?? ""));
    $sort = (int) ($_POST["sort_order"] ?? 0);
    if ($cid < 1 || $name === "") {
        echo "<script>alert('Category and name are required.');location.href='./manage-ws-subcategories'</script>";
        exit;
    }
    if (!$user->CountRows("ws_categories", array("id" => $cid))) {
        echo "<script>alert('Invalid category.');location.href='./manage-ws-subcategories'</script>";
        exit;
    }
    $user->insertTable(
        "ws_subcategories",
        array(
            "category_id" => $cid,
            "name" => htmlspecialchars($name, ENT_QUOTES, "UTF-8"),
            "sort_order" => $sort,
        ),
        false
    );
    echo "<script>alert('Subcategory added.');location.href='./manage-ws-subcategories'</script>";
    exit;
}

if (isset($_POST["updateWsSubSubmit"]) && !$tableMissing) {
    $id = (int) ($_POST["sub_id"] ?? 0);
    $cid = (int) ($_POST["ws_category_id"] ?? 0);
    $name = trim((string) ($_POST["name"] ?? ""));
    $sort = (int) ($_POST["sort_order"] ?? 0);
    if ($id < 1 || $cid < 1 || $name === "") {
        echo "<script>alert('Invalid data.');location.href='./manage-ws-subcategories'</script>";
        exit;
    }
    if (!$user->CountRows("ws_subcategories", array("id" => $id)) || !$user->CountRows("ws_categories", array("id" => $cid))) {
        $user->redirect("./manage-ws-subcategories");
    }
    $user->updateTable(
        "ws_subcategories",
        array(
            "category_id" => $cid,
            "name" => htmlspecialchars($name, ENT_QUOTES, "UTF-8"),
            "sort_order" => $sort,
        ),
        array("id" => $id)
    );
    echo "<script>alert('Updated.');location.href='./manage-ws-subcategories'</script>";
    exit;
}

if (isset($_POST["deleteWsSubSubmit"]) && !$tableMissing) {
    $id = (int) ($_POST["sub_id"] ?? 0);
    $ttl = htmlspecialchars((string) ($_POST["sub_title"] ?? ""), ENT_QUOTES, "UTF-8");
    if ($id > 0) {
        $deleteModalHtml = $user->confirmDeleteModal(
            $id,
            $ttl,
            "-",
            "Delete worksheet subcategory",
            "manage-ws-subcategories"
        );
    }
}

if (isset($_POST["confirmDeleteSubmit"]) && !$tableMissing) {
    $id = (int) ($_POST["deleteNameID"] ?? 0);
    if ($id > 0) {
        $nProd = EdiWsTaxonomy::countWsProductsForSubcategory($conn, $id);
        if ($nProd > 0) {
            echo "<script>alert('Cannot delete: " . (int) $nProd . " row(s) in ws_products use this subcategory.');location.href='./manage-ws-subcategories'</script>";
            exit;
        }
        $user->deleteTableRow("ws_subcategories", array("id" => $id));
    }
    echo "<script>alert('Deleted.');location.href='./manage-ws-subcategories'</script>";
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
    <?php echo $adminHeader->printAdminNav2("Worksheet subcategories"); ?>

    <div class="container-fluid py-4">
      <p class="text-sm text-muted mb-3">
        Subcategories for worksheet taxonomy (<code>ws_subcategories</code>).
        <a href="./manage-ws-categories" class="ms-2">← Worksheet categories</a>
      </p>

      <?php if ($tableMissing) { ?>
        <div class="alert alert-danger">
          Tables <code>ws_categories</code> / <code>ws_subcategories</code> not found. Run your SQL migration.
        </div>
      <?php } elseif (empty($wsCategories)) { ?>
        <div class="alert alert-warning">Add at least one <a href="./manage-ws-categories">worksheet category</a> first.</div>
      <?php } else { ?>

      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6 class="mb-0">All worksheet subcategories</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-3">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Category</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sort</th>
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
                      <td class='align-middle text-sm'>" . htmlspecialchars((string) $r["category_name"]) . "</td>
                      <td class='align-middle text-sm font-weight-bold'>" . htmlspecialchars((string) $r["name"]) . "</td>
                      <td class='align-middle text-sm'>" . (int) ($r["sort_order"] ?? 0) . "</td>
                      <td class='align-middle'>
                        <a href='manage-ws-subcategories?id=" . (int) $r["id"] . "' class='btn btn-sm btn-primary'>Edit</a>
                      </td>
                    </tr>";
                }
                if ($i === 0) {
                    echo "<tr><td colspan='5' class='text-sm text-muted px-3 py-4'>No subcategories yet.</td></tr>";
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
              <div class="col-md-4 mb-3">
                <label class="form-label">Worksheet category <span class="text-danger">*</span></label>
                <select name="ws_category_id" class="form-control" required <?php echo empty($wsCategories) ? "disabled" : ""; ?>>
                  <option value="">— Select —</option>
                  <?php foreach ($wsCategories as $wc) : ?>
                  <option value="<?php echo (int) $wc['id']; ?>"<?php echo ((int) $wc['id'] === $formWsCatId) ? " selected" : ""; ?>>
                    <?php echo htmlspecialchars((string) $wc['name'], ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-5 mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required maxlength="191"
                       value="<?php echo htmlspecialchars($formName); ?>">
              </div>
              <div class="col-md-3 mb-3">
                <label class="form-label">Sort order</label>
                <input type="number" name="sort_order" class="form-control" value="<?php echo (int) $formSort; ?>">
              </div>
            </div>
            <div class="edi-admin-form-actions">
              <?php
              if ($editMode) {
                  echo "<button type='submit' name='updateWsSubSubmit' class='btn btn-primary'>Update</button>
                        <button type='submit' name='deleteWsSubSubmit' class='btn btn-danger'>Delete</button>
                        <a href='./manage-ws-subcategories' class='btn btn-outline-secondary'>Cancel</a>";
              } else {
                  echo "<button type='submit' name='addWsSubSubmit' class='btn btn-success'>Add subcategory</button>";
              }
              ?>
            </div>
            <?php if ($editMode) { ?>
            <input type="hidden" name="sub_title" value="<?php echo htmlspecialchars($formName); ?>">
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
