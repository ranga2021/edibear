<?php
// session_start(); // Can be removed if not using PHP sessions elsewhere
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/class.widgets.php");

$userHeader = new HEADER("shop");
$user = new USER();
$widgets = new WIDGETS();

// --- 1. Fetch Filter Options from DB ---
$categories = $user->fetchAll(array("id", "name"), array("product_categories"), array());
$ageGroups = $user->fetchAll(array("DISTINCT age_group"), array("products"), array("status" => 1));
$brands = $user->fetchAll(array("DISTINCT brand"), array("products"), array("status" => 1));

// Get unique values for dropdowns
$conn = $user->getConnection();

// --- 2. Handle Filtering Logic ---
$catF = isset($_GET['category']) ? $_GET['category'] : '';
$ageF = isset($_GET['age']) ? $_GET['age'] : '';
$brandF = isset($_GET['brand']) ? $_GET['brand'] : '';
$priceF = isset($_GET['price']) ? $_GET['price'] : '';
$offerF = isset($_GET['offers']) ? $_GET['offers'] : '';

$query = "SELECT * FROM products WHERE status = 1";
$params = [];

if(!empty($catF)) { $query .= " AND category_id = :cat"; $params[':cat'] = $catF; }
if(!empty($ageF)) { $query .= " AND age_group = :age"; $params[':age'] = $ageF; }
if(!empty($brandF)) { $query .= " AND brand = :brand"; $params[':brand'] = $brandF; }

if($offerF == 'available') { 
    $query .= " AND discount_percentage > 0"; 
}

if(!empty($priceF)) {
    if($priceF == 'low') $query .= " ORDER BY discounted_price ASC";
    elseif($priceF == 'high') $query .= " ORDER BY discounted_price DESC";
} else {
    $query .= " ORDER BY id DESC";
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader() ?>
    <link rel="stylesheet" href="css/product_style.css">
</head>
<body>
    <?php echo $userHeader->printUserNav(); ?>
    <div class="page-header-bg"></div>
   

    <div class="container mt-5 page-header-content">
         <div class="col-lg-8 ">
                <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-0">
                        <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">The Honey Market</li>
                    </ol>
                </nav>
                 
                  <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>TREASURES</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>
                    

            </div>
        

        <form method="GET" action="" class="treasures-filters-form" id="treasures-filters-form" aria-label="Filter treasures">
            <div class="treasures-filters-row">
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-category">Category</label>
                    <select id="filter-category" name="category" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Category</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= htmlspecialchars((string) $c['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($catF == (string) $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-age">Age</label>
                    <select id="filter-age" name="age" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Age</option>
                        <?php foreach ($ageGroups as $a): ?>
                            <option value="<?= htmlspecialchars((string) $a['age_group'], ENT_QUOTES, 'UTF-8') ?>" <?= ($ageF === $a['age_group']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $a['age_group'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-brand">Brands</label>
                    <select id="filter-brand" name="brand" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Brands</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= htmlspecialchars((string) $b['brand'], ENT_QUOTES, 'UTF-8') ?>" <?= ($brandF === $b['brand']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $b['brand'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-price">Price</label>
                    <select id="filter-price" name="price" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Price</option>
                        <option value="low" <?= ($priceF === 'low') ? 'selected' : '' ?>>Low to High</option>
                        <option value="high" <?= ($priceF === 'high') ? 'selected' : '' ?>>High to Low</option>
                    </select>
                </div>
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-offers">Offers</label>
                    <select id="filter-offers" name="offers" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Offers</option>
                        <option value="available" <?= ($offerF === 'available') ? 'selected' : '' ?>>Available</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="row treasures-product-grid">
            <?php if(empty($products)): ?>
                <div class="col-12 text-center py-5"><h4>No treasures found!</h4></div>
            <?php else: foreach($products as $p): ?>
                <div class="col-lg-3 col-md-4 col-6 mb-4">
                    <div class="treasure-card">
                        <div class="img-container">
                            <a href="product_details.php?product_id=<?= $p['id'] ?>">
                               <img src="./img/products/<?= $p['image'] ?>" class="img-fluid cart-product-image">
                            </a>
                        </div>
                        <h6 class="product-name"><?= strtoupper($p['product_name']) ?></h6>
                        <div class="price-box">
                            <?php if ((float) $p['discounted_price'] > 0): ?>
                                <span class="old-price">LKR <?= number_format((float) $p['price'], 2, '.', '') ?></span>
                                <span class="new-price text-success">LKR <?= number_format((float) $p['discounted_price'], 2, '.', '') ?></span>
                            <?php else: ?>
                                <span class="new-price">LKR <?= number_format((float) $p['price'], 2, '.', '') ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <form class="cart-form">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button type="button" class="collect-btn add-to-cart-btn">Collect</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <?php echo $userHeader->printUserFooter(); ?>

    <script>
        document.querySelectorAll(".add-to-cart-btn").forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();

                // 1. Check LocalStorage for User Session
                const userSession = localStorage.getItem('user_session');

                if (!userSession) {
                    // If your system uses a function showLoginPopup(), call it. 
                    // Otherwise, redirect to login.
                    if (typeof showLoginPopup === "function") {
                        showLoginPopup();
                    } else {
                        window.location.href = './login';
                    }
                    return;
                }

                const form = this.closest("form");
                const productCard = this.closest(".treasure-card");
                const productImage = productCard ? productCard.querySelector(".cart-product-image") : null;
                const cartIcon = document.querySelector("#cart-icon");

                // --- Animation Logic ---
                if (productImage) {
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

                    setTimeout(() => { imgClone.remove(); }, 800);
                }

                // 2. Prepare Data with LocalStorage UID
                const formData = new FormData(form);
                formData.append('uid', userSession); // Add the user ID to the request

                // 3. Send Ajax Request to add_to_cart.php
                fetch("add_to_cart.php", {
                    method: "POST",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // increase cart count
let count = localStorage.getItem('cart_count');
count = count ? parseInt(count) : 0;
localStorage.setItem('cart_count', count + 1);

// show dot instantly
const dot = document.getElementById('cart-dot');
if (dot) dot.style.display = 'block';
                    /* CART BOUNCE EFFECT */
                    if (cartIcon) {
                        cartIcon.classList.add("bounce");
                        setTimeout(() => cartIcon.classList.remove("bounce"), 400);
                    }
                });
            });
        });
    </script>
</body>
</html>