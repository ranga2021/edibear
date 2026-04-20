<?php
session_start();
require_once("./classes/class.user.php");

$user = new USER();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ./");
    exit();
}

$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$rating     = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$name       = isset($_POST['name']) ? trim($_POST['name']) : '';
$email      = isset($_POST['email']) ? trim($_POST['email']) : '';
$review     = isset($_POST['review']) ? trim($_POST['review']) : '';

// Basic validation
if (
    $product_id <= 0 ||
    $rating < 1 || $rating > 5 ||
    $name === '' ||
    $email === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    header("Location: product_details.php?product_id=" . $product_id . "&review_error=1");
    exit();
}

$created_at = date('Y-m-d H:i:s');

$user->insertTable(
    "product_review",
    array(
        "product_id" => $product_id,
        "name"       => $name,
        "email"      => $email,
        "rating"     => $rating,
        "review"     => $review,
        "created_at" => $created_at
    )
);

header("Location: product_details.php?product_id=" . $product_id . "&review_submitted=1");
exit();

