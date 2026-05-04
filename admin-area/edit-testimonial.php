<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("testimonials");
$user = new USER();

if (!$user->is_loggedin()) {
    $user->redirect("./index.php");
}

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

$touristRows = $user->fetchAll(array("id", "name", "country", "email"), array("tourists"), array("id" => $t["user_id"]));
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
    $reviewerEmail = trim((string) ($_POST["reviewerEmail"] ?? ""));
    $oneWord = trim((string) ($_POST["oneWord"] ?? ""));
    $review = trim((string) ($_POST["reviewBody"] ?? ""));
    $ratings = (int) ($_POST["ratings"] ?? 5);
    $ratings = max(1, min(5, $ratings));

    $status = isset($_POST["testimonial_status"]) ? (int) $_POST["testimonial_status"] : (int) $t["status"];
    if (!in_array($status, array(-1, 0, 1), true)) {
        $status = (int) $t["status"];
    }

    $reviewerName = substr(strip_tags($reviewerName), 0, 100);
    $reviewerCountry = substr(strip_tags($reviewerCountry), 0, 20);
    $oneWord = substr(strip_tags($oneWord), 0, 50);
    $review = substr(strip_tags($review), 0, 500);

    if ($reviewerEmail !== "" && !filter_var($reviewerEmail, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Please enter a valid email address.');history.back();</script>";
        exit;
    }

    if ($reviewerName === "" || $reviewerCountry === "" || $oneWord === "" || $review === "") {
        echo "<script>alert('Please fill in name, city\\/country, headline, and review.');history.back();</script>";
        exit;
    }

    $touristId = (int) $t["user_id"];

    $touristUpdate = array(
        "name" => $reviewerName,
        "country" => $reviewerCountry,
    );
    if ($reviewerEmail !== "") {
        $touristUpdate["email"] = substr($reviewerEmail, 0, 50);
    }

    $user->updateTable("tourists", $touristUpdate, array("id" => $touristId));

    $user->updateTable(
        "testimonials",
        array(
            "name" => $reviewerName,
            "ratings" => $ratings,
            "one_word" => $oneWord,
            "review" => $review,
            "status" => $status,
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

if (isset($_POST["adminDeleteTestimonialSubmit"])) {
    $postId = (int) ($_POST["testimonial_id"] ?? 0);
    if ($postId !== $id) {
        echo "<script>alert('Invalid testimonial.');location.href='./testimonials';</script>";
        exit;
    }
    $imgDir = __DIR__ . "/../img/testimonials/";
    foreach ($user->fetchAll(array("image"), array("testimonials_images"), array("testimonial_id" => $id)) as $oldRow) {
        $fn = basename(str_replace("\\", "/", (string) ($oldRow["image"] ?? "")));
        if ($fn !== "") {
            $p = $imgDir . $fn;
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }
    $user->deleteTableRow("testimonials_images", array("testimonial_id" => $id));
    $user->deleteTableRow("testimonials", array("id" => $id));
    echo "<script>alert('Testimonial deleted');location.href='./testimonials';</script>";
    exit;
}

$rawStatus = (int) $t["status"];
$curEmail = (string) ($tr["email"] ?? "");
?>
<script>
    const adminSession = localStorage.getItem("admin_session");
    const sessionTime = localStorage.getItem("session_time");
    const currentTime = Math.floor(Date.now() / 1000);
    if (!adminSession || (currentTime - sessionTime > 1200)) {
        localStorage.removeItem("admin_session");
        window.location.href = "index.php?error=session_expired";
    }
</script>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <meta property="og:title" content="Edit testimonial"/>
  <meta name="description" content="Admin — edit testimonial"/>
  <style>
    .edi-tm-edit-title {
      font-size: 1.35rem;
      font-weight: 700;
      letter-spacing: 0.06em;
    }
    .edi-tm-photo-ring {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 3px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      flex-shrink: 0;
      background: #f8fafc;
    }
    .edi-tm-photo-ring img { width: 100%; height: 100%; object-fit: cover; }
    .edi-star-bar .edi-star-btn {
      border: none;
      background: none;
      padding: 0 2px;
      font-size: 1.75rem;
      line-height: 1;
      color: #e2e8f0;
      cursor: pointer;
    }
    .edi-star-bar .edi-star-btn.edi-star-on { color: #fbbf24; }
    .edi-star-bar .edi-star-btn:focus { outline: none; }
  </style>
</head>
<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2("Edit testimonial"); ?>
    <div class="container-fluid py-4">
      <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
          <div class="card mb-4">
            <div class="card-body p-4">
              <h2 class="edi-tm-edit-title text-uppercase text-danger mb-4">Edit testimonial</h2>

              <form method="post" action="" enctype="multipart/form-data" id="edi-edit-tm-form">
                <input type="hidden" name="testimonial_id" value="<?php echo (int) $id; ?>">

                <div class="d-flex flex-wrap gap-4 mb-4">
                  <div class="edi-tm-photo-ring" id="ediTmEditPreview">
                    <?php if ($currentImage !== ""): ?>
                      <img src="../img/testimonials/<?php echo htmlspecialchars($currentImage, ENT_QUOTES, "UTF-8"); ?>" alt="">
                    <?php else: ?>
                      <span class="text-muted small text-center px-2">No photo</span>
                    <?php endif; ?>
                  </div>
                  <div class="flex-grow-1" style="min-width:240px;">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Name</label>
                        <input type="text" name="reviewerName" class="form-control" maxlength="100" required value="<?php echo htmlspecialchars($tr["name"], ENT_QUOTES, "UTF-8"); ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">City / Country</label>
                        <input type="text" name="reviewerCountry" class="form-control" maxlength="20" required value="<?php echo htmlspecialchars($tr["country"], ENT_QUOTES, "UTF-8"); ?>">
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label font-weight-bold">E-mail</label>
                      <input type="email" name="reviewerEmail" class="form-control" maxlength="50" value="<?php echo htmlspecialchars($curEmail, ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label font-weight-bold d-block">Rate your experience <span class="text-danger">*</span></label>
                      <input type="hidden" name="ratings" id="ediTmEditRatings" value="<?php echo (int) $t["ratings"]; ?>">
                      <div class="edi-star-bar" data-target="ediTmEditRatings" role="group" aria-label="Rating">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                        <button type="button" class="edi-star-btn" data-value="<?php echo $s; ?>" aria-label="<?php echo $s; ?> stars">★</button>
                        <?php endfor; ?>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label font-weight-bold">Say your review in one word <span class="text-danger">*</span></label>
                  <input type="text" name="oneWord" class="form-control" maxlength="50" required value="<?php echo htmlspecialchars($t["one_word"], ENT_QUOTES, "UTF-8"); ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label font-weight-bold">Leave a review <span class="text-danger">*</span></label>
                  <textarea name="reviewBody" id="ediTmEditReview" class="form-control" rows="6" maxlength="500" required><?php echo htmlspecialchars($t["review"], ENT_QUOTES, "UTF-8"); ?></textarea>
                  <div class="d-flex justify-content-end"><small class="text-muted">Character limit: <span id="ediTmEditCount">0</span> / 500</small></div>
                </div>
                <div class="mb-4">
                  <label class="form-label font-weight-bold">Replace photo (optional)</label>
                  <input type="file" name="adminTestimonialImage" id="ediTmEditFile" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="mb-4">
                  <label class="form-label font-weight-bold">Status</label>
                  <select name="testimonial_status" class="form-control" style="max-width:280px;">
                    <option value="1"<?php echo $rawStatus === 1 ? " selected" : ""; ?>>Approved</option>
                    <option value="0"<?php echo $rawStatus === 0 ? " selected" : ""; ?>>Pending</option>
                    <option value="-1"<?php echo $rawStatus === -1 ? " selected" : ""; ?>>Rejected</option>
                  </select>
                </div>

                <div class="edi-admin-form-actions">
                  <button type="submit" name="adminEditTestimonialSubmit" value="1" class="btn btn-success mb-0">Update</button>
                  <a href="./testimonials" class="btn btn-secondary mb-0">Cancel</a>
                </div>
              </form>

              <form method="post" action="" class="mt-3 d-inline-block" onsubmit="return confirm('Delete this testimonial and its photo? The reviewer account record is kept. This cannot be undone.');">
                <input type="hidden" name="testimonial_id" value="<?php echo (int) $id; ?>">
                <button type="submit" name="adminDeleteTestimonialSubmit" value="1" class="btn btn-danger mb-0">Delete</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script>
  (function () {
    function bindStarBar(container) {
      if (!container) return;
      var hid = document.getElementById(container.getAttribute("data-target"));
      if (!hid) return;
      var btns = container.querySelectorAll(".edi-star-btn");
      function paint(val) {
        var v = Math.max(1, Math.min(5, parseInt(val, 10) || 1));
        btns.forEach(function (b, idx) {
          b.classList.toggle("edi-star-on", idx < v);
        });
        hid.value = String(v);
      }
      btns.forEach(function (b) {
        b.addEventListener("click", function () {
          paint(parseInt(b.getAttribute("data-value"), 10) || 1);
        });
      });
      paint(hid.value);
    }
    bindStarBar(document.querySelector(".edi-star-bar"));

    var ta = document.getElementById("ediTmEditReview");
    var cnt = document.getElementById("ediTmEditCount");
    if (ta && cnt) {
      function upd() { cnt.textContent = String(ta.value.length); }
      ta.addEventListener("input", upd);
      upd();
    }
    var fin = document.getElementById("ediTmEditFile");
    var prev = document.getElementById("ediTmEditPreview");
    if (fin && prev) {
      fin.addEventListener("change", function () {
        var f = fin.files && fin.files[0];
        if (!f) return;
        var r = new FileReader();
        r.onload = function (ev) {
          prev.innerHTML = "<img src=\"" + ev.target.result + "\" alt=\"\">";
        };
        r.readAsDataURL(f);
      });
    }
  })();
  </script>
</body>
</html>
