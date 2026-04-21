<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");

$user = new USER();

if (!$user->is_loggedin()) {
    $user->doLogout();
}

// Long edit forms: idle timer is not refreshed until the next request. Treat a blog
// save POST as activity so createSiteMap's checkTimeout does not log the user out.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['addNewBlogSubmit']) || isset($_POST['updateBlogSubmit']))) {
    $_SESSION['timeout'] = time();
} elseif (!$user->checkTimeout()) {
    $user->doLogout();
}

$adminHeader = new HEADER("add-blog");
$widgets = new WIDGETS();

$editMode = false;
$currentBlogID = 0;

$currentBlogTag = "";
$currentBlogTitle = "";
$currentBlogMainDescription = "";
$currentBlogVideoUrl = "";
$currentBlogVideoStatus = "";
$currentBlogMainImage = "";

// ================= EDIT MODE =================
if (isset($_GET['id']) && $_GET['id'] > 0) {

    $currentBlogID = (int)$_GET['id'];

    if ($user->CountRows("blog_details", array("id"=>$currentBlogID))) {

        $editMode = true;

        $blogDetailsArr = $user->fetchAll(
            array("tag","title","image","description","video","video_status"),
            array("blog_details"),
            array("id"=>$currentBlogID)
        )[0];

        $currentBlogTag = $blogDetailsArr['tag'];
        $currentBlogTitle = $blogDetailsArr['title'];
        $currentBlogMainDescription = $blogDetailsArr['description'];
        $currentBlogVideoUrl = $blogDetailsArr['video'];
        $currentBlogVideoStatus = ($blogDetailsArr['video_status']=='1') ? "checked" : "";

        if (!empty($blogDetailsArr['image'])) {
            $currentBlogMainImage = "src='".$widgets->createCachelessImage("../img/blogs/".$blogDetailsArr['image'])."'";
        }

    } else {
        $user->redirect("./add-blog");
    }
}

// ================= SUBMIT =================
if (isset($_POST['addNewBlogSubmit']) || isset($_POST['updateBlogSubmit'])) {

    $inputBlogTag = htmlspecialchars($_POST['inputBlogTag'] ?? "");
    $inputBlogTitle = htmlspecialchars($_POST['inputBlogTitle'] ?? "");
    $inputBlogMainDescription = strip_tags($_POST['inputBlogMainDescription'] ?? "", "<br>");
    $inputBlogVideoUrl = htmlspecialchars($_POST['inputBlogVideoUrl'] ?? "");
    $blogVideoStatus = $_POST['blogVideoStatus'] ?? 0;

    $howManyDescriptions = isset($_POST['howManyDescriptions']) ? (int)$_POST['howManyDescriptions'] : 1;

    // ================= ADD =================
    if (isset($_POST['addNewBlogSubmit'])) {

        // IMAGE FIRST (IMPORTANT)
        $imageName = "";
        if (!empty($_FILES["inputBlogMainImage"]["name"])) {

            $ext = pathinfo($_FILES["inputBlogMainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = time().".".$ext;

            move_uploaded_file($_FILES["inputBlogMainImage"]["tmp_name"], "../img/blogs/".$imageName);
        }

        // INSERT WITH IMAGE (FIX)
        $blogID = $user->insertTable("blog_details", array(
            "tag"=>$inputBlogTag,
            "title"=>$inputBlogTitle,
            "description"=>$inputBlogMainDescription,
            "video"=>$inputBlogVideoUrl,
            "video_status"=>$blogVideoStatus,
            "image"=>$imageName,
            "status"=>1
        ), true);

        // BLOG DESCRIPTIONS
        for ($i=1; $i<=$howManyDescriptions; $i++) {

            $desc = strip_tags($_POST["inputBlogDescription$i"] ?? "", "<br>");

            if (!empty($desc) ||
                !empty($_FILES["inputBlogImageOne$i"]["name"]) ||
                !empty($_FILES["inputBlogImageTwo$i"]["name"])) {

                $descID = $user->insertTable("blog_descriptions", array(
                    "blog_id"=>$blogID,
                    "description"=>$desc
                ), true);

                $img1 = $img2 = "";

                if (!empty($_FILES["inputBlogImageOne$i"]["name"])) {
                    $ext = pathinfo($_FILES["inputBlogImageOne$i"]["name"], PATHINFO_EXTENSION);
                    $img1 = "$blogID-$descID-1.$ext";
                    move_uploaded_file($_FILES["inputBlogImageOne$i"]["tmp_name"], "../img/blogs/".$img1);
                }

                if (!empty($_FILES["inputBlogImageTwo$i"]["name"])) {
                    $ext = pathinfo($_FILES["inputBlogImageTwo$i"]["name"], PATHINFO_EXTENSION);
                    $img2 = "$blogID-$descID-2.$ext";
                    move_uploaded_file($_FILES["inputBlogImageTwo$i"]["tmp_name"], "../img/blogs/".$img2);
                }

                $user->updateTable("blog_descriptions", array(
                    "image_01"=>$img1,
                    "image_02"=>$img2
                ), array("id"=>$descID));
            }
        }

        echo "<script>alert('Blog added successfully');location.href='./createSiteMap?redirect=blogs'</script>";
    }

    // ================= UPDATE =================
    if (isset($_POST['updateBlogSubmit'])) {

        $user->updateTable("blog_details", array(
            "tag"=>$inputBlogTag,
            "title"=>$inputBlogTitle,
            "description"=>$inputBlogMainDescription,
            "video"=>$inputBlogVideoUrl,
            "video_status"=>$blogVideoStatus
        ), array("id"=>$currentBlogID));

        // IMAGE UPDATE
        if (!empty($_FILES["inputBlogMainImage"]["name"])) {

            if (!empty($blogDetailsArr['image']) && file_exists("../img/blogs/".$blogDetailsArr['image'])) {
                unlink("../img/blogs/".$blogDetailsArr['image']);
            }

            $ext = pathinfo($_FILES["inputBlogMainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = $currentBlogID.".".$ext;

            move_uploaded_file($_FILES["inputBlogMainImage"]["tmp_name"], "../img/blogs/".$imageName);

            $user->updateTable("blog_details", array("image"=>$imageName), array("id"=>$currentBlogID));
        }

        echo "<script>alert('Blog updated successfully');location.href='./createSiteMap?redirect=blogs'</script>";
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

<?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit Blog" : "Add Blog"); ?>

<div class="container-fluid py-4">
<div class="card p-3">

<form method="post" enctype="multipart/form-data">

<div class="row">
<?php
echo $widgets->inputGroup("Blog Tag", "inputBlogTag", "col-md-6", $currentBlogTag);
echo $widgets->inputGroup("Blog Title", "inputBlogTitle", "col-md-6", $currentBlogTitle);
?>
</div>

<div class="row mt-3">
<div class="col-md-6">
<label>Main Image</label>
<input type="file" name="inputBlogMainImage" class="form-control" <?php echo !$editMode ? "required" : ""; ?>>
</div>

<div class="col-md-6">
<img id="outputBlogMainImage" <?php echo $currentBlogMainImage; ?> style="max-height:200px;">
</div>
</div>

<div class="row mt-3">
<div class="col-12">
<label>Description</label>
<textarea name="inputBlogMainDescription" class="form-control" required><?php echo $currentBlogMainDescription;?></textarea>
</div>
</div>

<div class="mt-4">
<?php
if ($editMode) {
    echo "<button type='submit' name='updateBlogSubmit' class='btn btn-primary'>Update</button>";
} else {
    echo "<button type='submit' name='addNewBlogSubmit' class='btn btn-success'>Add</button>";
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