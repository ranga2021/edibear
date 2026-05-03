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

$adminHeader = new HEADER("add-worksheet");
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
            $ediProductSubcategories = EdiExplorerContent::dedupeProductSubcategoryRows($psc->fetchAll(PDO::FETCH_ASSOC));
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

// ================= DELETE =================
if (isset($_POST['confirmDeletepdfSubmit'])) {
    $delId = (int) ($_POST['deletepdfID'] ?? 0);
    if ($delId > 0 && $user->CountRows("pdf_details", array("id" => $delId))) {
        $dr = $user->fetchAll(array("image", "pdfupload"), array("pdf_details"), array("id" => $delId));
        if (!empty($dr[0])) {
            $img = (string) ($dr[0]['image'] ?? '');
            $pdfF = (string) ($dr[0]['pdfupload'] ?? '');
            if ($img !== '' && is_file("../img/pdf/" . $img)) {
                @unlink("../img/pdf/" . $img);
            }
            if ($pdfF !== '' && is_file("../img/pdf/" . $pdfF)) {
                @unlink("../img/pdf/" . $pdfF);
            }
        }
        $user->deleteTableRow("pdf_details", array("id" => $delId));
    }
    echo "<script>alert('Deleted successfully');location.href='./pdf'</script>";
    exit;
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

<?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit worksheet" : $adminHeader->getActivePageName()); ?>

<div class="container-fluid py-4">
<?php if (!$editMode) { $ediWorksheetAddCurrent = "pdf"; require __DIR__ . "/partials/edi_worksheet_type_switch.php"; } ?>
<div class="card p-4">

<form method="post" enctype="multipart/form-data" class="edi-add-worksheet-form">

<?php if ($editMode) { ?>
<h2 class="text-uppercase text-danger font-weight-bold mb-4">Edit worksheet</h2>
<?php } else { ?>
<h2 class="text-uppercase text-danger font-weight-bold mb-4">Add worksheet</h2>
<?php } ?>

<?php
$ediWsTagName = "inputpdfTag";
$ediWsTitleName = "inputpdfTitle";
$ediWsTagValue = $currentpdfTag;
$ediWsTitleValue = $currentpdfTitle;
require __DIR__ . "/partials/edi_worksheet_metadata_form.php";
?>

<div class="row justify-content-center align-items-end mt-4">
  <div class="col-md-6 col-lg-5">
    <div class="form-group mb-0">
      <label class="form-control-label" for="inputpdfMainImage">Main Image</label>
      <input type="file" name="inputpdfMainImage" id="inputpdfMainImage" class="form-control" accept="image/*" <?php echo !$editMode ? "required" : ""; ?> onchange="ediWsPreviewImage(event, 'outputpdfMainImage')">
    </div>
  </div>
  <div class="col-md-4 col-lg-3 text-center mt-3 mt-md-0">
    <img id="outputpdfMainImage" <?php echo $currentpdfMainImage; ?> alt="Preview" class="rounded border bg-light edi-ws-edit-preview-thumb" style="width:100px;height:140px;object-fit:contain;">
  </div>
</div>

<div class="row justify-content-center align-items-end mt-3">
  <div class="col-md-6 col-lg-5">
    <div class="form-group mb-0">
      <label class="form-control-label" for="inputpdfpdfupload">Main PDF</label>
      <input type="file" name="inputpdfpdfupload" id="inputpdfpdfupload" class="form-control" accept=".pdf,application/pdf" <?php echo !$editMode ? "required" : ""; ?>>
    </div>
  </div>
  <div class="col-md-4 col-lg-3 text-center mt-3 mt-md-0">
    <div class="rounded border bg-light d-inline-flex align-items-center justify-content-center text-muted edi-ws-edit-preview-thumb" style="width:100px;height:140px;" title="PDF">
      <i class="fas fa-file-pdf text-danger" style="font-size:2rem;" aria-hidden="true"></i>
    </div>
  </div>
</div>

<div class="row mt-4">
  <div class="col-12">
    <div class="form-group mb-0">
      <label class="form-control-label" for="inputpdfMainDescription">Main Description</label>
      <textarea name="inputpdfMainDescription" id="inputpdfMainDescription" class="form-control" rows="5" required><?php echo htmlspecialchars($currentpdfMainDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
  </div>
</div>

<div class="mt-4">
<?php
if ($editMode) {
    echo "<button type='submit' name='updatepdfSubmit' class='btn btn-success mr-2'>Update</button>";
    echo "<button type='button' class='btn btn-danger mr-2' onclick='deletepdfSubmit()'>Delete</button>";
} else {
    echo "<button type='submit' name='addNewpdfSubmit' class='btn btn-success mr-2'>Add</button>";
}
?>
<button type="button" class="btn btn-secondary" onclick="location.href='./pdf'">Cancel</button>
</div>

</form>

</div>
</div>

</main>

<?php echo $adminHeader->printAdminFooterJS(); ?>
<?php
if ($editMode) {
    $ediDelTitle = htmlspecialchars((string) $currentpdfTitle, ENT_QUOTES, 'UTF-8');
    $ediDelTag = htmlspecialchars((string) $currentpdfTag, ENT_QUOTES, 'UTF-8');
    echo "
<div class='modal fade' id='confirmDeletepdfModal' data-backdrop='static' tabindex='-1' role='dialog' aria-hidden='true' style='margin-top:200px'>
  <div class='modal-dialog' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Delete worksheet</h5>
      </div>
      <div class='modal-body'>
        <form method='post' class='text-center'>
          <p class='mb-2'>Document title: <strong>$ediDelTitle</strong><br>Tag: <strong>$ediDelTag</strong></p>
          <input type='hidden' name='deletepdfID' value='" . (int) $currentpdfID . "'>
          <input type='submit' class='btn btn-danger btn-sm' name='confirmDeletepdfSubmit' value='Delete'>
          <button class='btn btn-sm btn-secondary' type='button' data-dismiss='modal'>Cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>";
}
?>
<script>
function deletepdfSubmit() {
  $('#confirmDeletepdfModal').modal('show');
}
function ediWsPreviewImage(ev, imgId) {
  var f = ev.target.files && ev.target.files[0];
  var el = document.getElementById(imgId);
  if (!el || !f) return;
  if (f.type.indexOf('image/') === 0) {
    el.src = URL.createObjectURL(f);
    el.classList.add('border');
  }
}
</script>
</body>
</html>