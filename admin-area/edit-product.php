<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/edi_taxonomy.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("products");
$user = new USER();
$ediLanguages = EdiTaxonomy::loadLanguages($user->getConnection());
$ediGrades = EdiTaxonomy::loadGrades($user->getConnection());

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
if ($id < 1) {
    header("Location: ./products");
    exit;
}

$st = $user->runQuery("SELECT * FROM products WHERE id = :id LIMIT 1");
$st->execute([":id" => $id]);
$product = $st->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header("Location: ./products");
    exit;
}

$product_subcategories = [];
try {
    $catStmt = $user->runQuery("SELECT * FROM product_categories ORDER BY name ASC");
    $catStmt->execute();
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    $subCatStmt = $user->runQuery(
        "SELECT id, product_category_id, title FROM product_subcategories ORDER BY product_category_id ASC, title ASC"
    );
    $subCatStmt->execute();
    $product_subcategories = $subCatStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    $product_subcategories = [];
}

$msg = "";

if (isset($_POST["btn-update-product"])) {
    $product_subcategory_id = isset($_POST["product_subcategory"]) ? trim((string) $_POST["product_subcategory"]) : "";
    $product_subcategory_id = $product_subcategory_id === "" ? null : (int) $product_subcategory_id;
    $category_id = (int) $_POST["category"];
    if ($product_subcategory_id !== null) {
        $pair = $user->fetchAll(
            ["id"],
            ["product_subcategories"],
            ["id" => $product_subcategory_id, "product_category_id" => $category_id]
        );
        if (empty($pair)) {
            $product_subcategory_id = null;
        }
    }
    $brand = $_POST["brand"] ?? "";
    $age_group = trim((string) ($_POST["age_group"] ?? ""));
    $allowedGrades = EdiTaxonomy::allowedTitles($ediGrades);
    $prevAge = trim((string) ($product["age_group"] ?? ""));
    if ($age_group !== "" && !in_array($age_group, $allowedGrades, true) && $age_group !== $prevAge) {
        $age_group = $prevAge;
    }
    $price = $_POST["price"] ?? "";
    $discount = $_POST["discount"] ?? "";
    $disc_price = $_POST["discounted_price"] ?? "";
    $p_name = $_POST["product_name"] ?? "";
    $stock = $_POST["available"] ?? "";
    $description = $_POST["description"] ?? "";
    $language = trim((string) ($_POST["language"] ?? ""));
    $allowedLangs = EdiTaxonomy::allowedTitles($ediLanguages);
    $prevLang = trim((string) ($product["language"] ?? ""));
    if ($language !== "" && !in_array($language, $allowedLangs, true) && $language !== $prevLang) {
        $language = $prevLang;
    }
    $author = $_POST["author"] ?? "";

    $main_image = $product["image"];
    if (!empty($_FILES["main_image"]["name"])) {
        $main_image = basename($_FILES["main_image"]["name"]);
        $target = "../img/products/" . $main_image;
        move_uploaded_file($_FILES["main_image"]["tmp_name"], $target);
    }

    try {
        $hasMore = array_key_exists("more_details", $product);
        $hasPsub = array_key_exists("product_subcategory_id", $product);

        if ($hasPsub && $hasMore) {
            $stmt = $user->runQuery(
                "UPDATE products SET category_id=:cid, sub_category_id=NULL, product_subcategory_id=:pscid, brand=:brand, product_name=:pname, price=:price, discount_percentage=:disc, discounted_price=:dprice, age_group=:age, description=:desc, language=:lang, author=:auth, stock=:stock, image=:img, more_details=:md WHERE id=:id"
            );
            $stmt->execute([
                ":cid" => $category_id,
                ":pscid" => $product_subcategory_id,
                ":brand" => $brand,
                ":pname" => $p_name,
                ":price" => $price,
                ":disc" => $discount,
                ":dprice" => $disc_price,
                ":age" => $age_group,
                ":desc" => $description,
                ":lang" => $language,
                ":auth" => $author,
                ":stock" => $stock,
                ":img" => $main_image,
                ":md" => $_POST["more_details"] ?? "",
                ":id" => $id,
            ]);
        } elseif ($hasPsub) {
            $stmt = $user->runQuery(
                "UPDATE products SET category_id=:cid, sub_category_id=NULL, product_subcategory_id=:pscid, brand=:brand, product_name=:pname, price=:price, discount_percentage=:disc, discounted_price=:dprice, age_group=:age, description=:desc, language=:lang, author=:auth, stock=:stock, image=:img WHERE id=:id"
            );
            $stmt->execute([
                ":cid" => $category_id,
                ":pscid" => $product_subcategory_id,
                ":brand" => $brand,
                ":pname" => $p_name,
                ":price" => $price,
                ":disc" => $discount,
                ":dprice" => $disc_price,
                ":age" => $age_group,
                ":desc" => $description,
                ":lang" => $language,
                ":auth" => $author,
                ":stock" => $stock,
                ":img" => $main_image,
                ":id" => $id,
            ]);
        } elseif ($hasMore) {
            $stmt = $user->runQuery(
                "UPDATE products SET category_id=:cid, sub_category_id=NULL, brand=:brand, product_name=:pname, price=:price, discount_percentage=:disc, discounted_price=:dprice, age_group=:age, description=:desc, language=:lang, author=:auth, stock=:stock, image=:img, more_details=:md WHERE id=:id"
            );
            $stmt->execute([
                ":cid" => $category_id,
                ":brand" => $brand,
                ":pname" => $p_name,
                ":price" => $price,
                ":disc" => $discount,
                ":dprice" => $disc_price,
                ":age" => $age_group,
                ":desc" => $description,
                ":lang" => $language,
                ":auth" => $author,
                ":stock" => $stock,
                ":img" => $main_image,
                ":md" => $_POST["more_details"] ?? "",
                ":id" => $id,
            ]);
        } else {
            $stmt = $user->runQuery(
                "UPDATE products SET category_id=:cid, sub_category_id=NULL, brand=:brand, product_name=:pname, price=:price, discount_percentage=:disc, discounted_price=:dprice, age_group=:age, description=:desc, language=:lang, author=:auth, stock=:stock, image=:img WHERE id=:id"
            );
            $stmt->execute([
                ":cid" => $category_id,
                ":brand" => $brand,
                ":pname" => $p_name,
                ":price" => $price,
                ":disc" => $discount,
                ":dprice" => $disc_price,
                ":age" => $age_group,
                ":desc" => $description,
                ":lang" => $language,
                ":auth" => $author,
                ":stock" => $stock,
                ":img" => $main_image,
                ":id" => $id,
            ]);
        }
        $msg = "<div class='alert alert-success'>Product updated.</div>";
        $st = $user->runQuery("SELECT * FROM products WHERE id = :id LIMIT 1");
        $st->execute([":id" => $id]);
        $product = $st->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $msg = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
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
  <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg ">
    <?php echo $adminHeader->printAdminNav2("Edit Product"); ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
              <h6>PRODUCT INFORMATION</h6>
              <a href="./products" class="btn btn-sm btn-outline-secondary">Back to list</a>
            </div>
            <div class="card-body px-4 pt-0 pb-2">
              <?php echo $msg; ?>
              <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo (int) $id; ?>">
                <div class="row">
                  <div class="col-md-4">
                    <label>Category</label>
                    <select name="category" id="product_category_select" class="form-control" required>
                      <option value="">Select Category</option>
                      <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int) $cat["id"]; ?>" <?php echo ((int) $product["category_id"] === (int) $cat["id"]) ? "selected" : ""; ?>>
                          <?php echo htmlspecialchars($cat["name"]); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label>Subcategory</label>
                    <select name="product_subcategory" id="product_subcategory_select" class="form-control">
                      <option value="">Select Subcategory</option>
                      <?php foreach ($product_subcategories as $sub): ?>
                        <option value="<?php echo (int) $sub["id"]; ?>"
                          data-product-category-id="<?php echo (int) $sub["product_category_id"]; ?>"
                          <?php echo (isset($product["product_subcategory_id"]) && (int) $product["product_subcategory_id"] === (int) $sub["id"]) ? "selected" : ""; ?>>
                          <?php echo htmlspecialchars($sub["title"]); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label>Brand</label>
                    <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars((string) ($product["brand"] ?? "")); ?>">
                  </div>
                  <div class="col-md-3">
                    <label>Grade</label>
                    <select name="age_group" class="form-control" required>
                      <option value="">Select grade</option>
                      <?php
                      $curAge = trim((string) ($product["age_group"] ?? ""));
                      $ageMatched = false;
                      foreach ($ediGrades as $gr) {
                          $t = trim((string) ($gr["title"] ?? ""));
                          $sel = ($curAge === $t) ? " selected" : "";
                          if ($sel !== "") {
                              $ageMatched = true;
                          }
                          echo "<option value=\"" . htmlspecialchars($t, ENT_QUOTES, "UTF-8") . "\"$sel>" . htmlspecialchars($t, ENT_QUOTES, "UTF-8") . "</option>";
                      }
                      if (!$ageMatched && $curAge !== "") {
                          echo "<option value=\"" . htmlspecialchars($curAge, ENT_QUOTES, "UTF-8") . "\" selected>" . htmlspecialchars($curAge, ENT_QUOTES, "UTF-8") . " (current)</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-4">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars((string) $product["price"]); ?>">
                  </div>
                  <div class="col-md-4">
                    <label>Discount Percentage (*)</label>
                    <input type="number" name="discount" id="discount" class="form-control" value="<?php echo htmlspecialchars((string) $product["discount_percentage"]); ?>">
                  </div>
                  <div class="col-md-4">
                    <label>Discounted Price (Auto cal)</label>
                    <input type="text" id="discounted_price_display" class="form-control" readonly>
                    <input type="hidden" name="discounted_price" id="discounted_price_value" value="<?php echo htmlspecialchars((string) $product["discounted_price"]); ?>">
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-8">
                    <label>Product Name</label>
                    <input type="text" name="product_name" class="form-control" required value="<?php echo htmlspecialchars((string) $product["product_name"]); ?>">
                  </div>
                  <div class="col-md-4">
                    <label>Available</label>
                    <input type="number" name="available" class="form-control" value="<?php echo htmlspecialchars((string) $product["stock"]); ?>">
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-12">
                    <label>Main Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars((string) ($product["description"] ?? "")); ?></textarea>
                  </div>
                </div>

                <div class="row mt-4">
                  <div class="col-md-6">
                    <h6>Options</h6>
                    <div class="row mb-2">
                      <div class="col-5"><label class="form-label mb-0 d-block pt-2">Language</label></div>
                      <div class="col-7">
                        <select name="language" class="form-control" required>
                          <option value="">Select language</option>
                          <?php
                          $curLang = trim((string) ($product["language"] ?? ""));
                          $langMatched = false;
                          foreach ($ediLanguages as $lng) {
                              $t = trim((string) ($lng["title"] ?? ""));
                              $sel = ($curLang === $t) ? " selected" : "";
                              if ($sel !== "") {
                                  $langMatched = true;
                              }
                              echo "<option value=\"" . htmlspecialchars($t, ENT_QUOTES, "UTF-8") . "\"$sel>" . htmlspecialchars($t, ENT_QUOTES, "UTF-8") . "</option>";
                          }
                          if (!$langMatched && $curLang !== "") {
                              echo "<option value=\"" . htmlspecialchars($curLang, ENT_QUOTES, "UTF-8") . "\" selected>" . htmlspecialchars($curLang, ENT_QUOTES, "UTF-8") . " (current)</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-5"><input type="text" class="form-control" value="Author" readonly></div>
                      <div class="col-7"><input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars((string) ($product["author"] ?? "")); ?>"></div>
                    </div>
                  </div>
                  <?php if (array_key_exists("more_details", $product)): ?>
                  <div class="col-md-6">
                    <h6>More Details</h6>
                    <textarea name="more_details" id="more_details"><?php echo htmlspecialchars((string) ($product["more_details"] ?? "")); ?></textarea>
                    <script>CKEDITOR.replace("more_details");</script>
                  </div>
                  <?php endif; ?>
                </div>

                <div class="row mt-4">
                  <div class="col-md-6">
                    <label>Main Image</label>
                    <p class="text-xs text-muted mb-1">Current: <code><?php echo htmlspecialchars((string) ($product["image"] ?? "")); ?></code></p>
                    <?php if (!empty($product["image"])): ?>
                    <p class="mb-2"><img src="/img/products/<?php echo rawurlencode($product["image"]); ?>" alt="" class="img-fluid rounded" style="max-height:120px;"></p>
                    <?php endif; ?>
                    <input type="file" name="main_image" class="form-control">
                    <small class="text-muted">Leave empty to keep the current file.</small>
                  </div>
                </div>

                <div class="mt-4 mb-4">
                  <button type="submit" name="btn-update-product" class="btn btn-primary">Save changes</button>
                  <a href="./products" class="btn btn-secondary">Cancel</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>

<script>
    const priceInput = document.getElementById("price");
    const discInput = document.getElementById("discount");
    const displayInput = document.getElementById("discounted_price_display");
    const valueInput = document.getElementById("discounted_price_value");

    function calculate() {
        const price = parseFloat(priceInput.value) || 0;
        const disc = parseFloat(discInput.value) || 0;
        const discounted = price - (price * (disc / 100));
        displayInput.value = "LKR " + discounted.toFixed(2);
        valueInput.value = discounted.toFixed(2);
    }

    priceInput.addEventListener("input", calculate);
    discInput.addEventListener("input", calculate);
    calculate();

    (function () {
        const cat = document.getElementById("product_category_select");
        const sub = document.getElementById("product_subcategory_select");
        if (!cat || !sub) return;
        function syncSubcategories() {
            const cid = String(cat.value || "");
            for (let i = 0; i < sub.options.length; i++) {
                const opt = sub.options[i];
                if (opt.value === "") {
                    opt.disabled = false;
                    continue;
                }
                const ok = cid !== "" && opt.getAttribute("data-product-category-id") === cid;
                opt.disabled = !ok;
                if (!ok && opt.selected) {
                    sub.selectedIndex = 0;
                }
            }
        }
        cat.addEventListener("change", syncSubcategories);
        syncSubcategories();
    })();
</script>

  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>
