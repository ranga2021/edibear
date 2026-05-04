<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("testimonials");
$user = new USER();

if (!$user->is_loggedin()) {
    $user->redirect("./index.php");
}

$pdo = $user->getConnection();

// ----- List delete (same as legacy delete branch) -----
if (isset($_POST["adminDeleteTestimonialList"]) && isset($_POST["listTestimonialId"])) {
    $testimonialID = (int) $_POST["listTestimonialId"];
    if ($testimonialID < 1) {
        $user->redirect("./testimonials");
    }
    $imgDir = __DIR__ . "/../img/testimonials/";
    foreach ($user->fetchAll(array("image"), array("testimonials_images"), array("testimonial_id" => $testimonialID)) as $row) {
        $fn = basename(str_replace("\\", "/", (string) ($row["image"] ?? "")));
        if ($fn !== "") {
            $p = $imgDir . $fn;
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }
    $user->deleteTableRow("testimonials_images", array("testimonial_id" => $testimonialID));
    $user->deleteTableRow("testimonials", array("id" => $testimonialID));
    echo "<script>alert('Testimonial deleted.');location.href='./testimonials'</script>";
    exit;
}

// ----- Add -----
if (isset($_POST["adminAddTestimonialSubmit"])) {
    $reviewerName = trim((string) ($_POST["reviewerName"] ?? ""));
    $reviewerCountry = trim((string) ($_POST["reviewerCountry"] ?? ""));
    $reviewerEmail = trim((string) ($_POST["reviewerEmail"] ?? ""));
    $oneWord = trim((string) ($_POST["oneWord"] ?? ""));
    $review = trim((string) ($_POST["reviewBody"] ?? ""));
    $ratings = (int) ($_POST["ratings"] ?? 5);
    $publishNow = isset($_POST["publishNow"]) && $_POST["publishNow"] === "1";

    $ratings = max(1, min(5, $ratings));
    $reviewerName = substr(strip_tags($reviewerName), 0, 100);
    $reviewerCountry = substr(strip_tags($reviewerCountry), 0, 20);
    $oneWord = substr(strip_tags($oneWord), 0, 50);
    $review = substr(strip_tags($review), 0, 500);

    if ($reviewerEmail !== "" && !filter_var($reviewerEmail, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Please enter a valid email address, or leave it blank.');location.href='./testimonials#add-testimonial'</script>";
        exit;
    }

    if ($reviewerName === "" || $reviewerCountry === "" || $oneWord === "" || $review === "") {
        echo "<script>alert('Please fill in name, city\\/country, headline, and review.');location.href='./testimonials#add-testimonial'</script>";
        exit;
    }

    $uniq = bin2hex(random_bytes(4));
    $username = "u" . substr($uniq, 0, 19);
    if ($reviewerEmail === "") {
        $email = "t" . substr($uniq, 0, 6) . "@g.t";
    } else {
        $email = substr($reviewerEmail, 0, 50);
    }
    $hash = "pass" . password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
    $status = $publishNow ? 1 : 0;

    $user->insertTable(
        "tourists",
        array(
            "username" => $username,
            "name" => $reviewerName,
            "email" => $email,
            "country" => $reviewerCountry,
            "password" => $hash,
            "profile_pic" => "default.jpg",
            "status" => 1,
            "delete_status" => 0,
            "timestamp" => date("Y-m-d H:i:s"),
        )
    );

    $newTouristId = (int) $user->fetchAll(array("id"), array("tourists"), array("username" => $username))[0]["id"];

    $testimonialId = (int) $user->insertTable(
        "testimonials",
        array(
            "user_id" => $newTouristId,
            "name" => $reviewerName,
            "ratings" => $ratings,
            "one_word" => $oneWord,
            "review" => $review,
            "status" => $status,
        ),
        true
    );

    if (!empty($_FILES["adminTestimonialImage"]["name"]) && (int) $_FILES["adminTestimonialImage"]["error"] === UPLOAD_ERR_OK) {
        $allowedTypes = array("jpg", "jpeg", "png", "webp");
        $fileExt = strtolower(pathinfo($_FILES["adminTestimonialImage"]["name"], PATHINFO_EXTENSION));
        if (in_array($fileExt, $allowedTypes, true)) {
            $imgDir = __DIR__ . "/../img/testimonials/";
            if (!is_dir($imgDir)) {
                mkdir($imgDir, 0777, true);
            }
            $newFileName = substr(bin2hex(random_bytes(6)), 0, 12) . "." . $fileExt;
            $dest = $imgDir . $newFileName;
            if (strlen($newFileName) <= 20 && move_uploaded_file($_FILES["adminTestimonialImage"]["tmp_name"], $dest)) {
                $user->insertTable(
                    "testimonials_images",
                    array(
                        "testimonial_id" => $testimonialId,
                        "image" => $newFileName,
                    )
                );
            }
        }
    }

    echo "<script>alert('Testimonial added successfully');location.href='./testimonials'</script>";
    exit;
}

// ----- List query + pagination -----
$search = isset($_GET["search"]) ? trim((string) $_GET["search"]) : "";
$page = isset($_GET["page"]) ? max(1, (int) $_GET["page"]) : 1;
$perPage = 20;

$where = "";
$params = array();
if ($search !== "") {
    $where = " WHERE (tr.name LIKE :q OR t.name LIKE :q OR tr.email LIKE :q OR t.one_word LIKE :q OR t.review LIKE :q)";
    $params[":q"] = "%" . $search . "%";
}

$countSql = "SELECT COUNT(*) FROM testimonials t INNER JOIN tourists tr ON tr.id = t.user_id" . $where;

$totalRows = 0;
try {
    $stc = $pdo->prepare($countSql);
    $stc->execute($params);
    $totalRows = (int) $stc->fetchColumn();
} catch (Throwable $e) {
    $totalRows = 0;
}

$totalPages = $totalRows > 0 ? (int) ceil($totalRows / $perPage) : 1;
$page = min($page, max(1, $totalPages));
$offset = ($page - 1) * $perPage;

$listSql = "SELECT t.id, t.user_id, t.name AS t_name, t.ratings, t.one_word, t.review, t.status, t.timestamp,
    tr.name AS tourist_name, tr.country, tr.email AS tourist_email,
    (SELECT ti.image FROM testimonials_images ti WHERE ti.testimonial_id = t.id LIMIT 1) AS photo
    FROM testimonials t
    INNER JOIN tourists tr ON tr.id = t.user_id"
    . $where
    . " ORDER BY t.timestamp DESC LIMIT " . (int) $perPage . " OFFSET " . (int) $offset;

$rows = array();
try {
    $stl = $pdo->prepare($listSql);
    $stl->execute($params);
    $rows = $stl->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = array();
}

$showFrom = $totalRows === 0 ? 0 : $offset + 1;
$showTo = min($offset + count($rows), $totalRows);

function edi_testimonial_status_label($raw)
{
    $raw = (int) $raw;
    if ($raw === 1) {
        return "Published";
    }
    if ($raw === -1) {
        return "Rejected";
    }
    return "Pending";
}
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
  <style>
    .edi-tm-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.25rem;
    }
    .edi-tm-title {
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      margin: 0;
    }
    .edi-tm-search-form.edi-admin-search-inline .form-control { max-width: 24rem; }
    .edi-tm-name-cell { min-width: 200px; }
    .edi-tm-avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      object-fit: cover;
      flex-shrink: 0;
      background: #e2e8f0;
    }
    .edi-tm-avatar--ph {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 0.95rem;
      color: #64748b;
    }
    .edi-tm-stars { color: #fbbf24; letter-spacing: 1px; }
    .edi-tm-stars .far, .edi-tm-stars .edi-star-empty { color: #cbd5e1; }
    .edi-tm-review-snippet {
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      max-width: 28rem;
    }
    .edi-tm-form-title {
      font-size: 1.25rem;
      font-weight: 700;
      letter-spacing: 0.04em;
    }
    .edi-tm-photo-ring {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 3px dashed #cbd5e1;
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
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>
    <div class="container-fluid py-4">

      <div class="card mb-4" id="add-testimonial">
        <div class="card-body p-4">
          <h2 class="edi-tm-form-title text-uppercase text-danger mb-4">Add testimonial</h2>
          <form method="post" action="#add-testimonial" enctype="multipart/form-data" class="edi-tm-add-form">
            <div class="d-flex flex-wrap gap-4 mb-4">
              <div class="edi-tm-photo-ring" id="ediTmAddPreview">
                <span class="text-muted small text-center px-2">Profile photo</span>
              </div>
              <div class="flex-grow-1" style="min-width:220px;">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Name</label>
                    <input type="text" name="reviewerName" class="form-control" maxlength="100" required placeholder="Reviewer name">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">City / Country</label>
                    <input type="text" name="reviewerCountry" class="form-control" maxlength="20" required placeholder="e.g. Gampaha">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label font-weight-bold">E-mail</label>
                  <input type="email" name="reviewerEmail" class="form-control" maxlength="50" placeholder="Optional — max 50 characters in database">
                </div>
                <div class="mb-3">
                  <label class="form-label font-weight-bold d-block">Rate your experience <span class="text-danger">*</span></label>
                  <input type="hidden" name="ratings" id="ediTmAddRatings" value="5">
                  <div class="edi-star-bar" data-target="ediTmAddRatings" role="group" aria-label="Rating">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                    <button type="button" class="edi-star-btn edi-star-on" data-value="<?php echo $s; ?>" aria-label="<?php echo $s; ?> stars">★</button>
                    <?php endfor; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label font-weight-bold">Say your review in one word <span class="text-danger">*</span></label>
              <input type="text" name="oneWord" class="form-control" maxlength="50" required placeholder="e.g. GREAT WORK">
            </div>
            <div class="mb-3">
              <label class="form-label font-weight-bold">Leave a review <span class="text-danger">*</span></label>
              <textarea name="reviewBody" id="ediTmAddReview" class="form-control" rows="5" maxlength="500" required placeholder="Your review (max 500 characters)"></textarea>
              <div class="d-flex justify-content-end"><small class="text-muted"><span id="ediTmAddCount">0</span> / 500</small></div>
            </div>
            <div class="mb-3">
              <label class="form-label font-weight-bold">Photo (optional)</label>
              <input type="file" name="adminTestimonialImage" id="ediTmAddFile" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" name="publishNow" value="1" id="publishNow" checked>
              <label class="form-check-label" for="publishNow">Publish as approved</label>
            </div>
            <div class="edi-admin-form-actions">
              <button type="submit" name="adminAddTestimonialSubmit" value="1" class="btn btn-success mb-0">Add</button>
              <a href="./testimonials" class="btn btn-secondary mb-0">Cancel</a>
            </div>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-4">
          <div class="edi-tm-toolbar">
            <h2 class="edi-tm-title text-uppercase text-danger">Testimonial</h2>
            <form class="edi-tm-search-form edi-admin-search-inline mb-0" method="get" action="testimonials.php">
              <input type="hidden" name="page" value="1">
              <input type="search" name="search" class="form-control" placeholder="Name" value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>" autocomplete="off">
              <button type="submit" class="btn btn-success mb-0">Search</button>
            </form>
          </div>

          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Country / City</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Review</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($rows) === 0): ?>
                <tr><td colspan="6" class="text-center text-secondary text-sm py-4">No testimonials found.</td></tr>
                <?php else: ?>
                  <?php foreach ($rows as $r):
                      $tid = (int) $r["id"];
                      $dispName = trim((string) ($r["tourist_name"] ?? $r["t_name"] ?? ""));
                      $initial = $dispName !== "" ? strtoupper(substr($dispName, 0, 1)) : "?";
                      $email = htmlspecialchars((string) ($r["tourist_email"] ?? ""), ENT_QUOTES, "UTF-8");
                      $country = htmlspecialchars((string) ($r["country"] ?? ""), ENT_QUOTES, "UTF-8");
                      $headline = htmlspecialchars((string) ($r["one_word"] ?? ""), ENT_QUOTES, "UTF-8");
                      $reviewFull = (string) ($r["review"] ?? "");
                      $ediLen = function_exists("mb_strlen") ? mb_strlen($reviewFull) : strlen($reviewFull);
                      $ediCut = function_exists("mb_substr") ? mb_substr($reviewFull, 0, 157) : substr($reviewFull, 0, 157);
                      $snippet = htmlspecialchars($ediLen > 160 ? ($ediCut . "…") : $reviewFull, ENT_QUOTES, "UTF-8");
                      $rat = max(1, min(5, (int) ($r["ratings"] ?? 0)));
                      $ts = $r["timestamp"] ?? "";
                      $dateDisp = $ts !== "" ? date("M j, Y", strtotime((string) $ts)) : "—";
                      $stLabel = edi_testimonial_status_label($r["status"] ?? 0);
                      $photo = trim((string) ($r["photo"] ?? ""));
                      ?>
                <tr>
                  <td class="align-middle edi-tm-name-cell">
                    <div class="d-flex align-items-center" style="gap:12px;">
                      <?php if ($photo !== ""): ?>
                        <img class="edi-tm-avatar" src="../img/testimonials/<?php echo htmlspecialchars($photo, ENT_QUOTES, "UTF-8"); ?>" alt="">
                      <?php else: ?>
                        <span class="edi-tm-avatar edi-tm-avatar--ph"><?php echo htmlspecialchars($initial, ENT_QUOTES, "UTF-8"); ?></span>
                      <?php endif; ?>
                      <div>
                        <div class="font-weight-bold text-uppercase text-sm"><?php echo htmlspecialchars($dispName, ENT_QUOTES, "UTF-8"); ?></div>
                        <div class="text-xs text-muted"><?php echo $email; ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="align-middle"><span class="text-secondary text-sm"><?php echo $country !== "" ? $country : "—"; ?></span></td>
                  <td class="align-middle">
                    <div class="font-weight-bold text-sm mb-1"><?php echo $headline; ?></div>
                    <div class="text-secondary text-xs edi-tm-review-snippet mb-2"><?php echo $snippet; ?></div>
                    <div class="edi-tm-stars text-sm" aria-hidden="true"><?php
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $rat ? "★" : "<span class=\"edi-star-empty\">★</span>";
                    }
                    ?></div>
                  </td>
                  <td class="align-middle"><span class="text-secondary text-sm"><?php echo htmlspecialchars($dateDisp, ENT_QUOTES, "UTF-8"); ?></span></td>
                  <td class="align-middle"><span class="text-secondary text-sm"><?php echo htmlspecialchars($stLabel, ENT_QUOTES, "UTF-8"); ?></span></td>
                  <td class="align-middle text-center text-sm">
                    <a href="./edit-testimonial.php?id=<?php echo $tid; ?>" class="text-success font-weight-bold">Edit</a>
                    <span class="text-muted">/</span>
                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this testimonial?');">
                      <input type="hidden" name="listTestimonialId" value="<?php echo $tid; ?>">
                      <button type="submit" name="adminDeleteTestimonialList" value="1" class="btn btn-link text-danger font-weight-bold p-0 m-0 align-baseline" style="font-size:inherit;">Delete</button>
                    </form>
                  </td>
                </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if ($totalRows > 0): ?>
          <p class="text-center text-secondary text-sm mt-3 mb-0">
            Showing <?php echo (int) $showFrom; ?> to <?php echo (int) $showTo; ?> of <?php echo (int) $totalRows; ?> testimonials
          </p>
          <?php if ($totalPages > 1):
              $qs = $search !== "" ? ("search=" . rawurlencode($search) . "&") : "";
              ?>
          <nav class="d-flex justify-content-center mt-2" aria-label="Pagination">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item<?php echo $page <= 1 ? " disabled" : ""; ?>">
                <a class="page-link" href="testimonials.php?<?php echo $qs; ?>page=<?php echo max(1, $page - 1); ?>">&laquo;</a>
              </li>
              <?php
              for ($p = 1; $p <= $totalPages; $p++) {
                  $active = ($p === $page) ? " active" : "";
                  echo '<li class="page-item' . $active . '"><a class="page-link" href="testimonials.php?' . $qs . "page=" . $p . '">' . $p . "</a></li>";
              }
              ?>
              <li class="page-item<?php echo $page >= $totalPages ? " disabled" : ""; ?>">
                <a class="page-link" href="testimonials.php?<?php echo $qs; ?>page=<?php echo min($totalPages, $page + 1); ?>">&raquo;</a>
              </li>
            </ul>
          </nav>
          <?php endif; ?>
          <?php endif; ?>
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
        btns.forEach(function (b, idx) {
          b.classList.toggle("edi-star-on", idx < val);
        });
        hid.value = String(val);
      }
      btns.forEach(function (b) {
        b.addEventListener("click", function () {
          paint(parseInt(b.getAttribute("data-value"), 10) || 1);
        });
      });
      paint(parseInt(hid.value, 10) || 5);
    }
    bindStarBar(document.querySelector(".edi-tm-add-form .edi-star-bar"));

    var ta = document.getElementById("ediTmAddReview");
    var cnt = document.getElementById("ediTmAddCount");
    if (ta && cnt) {
      function upd() { cnt.textContent = String(ta.value.length); }
      ta.addEventListener("input", upd);
      upd();
    }
    var fin = document.getElementById("ediTmAddFile");
    var prev = document.getElementById("ediTmAddPreview");
    if (fin && prev) {
      fin.addEventListener("change", function () {
        prev.innerHTML = "<span class=\"text-muted small text-center px-2\">Profile photo</span>";
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
