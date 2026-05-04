<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");

  $adminHeader = new HEADER("event");
  $user = new USER();

  if (!$user->is_loggedin()) {
      $user->redirect("./index.php");
  }


  $eventId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
  if ($eventId <= 0) {
    $user->redirect('event.php');
  }

  // Fetch event
  $event = $user->fetchAll(
    array("id","category_id","title","description","main_image","application_file","deadline_date"),
    array("braveheart_events"),
    array("id" => $eventId)
  );
  if (empty($event)) {
    $user->redirect('event.php');
  }
  $event = $event[0];

  // Fetch categories
  $eventCategories = $user->fetchAll(
    array("id","name"),
    array("braveheart_categories"),
    array(),
    "name ASC"
  );

  // Fetch winners
  $existingWinners = $user->fetchAll(
    array("id","title","image"),
    array("braveheart_winners"),
    array("event_id" => $eventId),
    "position ASC, id ASC"
  );

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['updateEventSubmit'])) {
      $selectedCategory = isset($_POST['event_category']) ? $_POST['event_category'] : '';
      $eventTitle = trim(strip_tags((string) (isset($_POST["event_title"]) ? $_POST["event_title"] : "")));
      $eventTitle = function_exists("mb_substr") ? mb_substr($eventTitle, 0, 200) : substr($eventTitle, 0, 200);
      $description      = isset($_POST['event_description']) ? (string) $_POST['event_description'] : '';
      $deadlineDate     = isset($_POST['deadline_date']) ? $_POST['deadline_date'] : null;

      $categoryId = ctype_digit((string)$selectedCategory) ? (int)$selectedCategory : null;

      $user->updateTable(
        "braveheart_events",
        array(
          "category_id"   => $categoryId,
          "title"         => $eventTitle,
          "description"   => $description,
          "deadline_date" => $deadlineDate
        ),
        array("id" => $eventId)
      );

      $uploadDir = "../img/braveheart/";
      if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
      }

      // Main image
      if (!empty($_FILES["main_image"]["name"])) {
        $mainImageExt  = pathinfo($_FILES["main_image"]["name"], PATHINFO_EXTENSION);
        $mainImageName = $eventId . "." . $mainImageExt;
        move_uploaded_file($_FILES["main_image"]["tmp_name"], $uploadDir . $mainImageName);
        $user->updateTable(
          "braveheart_events",
          array("main_image" => $mainImageName),
          array("id" => $eventId)
        );
      }

      // Application file
      if (!empty($_FILES["application_file"]["name"])) {
        $appExt  = pathinfo($_FILES["application_file"]["name"], PATHINFO_EXTENSION);
        $appName = $eventId . "-application." . $appExt;
        move_uploaded_file($_FILES["application_file"]["tmp_name"], $uploadDir . $appName);
        $user->updateTable(
          "braveheart_events",
          array("application_file" => $appName),
          array("id" => $eventId)
        );
      }

      // Winners
      $winnerTitles = isset($_POST['winner_title']) && is_array($_POST['winner_title']) ? $_POST['winner_title'] : array();
      $existingWinnerImages = isset($_POST['existing_winner_image']) && is_array($_POST['existing_winner_image']) ? $_POST['existing_winner_image'] : array();

      // Remove existing winners and recreate from submitted
      $user->deleteTableRow("braveheart_winners", array("event_id" => $eventId));

      $position = 1;
      foreach ($winnerTitles as $index => $title) {
        $title = trim($title);
        if ($title === '') {
          continue;
        }

        $fileField = 'winner_image_' . $index;
        $imageName = '';
        if (!empty($_FILES[$fileField]['name'])) {
          $ext = pathinfo($_FILES[$fileField]['name'], PATHINFO_EXTENSION);
          $imageName = $eventId . '-winner-' . $position . '.' . $ext;
          move_uploaded_file($_FILES[$fileField]['tmp_name'], $uploadDir . $imageName);
        } elseif (isset($existingWinnerImages[$index])) {
          $imageName = $existingWinnerImages[$index];
        }

        if ($imageName !== '') {
          $user->insertTable(
            "braveheart_winners",
            array(
              "event_id" => $eventId,
              "title"    => $title,
              "image"    => $imageName,
              "position" => $position,
              "status"   => 1
            )
          );
          $position++;
        }
      }

      echo "<script>alert('Event updated successfully');location.href='event.php'</script>";
      exit;
    } elseif (isset($_POST['deleteEventSubmit'])) {
      $uploadDel = "../img/braveheart/";
      if (!empty($event["main_image"])) {
          @unlink($uploadDel . basename(str_replace("\\", "/", (string) $event["main_image"])));
      }
      if (!empty($event["application_file"])) {
          @unlink($uploadDel . basename(str_replace("\\", "/", (string) $event["application_file"])));
      }
      foreach ($existingWinners as $w) {
          if (!empty($w["image"])) {
              @unlink($uploadDel . basename(str_replace("\\", "/", (string) $w["image"])));
          }
      }
      $user->deleteTableRow("braveheart_winners", array("event_id" => $eventId));
      $user->deleteTableRow("braveheart_events", array("id" => $eventId));
      echo "<script>alert('Event deleted successfully');location.href='event.php'</script>";
      exit;
    }
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
  <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2("Edit event"); ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-body p-4">
              <h2 class="text-uppercase text-danger font-weight-bold h4 mb-4" style="letter-spacing:0.02em;">Edit event</h2>
              <form method="post" enctype="multipart/form-data" id="edi-edit-event-form">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Category</label>
                      <select name="event_category" class="form-control">
                        <option value="">Select Category</option>
                        <?php foreach ($eventCategories as $cat): ?>
                          <option
                            value="<?php echo $cat['id']; ?>"
                            <?php echo ((int)$event['category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>
                          >
                            <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Event Title</label>
                      <input type="text" name="event_title" class="form-control" value="<?php echo htmlspecialchars(html_entity_decode((string) $event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Main Image</label>
                      <input class="form-control" type="file" accept="image/*" name="main_image">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <p class="text-center mt-3">
                      <?php if (!empty($event['main_image'])): ?>
                        <img src="<?php echo '../img/braveheart/' . htmlspecialchars($event['main_image'], ENT_QUOTES, 'UTF-8'); ?>" style="max-height: 200px; max-width:100%" />
                      <?php endif; ?>
                    </p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Deadline Date</label>
                      <input
                        type="date"
                        name="deadline_date"
                        class="form-control"
                        value="<?php echo htmlspecialchars($event['deadline_date'], ENT_QUOTES, 'UTF-8'); ?>"
                      >
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <label class="form-control-label">Descriptions</label>
                      <textarea name="event_description" id="event_description" class="form-control" rows="8"><?php echo htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
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

                <div id="winnerRows">
                  <?php
                  $index = 0;
                  foreach ($existingWinners as $winner) {
                      $titleRaw = html_entity_decode((string) ($winner["title"] ?? ""), ENT_QUOTES | ENT_HTML5, "UTF-8");
                      $title = htmlspecialchars($titleRaw, ENT_QUOTES, "UTF-8");
                      $image = htmlspecialchars($winner["image"], ENT_QUOTES, "UTF-8");
                      echo "
                      <div class='row align-items-start mb-3 flex-wrap' data-index='$index'>
                          <input type='hidden' name='existing_winner_image[$index]' value='$image'>
                          <div class='col-md-4 mb-2 mb-md-0'>
                              <div class='form-group mb-0'>
                                  <label class='form-control-label'>Title</label>
                                  <input type='text' name='winner_title[$index]' class='form-control' value='$title'>
                              </div>
                          </div>
                          <div class='col-md-4 mb-2 mb-md-0'>
                              <div class='form-group mb-0'>
                                  <label class='form-control-label'>Image (JPG)</label>
                                  <input type='file' name='winner_image_$index' class='form-control' accept='image/jpeg,image/jpg,image/png,.jpg,.jpeg,.png'>
                              </div>
                          </div>
                          <div class='col-md-4 text-center'>
                              <small class='text-muted d-block mb-1'>Current</small>
                              <div class='border rounded bg-light d-inline-block p-2'>
                                  <img src='../img/braveheart/$image' alt='' style='max-height: 80px; max-width:100%' />
                              </div>
                              <div class='small text-muted mt-1 text-truncate' style='max-width:100%'>$title</div>
                          </div>
                      </div>";
                      $index++;
                  }
                  ?>
                </div>

                <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="addWinnerRow()">Add winners +</button>

                <div class="row mt-4">
                  <div class="col-12 edi-admin-form-actions">
                    <input type="submit" class="btn btn-success" name="updateEventSubmit" value="Update">
                    <input type="submit" class="btn btn-danger" name="deleteEventSubmit" value="Delete" onclick="return confirm('Delete this event and all winner images? This cannot be undone.');">
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
    var winnerIndex = <?php echo isset($index) ? (int)$index : 0; ?>;

    function bindWinnerPreview(row, idx) {
      var fileInput = row.querySelector('input[type="file"]');
      var wrap = row.querySelector("[data-preview-wrap]");
      if (!fileInput || !wrap) return;
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
        + "</div>";
      container.appendChild(row);
      bindWinnerPreview(row, idx);
    }

    document.addEventListener("DOMContentLoaded", function () {
      if (typeof CKEDITOR !== "undefined") {
        CKEDITOR.replace("event_description", { height: 220 });
      }
      document.querySelectorAll("#winnerRows .row[data-index]").forEach(function (row) {
        var idx = row.getAttribute("data-index");
        bindWinnerPreview(row, idx);
      });
    });

    document.getElementById("edi-edit-event-form").addEventListener("submit", function (e) {
      if (e.submitter && e.submitter.getAttribute("name") === "deleteEventSubmit") {
        return;
      }
      if (typeof CKEDITOR !== "undefined" && CKEDITOR.instances.event_description) {
        CKEDITOR.instances.event_description.updateElement();
      }
    });
  </script>
</body>
</html>

