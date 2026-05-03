<?php
require_once("../classes/class.user.php");
$user = new USER();

if (isset($_POST["id"]) && isset($_POST["status"])) {
    $id = (int) $_POST["id"];
    $status = ((int) $_POST["status"] === 1) ? 1 : 0;
    
    try {
        $stmt = $user->runQuery("UPDATE products SET status = :status WHERE id = :id");
        $stmt->execute(array(":status" => $status, ":id" => $id));
        echo "Success";
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo $e->getMessage();
    }
}
?>