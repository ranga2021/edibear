<?php

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
define('DB_USER', getenv('DB_USERNAME') ?: '');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET . ';dbname=' . DB_NAME,
    DB_USER,
    DB_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);


$stmt = $pdo->prepare("SELECT * FROM 'homework_details' WHERE 'title' LIKE ? OR 'description' LIKE ?" );
$stmt -> execute([
    "%".$_POST['search']. "%" , "%".$_POST['search']."%"
]);
$results = $stmt -> fetchAll();


?>