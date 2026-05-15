<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/edi_taxonomy.php");
  require_once("../classes/edi_explorer_content.php");
  require_once("../classes/edi_product_admin.php");
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

  $pdo = $user->getConnection();
  $hasMoreDetails = EdiExplorerContent::columnExists($pdo, "products", "more_details");
  $hasGalleryImages = EdiExplorerContent::columnExists($pdo, "products", "gallery_images");
  if (!$hasGalleryImages) {
      $hasGalleryImages = EdiExplorerContent::ensureNullableTextColumn($pdo, "products", "gallery_images");
  }
  $hasOptionsExtra = EdiExplorerContent::columnExists($pdo, "products", "options_extra");

  // Handle Form Submission
  if (isset($_POST["btn-add-product"]) || isset($_POST["btn-draft-product"])) {
      $isDraft = isset($_POST["btn-draft-product"]);
      $status = $isDraft ? 0 : 1;

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
      if (!in_array($age_group, $allowedGrades, true)) {
          $age_group = "";
      }
      $price = (float) ($_POST["price"] ?? 0);
      $discountRaw = trim((string) ($_POST["discount"] ?? ""));
      $discountPct = $discountRaw === "" ? 0.0 : (float) $discountRaw;
      if ($discountPct < 0) {
          $discountPct = 0.0;
      }
      if ($discountPct > 100) {
          $discountPct = 100.0;
      }
      $disc_price = 0.0;
      if ($discountPct > 0) {
          $dpr = trim((string) ($_POST["discounted_price"] ?? ""));
          $disc_price = ($dpr !== "" && is_numeric($dpr)) ? (float) $dpr : ($price - ($price * ($discountPct / 100)));
      }
      $p_name = $_POST["product_name"] ?? "";
      $stock = $_POST["available"] ?? "";
      $description = $_POST["description"] ?? "";
      $language = trim((string) ($_POST["language"] ?? ""));
      $allowedLangs = EdiTaxonomy::allowedTitles($ediLanguages);
      if (!in_array($language, $allowedLangs, true)) {
          $language = "";
      }
      $author = $_POST["author"] ?? "";
      $isbn = trim((string) ($_POST["isbn"] ?? ""));
      $weightKgRaw = trim((string) ($_POST["weight_kg"] ?? ""));
      $weight_kg = ($weightKgRaw === "") ? null : max(0.0, (float) $weightKgRaw);
      $weight = ($weight_kg !== null && $weight_kg > 0)
          ? (rtrim(rtrim(number_format($weight_kg, 4, ".", ""), "0"), ".") . " kg")
          : "";

      $main_image = "";
      if (!empty($_FILES["main_image"]["name"]) && is_uploaded_file($_FILES["main_image"]["tmp_name"])) {
          $main_image = basename((string) $_FILES["main_image"]["name"]);
          move_uploaded_file($_FILES["main_image"]["tmp_name"], "../img/products/" . $main_image);
      }

      if (!$isDraft && $main_image === "") {
          $msg = "<div class='alert alert-danger'>Please choose a main image before publishing (Add).</div>";
      } else {
          $optsJson = null;
          if ($hasOptionsExtra) {
              $ks = isset($_POST["extra_opt_k"]) ? (array) $_POST["extra_opt_k"] : array();
              $vs = isset($_POST["extra_opt_v"]) ? (array) $_POST["extra_opt_v"] : array();
              $pairs = array();
              $n = max(count($ks), count($vs));
              for ($i = 0; $i < $n; $i++) {
                  $k = trim((string) ($ks[$i] ?? ""));
                  $v = trim((string) ($vs[$i] ?? ""));
                  if ($k !== "" || $v !== "") {
                      $pairs[] = array("k" => $k, "v" => $v);
                  }
              }
              $optsJson = $pairs === array() ? null : json_encode($pairs, JSON_UNESCAPED_UNICODE);
          }

          $galleryEnc = null;
          if ($hasGalleryImages) {
              $slots = array("", "", "");
              $imgDir = dirname(__DIR__) . "/img/products";
              for ($g = 1; $g <= 3; $g++) {
                  $fk = "gallery_" . $g;
                  $fn = EdiProductAdmin::saveUploadedProductImage(isset($_FILES[$fk]) ? $_FILES[$fk] : null, $imgDir);
                  if ($fn !== "") {
                      $slots[$g - 1] = $fn;
                  }
              }
              $galleryEnc = EdiProductAdmin::encodeGallerySlots($slots);
          }

          $moreDetailsVal = ($hasMoreDetails && isset($_POST["more_details"])) ? (string) $_POST["more_details"] : "";

          $columns = array(
              "category_id", "sub_category_id", "product_subcategory_id", "brand", "product_name", "price",
              "discount_percentage", "discounted_price", "age_group", "description", "language", "author", "isbn",
              "weight", "weight_kg", "stock", "image", "status",
          );
          $placeholders = array(
              ":cid", "NULL", ":pscid", ":brand", ":pname", ":price", ":disc", ":dprice", ":age", ":desc", ":lang",
              ":auth", ":isbn", ":weight", ":wkg", ":stock", ":img", ":status",
          );
          $exec = array(
              ":cid" => $category_id,
              ":pscid" => $product_subcategory_id,
              ":brand" => $brand,
              ":pname" => $p_name,
              ":price" => $price,
              ":disc" => $discountPct,
              ":dprice" => $disc_price,
              ":age" => $age_group,
              ":desc" => $description,
              ":lang" => $language,
              ":auth" => $author,
              ":isbn" => $isbn,
              ":weight" => $weight,
              ":wkg" => $weight_kg,
              ":stock" => $stock,
              ":img" => $main_image,
              ":status" => $status,
          );

          if ($hasMoreDetails) {
              $columns[] = "more_details";
              $placeholders[] = ":md";
              $exec[":md"] = $moreDetailsVal;
          }
          if ($hasGalleryImages) {
              $columns[] = "gallery_images";
              $placeholders[] = ":gal";
              $exec[":gal"] = $galleryEnc;
          }
          if ($hasOptionsExtra) {
              $columns[] = "options_extra";
              $placeholders[] = ":ox";
              $exec[":ox"] = $optsJson;
          }

          try {
              $sql = "INSERT INTO products (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
              $stmt = $user->runQuery($sql);
              $stmt->execute($exec);
              $msg = "<div class='alert alert-success'>" . ($isDraft ? "Draft saved." : "Product added successfully!") . "</div>";
          } catch (PDOException $e) {
              $msg = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
          }
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
  <script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>
  <script>if (typeof CKEDITOR !== "undefined") { CKEDITOR.config.versionCheck = false; }</script>
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
            <div class="card-body px-4 py-3 edi-product-form">
              <?php if (isset($msg)) {
                  echo $msg;
              } ?>
              <h2 class="text-uppercase text-danger font-weight-bold h5 mb-4 edi-product-section-title">Product information</h2>
              <form id="edi-add-product-form" method="post" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label>Category</label>
                    <select name="category" id="product_category_select" class="form-control" required>
                      <option value="">Select Category</option>
                      <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int) $cat["id"]; ?>"><?php echo htmlspecialchars($cat["name"]); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label>Brand</label>
                    <input type="text" name="brand" class="form-control" placeholder="Sarasavi">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label>Age group</label>
                    <select name="age_group" class="form-control">
                      <option value="">Select age group</option>
                      <?php foreach ($ediGrades as $gr): ?>
                        <option value="<?php echo htmlspecialchars((string) $gr["title"], ENT_QUOTES, "UTF-8"); ?>">
                          <?php echo htmlspecialchars((string) $gr["title"], ENT_QUOTES, "UTF-8"); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label>Sub category</label>
                    <select name="product_subcategory" id="product_subcategory_select" class="form-control" disabled>
                      <option value="-1" disabled selected class="edi-shop-sub-need-cat">Please select a category first to see subcategories.</option>
                      <option value="" class="edi-shop-sub-none" hidden disabled>Subcategory (optional)</option>
                      <?php foreach ($product_subcategories as $sub): ?>
                        <option value="<?php echo (int) $sub["id"]; ?>"
                          data-product-category-id="<?php echo (int) $sub["product_category_id"]; ?>">
                          <?php echo htmlspecialchars($sub["title"]); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="700.00">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label>Discount percentage <span class="text-danger">*</span></label>
                    <input type="number" name="discount" id="discount" class="form-control" placeholder="e.g. 5" min="0" max="100" step="0.01">
                    <small class="text-muted">Leave empty or 0 for no discount.</small>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label>Discounted price (Auto cal)</label>
                    <input type="text" id="discounted_price_display" class="form-control" readonly>
                    <input type="hidden" name="discounted_price" id="discounted_price_value">
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-8 mb-3">
                    <label>Product name</label>
                    <input type="text" name="product_name" class="form-control font-weight-bold" placeholder="ENGLISH ALPHABET BOOK" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label>Available</label>
                    <input type="number" name="available" class="form-control" placeholder="10">
                    <small class="text-muted">Auto decrease when sold.</small>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-3">
                    <label>Main description</label>
                    <textarea name="description" class="form-control" rows="6" placeholder="Short description for listings"></textarea>
                  </div>
                </div>

                <div class="row mt-2">
                  <div class="<?php echo $hasMoreDetails ? "col-md-6" : "col-12"; ?> mb-3">
                    <h6 class="font-weight-bold mb-3">Options</h6>
                    <div class="row mb-2">
                      <div class="col-5"><label class="form-label mb-0 d-block pt-2">Language</label></div>
                      <div class="col-7">
                        <select name="language" class="form-control">
                          <option value="">Select language</option>
                          <?php foreach ($ediLanguages as $lng): ?>
                            <option value="<?php echo htmlspecialchars((string) $lng["title"], ENT_QUOTES, "UTF-8"); ?>">
                              <?php echo htmlspecialchars((string) $lng["title"], ENT_QUOTES, "UTF-8"); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-5"><label class="form-label mb-0 d-block pt-2">Author</label></div>
                      <div class="col-7"><input type="text" name="author" class="form-control" placeholder="John Jones"></div>
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
                        <small class="text-muted">Optional; shipping uses total cart weight.</small>
                      </div>
                    </div>
                    <?php if ($hasOptionsExtra): ?>
                    <div id="edi-extra-opt-append">
                      <div class="row mb-2 edi-extra-opt-row">
                        <div class="col-5"><input type="text" class="form-control" name="extra_opt_k[]" placeholder="Key"></div>
                        <div class="col-7"><input type="text" class="form-control" name="extra_opt_v[]" placeholder="Value"></div>
                      </div>
                    </div>
                    <button type="button" id="edi-add-opt-row" class="btn btn-sm btn-success mt-2">ADD ANOTHER OPTION +</button>
                    <?php endif; ?>
                  </div>
                  <?php if ($hasMoreDetails): ?>
                  <div class="col-md-6 mb-3">
                    <h6 class="font-weight-bold mb-3">More details</h6>
                    <textarea name="more_details" id="more_details"></textarea>
                    <script>CKEDITOR.replace("more_details", { versionCheck: false });</script>
                  </div>
                  <?php endif; ?>
                </div>

                <div class="row mt-3">
                  <div class="<?php echo $hasGalleryImages ? "col-lg-6" : "col-12"; ?> mb-3">
                    <label>Main image</label>
                    <div class="d-flex flex-wrap align-items-start" style="gap:12px;">
                      <div style="flex:1;min-width:200px;max-width:320px;">
                        <input type="file" name="main_image" id="main_image_input" class="form-control" accept="image/*">
                      </div>
                      <div id="main_image_preview" class="edi-product-thumb border rounded bg-light d-flex align-items-center justify-content-center text-muted small" style="width:120px;height:120px;flex-shrink:0;">Preview</div>
                    </div>
                  </div>
                  <?php if ($hasGalleryImages): ?>
                  <div class="col-lg-6 mb-3">
                    <label>Other images (3)</label>
                    <?php for ($gi = 1; $gi <= 3; $gi++): ?>
                    <div class="d-flex flex-wrap align-items-start mb-2" style="gap:10px;">
                      <div style="flex:1;min-width:180px;max-width:280px;">
                        <input type="file" name="gallery_<?php echo $gi; ?>" id="gallery_<?php echo $gi; ?>_input" class="form-control" accept="image/*">
                      </div>
                      <div id="gallery_preview_<?php echo $gi; ?>" class="edi-product-thumb edi-product-thumb--sm border rounded bg-light d-flex align-items-center justify-content-center text-muted small" style="width:80px;height:80px;flex-shrink:0;"><?php echo $gi; ?></div>
                    </div>
                    <?php endfor; ?>
                  </div>
                  <?php endif; ?>
                </div>

                <div class="mt-4 mb-2 edi-admin-form-actions">
                  <button type="submit" name="btn-draft-product" class="btn btn-success" value="1">Draft</button>
                  <button type="submit" name="btn-add-product" class="btn btn-success" value="1">Add</button>
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

    (function () {
        function bindPreview(inputId, holderId) {
            const inp = document.getElementById(inputId);
            const holder = document.getElementById(holderId);
            if (!inp || !holder) return;
            inp.addEventListener("change", function () {
                const f = inp.files && inp.files[0];
                if (!f) return;
                const r = new FileReader();
                r.onload = function (ev) {
                    holder.innerHTML = "<img src=\"" + ev.target.result + "\" class=\"rounded\" style=\"width:100%;height:100%;object-fit:cover;\" alt=\"\">";
                };
                r.readAsDataURL(f);
            });
        }
        bindPreview("main_image_input", "main_image_preview");
        for (let i = 1; i <= 3; i++) {
            bindPreview("gallery_" + i + "_input", "gallery_preview_" + i);
        }
        const addOpt = document.getElementById("edi-add-opt-row");
        const optAppend = document.getElementById("edi-extra-opt-append");
        if (addOpt && optAppend) {
            addOpt.addEventListener("click", function () {
                const row = document.createElement("div");
                row.className = "row mb-2 edi-extra-opt-row";
                row.innerHTML = "<div class=\"col-5\"><input type=\"text\" class=\"form-control\" name=\"extra_opt_k[]\" placeholder=\"Key\"></div><div class=\"col-7\"><input type=\"text\" class=\"form-control\" name=\"extra_opt_v[]\" placeholder=\"Value\"></div>";
                optAppend.appendChild(row);
            });
        }
        const frm = document.getElementById("edi-add-product-form");
        if (frm) {
            frm.addEventListener("submit", function (e) {
                const sub = e.submitter;
                if (sub && sub.name === "btn-add-product") {
                    const inp = document.getElementById("main_image_input");
                    if (inp && (!inp.files || inp.files.length === 0)) {
                        e.preventDefault();
                        alert("Please choose a main image before Add (publish). Draft can be saved without an image.");
                    }
                }
            });
        }
    })();
</script>

  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>