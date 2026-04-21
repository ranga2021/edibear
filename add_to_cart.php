<?php
require_once("./classes/class.user.php");

$user = new USER();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id']) || !isset($_POST['uid'])) {
    exit();
}

$product_id = (int) $_POST['product_id'];
$user_id = (int) $_POST['uid'];

if ($product_id <= 0 || $user_id <= 0) {
    exit();
}

// ✅ CHECK EXISTING ITEM
$existingCart = $user->fetchAll(
    array("id", "quantity"),
    array("cart"),
    array(
        "user_id" => $user_id,
        "product_id" => $product_id
    )
);

if (!empty($existingCart)) {

    // ✅ UPDATE QUANTITY
    $cart_id = (int) $existingCart[0]['id'];
    $newQty = (int) $existingCart[0]['quantity'] + 1;

    $user->updateTable(
        "cart",
        array("quantity" => $newQty),
        array("id" => $cart_id)
    );

} else {

    // ✅ INSERT NEW ITEM
    $user->insertTable(
        "cart",
        array(
            "user_id" => $user_id,
            "product_id" => $product_id,
            "quantity" => 1
        )
    );
}

// ✅ JSON RESPONSE FOR AJAX
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'status' => 'success',
    'user_id' => $user_id,
    'product_id' => $product_id
]);
exit();
?>