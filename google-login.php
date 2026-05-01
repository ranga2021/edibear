<?php
require_once("./classes/class.user.php");

header("Content-Type: application/json; charset=UTF-8");

$user = new USER();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data["email"]) || !is_string($data["email"])) {
    echo json_encode(["error" => "No data"]);
    exit;
}

$email = trim($data["email"]);
$name = isset($data["name"]) && is_string($data["name"]) ? trim($data["name"]) : "";

$userExists = $user->fetchAll(["id"], ["tourists"], ["email" => $email]);
if (empty($userExists)) {
    $userExists = $user->fetchAll(["id"], ["tourists"], ["username" => $email]);
}

if (!empty($userExists)) {
    $userID = (int) $userExists[0]["id"];
} else {
    try {
        $ok = $user->insertTable("tourists", [
            "username" => $email,
            "name" => $name !== "" ? $name : $email,
            "email" => $email,
            "country" => "Sri Lanka",
            "password" => "SOCIAL_LOGIN",
            "profile_pic" => "default.jpg",
            "status" => 1,
            "delete_status" => 0,
            "timestamp" => date("Y-m-d H:i:s"),
        ]);
        if (!$ok) {
            echo json_encode(["error" => "Could not create account"]);
            exit;
        }
        $userID = (int) $user->getConnection()->lastInsertId();
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error"]);
        exit;
    }
}

echo json_encode(["user_id" => $userID]);