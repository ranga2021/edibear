<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");

$user = new USER();
// This will trigger the JS removal we added above
$user->doLogout("index.php"); 
?>