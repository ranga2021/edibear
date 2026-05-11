<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");
require_once("../classes/edi_blog_extra_media.php");
require_once("../classes/edi_blog_story_sections.php");
require_once("../classes/edi_sitemap.php");

if (!function_exists("edi_blog_tag_split")) {
    /**
     * @return array{0:string,1:string,2:string}
     */
    function edi_blog_tag_split($tag)
    {
        $tag = trim((string) $tag);
        if ($tag === "") {
            return array("", "", "");
        }
        if (strpos($tag, " ||| ") !== false) {
            $p = explode(" ||| ", $tag, 3);
            return array(
                trim((string) ($p[0] ?? "")),
                trim((string) ($p[1] ?? "")),
                trim((string) ($p[2] ?? "")),
            );
        }
        return array("", "", $tag);
    }
}

function edi_blog_tag_merge($lang, $grade, $category)
{
    return trim((string) $lang) . " ||| " . trim((string) $grade) . " ||| " . trim((string) $category);
}

$user = new USER();

if (!$user->is_loggedin()) {
    $user->doLogout();
}

// Long edit forms: idle timer is not refreshed until the next request. Treat a blog
// save POST as activity so the session timeout check does not log the user out mid-edit.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['addNewBlogSubmit']) || isset($_POST['updateBlogSubmit']) || isset($_POST['confirmDeleteBlogSubmit']))) {
    $_SESSION['timeout'] = time();
} elseif (!$user->checkTimeout()) {
    $user->doLogout();
}

$adminHeader = new HEADER("add-blog");
$widgets = new WIDGETS();

$editMode = false;
$currentBlogID = 0;

$currentBlogTag = "";
$currentBlogLanguage = "";
$currentBlogGrade = "";
$currentBlogCategory = "";
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
        $tri = edi_blog_tag_split($currentBlogTag);
        $currentBlogLanguage = $tri[0];
        $currentBlogGrade = $tri[1];
        $currentBlogCategory = $tri[2];
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
while (count($descSlots) < 1) {
    $descSlots[] = array('id' => 0, 'description' => '', 'image_01' => '', 'image_02' => '');
}
$descSlots = array_slice($descSlots, 0, 12);
while (count($descSlots) < 12) {
    $descSlots[] = array('id' => 0, 'description' => '', 'image_01' => '', 'image_02' => '');
}
$descSlotCount = 1;
for ($ediSi = 1; $ediSi <= 12; $ediSi++) {
    $ediSlot = $descSlots[$ediSi - 1];
    $ediD = trim((string) ($ediSlot['description'] ?? ''));
    $ediI1 = trim((string) ($ediSlot['image_01'] ?? ''));
    $ediI2 = trim((string) ($ediSlot['image_02'] ?? ''));
    if ($ediD !== '' || $ediI1 !== '' || $ediI2 !== '') {
        $descSlotCount = $ediSi;
    }
}

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

// ================= DELETE (edit mode only) =================
if (isset($_POST['confirmDeleteBlogSubmit'])) {

    $deleteBlogID = (int) ($_POST['deleteBlogID'] ?? 0);
    if (!$editMode || $deleteBlogID < 1 || $deleteBlogID !== $currentBlogID) {
        echo "<script>alert('Invalid request.');location.href='./blogs';</script>";
        exit;
    }
    if (!$user->CountRows("blog_details", array("id" => $deleteBlogID))) {
        echo "<script>alert('Blog not found.');location.href='./blogs';</script>";
        exit;
    }

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'blogs';

    foreach ($user->fetchAll(array("image_01", "image_02"), array("blog_descriptions"), array("blog_id" => $deleteBlogID)) as $row) {
        foreach (array("image_01", "image_02") as $col) {
            $fn = basename(str_replace("\\", "/", trim((string) ($row[$col] ?? ""))));
            if ($fn !== "") {
                $p = $uploadDir . DIRECTORY_SEPARATOR . $fn;
                if (is_file($p)) {
                    @unlink($p);
                }
            }
        }
    }
    $user->deleteTableRow("blog_descriptions", array("blog_id" => $deleteBlogID));

    EdiBlogExtraMedia::deleteAllForBlog($user, $deleteBlogID, $uploadDir);

    $mainRows = $user->fetchAll(array("image"), array("blog_details"), array("id" => $deleteBlogID));
    if (!empty($mainRows[0]["image"])) {
        $im = basename(str_replace("\\", "/", (string) $mainRows[0]["image"]));
        if ($im !== "") {
            $p = $uploadDir . DIRECTORY_SEPARATOR . $im;
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }

    $user->deleteTableRow("blog_details", array("id" => $deleteBlogID));

    edi_regenerate_public_sitemap($user);
    edi_admin_flash_success('Blog deleted successfully.');
    $user->redirect('./blogs');
}

// ================= SUBMIT =================
if (isset($_POST['addNewBlogSubmit']) || isset($_POST['updateBlogSubmit'])) {

    $inputBlogTag = substr(edi_blog_tag_merge(
        $_POST['inputBlogLanguage'] ?? "",
        $_POST['inputBlogGrade'] ?? "",
        $_POST['inputBlogCategory'] ?? ""
    ), 0, 255);
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

        edi_regenerate_public_sitemap($user);
        edi_admin_flash_success('Blog added successfully.');
        $user->redirect('./blogs');
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

        edi_regenerate_public_sitemap($user);
        edi_admin_flash_success('Blog updated successfully.');
        $user->redirect('./blogs');
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
<div class="card shadow-sm border-0">
<div class="card-body px-4 py-4 edi-blog-form">

<form method="post" enctype="multipart/form-data" id="edi-add-blog-form">

<h1 class="edi-blog-form-title text-uppercase mb-4"><?php echo $editMode ? "Edit blog" : "Add blog"; ?></h1>

<div class="row">
  <div class="col-md-4 mb-3">
    <label for="inputBlogLanguage">Language</label>
    <input type="text" name="inputBlogLanguage" id="inputBlogLanguage" class="form-control" value="<?php echo htmlspecialchars($currentBlogLanguage, ENT_QUOTES, 'UTF-8'); ?>" placeholder="English">
  </div>
  <div class="col-md-4 mb-3">
    <label for="inputBlogGrade">Grade</label>
    <input type="text" name="inputBlogGrade" id="inputBlogGrade" class="form-control" value="<?php echo htmlspecialchars($currentBlogGrade, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Grade 1">
  </div>
  <div class="col-md-4 mb-3">
    <label for="inputBlogCategory">Category</label>
    <input type="text" name="inputBlogCategory" id="inputBlogCategory" class="form-control" value="<?php echo htmlspecialchars($currentBlogCategory, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Writing">
  </div>
</div>
<p class="text-muted small mb-3">These three fields are saved together as the blog tag (shown on the post). Older posts used a single tag — edit to split into language / grade / category if you like.</p>

<div class="row">
  <div class="col-12 mb-3">
    <label for="inputBlogTitle">Document title</label>
    <input type="text" name="inputBlogTitle" id="inputBlogTitle" class="form-control" required value="<?php echo htmlspecialchars($currentBlogTitle, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Letter A writing">
  </div>
</div>

<div class="row">
  <div class="col-12 mb-3">
    <label for="inputBlogMainImage">Main image</label>
    <div class="d-flex flex-wrap align-items-start" style="gap:12px;">
      <div style="flex:1;min-width:220px;max-width:360px;">
        <input type="file" name="inputBlogMainImage" id="inputBlogMainImage" class="form-control" accept="image/*" <?php echo !$editMode ? "required" : ""; ?>>
      </div>
      <div id="edi_blog_main_preview" class="edi-blog-thumb border rounded bg-light d-flex align-items-center justify-content-center text-muted small" style="width:120px;height:120px;flex-shrink:0;">Preview</div>
    </div>
    <?php if ($editMode && $currentBlogMainImage !== ""): ?>
    <p class="mt-2 mb-0"><img <?php echo $currentBlogMainImage; ?> alt="" class="rounded" style="max-height:160px;"></p>
    <?php endif; ?>
  </div>
</div>

<div class="row">
  <div class="col-12 mb-3">
    <label for="inputBlogMainDescription">Main description</label>
    <textarea name="inputBlogMainDescription" id="inputBlogMainDescription" class="form-control" rows="8" required placeholder="Intro text for the post"><?php echo str_replace('</textarea', '<\/textarea', (string) $currentBlogMainDescription); ?></textarea>
  </div>
</div>

<h2 class="h6 font-weight-bold mb-3">Description</h2>
<p class="text-muted small mb-2">Optional blocks (up to 12). Each block: text plus up to two images. HTML <code>&lt;br&gt;</code> is allowed.</p>
<input type="hidden" name="desc_slot_count" id="desc_slot_count" value="<?php echo (int) $descSlotCount; ?>">
<?php
for ($s = 1; $s <= 12; $s++) {
    $slot = $descSlots[$s - 1];
    $im1 = basename(str_replace('\\', '/', (string) ($slot['image_01'] ?? '')));
    $im2 = basename(str_replace('\\', '/', (string) ($slot['image_02'] ?? '')));
    $hiddenBlock = ($s > $descSlotCount) ? ' style="display:none;"' : '';
    ?>
<div id="edi-desc-block-<?php echo $s; ?>" class="edi-blog-desc-block mb-3 p-3 rounded border bg-white"<?php echo $hiddenBlock; ?>>
  <div class="d-flex justify-content-between align-items-center mb-2">
    <span class="font-weight-bold text-secondary"><?php echo str_pad((string) $s, 2, "0", STR_PAD_LEFT); ?>.</span>
  </div>
  <div class="form-group mb-3">
    <label class="sr-only">Description <?php echo $s; ?></label>
    <textarea name="inputBlogDescription<?php echo $s; ?>" class="form-control" rows="5" placeholder="Section text"><?php echo str_replace('</textarea', '<\/textarea', (string) ($slot['description'] ?? '')); ?></textarea>
  </div>
  <div class="row">
    <div class="col-md-6 mb-2">
      <label>Image 1</label>
      <input type="file" name="inputBlogImageOne<?php echo $s; ?>" class="form-control" accept="image/*">
      <?php
      if ($im1 !== '') {
          $him1 = htmlspecialchars($im1, ENT_QUOTES, 'UTF-8');
          echo '<input type="hidden" name="desc_image_01_existing_' . $s . '" id="desc_image_01_existing_' . $s . '" value="' . $him1 . '">';
          echo '<div class="mt-1"><small class="text-muted">Current: ' . $him1 . '</small><br><img src="../img/blogs/' . $him1 . '" alt="" class="rounded" style="max-height:72px;"></div>';
          echo '<button type="button" class="btn btn-link text-danger btn-sm p-0 mt-1 edi-clear-desc-img" data-target="desc_image_01_existing_' . $s . '">Remove</button>';
      }
      ?>
    </div>
    <div class="col-md-6 mb-2">
      <label>Image 2</label>
      <input type="file" name="inputBlogImageTwo<?php echo $s; ?>" class="form-control" accept="image/*">
      <?php
      if ($im2 !== '') {
          $him2 = htmlspecialchars($im2, ENT_QUOTES, 'UTF-8');
          echo '<input type="hidden" name="desc_image_02_existing_' . $s . '" id="desc_image_02_existing_' . $s . '" value="' . $him2 . '">';
          echo '<div class="mt-1"><small class="text-muted">Current: ' . $him2 . '</small><br><img src="../img/blogs/' . $him2 . '" alt="" class="rounded" style="max-height:72px;"></div>';
          echo '<button type="button" class="btn btn-link text-danger btn-sm p-0 mt-1 edi-clear-desc-img" data-target="desc_image_02_existing_' . $s . '">Remove</button>';
      }
      ?>
    </div>
  </div>
</div>
<?php } ?>

<p class="mb-4"><button type="button" class="btn btn-link text-success font-weight-bold p-0" id="edi_blog_add_desc">Add more Descriptions +</button></p>

<div class="row align-items-center mb-4">
  <div class="col-md-8 mb-3 mb-md-0">
    <label for="inputBlogVideoUrl">Activity video</label>
    <input type="url" name="inputBlogVideoUrl" id="inputBlogVideoUrl" class="form-control" value="<?php echo htmlspecialchars($currentBlogVideoUrl, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.youtube.com/watch?v=…">
    <small class="text-muted">Paste a YouTube (or embeddable) link. Shown on the blog page when the toggle is on.</small>
  </div>
  <div class="col-md-4 d-flex align-items-center justify-content-md-end">
    <span class="small font-weight-bold text-muted mr-3 mb-0">Show on page</span>
    <label class="edi-product-status-switch mb-0">
      <input type="checkbox" id="blogVideoStatus" name="blogVideoStatus" value="1" <?php echo $currentBlogVideoStatus; ?>>
      <span class="edi-slider"></span>
    </label>
  </div>
</div>

<h2 class="h6 font-weight-bold mb-2">Extra gallery (optional)</h2>
<p class="text-muted small mb-3">Up to eight items after story sections. Run <code>sql/migration_blog_extra_media.sql</code> if needed.</p>
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

<div class="mt-4 mb-2 edi-admin-form-actions">
<?php
if ($editMode) {
    echo "<button type='submit' name='updateBlogSubmit' class='btn btn-success'>Update</button>";
    echo "<a href='./blogs' class='btn btn-secondary'>Cancel</a>";
} else {
    echo "<button type='submit' name='addNewBlogSubmit' class='btn btn-success'>Add</button>";
    echo "<a href='./blogs' class='btn btn-secondary'>Cancel</a>";
}
?>
</div>

</form>

<?php if ($editMode) { ?>
<form method="post" class="mb-3" onsubmit="return confirm('Delete this blog post and all section images? This cannot be undone.');">
  <input type="hidden" name="deleteBlogID" value="<?php echo (int) $currentBlogID; ?>">
  <button type="submit" name="confirmDeleteBlogSubmit" value="1" class="btn btn-danger">Delete post</button>
</form>
<?php } ?>

</div>
</div>
</div>

</main>

<script>
(function () {
    var inp = document.getElementById("inputBlogMainImage");
    var prev = document.getElementById("edi_blog_main_preview");
    if (inp && prev) {
        inp.addEventListener("change", function () {
            var f = inp.files && inp.files[0];
            if (!f) return;
            var r = new FileReader();
            r.onload = function (ev) {
                prev.innerHTML = "<img src=\"" + ev.target.result + "\" class=\"rounded\" style=\"width:100%;height:100%;object-fit:cover;\" alt=\"\">";
            };
            r.readAsDataURL(f);
        });
    }
    var addBtn = document.getElementById("edi_blog_add_desc");
    var countEl = document.getElementById("desc_slot_count");
    if (addBtn && countEl) {
        addBtn.addEventListener("click", function () {
            var vis = parseInt(countEl.value, 10) || 1;
            if (vis >= 12) return;
            vis++;
            countEl.value = String(vis);
            var blk = document.getElementById("edi-desc-block-" + vis);
            if (blk) blk.style.display = "";
        });
    }
    document.querySelectorAll(".edi-clear-desc-img").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var id = btn.getAttribute("data-target");
            var h = id ? document.getElementById(id) : null;
            if (h) h.value = "";
            var wrap = btn.previousElementSibling;
            if (wrap && wrap.tagName === "DIV") wrap.style.display = "none";
            btn.style.display = "none";
        });
    });
})();
</script>

<?php echo $adminHeader->printAdminFooterJS(); ?>

</body>
</html>