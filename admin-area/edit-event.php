<?php
  session_start();
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");

  $adminHeader = new HEADER("add-event");
  $user = new USER();


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
      $eventTitle       = htmlspecialchars(isset($_POST['event_title']) ? $_POST['event_title'] : '');
      $description      = strip_tags(isset($_POST['event_description']) ? $_POST['event_description'] : '', "<br>");
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
        }
        elseif (isset($existingWinnerImages[$index])) {
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
      $user->deleteTableRow("braveheart_winners", array("event_id" => $eventId));
      $user->deleteTableRow("braveheart_events", array("id" => $eventId));
      echo "<script>alert('Event deleted successfully');location.href='event.php'</script>";
      exit;
    }
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2("Edit Brave Heart Event"); ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <form method="post" enctype="multipart/form-data">
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
                      <input type="text" name="event_title" class="form-control" value="<?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
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
                      <textarea name="event_description" class="form-control" rows="5"><?php echo htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Application Upload</label>
                      <input class="form-control" type="file" accept="application/pdf" name="application_file">
                    </div>
                  </div>
                </div>

                <hr>
                <h6>Winner Details</h6>

                <div id="winnerRows">
                  <?php
                  $index = 0;
                  foreach ($existingWinners as $winner) {
                      $title = htmlspecialchars($winner['title'], ENT_QUOTES, 'UTF-8');
                      $image = htmlspecialchars($winner['image'], ENT_QUOTES, 'UTF-8');
                      echo "
                      <div class='row align-items-center mb-3' data-index='$index'>
                          <input type='hidden' name='existing_winner_image[$index]' value='$image'>
                          
                          <div class='col-md-4'>
                              <div class='form-group'>
                                  <label class='form-control-label'>Title</label>
                                  <input type='text' name='winner_title[$index]' class='form-control' value='$title'>
                              </div>
                          </div>
                          <div class='col-md-4'>
                              <div class='form-group'>
                                  <label class='form-control-label'>Change Image (Optional)</label>
                                  <input type='file' name='winner_image_$index' class='form-control' accept='image/*'>
                              </div>
                          </div>
                          <div class='col-md-4'>
                              <p class='text-center mt-3'>
                                  <img src='../img/braveheart/$image' style='max-height: 80px; max-width:100%' />
                              </p>
                          </div>
                      </div>";
                      $index++;
                  }
                  ?>
                </div>

                <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="addWinnerRow()">Add Winners +</button>

                <div class="row mt-4">
                  <div class="col-12 edi-admin-form-actions">
                    <input type="submit" class="btn btn-success" name="updateEventSubmit" value="Update">
                    <input type="submit" class="btn btn-danger" name="deleteEventSubmit" value="Delete" onclick="return confirm('Are you sure you want to delete this event?');">
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

    function addWinnerRow() {
      var container = document.getElementById('winnerRows');
      var idx = winnerIndex++;
      var row = document.createElement('div');
      row.className = 'row align-items-center mb-3';
      row.setAttribute('data-index', idx);
      row.innerHTML = ''
        + '<div class="col-md-4">'
        + '  <div class="form-group">'
        + '    <label class="form-control-label">Title</label>'
        + '    <input type="text" name="winner_title[' + idx + ']" class="form-control">'
        + '  </div>'
        + '</div>'
        + '<div class="col-md-4">'
        + '  <div class="form-group">'
        + '    <label class="form-control-label">Image (JPG)</label>'
        + '    <input type="file" name="winner_image_' + idx + '" class="form-control" accept="image/*">'
        + '  </div>'
        + '</div>'
        + '<div class="col-md-4"></div>';
      container.appendChild(row);
    }
  </script>
</body>
</html>

