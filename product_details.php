<?php

require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/class.widgets.php");

$userHeader = new HEADER("products");
$user = new USER();
$widgets = new WIDGETS();

// Get the product ID from the URL
$product_id = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

// Fetch the product details from the database
$conn = $user->getConnection();
$query = "SELECT * FROM products WHERE id = :product_id AND status = 1";
$stmt = $conn->prepare($query);
$stmt->execute([':product_id' => $product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<h2>Product not found!</h2>";
    exit;
}

// Stock quantity
$stock = (int) $product['stock'];

// Fetch reviews for this product
$reviews = $user->fetchAll(
    array("id", "product_id", "name", "email", "rating", "review", "created_at"),
    array("product_review"),
    array("product_id" => $product['id']),
    "created_at DESC"
);

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
<body>
    <?php echo $userHeader->printUserNav(); ?>

    <div class="page-header-bg"></div>
    <div class="container mt-5 page-header-content">
        <nav class="edi-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-0">
                <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="product_page.php">The Honey Market</a></li>
                <li class="breadcrumb-item active"><?= strtoupper($product['product_name']) ?></li>
            </ol>
        </nav>

        <div class="edi-page-title-row">
            <h1><?= strtoupper($product['product_name']) ?></h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

        <!-- Product Details Section -->
        <div class="row">
            <div class="col-md-6">
                <img src="./img/products/<?= $product['image'] ?>" class="img-fluid main-product-image">
            </div>
            <div class="col-md-6">
                <p><strong>Description: </strong><?= $product['description'] ?></p>
                <div class="price-box">
                    <?php if ($product['discounted_price'] > 0): ?>
                        <span class="old-price">LKR <?= $product['price'] ?>.00</span>
                        <span class="new-price text-success">LKR <?= $product['discounted_price'] ?>.00</span>
                    <?php else: ?>
                        <span class="new-price">LKR <?= $product['price'] ?>.00</span>
                    <?php endif; ?>
                </div>
                
                <p><strong>Age Group:</strong> <?= $product['age_group'] ?> years</p>
                <p><strong>Publisher:</strong> <?= $product['brand'] ?></p>
                <p><strong></strong> <?= $product['author'] ?></p>

                <!-- Quantity Input and Stock Availability -->
                <form class="d-flex align-items-center mb-3" style="gap: 15px;" id="productDetailsCartForm" method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div class="quantity-selector-container">
                        <button type="button" class="qty-btn" id="decreaseBtn">−</button>
                        <input type="number" id="quantity" name="quantity" min="1" max="<?= $stock ?>" value="1" class="qty-input" readonly>
                        <button type="button" class="qty-btn" id="increaseBtn">+</button>
                    </div>

                    <button type="submit" class="collect-btn add-to-cart-btn" id="addToCartBtn">Collect</button>

                    <div class="stock-status-text">
                        <?= $stock ?> In Stock
                    </div>
                </form>

                <p class="product-tags">Tags : <?= $product['category_name'] ?? 'Books' ?>, <?= $product['age_group'] ?> Year, <?= $product['brand'] ?></p>
            </div>
        </div>
        <div class="details-tabs mt-5">

    <!-- TAB BUTTONS -->
    <div class="tab-buttons">
        <button class="tab-btn active" onclick="showTab('details')">More Details</button>
        <button class="tab-btn" onclick="showTab('reviews')">Reviews</button>
    </div>

    <hr class="custom-hr">

    <!-- DETAILS TAB -->
    <div id="details" class="tab-content active">
        <p><?= nl2br($product['more_details']) ?></p>
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
        
        <!--m-->

        <div class="write-review-container">
            <div class="section-divider"></div>
            <h3 class="review-title">WRITE A REVIEW</h3>
            
            <form method="POST" action="submit_review.php" class="review-form">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <div class="rating-group mb-4">
                    <label class="form-label d-block mb-1">Rate your experience (required)</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required /><label for="star5" title="5 stars"></label>
                        <input type="radio" id="star4" name="rating" value="4" required /><label for="star4" title="4 stars"></label>
                        <input type="radio" id="star3" name="rating" value="3" required /><label for="star3" title="3 stars"></label>
                        <input type="radio" id="star2" name="rating" value="2" required /><label for="star2" title="2 stars"></label>
                        <input type="radio" id="star1" name="rating" value="1" required /><label for="star1" title="1 star"></label>
                    </div>
                </div>

                <div class="form-row mb-4">
                    <div class="col-md-4">
                        <label for="name" class="form-label">Name (required)</label>
                        <input type="text" name="name" id="name" class="form-input" required>
                    </div>
                    <div class="col-md-8">
                        <label for="email" class="form-label">E-mail (required)</label>
                        <input type="email" name="email" id="email" class="form-input" required>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="review" class="form-label">Leave a review (Optional)</label>
                    <textarea name="review" id="review" rows="4" class="form-input-textarea"></textarea>
                </div>

                <button type="submit" class="submit-review-btn">SUBMIT</button>
            </form>
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

        // Cart Animation and Add to Cart (same behavior as index.php / product_page.php)
        document.querySelectorAll(".add-to-cart-btn").forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();


                const form = this.closest("form");
                const productImage = document.querySelector(".main-product-image");
                const cartIcon = document.querySelector("#cart-icon") || document.querySelector(".fa-shopping-cart");

                // Basic stock validation
                const qtyInput = document.getElementById("quantity");
                const quantity = parseInt(qtyInput.value, 10);
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
                
                const userSession = localStorage.getItem('user_session');
                const formData = new FormData(form);
                formData.append('uid', userSession);

                setTimeout(() => {
                    imgClone.remove();

                    // SEND CART REQUEST WITHOUT PAGE RELOAD
                    if (form) {
                       fetch("add_to_cart.php", {
    method: "POST",
    credentials: "same-origin",
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: new FormData(form)
})
.then(response => response.json())
.then(data => {

    // ✅ ONLY update UI after success
    if (data.status === "success") {

        // increase cart count
        let count = localStorage.getItem('cart_count');
        count = count ? parseInt(count) : 0;
        localStorage.setItem('cart_count', count + 1);

        // show dot
        const dot = document.getElementById('cart-dot');
        if (dot) dot.style.display = 'block';

        // bounce effect
        if (cartIcon) {
            cartIcon.classList.add("bounce");
            setTimeout(() => cartIcon.classList.remove("bounce"), 400);
        }

    } else {
        alert("Failed to add to cart");
    }
})
.catch(err => {
    console.error(err);
});
                    }

                    // CART BOUNCE EFFECT
                    if (cartIcon) {
                        cartIcon.classList.add("bounce");
                        setTimeout(() => cartIcon.classList.remove("bounce"), 400);
                    }
                }, 800);
            });
        });
    </script>
    <script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

    document.getElementById(tab).classList.add('active');

    // FIX: use event safely
    const btns = document.querySelectorAll('.tab-btn');
    btns.forEach(btn => {
        if (btn.textContent.toLowerCase().includes(tab)) {
            btn.classList.add('active');
        }
    });
}
</script>
</body>
</html>