<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/edi_taxonomy.php");
require_once("../classes/edi_explorer_content.php");
require_once("../classes/edi_product_admin.php");
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

$ediProductHasShopCategory = (int) ($product["category_id"] ?? 0) > 0;

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
$hasPsub = EdiExplorerContent::columnExists($pdo, "products", "product_subcategory_id");

$msg = "";

if (isset($_POST["btn-delete-product"])) {
    $delId = (int) ($_POST["product_id"] ?? 0);
    if ($delId !== $id || $delId < 1) {
        header("Location: ./products");
        exit;
    }
    $imgDirFs = dirname(__DIR__) . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "products";
    $mainImg = basename(str_replace("\\", "/", (string) ($product["image"] ?? "")));
    if ($mainImg !== "") {
        $pMain = $imgDirFs . DIRECTORY_SEPARATOR . $mainImg;
        if (is_file($pMain)) {
            @unlink($pMain);
        }
    }
    if ($hasGalleryImages) {
        foreach (EdiProductAdmin::gallerySlotsFromDb((string) ($product["gallery_images"] ?? "")) as $gf) {
            $gf = basename(str_replace("\\", "/", trim((string) $gf)));
            if ($gf === "") {
                continue;
            }
            $gp = $imgDirFs . DIRECTORY_SEPARATOR . $gf;
            if (is_file($gp)) {
                @unlink($gp);
            }
        }
    }
    try {
        $pdoDel = $user->getConnection();
        $chkOi = $pdoDel->query("SHOW TABLES LIKE " . $pdoDel->quote("order_items"));
        if ($chkOi && $chkOi->rowCount() > 0) {
            $stOi = $pdoDel->prepare("DELETE FROM order_items WHERE product_id = :pid");
            $stOi->execute(array(":pid" => $delId));
        }
    } catch (Throwable $e) {
        // continue; product delete may still succeed
    }
    try {
        $user->deleteTableRow("products", array("id" => $delId));
        header("Location: ./products");
        exit;
    } catch (PDOException $e) {
        $msg = "<div class='alert alert-danger'>Could not delete product: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

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
    $prevLang = trim((string) ($product["language"] ?? ""));
    if ($language !== "" && !in_array($language, $allowedLangs, true) && $language !== $prevLang) {
        $language = $prevLang;
    }
    $author = $_POST["author"] ?? "";
    $isbn = trim((string) ($_POST["isbn"] ?? ""));
    $weightKgRaw = trim((string) ($_POST["weight_kg"] ?? ""));
    $weight_kg = ($weightKgRaw === "") ? null : max(0.0, (float) $weightKgRaw);
    $weight = ($weight_kg !== null && $weight_kg > 0)
        ? (rtrim(rtrim(number_format($weight_kg, 4, ".", ""), "0"), ".") . " kg")
        : "";

    $main_image = $product["image"];
    if (!empty($_FILES["main_image"]["name"]) && is_uploaded_file($_FILES["main_image"]["tmp_name"])) {
        $main_image = basename((string) $_FILES["main_image"]["name"]);
        $target = "../img/products/" . $main_image;
        move_uploaded_file($_FILES["main_image"]["tmp_name"], $target);
    }

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
        $slots = EdiProductAdmin::gallerySlotsFromDb((string) ($product["gallery_images"] ?? ""));
        $imgDir = dirname(__DIR__) . "/img/products";
        for ($g = 1; $g <= EdiProductAdmin::GALLERY_SLOTS; $g++) {
            $fk = "gallery_" . $g;
            $newFn = EdiProductAdmin::saveUploadedProductImage(isset($_FILES[$fk]) ? $_FILES[$fk] : null, $imgDir);
            if ($newFn !== "") {
                $slots[$g - 1] = $newFn;
            } else {
                $slots[$g - 1] = isset($_POST["gallery_keep_" . $g]) ? trim((string) $_POST["gallery_keep_" . $g]) : $slots[$g - 1];
            }
        }
        $galleryEnc = EdiProductAdmin::encodeGallerySlots($slots);
    }

    try {
        $sets = array("category_id=:cid", "sub_category_id=NULL");
        $exec = array(":cid" => $category_id, ":id" => $id);
        if ($hasPsub) {
            $sets[] = "product_subcategory_id=:pscid";
            $exec[":pscid"] = $product_subcategory_id;
        }
        $sets = array_merge(
            $sets,
            array(
                "brand=:brand",
                "product_name=:pname",
                "price=:price",
                "discount_percentage=:disc",
                "discounted_price=:dprice",
                "age_group=:age",
                "description=:desc",
                "language=:lang",
                "author=:auth",
                "isbn=:isbn",
                "weight=:weight",
                "weight_kg=:wkg",
                "stock=:stock",
                "image=:img",
            )
        );
        $exec[":brand"] = $brand;
        $exec[":pname"] = $p_name;
        $exec[":price"] = $price;
        $exec[":disc"] = $discountPct;
        $exec[":dprice"] = $disc_price;
        $exec[":age"] = $age_group;
        $exec[":desc"] = $description;
        $exec[":lang"] = $language;
        $exec[":auth"] = $author;
        $exec[":isbn"] = $isbn;
        $exec[":weight"] = $weight;
        $exec[":wkg"] = $weight_kg;
        $exec[":stock"] = $stock;
        $exec[":img"] = $main_image;
        if ($hasMoreDetails) {
            $sets[] = "more_details=:md";
            $exec[":md"] = isset($_POST["more_details"]) ? (string) $_POST["more_details"] : "";
        }
        if ($hasGalleryImages) {
            $sets[] = "gallery_images=:gal";
            $exec[":gal"] = $galleryEnc;
        }
        if ($hasOptionsExtra) {
            $sets[] = "options_extra=:ox";
            $exec[":ox"] = $optsJson;
        }

        $sql = "UPDATE products SET " . implode(", ", $sets) . " WHERE id=:id";
        $stmt = $user->runQuery($sql);
        $stmt->execute($exec);
        $msg = "<div class='alert alert-success'>Product updated.</div>";
        $st = $user->runQuery("SELECT * FROM products WHERE id = :id LIMIT 1");
        $st->execute([":id" => $id]);
        $product = $st->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $msg = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

$galSlots = $hasGalleryImages ? EdiProductAdmin::gallerySlotsFromDb((string) ($product["gallery_images"] ?? "")) : array_fill(0, EdiProductAdmin::GALLERY_SLOTS, "");
$decodedExtraOpts = array();
if ($hasOptionsExtra) {
    if (!empty($product["options_extra"])) {
        $d = json_decode((string) $product["options_extra"], true);
        if (is_array($d)) {
            foreach ($d as $pair) {
                if (is_array($pair)) {
                    $decodedExtraOpts[] = array(
                        "k" => trim((string) ($pair["k"] ?? "")),
                        "v" => trim((string) ($pair["v"] ?? "")),
                    );
                }
            }
        }
    }
    $decodedExtraOpts[] = array("k" => "", "v" => "");
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
  <script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>
  <script>if (typeof CKEDITOR !== "undefined") { CKEDITOR.config.versionCheck = false; }</script>
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
              <span class="text-muted small font-weight-bold text-uppercase">Edit product</span>
              <a href="./products" class="btn btn-sm btn-outline-secondary">Back to list</a>
            </div>
            <div class="card-body px-4 py-3 edi-product-form">
              <?php echo $msg; ?>
              <h2 class="text-uppercase text-danger font-weight-bold h5 mb-4 edi-product-section-title">Product information</h2>
              <form id="edi-edit-product-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo (int) $id; ?>">
                <div class="row">
                  <div class="col-md-4 mb-3">
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
                  <div class="col-md-4 mb-3">
                    <label>Brand</label>
                    <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars((string) ($product["brand"] ?? "")); ?>">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label>Age group</label>
                    <select name="age_group" class="form-control">
                      <option value="">Select age group</option>
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

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label>Sub category</label>
                    <select name="product_subcategory" id="product_subcategory_select" class="form-control"<?php echo $ediProductHasShopCategory ? "" : " disabled"; ?>>
                      <option value="-1" disabled class="edi-shop-sub-need-cat"<?php echo !$ediProductHasShopCategory ? " selected" : ""; ?>>Please select a category first to see subcategories.</option>
                      <option value="" class="edi-shop-sub-none"<?php echo ($ediProductHasShopCategory && (int) ($product["product_subcategory_id"] ?? 0) === 0) ? " selected" : ""; ?> <?php echo !$ediProductHasShopCategory ? "hidden disabled" : ""; ?>>Subcategory (optional)</option>
                      <?php foreach ($product_subcategories as $sub): ?>
                        <option value="<?php echo (int) $sub["id"]; ?>"
                          data-product-category-id="<?php echo (int) $sub["product_category_id"]; ?>"
                          <?php echo (isset($product["product_subcategory_id"]) && (int) $product["product_subcategory_id"] === (int) $sub["id"]) ? "selected" : ""; ?>>
                          <?php echo htmlspecialchars($sub["title"]); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars((string) $product["price"]); ?>">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label>Discount percentage <span class="text-danger">*</span></label>
                    <input type="number" name="discount" id="discount" class="form-control" value="<?php echo htmlspecialchars((string) $product["discount_percentage"]); ?>" min="0" max="100" step="0.01" placeholder="0">
                    <small class="text-muted">Leave empty or 0 for no discount.</small>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label>Discounted price (Auto cal)</label>
                    <input type="text" id="discounted_price_display" class="form-control" readonly>
                    <input type="hidden" name="discounted_price" id="discounted_price_value" value="<?php echo htmlspecialchars((string) $product["discounted_price"]); ?>">
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-8 mb-3">
                    <label>Product name</label>
                    <input type="text" name="product_name" class="form-control font-weight-bold" required value="<?php echo htmlspecialchars((string) $product["product_name"]); ?>">
                  </div>
                  <div class="col-md-4 mb-3">
                    <label>Available</label>
                    <input type="number" name="available" class="form-control" value="<?php echo htmlspecialchars((string) $product["stock"]); ?>">
                    <small class="text-muted">Auto decrease when sold.</small>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12 mb-3">
                    <label>Main description</label>
                    <textarea name="description" class="form-control" rows="6"><?php echo htmlspecialchars((string) ($product["description"] ?? "")); ?></textarea>
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
                      <div class="col-5"><label class="form-label mb-0 d-block pt-2">Author</label></div>
                      <div class="col-7"><input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars((string) ($product["author"] ?? "")); ?>"></div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-5"><label class="form-label mb-0 d-block pt-2">ISBN</label></div>
                      <div class="col-7">
                        <input type="text" name="isbn" class="form-control" value="<?php echo htmlspecialchars((string) ($product["isbn"] ?? "")); ?>" placeholder="978-3-16-148410-0">
                        <small class="text-muted">Optional; shown on the product page when filled.</small>
                      </div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-5"><label class="form-label mb-0 d-block pt-2">Weight (kg)</label></div>
                      <div class="col-7">
                        <?php
                        $wkVal = "";
                        if (isset($product["weight_kg"]) && $product["weight_kg"] !== null && $product["weight_kg"] !== "") {
                            $wkVal = (string) (0 + (float) $product["weight_kg"]);
                        }
                        ?>
                        <input type="number" name="weight_kg" class="form-control" step="0.0001" min="0" value="<?php echo htmlspecialchars($wkVal, ENT_QUOTES, "UTF-8"); ?>" placeholder="e.g. 0.25">
                        <small class="text-muted">Optional; shipping uses total cart weight.</small>
                      </div>
                    </div>
                    <?php if ($hasOptionsExtra): ?>
                    <div id="edi-extra-opt-append">
                      <?php foreach ($decodedExtraOpts as $ex): ?>
                      <div class="row mb-2 edi-extra-opt-row">
                        <div class="col-5"><input type="text" class="form-control" name="extra_opt_k[]" placeholder="Key" value="<?php echo htmlspecialchars($ex["k"], ENT_QUOTES, "UTF-8"); ?>"></div>
                        <div class="col-7"><input type="text" class="form-control" name="extra_opt_v[]" placeholder="Value" value="<?php echo htmlspecialchars($ex["v"], ENT_QUOTES, "UTF-8"); ?>"></div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                    <button type="button" id="edi-add-opt-row" class="btn btn-sm btn-success mt-2">ADD ANOTHER OPTION +</button>
                    <?php endif; ?>
                  </div>
                  <?php if ($hasMoreDetails): ?>
                  <div class="col-md-6 mb-3">
                    <h6 class="font-weight-bold mb-3">More details</h6>
                    <textarea name="more_details" id="more_details"><?php echo htmlspecialchars((string) ($product["more_details"] ?? "")); ?></textarea>
                    <script>CKEDITOR.replace("more_details", { versionCheck: false });</script>
                  </div>
                  <?php endif; ?>
                </div>

                <div class="row mt-3">
                  <div class="<?php echo $hasGalleryImages ? "col-lg-6" : "col-12"; ?> mb-3">
                    <label>Main image</label>
                    <p class="text-xs text-muted mb-1">Current file: <code><?php echo htmlspecialchars((string) ($product["image"] ?? "")); ?></code></p>
                    <div class="d-flex flex-wrap align-items-start" style="gap:12px;">
                      <div style="flex:1;min-width:200px;max-width:320px;">
                        <input type="file" name="main_image" id="main_image_input" class="form-control" accept="image/*">
                        <small class="text-muted d-block mt-1">Leave empty to keep the current file.</small>
                      </div>
                      <div id="main_image_preview" class="edi-product-thumb border rounded bg-light d-flex align-items-center justify-content-center text-muted small overflow-hidden" style="width:120px;height:120px;flex-shrink:0;">
                        <?php if (!empty($product["image"])): ?>
                          <img src="/img/products/<?php echo rawurlencode($product["image"]); ?>" alt="" class="rounded" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                          Preview
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?php if ($hasGalleryImages): ?>
                  <div class="col-lg-6 mb-3">
                    <label>Other images (<?php echo EdiProductAdmin::GALLERY_SLOTS; ?>)</label>
                    <?php for ($gi = 1; $gi <= EdiProductAdmin::GALLERY_SLOTS; $gi++):
                        $slotFn = $galSlots[$gi - 1] ?? "";
                        ?>
                    <input type="hidden" name="gallery_keep_<?php echo $gi; ?>" value="<?php echo htmlspecialchars($slotFn, ENT_QUOTES, "UTF-8"); ?>">
                    <div class="d-flex flex-wrap align-items-start mb-2" style="gap:10px;">
                      <div style="flex:1;min-width:180px;max-width:280px;">
                        <input type="file" name="gallery_<?php echo $gi; ?>" id="gallery_<?php echo $gi; ?>_input" class="form-control" accept="image/*">
                      </div>
                      <div id="gallery_preview_<?php echo $gi; ?>" class="edi-product-thumb edi-product-thumb--sm border rounded bg-light d-flex align-items-center justify-content-center text-muted small overflow-hidden" style="width:80px;height:80px;flex-shrink:0;">
                        <?php if ($slotFn !== ""): ?>
                          <img src="/img/products/<?php echo rawurlencode($slotFn); ?>" alt="" class="rounded" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                          <?php echo $gi; ?>
                        <?php endif; ?>
                      </div>
                    </div>
                    <?php endfor; ?>
                  </div>
                  <?php endif; ?>
                </div>

                <div class="mt-4 mb-2 edi-admin-form-actions">
                  <button type="submit" name="btn-update-product" class="btn btn-success" value="1">Save changes</button>
                  <a href="./products" class="btn btn-secondary">Cancel</a>
                </div>
              </form>

              <form method="post" class="mt-2" onsubmit="return confirm('Delete this product and its images from the server? Cart rows are removed automatically. This cannot be undone.');">
                <input type="hidden" name="product_id" value="<?php echo (int) $id; ?>">
                <button type="submit" name="btn-delete-product" value="1" class="btn btn-danger btn-sm">Delete product</button>
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

    priceInput.addEventListener("input", calculate);
    discInput.addEventListener("input", calculate);
    calculate();

    (function () {
        const cat = document.getElementById("product_category_select");
        const sub = document.getElementById("product_subcategory_select");
        if (!cat || !sub) return;
        const needCat = sub.querySelector("option.edi-shop-sub-need-cat");
        const noneOpt = sub.querySelector("option.edi-shop-sub-none");
        function syncSubcategories() {
            const cid = String(cat.value || "");
            const realOpts = sub.querySelectorAll("option[data-product-category-id]");
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
            }
            const sel = String(sub.value || "");
            let stillOk = false;
            for (let j = 0; j < realOpts.length; j++) {
                const opt = realOpts[j];
                const ok = opt.getAttribute("data-product-category-id") === cid;
                opt.hidden = !ok;
                opt.disabled = !ok;
                if (ok && opt.value === sel) {
                    stillOk = true;
                }
            }
            if (!stillOk && noneOpt) {
                noneOpt.selected = true;
            }
        }
        cat.addEventListener("change", function () {
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
        for (let i = 1; i <= <?php echo EdiProductAdmin::GALLERY_SLOTS; ?>; i++) {
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
    })();
</script>

  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>
