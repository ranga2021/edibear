<?php
session_start();
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");

$user = new USER();
$userHeader = new HEADER("cart");

// NEW: Use user_id from POST (passed from the checkout form)
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

// If no POST data or no user_id, redirect back
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $user_id === 0) {
    header("Location: checkout.php");
    exit;
}

// Basic cart check using user_id instead of session_id
$cartItems = $user->fetchAll(
    array("product_id","quantity"),
    array("cart"),
    array("user_id" => $user_id)
);

if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

// Collect billing data
$firstName   = trim($_POST['first_name'] ?? '');
$lastName    = trim($_POST['last_name'] ?? '');
$companyName = trim($_POST['company_name'] ?? '');
$addressLine = trim($_POST['address_line'] ?? '');
$city        = trim($_POST['city'] ?? '');
$postalCode  = trim($_POST['postal_code'] ?? '');
$email       = trim($_POST['email'] ?? '');
$district    = trim($_POST['district'] ?? '');
$mobile      = trim($_POST['mobile'] ?? '');
$paymentRaw  = $_POST['payment_option'] ?? 'cod';
$voucherCode = trim($_POST['voucher_code'] ?? '');

$paymentMethod = $paymentRaw === 'bank_transfer' ? 'bank_transfer' : 'cod';

// Recalculate totals
$total = 0;
foreach ($cartItems as $item) {
    $product = $user->fetchAll(
        array("id","product_name","price","discounted_price"),
        array("products"),
        array("id"=>$item['product_id'])
    )[0];

    $price = $product['discounted_price'] > 0 ? $product['discounted_price'] : $product['price'];
    $subtotal = $price * $item['quantity'];
    $total += $subtotal;
}

$shipping = $total > 0 ? 450 : 0;
$orderTotal = $total + $shipping;

// Generate order number
$prefix = $paymentMethod === 'bank_transfer' ? 'B#' : 'C#';
$orderNumber = $prefix . date('ymdHis');
$createdAt = date('Y-m-d H:i:s');

// Insert order (Updated to include user_id if your table has it, otherwise keeps mapping clean)
$paymentStatus = 'pending';
$orderStatus = 'Order Placed';

$user->insertTable(
    "orders",
    array(
        "order_number"   => $orderNumber,
        "session_id"     => $user_id, // We map user_id here to keep consistency with your schema
        "first_name"     => $firstName,
        "last_name"      => $lastName,
        "company_name"   => $companyName,
        "address_line"   => $addressLine,
        "city"           => $city,
        "postal_code"    => $postalCode,
        "district"       => $district,
        "email"          => $email,
        "mobile"         => $mobile,
        "payment_method" => $paymentMethod,
        "payment_status" => $paymentStatus,
        "order_status"   => $orderStatus,
        "subtotal"       => $total,
        "shipping"       => $shipping,
        "total"          => $orderTotal,
        "created_at"     => $createdAt
    )
);

// Clear cart after placing order using user_id
$user->deleteTableRow("cart", array("user_id" => $user_id));

?>

<!DOCTYPE html>
<html>
<head>
    <script>
        // Security check for local storage login
        const userSession = localStorage.getItem('user_session');
        if (!userSession) {
            window.location.replace('./login');
        }
    </script>
<?php echo $userHeader->printUserHeader(); ?>
</head>

<body>

<?php echo $userHeader->printUserNav(); ?>

<div class="page-header-bg"></div>
<div class="container order-complete-container page-header-content">
    <nav class="edi-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0 mb-0">
            <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="product_page.php">The Honey Market</a></li>
            <li class="breadcrumb-item active">Treasures</li>
        </ol>
    </nav>

    
     <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>ORDER COMPLETE</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

    <div class="honey-cart-steps mb-4 text-center">
        <span class="step">HONEY CART</span>
        <span class="step-separator">&gt;</span>
        <span class="step">CHECKOUT</span>
        <span class="step-separator">&gt;</span>
        <span class="step active">ORDER COMPLETE</span>
    </div>

    <div class="row order-complete-row">
        <div class="col-lg-8">
            <h4 class="order-summary-title">Order Summary</h4>

            <div class="order-summary-box">
                <div class="order-payment-heading">
                    <?php if ($paymentMethod === 'bank_transfer'): ?>
                        <h5>BANK TRANSFER</h5>
                    <?php else: ?>
                        <h5>CASH ON DELIVERY</h5>
                    <?php endif; ?>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <p class="mb-0"><?php echo htmlspecialchars($firstName . ' ' . $lastName, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if ($companyName !== ''): ?>
                            <p class="mb-0"><?php echo htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <p class="mb-0"><?php echo htmlspecialchars($addressLine, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="mb-0"><?php echo htmlspecialchars($city . ', ' . $district, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="mb-0"><?php echo htmlspecialchars($postalCode, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="mb-0"><?php echo htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="mb-0"><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="col-md-6 text-md-right mt-3 mt-md-0">
                        <p class="mb-1"><strong>Order Number - <?php echo htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        <p class="mb-0">Date - <?php echo date('F d, Y', strtotime($createdAt)); ?></p>
                    </div>
                </div>
            </div>

            <div class="order-special-note mt-4">
                <h5>SPECIAL NOTE</h5>
                <?php if ($paymentMethod === 'bank_transfer'): ?>
                    <p class="mb-2">
                        Please transfer the total amount to the bank account below and keep your payment receipt. 
                        Once payment is confirmed, your order will be processed.
                    </p>
                    <ul>
                        <li>Account Name: EDIBEAR (PRIVATE) LIMITED</li>
                        <li>Account Number: 1000400531</li>
                        <li>Bank: COMMERCIAL BANK</li>
                        <li>Branch: GAMPHA BRANCH</li>
                    </ul>
                    <p class="mb-0">
                        After making the bank transfer, please send the payment slip and your order number to our email so we can verify your payment quickly.
                    </p>
                <?php else: ?>
                    <p class="mb-0">
                        Your order will be delivered to your address. Please make sure someone is available to receive the package and pay the amount in cash upon delivery.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="order-complete-summary">
                <h5 class="summary-title">YOUR ORDER</h5>
                <div class="summary-total-row">
                    <span>Order Total</span>
                    <span>Rs. <?php echo number_format($orderTotal, 2); ?></span>
                </div>
                <button class="btn btn-success btn-block mt-3" type="button" disabled>PLACED ORDER</button>

                <div class="order-thankyou-box mt-4 text-center">
                    <div class="order-thankyou-icon">&#10003;</div>
                    <p class="mb-1"><strong>THANK YOU.</strong></p>
                    <p class="mb-0">YOUR ORDER HAS BEEN RECEIVED</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $userHeader->printUserFooter(); ?>

</body>
</html>