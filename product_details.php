<?php

require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/class.widgets.php");
require_once("./classes/edi_discount_badge.php");
require_once("./classes/edi_shipping.php");
require_once("./classes/edi_product_admin.php");

$userHeader = new HEADER("shop");
$user = new USER();
$widgets = new WIDGETS();

// Product id: support product_id= and id= (older links)
$product_id = 0;
if (isset($_GET['product_id'])) {
    $product_id = (int) $_GET['product_id'];
} elseif (isset($_GET['id'])) {
    $product_id = (int) $_GET['id'];
}

$conn = $user->getConnection();
$product = null;
try {
    $query = "SELECT p.*, c.name AS category_name 
              FROM products p 
              LEFT JOIN product_categories c ON c.id = p.category_id 
              WHERE p.id = :product_id AND p.status = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute([':product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $product = null;
    try {
        $stmt2 = $conn->prepare("SELECT * FROM products WHERE id = :product_id AND status = 1");
        $stmt2->execute([':product_id' => $product_id]);
        $product = $stmt2->fetch(PDO::FETCH_ASSOC);
        if (is_array($product) && !array_key_exists('category_name', $product)) {
            $product['category_name'] = '';
        }
    } catch (Throwable $e2) {
        $product = null;
    }
}

if (!$product || !is_array($product)) {
    header('HTTP/1.0 404 Not Found');
    echo "<h2>Product not found!</h2>";
    exit;
}

// Stock quantity
$stock = (int) $product['stock'];

// Reviews (table may be missing on older installs — do not break the whole page)
$reviews = array();
try {
    $revStmt = $conn->prepare(
        "SELECT id, product_id, name, email, rating, review, created_at 
         FROM product_review 
         WHERE product_id = :pid 
         ORDER BY created_at DESC"
    );
    $revStmt->execute([':pid' => (int) $product['id']]);
    $fetched = $revStmt->fetchAll(PDO::FETCH_ASSOC);
    if (is_array($fetched)) {
        $reviews = $fetched;
    }
} catch (Throwable $e) {
    $reviews = array();
}

$totalReviews = count($reviews);
$averageRating = 0;

if ($totalReviews > 0) {
    $sum = 0;
    foreach ($reviews as $r) {
        $sum += (int) $r['rating'];
    }
    $averageRating = $sum / $totalReviews;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader() ?>
    <link rel="stylesheet" href="css/product_details.css">
</head>
<body class="product-details-page">
    <?php echo $userHeader->printUserNav(); ?>

    <div class="page-header-bg"></div>
    <div class="container-fluid mt-5 page-header-content edi-treasure-detail-outer">
        <div class="container edi-treasure-detail">
        <nav class="edi-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-0 flex-wrap">
                <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="./product_page.php">The Honey Market</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></li>
            </ol>
        </nav>

        <div class="edi-page-title-row edi-treasure-detail-heading">
            <h1>TREASURE DETAILS</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

        <div class="row edi-treasure-detail-main justify-content-center">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="edi-treasure-gallery">
                    <div class="edi-treasure-gallery-main">
                        <?php
                        $galleryDiscountPct = edi_discount_badge_pct($product);
                        if ($galleryDiscountPct !== null) {
                            echo '<span class="edi-discount-hex edi-discount-hex--large" aria-label="' . (int) $galleryDiscountPct . ' percent off">' . (int) $galleryDiscountPct . '%</span>';
                        }
                        ?>
                        <img src="./img/products/<?= htmlspecialchars((string) $product['image'], ENT_QUOTES, 'UTF-8') ?>" class="img-fluid main-product-image" alt="<?= htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="edi-treasure-gallery-thumbs" aria-hidden="true">
                        <?php
                        $mainSrc = "./img/products/" . htmlspecialchars((string) $product['image'], ENT_QUOTES, 'UTF-8');
                        $galSlotsFe = EdiProductAdmin::gallerySlotsFromDb((string) ($product['gallery_images'] ?? ''));
                        $allImages = array($mainSrc);
                        foreach ($galSlotsFe as $gs) {
                            if ($gs !== '') {
                                $allImages[] = "./img/products/" . htmlspecialchars($gs, ENT_QUOTES, 'UTF-8');
                            }
                        }
                        foreach ($allImages as $ti => $imgSrc) {
                            $active = $ti === 0 ? ' active' : '';
                            echo '<div class="edi-treasure-thumb' . $active . '" data-full="' . $imgSrc . '"><img src="' . $imgSrc . '" class="img-fluid" alt=""></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-left product-details-info">
                <h2 class="edi-treasure-product-title"><?= strtoupper(htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8')) ?></h2>
                <div class="price-box edi-treasure-price-row mb-3">
                    <?php if ((float) $product['discounted_price'] > 0): ?>
                        <span class="old-price">LKR <?= number_format((float) $product['price'], 2, '.', '') ?></span>
                        <span class="new-price text-success">LKR <?= number_format((float) $product['discounted_price'], 2, '.', '') ?></span>
                    <?php else: ?>
                        <span class="new-price">LKR <?= number_format((float) $product['price'], 2, '.', '') ?></span>
                    <?php endif; ?>
                </div>
                <p class="product-details-lead mb-4"><?= nl2br(htmlspecialchars((string) ($product['description'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>

                <?php
                $ageLabel = trim((string) ($product['age_group'] ?? ''));
                $ageDisplay = $ageLabel;
                if ($ageLabel !== '' && !preg_match('/\b(yrs?|years?)\b/i', $ageLabel)) {
                    $ageDisplay = $ageLabel . ' yrs';
                }
                $catTag = trim((string) ($product['category_name'] ?? 'Books'));

                $metaHas = static function ($v) {
                    $s = trim((string) $v);
                    if ($s === '') {
                        return false;
                    }
                    $placeholders = array('—', '-', '–', '−', 'n/a', 'na', 'none', 'tbd');
                    if (in_array($s, $placeholders, true) || in_array(strtolower($s), array('n/a', 'na', 'none', 'tbd'), true)) {
                        return false;
                    }
                    return true;
                };

                $metaRows = array();
                if ($metaHas($product['language'] ?? '')) {
                    $metaRows[] = array('Language', trim((string) $product['language']));
                }
                if ($metaHas($product['author'] ?? '')) {
                    $metaRows[] = array('Author', trim((string) $product['author']));
                }
                if ($metaHas($product['isbn'] ?? '')) {
                    $metaRows[] = array('ISBN', trim((string) $product['isbn']));
                }
                if ($metaHas($ageLabel)) {
                    $metaRows[] = array('Grade', $ageLabel);
                }
                if ($metaHas($product['brand'] ?? '')) {
                    $metaRows[] = array('Publisher', trim((string) $product['brand']));
                }
                $kgForMeta = EdiShipping::productKgFromRow($product);
                if ($metaHas($product['weight'] ?? '')) {
                    $metaRows[] = array('Weight', trim((string) $product['weight']));
                } elseif ($kgForMeta > 0) {
                    $metaRows[] = array('Weight', rtrim(rtrim(sprintf('%.4f', $kgForMeta), '0'), '.') . ' kg');
                }
                if (!empty($product['options_extra'])) {
                    $extraOpts = json_decode((string) $product['options_extra'], true);
                    if (is_array($extraOpts)) {
                        foreach ($extraOpts as $ex) {
                            $ek = trim((string) ($ex['k'] ?? ''));
                            $ev = trim((string) ($ex['v'] ?? ''));
                            if ($ek !== '' && $ev !== '') {
                                $metaRows[] = array($ek, $ev);
                            }
                        }
                    }
                }
                ?>
                <?php if (count($metaRows) > 0): ?>
                <table class="table table-borderless edi-treasure-meta-table mb-4">
                    <tbody>
                        <?php foreach ($metaRows as $mr): ?>
                        <tr>
                            <th scope="row"><?= htmlspecialchars($mr[0], ENT_QUOTES, 'UTF-8') ?></th>
                            <td><?= htmlspecialchars($mr[1], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <form class="edi-treasure-cart-row d-flex flex-wrap align-items-center mb-3" id="productDetailsCartForm" method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <div class="quantity-selector-container">
                        <button type="button" class="qty-btn" id="decreaseBtn">−</button>
                        <input type="number" id="quantity" name="quantity" min="1" max="<?= $stock ?>" value="1" class="qty-input" readonly>
                        <button type="button" class="qty-btn" id="increaseBtn">+</button>
                    </div>
                    <button type="submit" class="collect-btn add-to-cart-btn" id="addToCartBtn">Collect</button>
                    <div class="stock-status-text"><?= (int) $stock ?> In Stock</div>
                    <button type="button" class="btn btn-link text-danger edi-wishlist-btn p-0 ml-lg-2" title="Wishlist" aria-label="Add to wishlist">♥</button>
                </form>

                <p class="product-tags">Tags: <?= htmlspecialchars($catTag, ENT_QUOTES, 'UTF-8') ?><?= $ageLabel !== '' ? ', ' . htmlspecialchars($ageDisplay, ENT_QUOTES, 'UTF-8') : '' ?><?= !empty($product['brand']) ? ', ' . htmlspecialchars((string) $product['brand'], ENT_QUOTES, 'UTF-8') : '' ?></p>
            </div>
        </div>
        <div class="details-tabs mt-5">

    <!-- TAB BUTTONS -->
    <div class="tab-buttons">
        <button type="button" class="tab-btn active" data-tab="details" onclick="showTab('details')">More Details</button>
        <button type="button" class="tab-btn" data-tab="reviews" onclick="showTab('reviews')">Reviews</button>
    </div>

    <hr class="custom-hr" aria-hidden="true">

    <!-- DETAILS TAB -->
    <div id="details" class="tab-content active">
        <div class="edi-more-details-body">
            <?php
            $md = (string) ($product['more_details'] ?? '');
            echo $md !== '' ? $md : '<p class="text-muted">No extra details for this treasure yet.</p>';
            ?>
        </div>
    </div>

    <!-- REVIEWS TAB -->
    <div id="reviews" class="tab-content">

        <div class="reviews-section mt-3">

            <?php if (isset($_GET['review_submitted'])): ?>
                <div class="alert alert-success mt-2 mb-3">
                    Thank you! Your review has been added.
                </div>
            <?php elseif (isset($_GET['review_error'])): ?>
                <div class="alert alert-danger mt-2 mb-3">
                    There was a problem submitting your review. Please check the form and try again.
                </div>
            <?php endif; ?>

            <?php if ($totalReviews > 0): ?>
                <div class="review">
                    <strong><?= number_format($averageRating, 1) ?>/5</strong>
                    (<?= $totalReviews ?> Ratings)
                </div>

                <?php foreach ($reviews as $r): ?>
                    <div class="review">
                        <strong><?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <div class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= (int) $r['rating'] ? '⭐' : '☆' ?>
                            <?php endfor; ?>
                        </div>
                        <?php if (!empty($r['review'])): ?>
                            <p><?= nl2br(htmlspecialchars($r['review'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>
                        <span><?= date('d F, Y', strtotime($r['created_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="review">
                    <p>No reviews yet. Be the first to review this treasure!</p>
                </div>
            <?php endif; ?>
        </div>
        </div>

        <hr class="custom-hr custom-hr--before-review" aria-hidden="true">

        <div class="write-review-container">
            <h3 class="review-title">WRITE A REVIEW</h3>
            
            <form method="POST" action="submit_review.php" class="review-form">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <div class="rating-group mb-2">
                    <label class="form-label d-block mb-1">Rate your experience (required)</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required /><label for="star5" title="5 stars"></label>
                        <input type="radio" id="star4" name="rating" value="4" required /><label for="star4" title="4 stars"></label>
                        <input type="radio" id="star3" name="rating" value="3" required /><label for="star3" title="3 stars"></label>
                        <input type="radio" id="star2" name="rating" value="2" required /><label for="star2" title="2 stars"></label>
                        <input type="radio" id="star1" name="rating" value="1" required /><label for="star1" title="1 star"></label>
                    </div>
                </div>

                <div class="form-row mb-3">
                    <div class="col-md-4">
                        <label for="name" class="form-label">Name (required)</label>
                        <input type="text" name="name" id="name" class="form-input" required>
                    </div>
                    <div class="col-md-8">
                        <label for="email" class="form-label">E-mail (required)</label>
                        <input type="email" name="email" id="email" class="form-input" required>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label for="review" class="form-label">Leave a review (Optional)</label>
                    <textarea name="review" id="review" rows="4" class="form-input-textarea"></textarea>
                </div>

                <button type="submit" class="submit-review-btn">SUBMIT</button>
            </form>
        </div>

    </div>
        </div>
    </div>

    <?php echo $userHeader->printUserFooter(); ?>

    <script>
        // Pass login status to JavaScript
        var isLoggedIn = <?php echo isset($_SESSION['session_tourism_user']) ? 'true' : 'false'; ?>;
        
        // Quantity button functions
        document.getElementById("decreaseBtn").addEventListener("click", function() {
            var qty = document.getElementById("quantity");
            var currentVal = parseInt(qty.value);
            if (currentVal > 1) {
                qty.value = currentVal - 1;
            }
        });

        document.getElementById("increaseBtn").addEventListener("click", function() {
            var qty = document.getElementById("quantity");
            var currentVal = parseInt(qty.value);
            if (currentVal < <?= $stock ?>) {
                qty.value = currentVal + 1;
            }
        });

        // Cart: require login uid (add_to_cart.php), match JSON shape from add_to_cart.php
        document.querySelectorAll(".add-to-cart-btn").forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();

                const userSession = localStorage.getItem("user_session");
                if (!userSession) {
                    if (typeof showLoginPopup === "function") {
                        showLoginPopup();
                    } else {
                        window.location.href = "./login";
                    }
                    return;
                }

                const form = this.closest("form");
                const productImage = document.querySelector(".main-product-image");
                const cartIcon = document.querySelector("#cart-icon") || document.querySelector(".fa-shopping-cart");

                const qtyInput = document.getElementById("quantity");
                const quantity = parseInt(qtyInput ? qtyInput.value : "1", 10) || 1;
                if (quantity > <?= $stock ?>) {
                    alert("Not enough stock available.");
                    return;
                }

                if (!productImage) {
                    if (form) form.submit();
                    return;
                }

                const imgClone = productImage.cloneNode(true);
                const rect = productImage.getBoundingClientRect();
                const cartRect = cartIcon ? cartIcon.getBoundingClientRect() : null;

                imgClone.style.position = "fixed";
                imgClone.style.left = rect.left + "px";
                imgClone.style.top = rect.top + "px";
                imgClone.style.width = rect.width + "px";
                imgClone.style.zIndex = 9999;
                imgClone.style.transition = "all 0.8s ease-in-out";

                document.body.appendChild(imgClone);

                setTimeout(() => {
                    if (cartRect) {
                        imgClone.style.left = cartRect.left + "px";
                        imgClone.style.top = cartRect.top + "px";
                        imgClone.style.width = "20px";
                        imgClone.style.opacity = "0.3";
                    }
                }, 10);

                const formData = new FormData(form);
                formData.append("uid", userSession);

                setTimeout(() => {
                    imgClone.remove();

                    if (form) {
                        fetch("add_to_cart.php", {
                            method: "POST",
                            credentials: "same-origin",
                            headers: { "X-Requested-With": "XMLHttpRequest" },
                            body: formData
                        })
                            .then(function (response) { return response.json(); })
                            .then(function (data) {
                                var ok = data && (data.status === "success" || data.success === true);
                                if (!ok) {
                                    alert("Failed to add to cart");
                                    return;
                                }
                                var addedQty = data.quantity || 1;
                                var count = localStorage.getItem("cart_count");
                                count = count ? parseInt(count, 10) : 0;
                                localStorage.setItem("cart_count", String(count + addedQty));
                                if (typeof window.edibearSyncCartBadge === "function") {
                                    window.edibearSyncCartBadge();
                                }
                                if (cartIcon) {
                                    cartIcon.classList.add("bounce");
                                    setTimeout(function () { cartIcon.classList.remove("bounce"); }, 400);
                                }
                            })
                            .catch(function (err) {
                                console.error(err);
                                alert("Failed to add to cart");
                            });
                    }

                    if (cartIcon) {
                        cartIcon.classList.add("bounce");
                        setTimeout(function () { cartIcon.classList.remove("bounce"); }, 400);
                    }
                }, 800);
            });
        });
    </script>
    <script>
document.querySelectorAll('.edi-treasure-thumb').forEach(function(thumb) {
    thumb.addEventListener('click', function() {
        var src = this.getAttribute('data-full');
        if (!src) return;
        var main = document.querySelector('.main-product-image');
        if (main) main.src = src;
        document.querySelectorAll('.edi-treasure-thumb').forEach(function(t) { t.classList.remove('active'); });
        this.classList.add('active');
    });
});
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(function (el) { el.classList.remove('active'); });
    document.querySelectorAll('.tab-btn').forEach(function (el) { el.classList.remove('active'); });
    var panel = document.getElementById(tab);
    if (panel) { panel.classList.add('active'); }
    document.querySelectorAll('.tab-btn[data-tab="' + tab + '"]').forEach(function (el) { el.classList.add('active'); });
}
</script>
</body>
</html>