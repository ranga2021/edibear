<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("add-event");
$user = new USER();

if (!$user->is_loggedin()) {
    $user->redirect("./index.php");
}

// ================= FORM SUBMIT =================
if (isset($_POST["addNewEventSubmit"])) {

    $selectedCategory = $_POST["event_category"] ?? "";
    $newCategoryText = trim((string) ($_POST["new_category"] ?? ""));
    /* Store plain text; escaping is done on output (double htmlspecialchars caused visible &#039;). */
    $eventTitle = trim(strip_tags((string) ($_POST["event_title"] ?? "")));
    $eventTitle = function_exists("mb_substr") ? mb_substr($eventTitle, 0, 200) : substr($eventTitle, 0, 200);
    $description = isset($_POST["event_description"]) ? (string) $_POST["event_description"] : "";
    $deadlineDate = $_POST["deadline_date"] ?? null;

    // ================= CATEGORY =================
    $categoryId = null;

    if ($selectedCategory === "other" && $newCategoryText !== "") {

        $existing = $user->fetchAll(
            array("id"),
            array("braveheart_categories"),
            array("name" => $newCategoryText)
        );

        if (!empty($existing)) {
            $categoryId = (int) $existing[0]["id"];
        } else {
            $categoryId = (int) $user->insertTable(
                "braveheart_categories",
                array("name" => $newCategoryText, "status" => 1),
                true
            );
        }
    } elseif (ctype_digit((string) $selectedCategory)) {
        $categoryId = (int) $selectedCategory;
    }

    // ================= INSERT EVENT =================
    $eventId = (int) $user->insertTable(
        "braveheart_events",
        array(
            "category_id" => $categoryId,
            "title" => $eventTitle,
            "description" => $description,
            "deadline_date" => $deadlineDate,
            "status" => 1,
        ),
        true
    );

    $uploadDir = "../img/braveheart/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    // ================= MAIN IMAGE =================
    if (!empty($_FILES["main_image"]["name"])) {
        $ext = pathinfo((string) $_FILES["main_image"]["name"], PATHINFO_EXTENSION);
        $fileName = $eventId . "." . $ext;

        move_uploaded_file($_FILES["main_image"]["tmp_name"], $uploadDir . $fileName);

        $user->updateTable("braveheart_events", array("main_image" => $fileName), array("id" => $eventId));
    }

    // ================= PDF =================
    if (!empty($_FILES["application_file"]["name"])) {
        $ext = pathinfo((string) $_FILES["application_file"]["name"], PATHINFO_EXTENSION);
        $fileName = $eventId . "-application." . $ext;

        move_uploaded_file($_FILES["application_file"]["tmp_name"], $uploadDir . $fileName);

        $user->updateTable("braveheart_events", array("application_file" => $fileName), array("id" => $eventId));
    }

    // ================= WINNERS =================
    $winnerTitles = isset($_POST["winner_title"]) && is_array($_POST["winner_title"]) ? $_POST["winner_title"] : array();
    $position = 1;
    foreach ($winnerTitles as $index => $title) {
        $title = trim((string) $title);
        if ($title === "") {
            continue;
        }
        $fileField = "winner_image_" . $index;
        $imageName = "";
        if (!empty($_FILES[$fileField]["name"])) {
            $ext = pathinfo((string) $_FILES[$fileField]["name"], PATHINFO_EXTENSION);
            $imageName = $eventId . "-winner-" . $position . "." . $ext;
            move_uploaded_file($_FILES[$fileField]["tmp_name"], $uploadDir . $imageName);
        }
        if ($imageName !== "") {
            $user->insertTable(
                "braveheart_winners",
                array(
                    "event_id" => $eventId,
                    "title" => $title,
                    "image" => $imageName,
                    "position" => $position,
                    "status" => 1,
                )
            );
            $position++;
        }
    }

    echo "<script>alert('Event added successfully');location.href='event.php'</script>";
    exit;
}

// ================= FETCH CATEGORIES =================
try {
    $eventCategories = $user->fetchAll(
        array("id", "name"),
        array("braveheart_categories"),
        array(),
        "name ASC"
    );
} catch (Exception $e) {
    $eventCategories = array();
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
  <script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>
  <script>if (typeof CKEDITOR !== "undefined") { CKEDITOR.config.versionCheck = false; }</script>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-body p-4">
              <h2 class="text-uppercase text-danger font-weight-bold h4 mb-4" style="letter-spacing:0.02em;">Add event</h2>
              <form method="post" enctype="multipart/form-data" id="edi-add-event-form">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Category</label>
                      <select name="event_category" id="eventCategory" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($eventCategories as $cat): ?>
                          <option value="<?php echo (int) $cat["id"]; ?>">
                            <?php echo htmlspecialchars($cat["name"], ENT_QUOTES, "UTF-8"); ?>
                          </option>
                        <?php endforeach; ?>
                        <option value="other">Other</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Event Title</label>
                      <input type="text" name="event_title" class="form-control" required>
                    </div>
                  </div>
                </div>

                <div class="row" id="newCategoryWrapper" style="display: none;">
                  <div class="col-12">
                    <div class="form-group">
                      <label class="form-control-label">New category name</label>
                      <input type="text" name="new_category" class="form-control" placeholder="e.g. Writing" maxlength="120">
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Main Image</label>
                      <input class="form-control" type="file" accept="image/*" onchange="loadImageFile(event)" name="main_image" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <p class="text-center mt-3 mb-0">
                      <img id="outputmain_image" alt="" style="max-height: 200px; max-width:100%; display:none;" />
                    </p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Deadline date</label>
                      <input type="date" name="deadline_date" class="form-control" required>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <label class="form-control-label">Descriptions</label>
                      <textarea name="event_description" id="event_description" class="form-control" rows="8"></textarea>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Application Upload (PDF)</label>
                      <input class="form-control" type="file" accept="application/pdf,.pdf" name="application_file">
                    </div>
                  </div>
                </div>

                <hr class="my-4">
                <h6 class="text-uppercase text-muted font-weight-bold mb-3">Winner details</h6>
                <p class="text-sm text-muted mb-3">Add winner title and JPG image for each winner. Rows without an image are skipped.</p>

                <div id="winnerRows"></div>

                <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="addWinnerRow()">Add winners +</button>

                <div class="row mt-4">
                  <div class="col-12 edi-admin-form-actions">
                    <input type="submit" class="btn btn-success" name="addNewEventSubmit" value="Add">
                    <a href="event.php" class="btn btn-secondary">Cancel</a>
                  </div>
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

  <script>
    document.getElementById("eventCategory").addEventListener("change", function () {
      var wrapper = document.getElementById("newCategoryWrapper");
      wrapper.style.display = this.value === "other" ? "block" : "none";
    });

    function loadImageFile(event) {
      var imageDivID = "output" + event.target.name;
      var image = document.getElementById(imageDivID);
      if (image && event.target.files && event.target.files[0]) {
        image.style.display = "";
        image.src = URL.createObjectURL(event.target.files[0]);
      }
    }

    var winnerIndex = 0;
    function addWinnerRow() {
      var container = document.getElementById("winnerRows");
      var idx = winnerIndex++;
      var row = document.createElement("div");
      row.className = "row align-items-start mb-3 flex-wrap";
      row.setAttribute("data-index", idx);
      row.innerHTML = ""
        + '<div class="col-md-4 mb-2 mb-md-0">'
        + '  <div class="form-group mb-0">'
        + '    <label class="form-control-label">Title</label>'
        + '    <input type="text" name="winner_title[' + idx + ']" class="form-control" placeholder="Name — location (age)">'
        + '  </div>'
        + '</div>'
        + '<div class="col-md-4 mb-2 mb-md-0">'
        + '  <div class="form-group mb-0">'
        + '    <label class="form-control-label">Image (JPG)</label>'
        + '    <input type="file" name="winner_image_' + idx + '" class="form-control" accept="image/jpeg,image/jpg,image/png,.jpg,.jpeg,.png">'
        + '  </div>'
        + '</div>'
        + '<div class="col-md-4 text-center">'
        + '  <small class="text-muted d-block mb-1">Preview</small>'
        + '  <div class="border rounded bg-light d-inline-block p-2" style="min-height:72px;min-width:72px;" data-preview-wrap="' + idx + '"></div>'
        + '</div>';
      container.appendChild(row);
      var fileInput = row.querySelector('input[type="file"]');
      var wrap = row.querySelector("[data-preview-wrap]");
      if (fileInput && wrap) {
        fileInput.addEventListener("change", function () {
          wrap.innerHTML = "";
          if (fileInput.files && fileInput.files[0]) {
            var img = document.createElement("img");
            img.alt = "";
            img.style.maxHeight = "72px";
            img.style.maxWidth = "100%";
            img.src = URL.createObjectURL(fileInput.files[0]);
            wrap.appendChild(img);
          }
        });
      }
    }

    document.addEventListener("DOMContentLoaded", function () {
      if (typeof CKEDITOR !== "undefined") {
        CKEDITOR.replace("event_description", { height: 220, versionCheck: false });
      }
      addWinnerRow();
    });

    document.getElementById("edi-add-event-form").addEventListener("submit", function () {
      if (typeof CKEDITOR !== "undefined" && CKEDITOR.instances.event_description) {
        CKEDITOR.instances.event_description.updateElement();
      }
    });
  </script>
</body>
</html>
