<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SESSION FIX
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");
require_once("../classes/edi_explorer_content.php");
require_once("../classes/edi_taxonomy.php");

$adminHeader = new HEADER("add-pdf");
$user = new USER();
$widgets = new WIDGETS();
$ediConn = $user->getConnection();
$ediLanguages = EdiTaxonomy::loadLanguages($ediConn);
$ediGrades = EdiTaxonomy::loadGrades($ediConn);
$ediCurLanguageId = 0;
$ediCurGradeId = 0;
$ediHasPcat = EdiExplorerContent::columnExists($ediConn, "pdf_details", "product_category_id");
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
$currentpdfID = 0;

$currentpdfTag = "";
$currentpdfTitle = "";
$currentpdfMainDescription = "";
$currentpdfVideoUrl = "";
$currentpdfVideoStatus = "";
$currentpdfMainImage = "";

// ================= EDIT MODE =================
if (isset($_GET['id']) && $_GET['id'] > 0) {

    $currentpdfID = (int)$_GET['id'];

    if ($user->CountRows("pdf_details", array("id"=>$currentpdfID))) {

        $editMode = true;

        $pdfDetailsArr = $user->fetchAll(
            array("tag","title","image","description","video","video_status","pdfupload","language_id","grade_id"),
            array("pdf_details"),
            array("id"=>$currentpdfID)
        )[0];
        $ediCurLanguageId = (int)($pdfDetailsArr['language_id'] ?? 0);
        $ediCurGradeId = (int)($pdfDetailsArr['grade_id'] ?? 0);

        $currentpdfTag = $pdfDetailsArr['tag'];
        $currentpdfTitle = $pdfDetailsArr['title'];
        $currentpdfMainDescription = $pdfDetailsArr['description'];
        $currentpdfVideoUrl = $pdfDetailsArr['video'];
        $currentpdfVideoStatus = ($pdfDetailsArr['video_status']=='1') ? "checked" : "";

        if (!empty($pdfDetailsArr['image'])) {
            $currentpdfMainImage = "src='".$widgets->createCachelessImage("../img/pdf/".$pdfDetailsArr['image'])."'";
        }

        if ($ediHasPcat) {
            $r = $ediConn->query("SELECT `product_category_id`, `product_subcategory_id` FROM `pdf_details` WHERE `id` = " . (int) $currentpdfID);
            if ($r) {
                $ediRow = $r->fetch(PDO::FETCH_ASSOC);
                if ($ediRow) {
                    $ediCurPcat = (int)($ediRow["product_category_id"] ?? 0);
                    $ediCurPsub = (int)($ediRow["product_subcategory_id"] ?? 0);
                }
            }
        }

    } else {
        $user->redirect("./add-pdf");
    }
}

// ================= FORM SUBMIT =================
if (isset($_POST['addNewpdfSubmit']) || isset($_POST['updatepdfSubmit'])) {

    $inputpdfTag = htmlspecialchars($_POST['inputpdfTag'] ?? "");
    $inputpdfTitle = htmlspecialchars($_POST['inputpdfTitle'] ?? "");
    $inputpdfMainDescription = strip_tags($_POST['inputpdfMainDescription'] ?? "", "<br>");
    $inputpdfVideoUrl = htmlspecialchars($_POST['inputpdfVideoUrl'] ?? "");
    $pdfVideoStatus = $_POST['pdfVideoStatus'] ?? 0;

    // ================= ADD =================
    if (isset($_POST['addNewpdfSubmit'])) {

        // IMAGE FIRST
        $imageName = "";
        if (!empty($_FILES["inputpdfMainImage"]["name"])) {

            $ext = pathinfo($_FILES["inputpdfMainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = time().".".$ext;

            move_uploaded_file($_FILES["inputpdfMainImage"]["tmp_name"], "../img/pdf/".$imageName);
        }

        // PDF FILE
        $pdfName = "";
        if (!empty($_FILES["inputpdfpdfupload"]["name"])) {

            $ext = pathinfo($_FILES["inputpdfpdfupload"]["name"], PATHINFO_EXTENSION);
            $pdfName = time()."_pdf.".$ext;

            move_uploaded_file($_FILES["inputpdfpdfupload"]["tmp_name"], "../img/pdf/".$pdfName);
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
        $lg = EdiTaxonomy::contentLanguageGradeFromPost($ediLanguages, $ediGrades);
        if ($lg['language_id'] === null || $lg['grade_id'] === null) {
            echo "<script>alert('Please select a valid language and grade.');history.back();</script>";
            exit;
        }
        $mappedContent = array("main_cat_id" => 1, "sub_cat_id" => 1);
        if ($ediHasPcat) {
            $mappedContent = EdiExplorerContent::mapProductSelectionsToContentCategoryIds($ediConn, $epc, $eps);
            if ($mappedContent["main_cat_id"] === null) {
                $mappedContent["main_cat_id"] = 1;
            }
            if ($mappedContent["sub_cat_id"] === null) {
                $mappedContent["sub_cat_id"] = 1;
            }
        }
        $insertRow = array(
            "tag"=>$inputpdfTag,
            "title"=>$inputpdfTitle,
            "description"=>$inputpdfMainDescription,
            "video"=>$inputpdfVideoUrl,
            "video_status"=>$pdfVideoStatus,
            "image"=>$imageName,
            "pdfupload"=>$pdfName,
            "status"=>1,
            "download_count"=>0,
            "main_cat_id"=>$mappedContent["main_cat_id"],
            "sub_cat_id"=>$mappedContent["sub_cat_id"],
            "language_id"=>$lg['language_id'],
            "grade_id"=>$lg['grade_id']
        );
        if ($ediHasPcat) {
            $insertRow["product_category_id"] = $epc > 0 ? $epc : null;
            $insertRow["product_subcategory_id"] = $eps > 0 ? $eps : null;
        }
        $pdfID = $user->insertTable("pdf_details", $insertRow, true);

        echo "<script>alert('PDF added successfully');location.href='./createSiteMap?redirect=pdf'</script>";
    }

    // ================= UPDATE =================
    if (isset($_POST['updatepdfSubmit'])) {

        $lg = EdiTaxonomy::contentLanguageGradeFromPost($ediLanguages, $ediGrades);
        if ($lg['language_id'] === null || $lg['grade_id'] === null) {
            echo "<script>alert('Please select a valid language and grade.');history.back();</script>";
            exit;
        }
        $upRow = array(
            "tag"=>$inputpdfTag,
            "title"=>$inputpdfTitle,
            "description"=>$inputpdfMainDescription,
            "video"=>$inputpdfVideoUrl,
            "video_status"=>$pdfVideoStatus,
            "language_id"=>$lg['language_id'],
            "grade_id"=>$lg['grade_id']
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
            $upRow["product_category_id"] = $epc > 0 ? $epc : null;
            $upRow["product_subcategory_id"] = $eps > 0 ? $eps : null;
            $mappedContent = EdiExplorerContent::mapProductSelectionsToContentCategoryIds($ediConn, $epc, $eps);
            $upRow["main_cat_id"] = $mappedContent["main_cat_id"];
            $upRow["sub_cat_id"] = $mappedContent["sub_cat_id"];
        }
        $user->updateTable("pdf_details", $upRow, array("id"=>$currentpdfID));

        // IMAGE UPDATE
        if (!empty($_FILES["inputpdfMainImage"]["name"])) {

            if (!empty($pdfDetailsArr['image']) && file_exists("../img/pdf/".$pdfDetailsArr['image'])) {
                unlink("../img/pdf/".$pdfDetailsArr['image']);
            }

            $ext = pathinfo($_FILES["inputpdfMainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = $currentpdfID.".".$ext;

            move_uploaded_file($_FILES["inputpdfMainImage"]["tmp_name"], "../img/pdf/".$imageName);

            $user->updateTable("pdf_details", array("image"=>$imageName), array("id"=>$currentpdfID));
        }

        // PDF UPDATE
        if (!empty($_FILES["inputpdfpdfupload"]["name"])) {

            if (!empty($pdfDetailsArr['pdfupload']) && file_exists("../img/pdf/".$pdfDetailsArr['pdfupload'])) {
                unlink("../img/pdf/".$pdfDetailsArr['pdfupload']);
            }

            $ext = pathinfo($_FILES["inputpdfpdfupload"]["name"], PATHINFO_EXTENSION);
            $pdfName = $currentpdfID.".".$ext;

            move_uploaded_file($_FILES["inputpdfpdfupload"]["tmp_name"], "../img/pdf/".$pdfName);

            $user->updateTable("pdf_details", array("pdfupload"=>$pdfName), array("id"=>$currentpdfID));
        }

        echo "<script>alert('PDF updated successfully');location.href='./createSiteMap?redirect=pdf'</script>";
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

<?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit PDF" : "Add PDF"); ?>

<div class="container-fluid py-4">
<div class="card p-3">

<form method="post" enctype="multipart/form-data">

<div class="row">
<?php
echo $widgets->inputGroup("Tags (slash-separated, e.g. Animals / Easy)", "inputpdfTag", "col-md-6", $currentpdfTag);
echo $widgets->inputGroup("PDF Title", "inputpdfTitle", "col-md-6", $currentpdfTitle);
?>
</div>
<?php require __DIR__ . "/content_language_grade_fields.php"; ?>
<?php require __DIR__ . "/product_taxonomy_content_fields.php"; ?>

<div class="row mt-3">
<div class="col-md-6">
<label>Main Image</label>
<input type="file" name="inputpdfMainImage" class="form-control" <?php echo !$editMode ? "required" : ""; ?>>
</div>

<div class="col-md-6">
<img id="outputpdfMainImage" <?php echo $currentpdfMainImage; ?> style="max-height:200px;">
</div>
</div>

<div class="row mt-3">
<div class="col-md-6">
<label>Main PDF</label>
<input type="file" name="inputpdfpdfupload" class="form-control" <?php echo !$editMode ? "required" : ""; ?>>
</div>
</div>

<div class="row mt-3">
<div class="col-12">
<label>Description</label>
<textarea name="inputpdfMainDescription" class="form-control" required><?php echo $currentpdfMainDescription;?></textarea>
</div>
</div>

<div class="mt-4">
<?php
if ($editMode) {
    echo "<button type='submit' name='updatepdfSubmit' class='btn btn-primary'>Update</button>";
} else {
    echo "<button type='submit' name='addNewpdfSubmit' class='btn btn-success'>Add</button>";
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