<?php
require_once("./classes/class.user.php");

$user = new USER();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error"=>"No data"]);
    exit;
}

$email = $data['email'];

$userExists = $user->fetchAll(
    ["id"],
    ["tourists"],
    ["username"=>$email]
);

if ($userExists) {
    $userID = $userExists[0]['id'];
} else {
    $user->insert("tourists", [
        "username"=>$email,
        "password"=>"SOCIAL_LOGIN",
        "status"=>1
    ]);

    $userID = $user->getConnection()->lastInsertId();
}

echo json_encode(["user_id"=>$userID]);