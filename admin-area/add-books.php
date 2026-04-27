<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// âœ… SESSION FIX (very important for your server)
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");
require_once("../classes/edi_explorer_content.php");

$adminHeader = new HEADER("add-books");
$user = new USER();
$widgets = new WIDGETS();
$ediConn = $user->getConnection();
$ediHasPcat = EdiExplorerContent::columnExists($ediConn, "books_details", "product_category_id");
$ediProductCategories = array();
$ediProductSubcategories = array();
$ediCurPcat = 0;
$ediCurPsub = 0;
if ($ediHasPcat) {
    try {
        $ediProductCategories = $user->fetchAll(array("id", "name"), array("product_categories"), array("status" => 1));
    } catch (Throwable $e) {
        $ediProductCategories = $user->fetchAll(array("id", "name"), array("product_categories"), array());
    }
    try {
        $psc = $ediConn->query("SELECT id, product_category_id, title FROM product_subcategories ORDER BY product_category_id ASC, title ASC");
        if ($psc) {
            $ediProductSubcategories = $psc->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        $ediProductSubcategories = array();
    }
}

$editMode = false;
$currentbooksID = 0;

$currentbooksTag = "";
$currentbooksTitle = "";
$currentbooksMainDescription = "";
$currentbooksVideoUrl = "";
$currentbooksVideoStatus = "";
$currentbooksMainImage = "";

// ================= EDIT MODE =================
if (isset($_GET['id']) && $_GET['id'] > 0) {

    $currentbooksID = (int)$_GET['id'];

    if ($user->CountRows("books_details", array("id"=>$currentbooksID))) {

        $editMode = true;

        $booksDetailsArr = $user->fetchAll(
            array("tag","title","image","description","video","video_status","pdfupload"),
            array("books_details"),
            array("id"=>$currentbooksID)
        )[0];

        $currentbooksTag = $booksDetailsArr['tag'];
        $currentbooksTitle = $booksDetailsArr['title'];
        $currentbooksMainDescription = $booksDetailsArr['description'];
        $currentbooksVideoUrl = $booksDetailsArr['video'];
        $currentbooksVideoStatus = ($booksDetailsArr['video_status']=='1') ? "checked" : "";

        if (!empty($booksDetailsArr['image'])) {
            $currentbooksMainImage = "src='".$widgets->createCachelessImage("../img/books/".$booksDetailsArr['image'])."'";
        }

        if ($ediHasPcat) {
            $r = $ediConn->query("SELECT `product_category_id`, `product_subcategory_id` FROM `books_details` WHERE `id` = " . (int) $currentbooksID);
            if ($r) {
                $ediRow = $r->fetch(PDO::FETCH_ASSOC);
                if ($ediRow) {
                    $ediCurPcat = (int)($ediRow["product_category_id"] ?? 0);
                    $ediCurPsub = (int)($ediRow["product_subcategory_id"] ?? 0);
                }
            }
        }

    } else {
        $user->redirect("./add-books");
    }
}

// ================= FORM SUBMIT =================
if (isset($_POST['addNewbooksSubmit']) || isset($_POST['updatebooksSubmit'])) {

    $inputbooksTag = htmlspecialchars($_POST['inputbooksTag'] ?? "");
    $inputbooksTitle = htmlspecialchars($_POST['inputbooksTitle'] ?? "");
    $inputbooksMainDescription = strip_tags($_POST['inputbooksMainDescription'] ?? "", "<br>");
    $inputbooksVideoUrl = htmlspecialchars($_POST['inputbooksVideoUrl'] ?? "");
    $booksVideoStatus = $_POST['booksVideoStatus'] ?? 0;

    // ================= ADD =================
    if (isset($_POST['addNewbooksSubmit'])) {

        // ðŸ”¥ IMAGE FIRST (IMPORTANT FIX)
        $imageName = "";
        if (!empty($_FILES["inputbooksMainImage"]["name"])) {

            $ext = pathinfo($_FILES["inputbooksMainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = time().".".$ext;

            move_uploaded_file($_FILES["inputbooksMainImage"]["tmp_name"], "../img/books/".$imageName);
        }

        // PDF
        $pdfName = "";
        if (!empty($_FILES["inputbookspdfupload"]["name"])) {

            $ext = pathinfo($_FILES["inputbookspdfupload"]["name"], PATHINFO_EXTENSION);
            $pdfName = time()."_pdf.".$ext;

            move_uploaded_file($_FILES["inputbookspdfupload"]["tmp_name"], "../img/books/".$pdfName);
        }

        $epc = isset($_POST['edi_content_product_category']) ? (int) $_POST['edi_content_product_category'] : 0;
        $eps = isset($_POST['edi_content_product_subcategory']) ? (int) $_POST['edi_content_product_subcategory'] : 0;
        if ($epc > 0 && $eps > 0) {
            $chk = $ediConn->prepare("SELECT `id` FROM `product_subcategories` WHERE `id` = ? AND `product_category_id` = ?");
            $chk->execute(array($eps, $epc));
            if ($chk->fetchColumn() === false) {
                $eps = 0;
            }
        } elseif ($eps > 0 && $epc <= 0) {
            $eps = 0;
        }
        $insertRowB = array(
            "tag"=>$inputbooksTag,
            "title"=>$inputbooksTitle,
            "description"=>$inputbooksMainDescription,
            "video"=>$inputbooksVideoUrl,
            "video_status"=>$booksVideoStatus,
            "image"=>$imageName,
            "pdfupload"=>$pdfName,
            "status"=>1,
            "download_count"=>0,
            "main_cat_id"=>1,
            "sub_cat_id"=>1
        );
        if ($ediHasPcat) {
            $insertRowB["product_category_id"] = $epc > 0 ? $epc : null;
            $insertRowB["product_subcategory_id"] = $eps > 0 ? $eps : null;
        }
        $booksID = $user->insertTable("books_details", $insertRowB, true);

        echo "<script>alert('Book added successfully');location.href='./createSiteMap?redirect=books'</script>";
    }

    // ================= UPDATE =================
    if (isset($_POST['updatebooksSubmit'])) {

        $upB = array(
            "tag"=>$inputbooksTag,
            "title"=>$inputbooksTitle,
            "description"=>$inputbooksMainDescription,
            "video"=>$inputbooksVideoUrl,
            "video_status"=>$booksVideoStatus
        );
        if ($ediHasPcat) {
            $epc = isset($_POST['edi_content_product_category']) ? (int) $_POST['edi_content_product_category'] : 0;
            $eps = isset($_POST['edi_content_product_subcategory']) ? (int) $_POST['edi_content_product_subcategory'] : 0;
            if ($epc > 0 && $eps > 0) {
                $chk = $ediConn->prepare("SELECT `id` FROM `product_subcategories` WHERE `id` = ? AND `product_category_id` = ?");
                $chk->execute(array($eps, $epc));
                if ($chk->fetchColumn() === false) {
                    $eps = 0;
                }
            } elseif ($eps > 0 && $epc <= 0) {
                $eps = 0;
            }
            $upB["product_category_id"] = $epc > 0 ? $epc : null;
            $upB["product_subcategory_id"] = $eps > 0 ? $eps : null;
        }
        $user->updateTable("books_details", $upB, array("id"=>$currentbooksID));

        // IMAGE UPDATE
        if (!empty($_FILES["inputbooksMainImage"]["name"])) {

            if (!empty($booksDetailsArr['image']) && file_exists("../img/books/".$booksDetailsArr['image'])) {
                unlink("../img/books/".$booksDetailsArr['image']);
            }

            $ext = pathinfo($_FILES["inputbooksMainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = $currentbooksID.".".$ext;

            move_uploaded_file($_FILES["inputbooksMainImage"]["tmp_name"], "../img/books/".$imageName);

            $user->updateTable("books_details", array("image"=>$imageName), array("id"=>$currentbooksID));
        }

        // PDF UPDATE
        if (!empty($_FILES["inputbookspdfupload"]["name"])) {

            if (!empty($booksDetailsArr['pdfupload']) && file_exists("../img/books/".$booksDetailsArr['pdfupload'])) {
                unlink("../img/books/".$booksDetailsArr['pdfupload']);
            }

            $ext = pathinfo($_FILES["inputbookspdfupload"]["name"], PATHINFO_EXTENSION);
            $pdfName = $currentbooksID.".".$ext;

            move_uploaded_file($_FILES["inputbookspdfupload"]["tmp_name"], "../img/books/".$pdfName);

            $user->updateTable("books_details", array("pdfupload"=>$pdfName), array("id"=>$currentbooksID));
        }

        echo "<script>alert('Book updated successfully');location.href='./createSiteMap?redirect=books'</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php echo $adminHeader->printAdminHeader(); ?>
</head>

<body class="g-sidenav-show bg-gray-100">

<?php echo $adminHeader->printAdminNav(); ?>

<main class="main-content position-relative border-radius-lg">

<?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit books" : "Add books"); ?>

<div class="container-fluid py-4">
<div class="card p-3">

<form method="post" enctype="multipart/form-data">

<div class="row">
<?php
echo $widgets->inputGroup("books Tag", "inputbooksTag", "col-md-6", $currentbooksTag);
echo $widgets->inputGroup("books Title", "inputbooksTitle", "col-md-6", $currentbooksTitle);
?>
</div>
<?php require __DIR__ . "/product_taxonomy_content_fields.php"; ?>

<div class="row mt-3">
<div class="col-md-6">
<label>Main Image</label>
<input type="file" name="inputbooksMainImage" class="form-control" <?php echo !$editMode ? "required" : ""; ?>>
</div>

<div class="col-md-6">
<img id="outputbooksMainImage" <?php echo $currentbooksMainImage; ?> style="max-height:200px;">
</div>
</div>

<div class="row mt-3">
<div class="col-md-6">
<label>Main PDF</label>
<input type="file" name="inputbookspdfupload" class="form-control" <?php echo !$editMode ? "required" : ""; ?>>
</div>
</div>

<div class="row mt-3">
<div class="col-12">
<label>Description</label>
<textarea name="inputbooksMainDescription" class="form-control" required><?php echo $currentbooksMainDescription;?></textarea>
</div>
</div>

<div class="mt-4">
<?php
if ($editMode) {
    echo "<button type='submit' name='updatebooksSubmit' class='btn btn-primary'>Update</button>";
} else {
    echo "<button type='submit' name='addNewbooksSubmit' class='btn btn-success'>Add</button>";
}
?>
</div>

</form>

</div>
</div>

</main>

<?php echo $adminHeader->printAdminFooterJS(); ?>

</body>
</html>