<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("testimonials");
$user = new USER();

$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
if ($id < 1) {
    header("Location: ./testimonials");
    exit;
}

$rows = $user->fetchAll(
    array("id", "user_id", "name", "ratings", "one_word", "review", "status", "timestamp"),
    array("testimonials"),
    array("id" => $id)
);
if (empty($rows)) {
    header("Location: ./testimonials");
    exit;
}
$t = $rows[0];

$touristRows = $user->fetchAll(array("id", "name", "country"), array("tourists"), array("id" => $t["user_id"]));
if (empty($touristRows)) {
    header("Location: ./testimonials");
    exit;
}
$tr = $touristRows[0];

$imgRows = $user->fetchAll(array("image"), array("testimonials_images"), array("testimonial_id" => $id));
$currentImage = (!empty($imgRows[0]["image"])) ? (string) $imgRows[0]["image"] : "";

if (isset($_POST["adminEditTestimonialSubmit"])) {
    $postId = (int) ($_POST["testimonial_id"] ?? 0);
    if ($postId !== $id) {
        echo "<script>alert('Invalid testimonial.');location.href='./testimonials';</script>";
        exit;
    }

    $reviewerName = trim((string) ($_POST["reviewerName"] ?? ""));
    $reviewerCountry = trim((string) ($_POST["reviewerCountry"] ?? ""));
    $oneWord = trim((string) ($_POST["oneWord"] ?? ""));
    $review = trim((string) ($_POST["reviewBody"] ?? ""));
    $ratings = (int) ($_POST["ratings"] ?? 5);
    $ratings = max(1, min(5, $ratings));

    $reviewerName = substr(strip_tags($reviewerName), 0, 100);
    $reviewerCountry = substr(strip_tags($reviewerCountry), 0, 20);
    $oneWord = substr(strip_tags($oneWord), 0, 50);
    $review = substr(strip_tags($review), 0, 500);

    if ($reviewerName === "" || $reviewerCountry === "" || $oneWord === "" || $review === "") {
        echo "<script>alert('Please fill in reviewer name, country, headline, and review.');history.back();</script>";
        exit;
    }

    $touristId = (int) $t["user_id"];

    $user->updateTable(
        "tourists",
        array(
            "name" => $reviewerName,
            "country" => $reviewerCountry,
        ),
        array("id" => $touristId)
    );

    $user->updateTable(
        "testimonials",
        array(
            "name" => $reviewerName,
            "ratings" => $ratings,
            "one_word" => $oneWord,
            "review" => $review,
        ),
        array("id" => $id)
    );

    if (!empty($_FILES["adminTestimonialImage"]["name"]) && (int) $_FILES["adminTestimonialImage"]["error"] === UPLOAD_ERR_OK) {
        $allowedTypes = array("jpg", "jpeg", "png", "webp");
        $fileExt = strtolower(pathinfo($_FILES["adminTestimonialImage"]["name"], PATHINFO_EXTENSION));
        if (in_array($fileExt, $allowedTypes, true)) {
            $imgDir = __DIR__ . "/../img/testimonials/";
            if (!is_dir($imgDir)) {
                mkdir($imgDir, 0777, true);
            }
            foreach ($user->fetchAll(array("image"), array("testimonials_images"), array("testimonial_id" => $id)) as $oldRow) {
                $p = $imgDir . $oldRow["image"];
                if (is_file($p)) {
                    @unlink($p);
                }
            }
            $user->deleteTableRow("testimonials_images", array("testimonial_id" => $id));

            $newFileName = substr(bin2hex(random_bytes(6)), 0, 12) . "." . $fileExt;
            $dest = $imgDir . $newFileName;
            if (strlen($newFileName) <= 20 && move_uploaded_file($_FILES["adminTestimonialImage"]["tmp_name"], $dest)) {
                $user->insertTable(
                    "testimonials_images",
                    array(
                        "testimonial_id" => $id,
                        "image" => $newFileName,
                    )
                );
            }
        }
    }

    echo "<script>alert('Testimonial updated successfully');location.href='./testimonials';</script>";
    exit;
}

$rawStatus = (int) $t["status"];
$statusLabel = ($rawStatus === 1) ? "Approved" : (($rawStatus === -1) ? "Rejected" : "Pending");
?>
<script>
    const adminSession = localStorage.getItem('admin_session');
    const sessionTime = localStorage.getItem('session_time');
    const currentTime = Math.floor(Date.now() / 1000);
    if (!adminSession || (currentTime - sessionTime > 1200)) {
        localStorage.removeItem('admin_session');
        window.location.href = 'index.php?error=session_expired';
    }
</script>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <meta property="og:title" content="Edit testimonial"/>
  <meta name="description" content="Admin — edit testimonial"/>
</head>
<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-lg-8 col-md-10">
          <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <h4 class="mb-0">Edit testimonial</h4>
                <p class="text-sm text-muted mb-0">Update name, country, rating, headline, review, or photo. Status stays <?php echo htmlspecialchars($statusLabel, ENT_QUOTES, "UTF-8"); ?> — change it from the testimonials list.</p>
              </div>
              <a href="./testimonials" class="btn btn-sm btn-outline-secondary mb-0">Back to list</a>
            </div>
            <div class="card-body p-3">
              <form method="post" action="" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="testimonial_id" value="<?php echo (int) $id; ?>">
                <div class="col-md-4">
                  <label class="form-label">Reviewer name</label>
                  <input type="text" name="reviewerName" class="form-control" maxlength="100" required value="<?php echo htmlspecialchars($tr["name"], ENT_QUOTES, "UTF-8"); ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Country</label>
                  <input type="text" name="reviewerCountry" class="form-control" maxlength="20" required value="<?php echo htmlspecialchars($tr["country"], ENT_QUOTES, "UTF-8"); ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Rating</label>
                  <select name="ratings" class="form-control">
                    <?php
                    $curR = (int) $t["ratings"];
                    for ($r = 5; $r >= 1; $r--) {
                        $sel = ($r === $curR) ? " selected" : "";
                        echo "<option value=\"$r\"$sel>$r stars</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Headline (short)</label>
                  <input type="text" name="oneWord" class="form-control" maxlength="50" required value="<?php echo htmlspecialchars($t["one_word"], ENT_QUOTES, "UTF-8"); ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Photo (optional)</label>
                  <?php if ($currentImage !== "") { ?>
                    <div class="mb-2">
                      <img src="../img/testimonials/<?php echo htmlspecialchars($currentImage, ENT_QUOTES, "UTF-8"); ?>" alt="" class="rounded" style="max-height:80px;width:auto;">
                      <span class="text-xs text-muted d-block">Upload a new file to replace.</span>
                    </div>
                  <?php } ?>
                  <input type="file" name="adminTestimonialImage" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="col-12">
                  <label class="form-label">Review</label>
                  <textarea name="reviewBody" class="form-control" rows="5" maxlength="500" required><?php echo htmlspecialchars($t["review"], ENT_QUOTES, "UTF-8"); ?></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" name="adminEditTestimonialSubmit" value="1" class="btn btn-primary mb-0">Save changes</button>
                  <a href="./testimonials" class="btn btn-outline-secondary mb-0 ms-2">Cancel</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>
