<?php
session_start();
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/edi_shipping.php");

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
$totalWeightKg = 0.0;
foreach ($cartItems as $item) {
    $product = $user->fetchAll(
        '',
        array("products"),
        array("id"=>$item['product_id'])
    )[0];

    $price = $product['discounted_price'] > 0 ? $product['discounted_price'] : $product['price'];
    $subtotal = $price * $item['quantity'];
    $total += $subtotal;
    $totalWeightKg += EdiShipping::productKgFromRow($product) * (int) $item['quantity'];
}

$pdo = $user->getConnection();
$shipQuote = EdiShipping::quote($pdo, $totalWeightKg, $district);
$shipping = $total > 0 ? $shipQuote['shipping_total'] : 0.0;
$orderTotal = $total + $shipping;

$bankDetails = array("account_number" => "", "account_name" => "", "bank_name" => "", "branch_name" => "");
try {
    $bdRow = $pdo->query("SELECT * FROM edi_bank_details LIMIT 1")->fetch();
    if ($bdRow) {
        $bankDetails = $bdRow;
    }
} catch (Throwable $e) {}

// Generate order number
$prefix = $paymentMethod === 'bank_transfer' ? 'B#' : 'C#';
$orderNumber = $prefix . date('ymdHis');
$createdAt = date('Y-m-d H:i:s');

// Insert order (Updated to include user_id if your table has it, otherwise keeps mapping clean)
$paymentStatus = 'pending';
$orderStatus = 'Order Placed';

$orderId = $user->insertTable(
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
    ),
    true
);

if ($orderId === false) {
    header("Location: checkout.php?uid=" . (int) $user_id . "&error=order");
    exit;
}

$orderItemsTable = false;
try {
    $chk = $pdo->query("SHOW TABLES LIKE " . $pdo->quote("order_items"));
    $orderItemsTable = $chk && $chk->rowCount() > 0;
} catch (Throwable $e) {
    $orderItemsTable = false;
}

if ($orderItemsTable) {
    foreach ($cartItems as $item) {
        $product = $user->fetchAll(
            "",
            array("products"),
            array("id" => $item["product_id"])
        )[0];
        $unit = (float) ($product["discounted_price"] > 0 ? $product["discounted_price"] : $product["price"]);
        $qty = (int) $item["quantity"];
        $lineTotal = $unit * $qty;
        $user->insertTable(
            "order_items",
            array(
                "order_id"     => $orderId,
                "product_id"   => (int) $item["product_id"],
                "product_name" => (string) ($product["product_name"] ?? ""),
                "quantity"     => $qty,
                "unit_price"   => $unit,
                "line_total"   => $lineTotal,
            )
        );
    }
}

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

    <div class="honey-cart-steps mb-2" aria-label="Checkout progress">
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
                    <ul class="order-note-list list-unstyled pl-0 mb-0">
                        <li class="order-note-item">
                            <p class="order-note-lang mb-1" lang="si">ඔබගේ තොරතුරු නිවැරදිව ඇතුළත් කොට තිබේදැයි පරීක්ෂා කර බලන්න. (නම/ලිපිනය/දුරකථන අංකය)</p>
                            <p class="order-note-lang mb-1" lang="ta">வாடிக்கையாளர் ஆர்டர் செய்த பிறகு, கொடுக்கப்பட்ட முகவரிக்கு பொருள் விநியோகிக்கப்படும். (மாவட்டம் / பிரதேசம் / நகரம் குறிப்பிட வேண்டும்)</p>
                            <p class="order-note-lang mb-0" lang="en">After placing an order, the product will be delivered to the provided address. (Please mention district / area / city clearly.)</p>
                        </li>
                        <li class="order-note-item">
                            <p class="order-note-lang mb-1" lang="si">කුරියර් ගාස්තුව පාර්සලයේ බර මත වෙනස් විය හැක.</p>
                            <p class="order-note-lang mb-1" lang="ta">பொருளின் எடையை அடிப்படையாகக் கொண்டு டெலிவரி கட்டணம் மாறுபடலாம்.</p>
                            <p class="order-note-lang mb-0" lang="en">Delivery charges may vary based on the package weight.</p>
                        </li>
                        <li class="order-note-item">
                            <p class="order-note-lang mb-1" lang="si">ඔබගේ ඇණවුම කළ පසු ඒ පිළිබඳව අප විසින් ඔබට ඊ-මේල් පණිවිඩයක් හරහා දන්වනු ඇත.</p>
                            <p class="order-note-lang mb-1" lang="ta">ஒரு ஆர்டர் உறுதி செய்யப்பட்டு விநியோக செயல்முறை தொடங்கிய பிறகு, எந்த மாற்றங்களும் அல்லது திருத்தங்களும் செய்ய முடியாது.</p>
                            <p class="order-note-lang mb-0" lang="en">Once an order is confirmed and processing has started, no changes or modifications can be made.</p>
                        </li>
                        <li class="order-note-item">
                            <p class="order-note-lang mb-1" lang="si">ඔබගේ ඇණවුම වැඩකරන දින 3-5 ක් ඇතුළත ලැබෙන අතර සමහර විට රටෙහි පවතින විවිධ හේතුන් මත පමා විය හැක.</p>
                            <p class="order-note-lang mb-1" lang="ta">ஆர்டர் செய்த நாளிலிருந்து 3-5 வேலை நாட்களுக்குள் பொருள் விநியோகிக்கப்படும். விசேஷ சூழ்நிலைகளில் தாமதம் ஏற்படலாம்.</p>
                            <p class="order-note-lang mb-0" lang="en">Delivery will be completed within 3–5 working days from the order date. Delays may occur due to special circumstances.</p>
                        </li>
                        <li class="order-note-item">
                            <p class="order-note-lang mb-1" lang="si">Bank transfer හරහා සිදු කිරීමේදී අපගේ පහත සඳහන් ගිණුම වෙත මුදල් බැර කොට අදාළ රිසිට් පත 075 5002004 දුරකථන අංකයට Whatsapp කරන්න. මුදල් ගෙවීමේදී Order ID එක පැහැදිලිව reference යටතේ සඳහන් කරන්න හෝ ඉහත දුරකථන අංකයට රිසිට්පත සමග ලැබීමට සලස්වන්න. මුදල් අපගේ ගිණුමට බැර වූ පසු පමණක් භාණ්ඩ කුරියර් කරනු ලැබේ.</p>
                            <p class="order-note-lang mb-1" lang="ta">வங்கி பரிமாற்றம் செய்வதற்கு, கீழே குறிப்பிடப்பட்டுள்ள எங்கள் கணக்கில் தொகையை செலுத்தவும், சம்பந்தப்பட்ட ரசீதை 075 5002004 என்ற எண்ணுக்கு WhatsApp மூலம் அனுப்பவும். பணம் செலுத்தும் போது Order ID ஐ reference பகுதியில் தெளிவாக குறிப்பிடவும் அல்லது ரசீதுடன் மேலே குறிப்பிடப்பட்ட தொலைபேசி எண்ணுக்கு அனுப்பவும். பணம் எங்கள் கணக்கில் வரவு செய்யப்பட்ட பின்னரே பொருட்கள் குரியர் மூலம் அனுப்பப்படும்.</p>
                            <p class="order-note-lang mb-0" lang="en">When making a bank transfer, please deposit the amount to the account mentioned below and send the relevant receipt via WhatsApp to 075 5002004. When making the payment, clearly mention the Order ID in the reference section, or send the receipt along with the Order ID to the above phone number. Goods will be dispatched by courier only after the payment has been credited to our account.</p>
                        </li>
                    </ul>
                    <?php if ($bankDetails['account_number'] !== ''): ?>
                    <div class="bank-details-block mt-3">
                        <p class="mb-1"><span lang="si">ගිණුම් අංකය</span> / <span lang="ta">கணக்கு எண்</span> / Account Number : <strong><?php echo htmlspecialchars($bankDetails['account_number'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        <p class="mb-1"><span lang="si">ගිණුම් හිමියාගේ නම</span> / <span lang="ta">கணக்கு பெயர்</span> / Account Name : <strong><?php echo htmlspecialchars($bankDetails['account_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        <p class="mb-1"><span lang="si">බැංකුව</span> / <span lang="ta">வங்கி</span> / Bank : <strong><?php echo htmlspecialchars($bankDetails['bank_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        <p class="mb-0"><span lang="si">ශාඛාව</span> / <span lang="ta">கிளை</span> / Branch : <strong><?php echo htmlspecialchars($bankDetails['branch_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    </div>
                    <?php endif; ?>
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