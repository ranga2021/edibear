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
require_once("../classes/edi_taxonomy.php");

$adminHeader = new HEADER("add-worksheet");
$user = new USER();
$widgets = new WIDGETS();
$ediConn = $user->getConnection();
$ediLanguages = EdiTaxonomy::loadLanguages($ediConn);
$ediGrades = EdiTaxonomy::loadGrades($ediConn);
$ediCurLanguageId = 0;
$ediCurGradeId = 0;
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
            $ediProductSubcategories = EdiExplorerContent::dedupeProductSubcategoryRows($psc->fetchAll(PDO::FETCH_ASSOC));
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
            array("tag","title","image","description","video","video_status","pdfupload","language_id","grade_id"),
            array("books_details"),
            array("id"=>$currentbooksID)
        )[0];
        $ediCurLanguageId = (int)($booksDetailsArr['language_id'] ?? 0);
        $ediCurGradeId = (int)($booksDetailsArr['grade_id'] ?? 0);

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

// ================= DELETE =================
if (isset($_POST['confirmDeletebooksSubmit'])) {
    $delId = (int) ($_POST['deletebooksID'] ?? 0);
    if ($delId > 0 && $user->CountRows("books_details", array("id" => $delId))) {
        $dr = $user->fetchAll(array("image", "pdfupload"), array("books_details"), array("id" => $delId));
        if (!empty($dr[0])) {
            $img = (string) ($dr[0]['image'] ?? '');
            $pdfF = (string) ($dr[0]['pdfupload'] ?? '');
            if ($img !== '' && is_file("../img/books/" . $img)) {
                @unlink("../img/books/" . $img);
            }
            if ($pdfF !== '' && is_file("../img/books/" . $pdfF)) {
                @unlink("../img/books/" . $pdfF);
            }
        }
        $user->deleteTableRow("books_details", array("id" => $delId));
    }
    echo "<script>alert('Deleted successfully');location.href='./books'</script>";
    exit;
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
        $lg = EdiTaxonomy::contentLanguageGradeFromPost($ediLanguages, $ediGrades);
        if ($lg['language_id'] === null || $lg['grade_id'] === null) {
            echo "<script>alert('Please select a valid language and grade.');history.back();</script>";
            exit;
        }
        $mappedContentB = array("main_cat_id" => 1, "sub_cat_id" => 1);
        if ($ediHasPcat) {
            $mappedContentB = EdiExplorerContent::mapProductSelectionsToContentCategoryIds($ediConn, $epc, $eps);
            if ($mappedContentB["main_cat_id"] === null) {
                $mappedContentB["main_cat_id"] = 1;
            }
            if ($mappedContentB["sub_cat_id"] === null) {
                $mappedContentB["sub_cat_id"] = 1;
            }
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
            "main_cat_id"=>$mappedContentB["main_cat_id"],
            "sub_cat_id"=>$mappedContentB["sub_cat_id"],
            "language_id"=>$lg['language_id'],
            "grade_id"=>$lg['grade_id']
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

        $lg = EdiTaxonomy::contentLanguageGradeFromPost($ediLanguages, $ediGrades);
        if ($lg['language_id'] === null || $lg['grade_id'] === null) {
            echo "<script>alert('Please select a valid language and grade.');history.back();</script>";
            exit;
        }
        $upB = array(
            "tag"=>$inputbooksTag,
            "title"=>$inputbooksTitle,
            "description"=>$inputbooksMainDescription,
            "video"=>$inputbooksVideoUrl,
            "video_status"=>$booksVideoStatus,
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
            $upB["product_category_id"] = $epc > 0 ? $epc : null;
            $upB["product_subcategory_id"] = $eps > 0 ? $eps : null;
            $mappedContentB = EdiExplorerContent::mapProductSelectionsToContentCategoryIds($ediConn, $epc, $eps);
            $upB["main_cat_id"] = $mappedContentB["main_cat_id"];
            $upB["sub_cat_id"] = $mappedContentB["sub_cat_id"];
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

<?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit worksheet" : $adminHeader->getActivePageName()); ?>

<div class="container-fluid py-4">
<?php if (!$editMode) { $ediWorksheetAddCurrent = "books"; require __DIR__ . "/partials/edi_worksheet_type_switch.php"; } ?>
<div class="card p-4">

<form method="post" enctype="multipart/form-data" class="edi-add-worksheet-form">

<?php if ($editMode) { ?>
<h2 class="text-uppercase text-danger font-weight-bold mb-4">Edit worksheet</h2>
<?php } else { ?>
<h2 class="text-uppercase text-danger font-weight-bold mb-4">Add worksheet</h2>
<?php } ?>

<?php
$ediWsTagName = "inputbooksTag";
$ediWsTitleName = "inputbooksTitle";
$ediWsTagValue = $currentbooksTag;
$ediWsTitleValue = $currentbooksTitle;
require __DIR__ . "/partials/edi_worksheet_metadata_form.php";
?>

<div class="row justify-content-center align-items-end mt-4">
  <div class="col-md-6 col-lg-5">
    <div class="form-group mb-0">
      <label class="form-control-label" for="inputbooksMainImage">Main Image</label>
      <input type="file" name="inputbooksMainImage" id="inputbooksMainImage" class="form-control" accept="image/*" <?php echo !$editMode ? "required" : ""; ?> onchange="ediWsPreviewImage(event, 'outputbooksMainImage')">
    </div>
  </div>
  <div class="col-md-4 col-lg-3 text-center mt-3 mt-md-0">
    <img id="outputbooksMainImage" <?php echo $currentbooksMainImage; ?> alt="Preview" class="rounded border bg-light edi-ws-edit-preview-thumb" style="width:100px;height:140px;object-fit:contain;">
  </div>
</div>

<div class="row justify-content-center align-items-end mt-3">
  <div class="col-md-6 col-lg-5">
    <div class="form-group mb-0">
      <label class="form-control-label" for="inputbookspdfupload">Main PDF</label>
      <input type="file" name="inputbookspdfupload" id="inputbookspdfupload" class="form-control" accept=".pdf,application/pdf" <?php echo !$editMode ? "required" : ""; ?>>
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
      <label class="form-control-label" for="inputbooksMainDescription">Main Description</label>
      <textarea name="inputbooksMainDescription" id="inputbooksMainDescription" class="form-control" rows="5" required><?php echo htmlspecialchars($currentbooksMainDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>
  </div>
</div>

<div class="mt-4">
<?php
if ($editMode) {
    echo "<button type='submit' name='updatebooksSubmit' class='btn btn-success mr-2'>Update</button>";
    echo "<button type='button' class='btn btn-danger mr-2' onclick='deletebooksSubmit()'>Delete</button>";
} else {
    echo "<button type='submit' name='addNewbooksSubmit' class='btn btn-success mr-2'>Add</button>";
}
?>
<button type="button" class="btn btn-secondary" onclick="location.href='./books'">Cancel</button>
</div>

</form>

</div>
</div>

</main>

<?php echo $adminHeader->printAdminFooterJS(); ?>
<?php
if ($editMode) {
    $ediDelTitle = htmlspecialchars((string) $currentbooksTitle, ENT_QUOTES, 'UTF-8');
    $ediDelTag = htmlspecialchars((string) $currentbooksTag, ENT_QUOTES, 'UTF-8');
    echo "
<div class='modal fade' id='confirmDeletebooksModal' data-backdrop='static' tabindex='-1' role='dialog' aria-hidden='true' style='margin-top:200px'>
  <div class='modal-dialog' role='document'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Delete worksheet</h5>
      </div>
      <div class='modal-body'>
        <form method='post' class='text-center'>
          <p class='mb-2'>Document title: <strong>$ediDelTitle</strong><br>Tag: <strong>$ediDelTag</strong></p>
          <input type='hidden' name='deletebooksID' value='" . (int) $currentbooksID . "'>
          <input type='submit' class='btn btn-danger btn-sm' name='confirmDeletebooksSubmit' value='Delete'>
          <button class='btn btn-sm btn-secondary' type='button' data-dismiss='modal'>Cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>";
}
?>
<script>
function deletebooksSubmit() {
  $('#confirmDeletebooksModal').modal('show');
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