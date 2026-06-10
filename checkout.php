<?php
session_start();
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/edi_shipping.php");

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
    $totalItems += $item['quantity'];
    $totalWeightKg += EdiShipping::productKgFromRow($product) * (int) $item['quantity'];
}

$pdo = $user->getConnection();
$weightShipFee = $total > 0 ? EdiShipping::weightShippingFee($pdo, $totalWeightKg) : 0.0;
$districtRows = EdiShipping::fetchDistricts($pdo);
$districtFeeMap = array();
foreach ($districtRows as $dr) {
    $key = strtolower(trim((string) ($dr['name'] ?? '')));
    if ($key !== '') {
        $districtFeeMap[$key] = (float) ($dr['fee_lkr'] ?? 0);
    }
}
$districtFeeInitial = 0.0;
$shipping = $weightShipFee + $districtFeeInitial;
$orderTotal = $total + $shipping;

$bankDetails = array("account_number" => "", "account_name" => "", "bank_name" => "", "branch_name" => "");
try {
    $bdRow = $pdo->query("SELECT * FROM edi_bank_details LIMIT 1")->fetch();
    if ($bdRow) {
        $bankDetails = $bdRow;
    }
} catch (Throwable $e) {}
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
            if (typeof window.edibearSyncCartBadge === 'function') {
                window.edibearSyncCartBadge();
            }

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
                            <?php if (!empty($districtRows)): ?>
                            <select name="district" id="ediCheckoutDistrict" class="form-control checkout-input" required>
                                <option value="" disabled selected hidden>Choose district…</option>
                                <?php foreach ($districtRows as $dr): ?>
                                    <?php
                                    $dn = (string) ($dr['name'] ?? '');
                                    if ($dn === '') {
                                        continue;
                                    }
                                    ?>
                                <option value="<?php echo htmlspecialchars($dn, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($dn, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input type="text" name="district" id="ediCheckoutDistrict" class="form-control checkout-input" placeholder="District" required>
                            <?php endif; ?>
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
                    
                    <div id="bankDetailsBlock" class="bank-details-block mt-3" style="display:none;">
                    <div class="order-note-item">
                            <p class="checkout-label mb-1" lang="si">Bank transfer හරහා සිදු කිරීමේදී අපගේ පහත සඳහන් ගිණුම වෙත මුදල් බැර කොට අදාළ රිසිට් පත 075 5002004 දුරකථන අංකයට Whatsapp කරන්න. මුදල් ගෙවීමේදී Order ID එක පැහැදිලිව reference යටතේ සඳහන් කරන්න හෝ ඉහත දුරකථන අංකයට රිසිට්පත සමග ලැබී
                            <p class="order-note-lang mb-1" lang="ta">வங்கி பரிமாற்றம் செய்வதற்கு, கீழே குறிப்பிடப்பட்டுள்ள எங்கள் கணக்கில் தொகையை செலுத்தவும், சம்பந்தப்பட்ட ரசீதை 075 5002004 என்ற எண்ணுக்கு WhatsApp மூலம் அனுப்பவும். பணம் செலுத்தும் போது Order ID ஐ reference பகுதியில் தெளிவாக குறிப்பிடவும் அல்லது ரசீதுடன் மேலே குறிப்பிடப்பட்ட தொலைபேசி எண்ணுக்கு அனுப்பவும். பணம் எங்கள் கணக்கில் வரவு செய்யப்பட்ட பின்னரே பொருட்கள் குரியர் மூலம் அனுப்பப்படும்.</p>
                            <p class="order-note-lang mb-0" lang="en">When making a bank transfer, please deposit the amount to the account mentioned below and send the relevant receipt via WhatsApp to 075 5002004. When making the payment, clearly mention the Order ID in the reference section, or send the receipt along with the Order ID to the above phone number. Goods will be dispatched by courier only after the payment has been credited to our account.</p>
                    </div>
                        <h6 class="checkout-label mb-2">Bank Details</h6>
                        <p class="mb-1"><span lang="si">ගිණුම් අංකය</span> / <span lang="ta">கணக்கு எண்</span> / Account Number : <strong><?php echo htmlspecialchars($bankDetails['account_number'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        <p class="mb-1"><span lang="si">ගිණුම් හිමියාගේ නම</span> / <span lang="ta">கணக்கு பெயர்</span> / Account Name : <strong><?php echo htmlspecialchars($bankDetails['account_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        <p class="mb-1"><span lang="si">බැංකුව</span> / <span lang="ta">வங்கி</span> / Bank : <strong><?php echo htmlspecialchars($bankDetails['bank_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                        <p class="mb-0"><span lang="si">ශාඛාව</span> / <span lang="ta">கிளை</span> / Branch : <strong><?php echo htmlspecialchars($bankDetails['branch_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
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
                            <span>Cart weight</span>
                            <span><?php echo number_format($totalWeightKg, 3); ?> kg</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping (weight tier)</span>
                            <span id="ediShipWeightLine">Rs. <?php echo number_format($weightShipFee, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>District fee</span>
                            <span id="ediShipDistrictLine">Rs. <?php echo number_format($districtFeeInitial, 2); ?></span>
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
                            <span id="ediCheckoutOrderTotal">Rs. <?php echo number_format($orderTotal, 2); ?></span>
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
<?php if (!empty($cartItems)): ?>
<script>
(function () {
    var sub = <?php echo json_encode((float) $total); ?>;
    var wFee = <?php echo json_encode((float) $weightShipFee); ?>;
    var distFees = <?php echo json_encode($districtFeeMap); ?>;
    var sel = document.getElementById('ediCheckoutDistrict');
    var dLine = document.getElementById('ediShipDistrictLine');
    var totEl = document.getElementById('ediCheckoutOrderTotal');
    if (!sel || !dLine || !totEl) return;
    function money(n) {
        return 'Rs. ' + Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function refresh() {
        var d = 0;
        if (sel.tagName === 'SELECT') {
            var v = (sel.value || '').trim().toLowerCase();
            if (v && distFees && Object.prototype.hasOwnProperty.call(distFees, v)) {
                d = parseFloat(distFees[v]) || 0;
            }
        }
        dLine.textContent = money(d);
        totEl.textContent = money(sub + wFee + d);
    }
    sel.addEventListener('change', refresh);
    sel.addEventListener('input', refresh);
})();

(function () {
    var codRadio = document.getElementById('codOption');
    var bankRadio = document.getElementById('bankOption');
    var bankDetails = document.getElementById('bankDetailsBlock');
    if (!codRadio || !bankRadio || !bankDetails) return;
    function toggle() {
        bankDetails.style.display = bankRadio.checked ? 'block' : 'none';
    }
    codRadio.addEventListener('change', toggle);
    bankRadio.addEventListener('change', toggle);
})();
</script>
<?php endif; ?>

</body>
</html>

