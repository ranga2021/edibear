<?php
header('Content-Type: application/json; charset=utf-8');

require_once("./classes/class.user.php");
require_once("./classes/edi_voucher.php");

$code     = trim($_POST['voucher_code'] ?? '');
$subtotal = (float) ($_POST['subtotal'] ?? 0);

$user = new USER();
$pdo  = $user->getConnection();

if (!EdiVoucher::tableReady($pdo)) {
    echo json_encode(array('valid' => false, 'error' => 'Voucher system is not configured.'));
    exit;
}

$result = EdiVoucher::validate($pdo, $code, $subtotal);

if ($result['valid']) {
    echo json_encode(array(
        'valid'         => true,
        'discount'      => $result['discount'],
        'discount_type' => $result['voucher']['discount_type'],
        'discount_value'=> (float) $result['voucher']['discount_value'],
        'code'          => $result['voucher']['code'],
    ));
} else {
    echo json_encode(array('valid' => false, 'error' => $result['error']));
}
