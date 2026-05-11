<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ SESSION FIX (same as add-books)
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");
require_once("../classes/edi_sitemap.php");

$adminHeader = new HEADER("add-ad1");
$user = new USER();
$widgets = new WIDGETS();

$editMode = false;
$currentad1ID = 0;

$currentad1Tag = "";
$currentad1Title = "";
$currentad1MainDescription = "";
$currentad1VideoUrl = "";
$currentad1VideoStatus = "";
$currentad1MainImage = "";
$currentad1adlink = "";

// ================= EDIT MODE =================
if (isset($_GET['id']) && $_GET['id'] > 0) {

    $currentad1ID = (int)$_GET['id'];

    if ($user->CountRows("ad1_details", array("id"=>$currentad1ID))) {

        $editMode = true;

        $ad1DetailsArr = $user->fetchAll(
            array("tag","title","image","description","video","video_status","adlink"),
            array("ad1_details"),
            array("id"=>$currentad1ID)
        )[0];

        $currentad1Tag = $ad1DetailsArr['tag'];
        $currentad1Title = $ad1DetailsArr['title'];
        $currentad1MainDescription = $ad1DetailsArr['description'];
        $currentad1VideoUrl = $ad1DetailsArr['video'];
        $currentad1VideoStatus = ($ad1DetailsArr['video_status']=='1') ? "checked" : "";
        $currentad1adlink = $ad1DetailsArr['adlink'];

        if (!empty($ad1DetailsArr['image'])) {
            $currentad1MainImage = "src='".$widgets->createCachelessImage("../img/ad1/".$ad1DetailsArr['image'])."'";
        }

    } else {
        $user->redirect("./add-ad1");
    }
}

// ================= DELETE (separate from add/update POST) =================
if (isset($_POST['confirmDeletead1Submit'])) {

    $deletead1ID = (int) ($_POST['deletead1ID'] ?? 0);
    if (!$editMode || $deletead1ID < 1 || $deletead1ID !== $currentad1ID || !$user->CountRows("ad1_details", array("id" => $deletead1ID))) {
        echo "<script>alert('Invalid request.');location.href='./add-ad1'</script>";
        exit;
    }

    foreach ($user->fetchAll(array("image_01", "image_02"), array("ad1_descriptions"), array("ad1_id" => $deletead1ID)) as $row) {
        if (!empty($row['image_01'])) {
            @unlink("../img/ad1/" . $row['image_01']);
        }
        if (!empty($row['image_02'])) {
            @unlink("../img/ad1/" . $row['image_02']);
        }
    }

    $user->deleteTableRow("ad1_descriptions", array("ad1_id" => $deletead1ID));

    $main = $user->fetchAll(array("image"), array("ad1_details"), array("id" => $deletead1ID));
    if (!empty($main[0]['image'])) {
        @unlink("../img/ad1/" . $main[0]['image']);
    }

    $user->deleteTableRow("ad1_details", array("id" => $deletead1ID));

    edi_regenerate_public_sitemap($user);
    edi_admin_flash_success('Ad1 deleted successfully.');
    $user->redirect('./ad1');
    exit;
}

// ================= FORM SUBMIT =================
if (isset($_POST['addNewad1Submit']) || isset($_POST['updatead1Submit'])) {

    $inputad1Tag = htmlspecialchars($_POST['inputad1Tag'] ?? "");
    $inputad1Title = htmlspecialchars($_POST['inputad1Title'] ?? "");
    $inputad1MainDescription = strip_tags($_POST['inputad1MainDescription'] ?? "", "<br>");
    $inputad1VideoUrl = htmlspecialchars($_POST['inputad1VideoUrl'] ?? "");
    $inputad1adlink = htmlspecialchars($_POST['inputad1adlink'] ?? "");
    $ad1VideoStatus = $_POST['ad1VideoStatus'] ?? 0;

    // ================= ADD =================
    if (isset($_POST['addNewad1Submit'])) {

        // IMAGE
        $imageName = "";
        if (!empty($_FILES["inputad1MainImage"]["name"])) {
            $ext = pathinfo($_FILES["inputad1MainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = time().".".$ext;
            move_uploaded_file($_FILES["inputad1MainImage"]["tmp_name"], "../img/ad1/".$imageName);
        }

        // INSERT
        $ad1ID = $user->insertTable("ad1_details", array(
            "tag"=>$inputad1Tag,
            "title"=>$inputad1Title,
            "description"=>$inputad1MainDescription,
            "video"=>$inputad1VideoUrl,
            "video_status"=>$ad1VideoStatus,
            "image"=>$imageName,
            "adlink"=>$inputad1adlink
        ), true);

        edi_regenerate_public_sitemap($user);
        edi_admin_flash_success('Ad added successfully.');
        $user->redirect('./ad1');
    }

    // ================= UPDATE =================
    if (isset($_POST['updatead1Submit'])) {

        $user->updateTable("ad1_details", array(
            "tag"=>$inputad1Tag,
            "title"=>$inputad1Title,
            "description"=>$inputad1MainDescription,
            "video"=>$inputad1VideoUrl,
            "video_status"=>$ad1VideoStatus,
            "adlink"=>$inputad1adlink
        ), array("id"=>$currentad1ID));

        // IMAGE UPDATE
        if (!empty($_FILES["inputad1MainImage"]["name"])) {

            if (!empty($ad1DetailsArr['image']) && file_exists("../img/ad1/".$ad1DetailsArr['image'])) {
                unlink("../img/ad1/".$ad1DetailsArr['image']);
            }

            $ext = pathinfo($_FILES["inputad1MainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = $currentad1ID.".".$ext;

            move_uploaded_file($_FILES["inputad1MainImage"]["tmp_name"], "../img/ad1/".$imageName);

            $user->updateTable("ad1_details", array("image"=>$imageName), array("id"=>$currentad1ID));
        }

        edi_regenerate_public_sitemap($user);
        edi_admin_flash_success('Ad updated successfully.');
        $user->redirect('./ad1');
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

<?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit ad1" : "Add ad1"); ?>

<div class="container-fluid py-4">
<div class="card p-3">

<form method="post" enctype="multipart/form-data">

<div class="row">
<?php
echo $widgets->inputGroup("Ad Link", "inputad1adlink", "col-md-12", $currentad1adlink);
?>
</div>

<div class="row mt-3">
<div class="col-md-6">
<label>Main Image</label>
<input type="file" name="inputad1MainImage" class="form-control" <?php echo !$editMode ? "required" : ""; ?>>
</div>

<div class="col-md-6">
<img id="outputad1MainImage" <?php echo $currentad1MainImage; ?> style="max-height:200px;">
</div>
</div>

<div class="mt-4">
<?php
if ($editMode) {
    echo "<button type='submit' name='updatead1Submit' class='btn btn-primary'>Update</button>";
    echo " <a href='./ad1' class='btn btn-secondary'>Cancel</a>";
} else {
    echo "<button type='submit' name='addNewad1Submit' class='btn btn-success'>Add</button>";
}
?>
</div>

</form>

<?php if ($editMode) { ?>
<form method="post" class="mt-2" onsubmit="return confirm('Delete this ad and its images? This cannot be undone.');">
  <input type="hidden" name="deletead1ID" value="<?php echo (int) $currentad1ID; ?>">
  <button type="submit" name="confirmDeletead1Submit" value="1" class="btn btn-danger btn-sm">Delete</button>
</form>
<?php } ?>

</div>
</div>

</main>

<?php echo $adminHeader->printAdminFooterJS(); ?>

</body>
</html>