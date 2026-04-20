<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("manage-product-subcategories");
$user = new USER();

$deleteModalHtml = "";
$editMode = false;
$editId = 0;
$formMainId = 0;
$formTitle = "";
$formDesc = "";

try {
    $st = $user->runQuery(
        "SELECT s.*, m.title AS main_title 
         FROM sub_category s 
         LEFT JOIN main_category m ON s.main_cat_id = m.id 
         ORDER BY COALESCE(m.title, '') ASC, s.title ASC"
    );
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $rows = [];
}

try {
    $mst = $user->runQuery("SELECT id, title FROM main_category ORDER BY title ASC");
    $mst->execute();
    $mainCategories = $mst->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mainCategories = [];
}

if (isset($_GET["id"]) && ctype_digit((string) $_GET["id"])) {
    $editId = (int) $_GET["id"];
    $found = $user->fetchAll(
        ["id", "main_cat_id", "title", "description"],
        ["sub_category"],
        ["id" => $editId]
    );
    if (!empty($found)) {
        $editMode = true;
        $formMainId = (int) ($found[0]["main_cat_id"] ?? 0);
        $formTitle = (string) ($found[0]["title"] ?? "");
        $formDesc = (string) ($found[0]["description"] ?? "");
    } else {
        $user->redirect("./manage-product-subcategories");
    }
}

if (isset($_POST["addMainGroupSubmit"])) {
    $mt = trim((string) ($_POST["main_group_title"] ?? ""));
    $md = trim((string) ($_POST["main_group_description"] ?? ""));
    if ($mt === "") {
        echo "<script>alert('Main group title is required.');location.href='./manage-product-subcategories'</script>";
        exit;
    }
    $nidStmt = $user->runQuery("SELECT COALESCE(MAX(id), 0) + 1 AS n FROM main_category");
    $nidStmt->execute();
    $nidRow = $nidStmt->fetch(PDO::FETCH_ASSOC);
    $newMainId = (int) ($nidRow["n"] ?? 1);
    $user->insertTable(
        "main_category",
        [
            "id" => $newMainId,
            "title" => htmlspecialchars($mt, ENT_QUOTES, "UTF-8"),
            "description" => $md !== "" ? htmlspecialchars($md, ENT_QUOTES, "UTF-8") : "",
        ],
        false
    );
    echo "<script>alert('Main group added. You can now create subcategories under it.');location.href='./manage-product-subcategories'</script>";
    exit;
}

if (isset($_POST["addSubSubmit"])) {
    $mainId = (int) ($_POST["main_cat_id"] ?? 0);
    $title = trim((string) ($_POST["title"] ?? ""));
    $desc = trim((string) ($_POST["description"] ?? ""));
    if ($mainId < 1 || $title === "") {
        echo "<script>alert('Main group and title are required.');location.href='./manage-product-subcategories'</script>";
        exit;
    }
    if (!$user->CountRows("main_category", ["id" => $mainId])) {
        echo "<script>alert('Invalid main group.');location.href='./manage-product-subcategories'</script>";
        exit;
    }
    $nidStmt = $user->runQuery("SELECT COALESCE(MAX(id), 0) + 1 AS n FROM sub_category");
    $nidStmt->execute();
    $nidRow = $nidStmt->fetch(PDO::FETCH_ASSOC);
    $newId = (int) ($nidRow["n"] ?? 1);
    $user->insertTable(
        "sub_category",
        [
            "id" => $newId,
            "main_cat_id" => $mainId,
            "title" => htmlspecialchars($title, ENT_QUOTES, "UTF-8"),
            "description" => $desc !== "" ? htmlspecialchars($desc, ENT_QUOTES, "UTF-8") : "",
        ],
        false
    );
    echo "<script>alert('Subcategory added.');location.href='./manage-product-subcategories'</script>";
    exit;
}

if (isset($_POST["updateSubSubmit"])) {
    $id = (int) ($_POST["sub_id"] ?? 0);
    $mainId = (int) ($_POST["main_cat_id"] ?? 0);
    $title = trim((string) ($_POST["title"] ?? ""));
    $desc = trim((string) ($_POST["description"] ?? ""));
    if ($id < 1 || $mainId < 1 || $title === "") {
        echo "<script>alert('Invalid data.');location.href='./manage-product-subcategories'</script>";
        exit;
    }
    if (!$user->CountRows("sub_category", ["id" => $id]) || !$user->CountRows("main_category", ["id" => $mainId])) {
        $user->redirect("./manage-product-subcategories");
    }
    $user->updateTable(
        "sub_category",
        [
            "main_cat_id" => $mainId,
            "title" => htmlspecialchars($title, ENT_QUOTES, "UTF-8"),
            "description" => $desc !== "" ? htmlspecialchars($desc, ENT_QUOTES, "UTF-8") : "",
        ],
        ["id" => $id]
    );
    echo "<script>alert('Subcategory updated.');location.href='./manage-product-subcategories'</script>";
    exit;
}

if (isset($_POST["deleteSubSubmit"])) {
    $id = (int) ($_POST["sub_id"] ?? 0);
    $ttl = htmlspecialchars((string) ($_POST["sub_title"] ?? ""), ENT_QUOTES, "UTF-8");
    if ($id > 0) {
        $deleteModalHtml = $user->confirmDeleteModal(
            $id,
            $ttl,
            "-",
            "Delete subcategory",
            "manage-product-subcategories"
        );
    }
}

if (isset($_POST["confirmDeleteSubmit"])) {
    $id = (int) ($_POST["deleteNameID"] ?? 0);
    if ($id > 0) {
        $used = (int) $user->CountRows("books_details", ["sub_cat_id" => $id])
            + (int) $user->CountRows("homework_details", ["sub_cat_id" => $id])
            + (int) $user->CountRows("pdf_details", ["sub_cat_id" => $id]);
        if ($used > 0) {
            echo "<script>alert('Cannot delete: this subcategory is used by " . $used . " book/homework/PDF item(s).');location.href='./manage-product-subcategories'</script>";
            exit;
        }
        $user->deleteTableRow("sub_category", ["id" => $id]);
    }
    echo "<script>alert('Subcategory deleted. Product links to this subcategory were cleared automatically if any.');location.href='./manage-product-subcategories'</script>";
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
        Subcategories appear in the <strong>Add Product</strong> subcategory list. Each one is stored under a <strong>main group</strong>
        (shared with books/homework/PDF filters in the database). Create a group such as <em>Shop</em> below if you want subcategories only for products.
        <a href="./manage-product-categories" class="ms-2">← Product categories</a>
      </p>

      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6 class="mb-0">Add main group</h6>
        </div>
        <div class="card-body">
          <p class="text-sm text-muted">Every subcategory belongs to a main group (e.g. <em>Shop</em>, <em>Books &amp; Papers</em>). Add a new group here if you need one for shop-only subcategories.</p>
          <form method="post" class="row align-items-end">
            <div class="col-md-4 mb-2 mb-md-0">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" name="main_group_title" class="form-control" maxlength="100" placeholder="Shop" required>
            </div>
            <div class="col-md-5 mb-2 mb-md-0">
              <label class="form-label">Description</label>
              <input type="text" name="main_group_description" class="form-control" maxlength="200" placeholder="Optional">
            </div>
            <div class="col-md-3">
              <button type="submit" name="addMainGroupSubmit" class="btn btn-outline-primary w-100">Add main group</button>
            </div>
          </form>
        </div>
      </div>

      <?php if (empty($mainCategories)) { ?>
        <div class="alert alert-warning">No main groups yet. Use the form above to create one (e.g. &quot;Shop&quot;), then add subcategories.</div>
      <?php } ?>

      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6 class="mb-0">All subcategories</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-3">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Main group</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                  <th class="text-secondary opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 0;
                foreach ($rows as $r) {
                    $i++;
                    $mt = $r["main_title"] ?? "—";
                    echo "<tr>
                      <td class='align-middle text-sm'>" . (int) $i . "</td>
                      <td class='align-middle text-sm'>" . htmlspecialchars((string) $mt) . "</td>
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
                <label class="form-label">Main group <span class="text-danger">*</span></label>
                <select name="main_cat_id" class="form-control" required <?php echo empty($mainCategories) ? "disabled" : ""; ?>>
                  <option value="">Select main group</option>
                  <?php foreach ($mainCategories as $m) { ?>
                    <option value="<?php echo (int) $m["id"]; ?>"
                      <?php echo ((int) $m["id"] === $formMainId) ? "selected" : ""; ?>>
                      <?php echo htmlspecialchars($m["title"] ?? ""); ?>
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
                  echo "<button type='submit' name='addSubSubmit' class='btn btn-success'" . (empty($mainCategories) ? " disabled" : "") . ">Add subcategory</button>";
              }
              ?>
            </div>
            <?php if ($editMode) { ?>
            <input type="hidden" name="sub_title" value="<?php echo htmlspecialchars($formTitle); ?>">
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
