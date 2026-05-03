<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/edi_taxonomy.php");
  require_once("../classes/edi_explorer_content.php");
  require_once("../classes/class.header.php");
  
  $adminHeader = new HEADER("add-product"); // Set active page for sidebar
  $user = new USER();
  $ediLanguages = EdiTaxonomy::loadLanguages($user->getConnection());
  $ediGrades = EdiTaxonomy::loadGrades($user->getConnection());

  $product_subcategories = [];
  try {
    $catStmt = $user->runQuery("SELECT * FROM product_categories ORDER BY name ASC");
    $catStmt->execute();
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    $subCatStmt = $user->runQuery(
        "SELECT id, product_category_id, title FROM product_subcategories ORDER BY product_category_id ASC, title ASC"
    );
    $subCatStmt->execute();
    $product_subcategories = EdiExplorerContent::dedupeProductSubcategoryRows($subCatStmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    $categories = [];
    $product_subcategories = [];
}

  

  // Handle Form Submission
  if (isset($_POST['btn-add-product'])) {
      $product_subcategory_id = isset($_POST['product_subcategory']) ? trim((string) $_POST['product_subcategory']) : '';
      $product_subcategory_id = $product_subcategory_id === '' ? null : (int) $product_subcategory_id;
      $category_id = (int) $_POST['category'];
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
      $brand       = $_POST['brand'];
      $age_group   = trim((string) ($_POST['age_group'] ?? ""));
      $allowedGrades = EdiTaxonomy::allowedTitles($ediGrades);
      if (!in_array($age_group, $allowedGrades, true)) {
          $age_group = "";
      }
      $price       = (float) ($_POST['price'] ?? 0);
      $discountRaw = trim((string) ($_POST['discount'] ?? ''));
      $discountPct = $discountRaw === '' ? 0.0 : (float) $discountRaw;
      if ($discountPct < 0) {
          $discountPct = 0.0;
      }
      if ($discountPct > 100) {
          $discountPct = 100.0;
      }
      $disc_price = 0.0;
      if ($discountPct > 0) {
          $dpr = trim((string) ($_POST['discounted_price'] ?? ''));
          $disc_price = ($dpr !== '' && is_numeric($dpr)) ? (float) $dpr : ($price - ($price * ($discountPct / 100)));
      }
      $p_name      = $_POST['product_name'];
      $stock       = $_POST['available'];
      $description = $_POST['description'];
      $language    = trim((string) ($_POST['language'] ?? ""));
      $allowedLangs = EdiTaxonomy::allowedTitles($ediLanguages);
      if (!in_array($language, $allowedLangs, true)) {
          $language = "";
      }
      $author      = $_POST['author'];
      $isbn        = trim((string) ($_POST['isbn'] ?? ''));
      $weightKgRaw = trim((string) ($_POST['weight_kg'] ?? ''));
      $weight_kg = ($weightKgRaw === '') ? null : max(0.0, (float) $weightKgRaw);
      $weight = ($weight_kg !== null && $weight_kg > 0)
          ? (rtrim(rtrim(number_format($weight_kg, 4, '.', ''), '0'), '.') . ' kg')
          : '';

      // Image Handling (Simplified - ensure your upload directory exists)
      $main_image = $_FILES['main_image']['name'];
      $target = "../img/products/" . basename($main_image);
      move_uploaded_file($_FILES['main_image']['tmp_name'], $target);

      try {
          $stmt = $user->runQuery("INSERT INTO products (category_id, sub_category_id, product_subcategory_id, brand, product_name, price, discount_percentage, discounted_price, age_group, description, language, author, isbn, weight, weight_kg, stock, image, status) 
                                 VALUES (:cid, NULL, :pscid, :brand, :pname, :price, :disc, :dprice, :age, :desc, :lang, :auth, :isbn, :weight, :wkg, :stock, :img, 1)");
          $stmt->execute(array(
              ":cid"=>$category_id, ":pscid"=>$product_subcategory_id, ":brand"=>$brand, ":pname"=>$p_name, ":price"=>$price, 
              ":disc"=>$discountPct, ":dprice"=>$disc_price, ":age"=>$age_group, ":desc"=>$description, 
              ":lang"=>$language, ":auth"=>$author, ":isbn"=>$isbn, ":weight"=>$weight, ":wkg"=>$weight_kg, ":stock"=>$stock, ":img"=>$main_image
          ));
          $msg = "<div class='alert alert-success'>Product added successfully!</div>";
      } catch (PDOException $e) {
          $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
      }
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
    <?php echo $adminHeader->printAdminNav2("Add Product"); ?>
    
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0">
              <h6>PRODUCT INFORMATION</h6>
              <?php if(isset($msg)) echo $msg; ?>
            </div>
            <div class="card-body px-4 pt-0 pb-2">
              <form method="post" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-4">
             <label>Category</label>
            <select name="category" id="product_category_select" class="form-control" required>
           <option value="">Select Category</option>
           <?php foreach($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-3">
        <label>Subcategory</label>
        <select name="product_subcategory" id="product_subcategory_select" class="form-control" disabled>
            <option value="-1" disabled selected class="edi-shop-sub-need-cat">Please select a category first to see subcategories.</option>
            <option value="" class="edi-shop-sub-none" hidden disabled>Subcategory (optional)</option>
            <?php foreach ($product_subcategories as $sub): ?>
                <option value="<?php echo (int) $sub['id']; ?>"
                    data-product-category-id="<?php echo (int) $sub['product_category_id']; ?>">
                    <?php echo htmlspecialchars($sub['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
                  <div class="col-md-4">
                    <label>Brand</label>
                    <input type="text" name="brand" class="form-control" placeholder="Sarasavi">
                  </div>
                  <div class="col-md-3">
        <label>Grade</label>
        <select name="age_group" class="form-control" required>
            <option value="">Select grade</option>
            <?php foreach ($ediGrades as $gr): ?>
            <option value="<?php echo htmlspecialchars((string) $gr['title'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars((string) $gr['title'], ENT_QUOTES, 'UTF-8'); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-4">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="LKR 700.00">
                  </div>
                  <div class="col-md-4">
                    <label>Discount percentage</label>
                    <input type="number" name="discount" id="discount" class="form-control" placeholder="e.g. 10" min="0" max="100" step="0.01">
                    <small class="text-muted">Optional; leave empty or 0 for no discount.</small>
                  </div>
                  <div class="col-md-4">
                    <label>Discounted price (auto)</label>
                    <input type="text" id="discounted_price_display" class="form-control" readonly>
                   <input type="hidden" name="discounted_price" id="discounted_price_value">
                 </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-8">
                    <label>Product Name</label>
                    <input type="text" name="product_name" class="form-control" placeholder="ENGLISH ALPHABET BOOK" required>
                  </div>
                  <div class="col-md-4">
                    <label>Available</label>
                    <input type="number" name="available" class="form-control" placeholder="10">
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-12">
                    <label>Main Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
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
                                <?php foreach ($ediLanguages as $lng): ?>
                                <option value="<?php echo htmlspecialchars((string) $lng['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                  <?php echo htmlspecialchars((string) $lng['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                                <?php endforeach; ?>
                              </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5"><input type="text" class="form-control" value="Author" readonly></div>
                            <div class="col-7"><input type="text" name="author" class="form-control" placeholder="Jhon Jhones"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5"><label class="form-label mb-0 d-block pt-2">ISBN</label></div>
                            <div class="col-7">
                              <input type="text" name="isbn" class="form-control" placeholder="978-3-16-148410-0">
                              <small class="text-muted">Optional; shown on the product page.</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5"><label class="form-label mb-0 d-block pt-2">Weight (kg)</label></div>
                            <div class="col-7">
                              <input type="number" name="weight_kg" class="form-control" step="0.0001" min="0" placeholder="e.g. 0.25">
                              <small class="text-muted">Optional. Used for cart total weight and shipping tiers.</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success mt-2">ADD ANOTHER OPTION +</button>
                    </div>
                    <div class="col-md-6">
                        <h6>More Details</h6>
                        <textarea name="more_details" id="more_details"></textarea>
                        <script>CKEDITOR.replace('more_details');</script>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <label>Main Image</label>
                        <input type="file" name="main_image" class="form-control" required>
                    </div>
                   
                </div>

                <div class="mt-4 mb-4">
                 
                  <button type="submit" name="btn-add-product" class="btn btn-success">Add</button>
                  <button type="reset" class="btn btn-secondary">CANCEL</button>
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
    const priceInput = document.getElementById('price');
    const discInput = document.getElementById('discount');
    const displayInput = document.getElementById('discounted_price_display');
    const valueInput = document.getElementById('discounted_price_value');

    function calculate() {
        const price = parseFloat(priceInput.value) || 0;
        const raw = String(discInput.value || "").trim();
        const disc = raw === "" ? 0 : parseFloat(raw);
        const hasDisc = !isNaN(disc) && disc > 0;
        if (!hasDisc) {
            displayInput.value = price > 0 ? "No discount (list price)" : "—";
            valueInput.value = "0";
            return;
        }
        const discounted = price - (price * (disc / 100));
        displayInput.value = "LKR " + discounted.toFixed(2);
        valueInput.value = discounted.toFixed(2);
    }

    priceInput.addEventListener('input', calculate);
    discInput.addEventListener('input', calculate);
    document.addEventListener('DOMContentLoaded', calculate);

    (function () {
        const cat = document.getElementById('product_category_select');
        const sub = document.getElementById('product_subcategory_select');
        if (!cat || !sub) return;
        const needCat = sub.querySelector('option.edi-shop-sub-need-cat');
        const noneOpt = sub.querySelector('option.edi-shop-sub-none');
        function syncSubcategories() {
            const cid = String(cat.value || '');
            const realOpts = sub.querySelectorAll('option[data-product-category-id]');
            if (!cid) {
                sub.disabled = true;
                if (needCat) {
                    needCat.hidden = false;
                    needCat.disabled = true;
                    needCat.selected = true;
                }
                if (noneOpt) {
                    noneOpt.hidden = true;
                    noneOpt.disabled = true;
                    noneOpt.selected = false;
                }
                for (let i = 0; i < realOpts.length; i++) {
                    realOpts[i].hidden = true;
                    realOpts[i].disabled = true;
                }
                return;
            }
            sub.disabled = false;
            if (needCat) {
                needCat.hidden = true;
                needCat.disabled = true;
                needCat.selected = false;
            }
            if (noneOpt) {
                noneOpt.hidden = false;
                noneOpt.disabled = false;
                noneOpt.selected = true;
            }
            let still = false;
            const sel = String(sub.value || '');
            for (let j = 0; j < realOpts.length; j++) {
                const opt = realOpts[j];
                const ok = opt.getAttribute('data-product-category-id') === cid;
                opt.hidden = !ok;
                opt.disabled = !ok;
                if (ok && opt.value === sel) {
                    still = true;
                }
            }
            if (still && noneOpt) {
                noneOpt.selected = false;
            }
        }
        cat.addEventListener('change', function () {
            if (noneOpt) {
                noneOpt.selected = true;
            }
            syncSubcategories();
        });
        syncSubcategories();
    })();
</script>

  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>