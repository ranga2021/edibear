<?php
session_start();
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/class.widgets.php");

$userHeader = new HEADER("");
$user = new USER();
$widgets = new WIDGETS();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader(); ?>
</head>

<body>

<?php echo $userHeader->printUserNav(); ?>
<div class="page-header-bg"></div>

<!-- PAGE START -->
<div class="container-fluid page-header-content" style="margin-top:0px; min-height:600px;">
    <div class="container">

        <!-- Breadcrumb -->
        <div style="margin-bottom:10px;">
            <i class="fa fa-home" style="color:#8bc34a;"></i>
            <a href="./" style="color:#666; text-decoration:none;"> Home</a>
            <span style="color:#999;"> » </span>
            <span style="color:#666;">Privacy Policy</span>
        </div>

        <!-- Title + Line -->
        <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
            <h2 style="font-weight:700; margin:0;">Privacy Policy</h2>
            <div style="flex:1; height:2px; background:#f4b400;"></div>
        </div>

        <!-- EMPTY CONTENT AREA -->
        <div style="min-height:400px;">
            <!-- You can add privacy content later -->
        </div>

    </div>
</div>

<!-- FOOTER -->
<?php echo $userHeader->printUserFooter(); ?>

</body>
</html>