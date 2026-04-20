<?php
session_start();
require_once("../classes/class.user.php");

$user = new USER();

if (!$user->is_loggedin()) {
    http_response_code(403);
    exit('Not authorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$status = isset($_POST['status']) ? (int) $_POST['status'] : 0;

if ($id <= 0) {
    http_response_code(400);
    exit('Invalid id');
}

try {
    $user->updateTable(
        "braveheart_events",
        array("status" => $status ? 1 : 0),
        array("id" => $id)
    );
    echo "OK";
} catch (Exception $e) {
    http_response_code(500);
    echo "Error";
}

