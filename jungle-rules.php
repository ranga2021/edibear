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
<div class="container-fluid page-header-content">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="edi-breadcrumb" aria-label="Breadcrumb">
            <i class="fa fa-home" aria-hidden="true"></i>
            <a href="./">Home</a>
            <span class="edi-bc-sep"> » </span>
            <span>Ground Rules</span>
        </nav>

        <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>Ground Rules</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

        <!-- EMPTY CONTENT AREA -->
        <div style="min-height:400px;">
            <!-- Add rules later -->
        </div>

    </div>
</div>

<!-- FOOTER -->
<?php echo $userHeader->printUserFooter(); ?>

</body>
</html>