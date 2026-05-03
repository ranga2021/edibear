<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");
require_once("../classes/edi_blog_extra_media.php");
require_once("../classes/edi_blog_story_sections.php");

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

$extraSlots = array();
for ($z = 0; $z < 8; $z++) {
    $extraSlots[$z] = array('kind' => 'image', 'caption' => '', 'video' => '', 'existing' => '');
}
$descSlots = array();
if (!empty($editMode) && $editMode && !empty($currentBlogID)) {
    $descRows = $user->fetchAll(
        array('id', 'description', 'image_01', 'image_02'),
        array('blog_descriptions'),
        array('blog_id' => $currentBlogID),
        'id ASC'
    );
    if (is_array($descRows)) {
        foreach ($descRows as $dr) {
            $descSlots[] = $dr;
        }
    }
}
while (count($descSlots) < 4) {
    $descSlots[] = array('id' => 0, 'description' => '', 'image_01' => '', 'image_02' => '');
}
$descSlots = array_slice($descSlots, 0, 12);
$descSlotCount = count($descSlots);

if (!empty($editMode) && $editMode && !empty($currentBlogID) && EdiBlogExtraMedia::tableExists($user->getConnection())) {
    $loaded = EdiBlogExtraMedia::fetchForBlog($user->getConnection(), (int) $currentBlogID);
    foreach ($loaded as $ix => $row) {
        if ($ix >= 8) {
            break;
        }
        $extraSlots[$ix]['kind'] = (isset($row['media_type']) && $row['media_type'] === 'video') ? 'video' : 'image';
        $extraSlots[$ix]['caption'] = (string) ($row['caption'] ?? '');
        if ($extraSlots[$ix]['kind'] === 'video') {
            $extraSlots[$ix]['video'] = (string) ($row['path'] ?? '');
        } else {
            $extraSlots[$ix]['existing'] = (string) ($row['path'] ?? '');
        }
    }
}

// ================= SUBMIT =================
if (isset($_POST['addNewBlogSubmit']) || isset($_POST['updateBlogSubmit'])) {

    $inputBlogTag = htmlspecialchars($_POST['inputBlogTag'] ?? "");
    $inputBlogTitle = htmlspecialchars($_POST['inputBlogTitle'] ?? "");
    $inputBlogMainDescription = strip_tags($_POST['inputBlogMainDescription'] ?? "", "<br>");
    $inputBlogVideoUrl = htmlspecialchars($_POST['inputBlogVideoUrl'] ?? "", ENT_QUOTES, 'UTF-8');
    $blogVideoStatus = isset($_POST['blogVideoStatus']) ? 1 : 0;

    $descSlotCountPost = min(12, max(1, (int) ($_POST['desc_slot_count'] ?? 4)));

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

        $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'blogs';

        EdiBlogStorySections::syncFromAdminPost($user, (int) $blogID, $descSlotCountPost, $uploadDir, false);

        EdiBlogExtraMedia::syncFromAdminPost($user, (int) $blogID, $uploadDir);

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

        $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'blogs';

        EdiBlogStorySections::syncFromAdminPost($user, (int) $currentBlogID, $descSlotCountPost, $uploadDir, true);

        EdiBlogExtraMedia::syncFromAdminPost($user, $currentBlogID, $uploadDir);

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
echo $widgets->inputGroup("Tags (use / between topics)", "inputBlogTag", "col-md-6", $currentBlogTag);
echo $widgets->inputGroup("Blog Title", "inputBlogTitle", "col-md-6", $currentBlogTitle);
?>
</div>
<div class="row">
    <div class="col-12">
        <small class="form-text text-muted">Example: <kbd>Hand craft</kbd> / <kbd>Animals</kbd> / <kbd>Fun</kbd> — each segment appears as its own tag at the top of The Hidden Den and on the post page (same style as free download topics).</small>
    </div>
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

<div class="row mt-3">
    <div class="col-md-8">
        <label for="inputBlogVideoUrl">Main YouTube URL (optional)</label>
        <input type="url" name="inputBlogVideoUrl" id="inputBlogVideoUrl" class="form-control" value="<?php echo htmlspecialchars($currentBlogVideoUrl, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.youtube.com/watch?v=…">
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" id="blogVideoStatus" name="blogVideoStatus" value="1" <?php echo $currentBlogVideoStatus; ?>>
            <label class="form-check-label" for="blogVideoStatus">Show this video on the blog page</label>
        </div>
    </div>
</div>

<h5 class="mt-4 mb-2">Story sections (optional)</h5>
<p class="text-muted text-sm mb-3">Up to twelve blocks (at least four empty slots on new posts). Each can include a description (HTML allowed) and up to two images. On <strong>Update</strong>, all sections are replaced by what you save here — images you keep must stay listed with their previews (or re-upload).</p>
<input type="hidden" name="desc_slot_count" value="<?php echo (int) $descSlotCount; ?>">
<?php
foreach ($descSlots as $idx => $slot) {
    $s = (int) $idx + 1;
    $im1 = basename(str_replace('\\', '/', (string) ($slot['image_01'] ?? '')));
    $im2 = basename(str_replace('\\', '/', (string) ($slot['image_02'] ?? '')));
    ?>
<div class="card mb-3 border-light">
    <div class="card-body">
        <h6 class="text-primary">Section <?php echo $s; ?></h6>
        <div class="form-group">
            <label>Description</label>
            <textarea name="inputBlogDescription<?php echo $s; ?>" class="form-control" rows="4"><?php echo str_replace('</textarea', '<\/textarea', (string) ($slot['description'] ?? '')); ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label>Image 1</label>
                <input type="file" name="inputBlogImageOne<?php echo $s; ?>" class="form-control-file" accept="image/*">
                <?php
                if ($im1 !== '') {
                    $him1 = htmlspecialchars($im1, ENT_QUOTES, 'UTF-8');
                    echo '<input type="hidden" name="desc_image_01_existing_' . $s . '" value="' . $him1 . '">';
                    echo '<div class="mt-1"><small class="text-muted">Current: ' . $him1 . '</small><br><img src="../img/blogs/' . $him1 . '" alt="" style="max-height:80px;border-radius:4px;"></div>';
                }
                ?>
            </div>
            <div class="col-md-6">
                <label>Image 2</label>
                <input type="file" name="inputBlogImageTwo<?php echo $s; ?>" class="form-control-file" accept="image/*">
                <?php
                if ($im2 !== '') {
                    $him2 = htmlspecialchars($im2, ENT_QUOTES, 'UTF-8');
                    echo '<input type="hidden" name="desc_image_02_existing_' . $s . '" value="' . $him2 . '">';
                    echo '<div class="mt-1"><small class="text-muted">Current: ' . $him2 . '</small><br><img src="../img/blogs/' . $him2 . '" alt="" style="max-height:80px;border-radius:4px;"></div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
    <?php
}
?>

<h5 class="mt-4 mb-2">Extra gallery images &amp; videos (optional)</h5>
<p class="text-muted text-sm mb-3">Up to eight items shown in order after story sections. Use YouTube watch or share links for videos. Re-saving replaces this list — leave a row empty to skip it. Run <code>sql/migration_blog_extra_media.sql</code> once if the table does not exist.</p>
<?php
for ($ei = 0; $ei < 8; $ei++) {
    $slot = $extraSlots[$ei];
    $kind = $slot['kind'] === 'video' ? 'video' : 'image';
    ?>
<div class="card mb-2 border-light">
    <div class="card-body py-3">
        <div class="row align-items-end">
            <div class="col-md-2">
                <label class="d-block">Type</label>
                <select name="extra_kind_<?php echo (int) $ei; ?>" class="form-control form-control-sm">
                    <option value="image"<?php echo $kind === 'image' ? ' selected' : ''; ?>>Image</option>
                    <option value="video"<?php echo $kind === 'video' ? ' selected' : ''; ?>>YouTube video</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="d-block">YouTube URL (if type is video)</label>
                <input type="url" name="extra_video_url_<?php echo (int) $ei; ?>" class="form-control form-control-sm" value="<?php echo htmlspecialchars($slot['video'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://youtu.be/…">
            </div>
            <div class="col-md-4">
                <label class="d-block">Image file (if type is image)</label>
                <input type="file" name="extra_image_<?php echo (int) $ei; ?>" class="form-control-file" accept="image/*">
                <?php
                if (!empty($slot['existing']) && $kind === 'image') {
                    $ex = htmlspecialchars(basename($slot['existing']), ENT_QUOTES, 'UTF-8');
                    echo '<input type="hidden" name="extra_image_existing_' . (int) $ei . '" value="' . $ex . '">';
                    echo '<div class="mt-1"><small class="text-muted">Current: ' . $ex . '</small><br><img src="../img/blogs/' . $ex . '" alt="" style="max-height:70px;border-radius:4px;"></div>';
                }
                ?>
            </div>
            <div class="col-md-2">
                <label class="d-block">Caption</label>
                <input type="text" name="extra_caption_<?php echo (int) $ei; ?>" class="form-control form-control-sm" value="<?php echo htmlspecialchars($slot['caption'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="250">
            </div>
        </div>
    </div>
</div>
    <?php
}
?>

<div class="mt-4 edi-admin-form-actions">
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