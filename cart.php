<?php
// session_start(); // You can likely remove this if you are purely using localStorage
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");

$user = new USER();
$userHeader = new HEADER("cart");

/**
 * 1. Get User ID from URL (Syncing with your localStorage logic)
 * Your JS redirects to ./account?uid=ID, so we expect uid here too.
 */
$user_id = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

// Handle delete item request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $deleteProductId = (int) $_POST['delete_product_id'];
    $currentUid = (int) $_POST['hidden_uid']; // Pass UID through form to maintain state
    
    if ($deleteProductId > 0 && $currentUid > 0) {
        $user->deleteTableRow(
            "cart",
            array(
                "user_id" => $currentUid,
                "product_id" => $deleteProductId
            )
        );
    }
    // Redirect back to cart with the UID parameter
    header("Location: cart.php?uid=" . $currentUid);
    exit;
}

// Only fetch items if we have a valid ID
$cartItems = [];
if ($user_id > 0) {
    $cartItems = $user->fetchAll(
        array("product_id","quantity"),
        array("cart"),
        array("user_id" => $user_id)
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader(); ?>
    
    <script>
        const userSession = localStorage.getItem('user_session');
        const urlParams = new URLSearchParams(window.location.search);
        const uid = urlParams.get('uid');

        // If not logged in at all, go to login
        if (!userSession) {
            window.location.replace('./login');
        } 
        // If logged in but the URL doesn't have the UID, redirect to include it
        else if (userSession && !uid) {
            window.location.replace('cart.php?uid=' + userSession);
        }
    </script>
</head>

<body>

<?php echo $userHeader->printUserNav(); ?>

<?php
$total = 0;
$totalItems = 0;

// Calculate Totals
foreach ($cartItems as $item) {
    $productResult = $user->fetchAll(
        array("price","discounted_price"),
        array("products"),
        array("id"=>$item['product_id'])
    );
    
    if(!empty($productResult)) {
        $product = $productResult[0];
        $price = $product['discounted_price'] > 0 ? $product['discounted_price'] : $product['price'];
        $total += ($price * $item['quantity']);
        $totalItems += $item['quantity'];
    }
}

$shipping = ($total > 0) ? 450 : 0;
$orderTotal = $total + $shipping;
?>

<div class="page-header-bg"></div>
<div class="container honey-cart-container page-header-content">
    <nav class="edi-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0 mb-0">
            <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="product_page.php">The Honey Market</a></li>
            <li class="breadcrumb-item active">Treasures</li>
        </ol>
    </nav>

    
     <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>HONEY CART</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

    <div class="honey-cart-steps mb-4 text-center">
        <span class="step active">HONEY CART</span>
        <span class="step-separator">&gt;</span>
        <span class="step">CHECKOUT</span>
        <span class="step-separator">&gt;</span>
        <span class="step">ORDER COMPLETE</span>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info text-center my-5">
            Your Honey Cart is empty. Start collecting treasures from the <a href="product_page.php" class="text-success">Honey Market</a>!
        </div>
        <script>
        // ✅ Clear cart count when cart is empty
        localStorage.removeItem('cart_count');

        // Optional: hide cart dot
        const dot = document.getElementById('cart-dot');
        if (dot) dot.style.display = 'none';
    </script>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <?php foreach ($cartItems as $item): ?>
                    <?php
                    $productArr = $user->fetchAll(
                        array("id","product_name","price","discounted_price","image","age_group","brand","language"),
                        array("products"),
                        array("id"=>$item['product_id'])
                    );
                    
                    if(empty($productArr)) continue;
                    $product = $productArr[0];

                    $price = $product['discounted_price'] > 0 ? $product['discounted_price'] : $product['price'];
                    $subtotal = $price * $item['quantity'];
                    ?>
                    <div class="honey-cart-item d-flex align-items-center mb-3">
                        <div class="honey-cart-item-image">
                            <img src="./img/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        </div>
                        <div class="honey-cart-item-info flex-grow-1">
                            <h5 class="item-title mb-1"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                            <div class="item-meta">
                                <?php echo htmlspecialchars($product['language'] ?? ''); ?>
                                <?php if (!empty($product['brand'])): ?>
                                    | <?php echo htmlspecialchars($product['brand']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="honey-cart-item-qty text-center">
                            <div class="qty-display">
                                <span class="minus disabled">−</span>
                                <span class="qty-number"><?php echo (int) $item['quantity']; ?></span>
                                <span class="plus disabled">+</span>
                            </div>
                        </div>
                        <div class="honey-cart-item-price text-right">
                            <div class="price-per-unit">Rs. <?php echo number_format($price, 2); ?></div>
                            <div class="price-subtotal">Rs. <?php echo number_format($subtotal, 2); ?></div>
                            
                            <form method="POST" action="cart.php?uid=<?php echo $user_id; ?>" class="d-inline">
                                <input type="hidden" name="delete_product_id" value="<?php echo (int) $item['product_id']; ?>">
                                <input type="hidden" name="hidden_uid" value="<?php echo $user_id; ?>">
                                <button type="submit" class="delete-link" style="background:none; border:none; color:red; cursor:pointer;">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-lg-4">
                <div class="honey-cart-summary">
                    <h5 class="summary-title">YOUR ORDER</h5>
                    <div class="summary-row">
                        <span>Subtotal (<?php echo $totalItems; ?> item<?php echo $totalItems > 1 ? 's' : ''; ?>)</span>
                        <span>Rs. <?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span>Rs. <?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <p class="summary-note">* Shipping based on weight and location.</p>
                    <div class="summary-total-row">
                        <span>Order Total</span>
                        <span>Rs. <?php echo number_format($orderTotal, 2); ?></span>
                    </div>
                    <a href="checkout.php?uid=<?php echo $user_id; ?>" class="btn btn-success btn-block mt-3">PROCEED TO CHECKOUT</a>
                    <a href="product_page.php" class="btn btn-link btn-block continue-shopping-link">CONTINUE SHOPPING</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php echo $userHeader->printUserFooter(); ?>

</body>
</html>