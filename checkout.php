<?php
session_start();
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");

$user = new USER();
$userHeader = new HEADER("cart");

$user_id = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;



$cartItems = $user->fetchAll(
    array("product_id","quantity"),
    array("cart"),
    array("user_id" => $user_id)
);

$total = 0;
$totalItems = 0;

foreach ($cartItems as $item) {
    $product = $user->fetchAll(
        array("id","product_name","price","discounted_price","image","age_group","brand","language"),
        array("products"),
        array("id"=>$item['product_id'])
    )[0];

    $price = $product['discounted_price'] > 0 ? $product['discounted_price'] : $product['price'];
    $subtotal = $price * $item['quantity'];

    $total += $subtotal;
    $totalItems += $item['quantity'];
}

$shipping = $total > 0 ? 450 : 0;
$orderTotal = $total + $shipping;
?>

<!DOCTYPE html>
<html>
<head>
    <script>
    
    
document.addEventListener('DOMContentLoaded', function () {

    const checkoutBtn = document.querySelector('button[type="submit"]');

    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function () {
            
            // clear cart indicator
            localStorage.removeItem('cart_count');

        });
    }

});


    const userSession = localStorage.getItem('user_session');
    const urlParams = new URLSearchParams(window.location.search);
    const uid = urlParams.get('uid');

    // Not logged in → go login
    if (!userSession) {
        window.location.replace('./login');
    }
    // Logged in but no uid in URL → fix URL
    else if (userSession && !uid) {
        window.location.replace('checkout.php?uid=' + userSession);
    }
</script>
<?php echo $userHeader->printUserHeader(); ?>
</head>

<body>

<?php echo $userHeader->printUserNav(); ?>

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
            <h1>CHECKOUT</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

    <div class="honey-cart-steps mb-2" aria-label="Checkout progress">
        <span class="step">HONEY CART</span>
        <span class="step-separator">&gt;</span>
        <span class="step active">CHECKOUT</span>
        <span class="step-separator">&gt;</span>
        <span class="step">ORDER COMPLETE</span>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info text-center my-5">
            Your Honey Cart is empty. Start collecting treasures from the <a href="product_page.php" class="text-success">Honey Market</a>!
        </div>
    <?php else: ?>
        <form class="checkout-form" method="POST" action="order_place.php">
            <div class="row checkout-row">
                <div class="col-lg-8">
                    <h4 class="checkout-section-title">Billing Details</h4>
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="checkout-label">First Name</label>
                            <input type="text" name="first_name" class="form-control checkout-input" placeholder="First Name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="checkout-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control checkout-input" placeholder="Last Name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="checkout-label">Company Name (Optional)</label>
                        <input type="text" name="company_name" class="form-control checkout-input" placeholder="Company Name (Optional)">
                    </div>

                    <div class="mb-3">
                        <label class="checkout-label">Address</label>
                        <input type="text" name="address_line" class="form-control checkout-input" placeholder="No. & Street Name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="checkout-label">Town / City</label>
                            <input type="text" name="city" class="form-control checkout-input" placeholder="Town / City" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="checkout-label">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control checkout-input" placeholder="Postal Code" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="checkout-label">Email Address</label>
                        <input type="email" name="email" class="form-control checkout-input" placeholder="Email Address" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="checkout-label">District</label>
                            <input type="text" name="district" class="form-control checkout-input" placeholder="District" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="checkout-label">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control checkout-input" placeholder="Mobile Number" required>
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input type="checkbox" class="form-check-input" id="termsCheck" name="agree_terms" value="1" required>
                        <label class="form-check-label" for="termsCheck">
                            I have read and agree to the website terms and conditions.
                        </label>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="honey-cart-summary checkout-summary">
                        <h5 class="summary-title">YOUR ORDER</h5>
                        <div class="summary-row">
                            <span>Subtotal (<?php echo $totalItems; ?> item<?php echo $totalItems > 1 ? 's' : ''; ?>)</span>
                            <span>Rs. <?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping (Weight Based)</span>
                            <span>Rs. <?php echo number_format($shipping, 2); ?></span>
                        </div>

                        <div class="voucher-block mt-3">
                            <label class="checkout-label">Enter voucher code</label>
                            <div class="input-group">
                                <input type="text" name="voucher_code" class="form-control checkout-input" placeholder="Enter voucher code">
                                <button class="btn btn-success" type="button">APPLY</button>
                            </div>
                        </div>

                        <div class="summary-total-row mt-3">
                            <span>Order Total</span>
                            <span>Rs. <?php echo number_format($orderTotal, 2); ?></span>
                        </div>

                        <div class="payment-options mt-3">
                            <h6 class="checkout-label mb-2">Payment Options</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_option" id="codOption" value="cod" checked>
                                <label class="form-check-label" for="codOption">
                                    Cash on delivery <small class="text-muted d-block">Pay with cash upon delivery.</small>
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="payment_option" id="bankOption" value="bank_transfer">
                                <label class="form-check-label" for="bankOption">
                                    Direct bank transfer <small class="text-muted d-block">Make your payment directly into our bank account.</small>
                                </label>
                            </div>
                        </div>

                        <button class="btn btn-success btn-block mt-4" type="submit">CHECKOUT</button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php echo $userHeader->printUserFooter(); ?>

</body>
</html>

