<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ SESSION FIX (same pattern)
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("add-event");
$user = new USER();

// ================= FORM SUBMIT =================
if (isset($_POST['addNewEventSubmit'])) {

    $selectedCategory = $_POST['event_category'] ?? '';
    $newCategoryText  = trim($_POST['new_category'] ?? '');
    $eventTitle       = htmlspecialchars($_POST['event_title'] ?? '');
    $description      = strip_tags($_POST['event_description'] ?? '', "<br>");
    $deadlineDate     = $_POST['deadline_date'] ?? null;

    // ================= CATEGORY =================
    $categoryId = null;

    if ($selectedCategory === 'other' && $newCategoryText !== '') {

        $existing = $user->fetchAll(
            array("id"),
            array("braveheart_categories"),
            array("name"=>$newCategoryText)
        );

        if (!empty($existing)) {
            $categoryId = (int)$existing[0]['id'];
        } else {
            $categoryId = $user->insertTable(
                "braveheart_categories",
                array("name"=>$newCategoryText, "status"=>1),
                true
            );
        }

    } elseif (ctype_digit((string)$selectedCategory)) {
        $categoryId = (int)$selectedCategory;
    }

    // ================= INSERT EVENT =================
    $eventId = $user->insertTable(
        "braveheart_events",
        array(
            "category_id"=>$categoryId,
            "title"=>$eventTitle,
            "description"=>$description,
            "deadline_date"=>$deadlineDate,
            "status"=>1
        ),
        true
    );

    $uploadDir = "../img/braveheart/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    // ================= MAIN IMAGE =================
    if (!empty($_FILES["main_image"]["name"])) {
        $ext = pathinfo($_FILES["main_image"]["name"], PATHINFO_EXTENSION);
        $fileName = $eventId.".".$ext;

        move_uploaded_file($_FILES["main_image"]["tmp_name"], $uploadDir.$fileName);

        $user->updateTable("braveheart_events", array("main_image"=>$fileName), array("id"=>$eventId));
    }

    // ================= PDF =================
    if (!empty($_FILES["application_file"]["name"])) {
        $ext = pathinfo($_FILES["application_file"]["name"], PATHINFO_EXTENSION);
        $fileName = $eventId."-application.".$ext;

        move_uploaded_file($_FILES["application_file"]["tmp_name"], $uploadDir.$fileName);

        $user->updateTable("braveheart_events", array("application_file"=>$fileName), array("id"=>$eventId));
    }

    echo "<script>alert('Event added successfully');location.href='./add-event'</script>";
    exit;
}

// ================= FETCH CATEGORIES =================
try {
    $eventCategories = $user->fetchAll(
        array("id","name"),
        array("braveheart_categories"),
        array(),
        "name ASC"
    );
} catch (Exception $e) {
    $eventCategories = array();
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
    <?php echo $adminHeader->printAdminNav2("Add Brave Heart Challenge"); ?>

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
                      <select name="event_category" id="eventCategory" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($eventCategories as $cat): ?>
                          <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
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
                      <label class="form-control-label">New Category</label>
                      <textarea name="new_category" class="form-control" rows="2" placeholder="Enter new category name"></textarea>
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
                    <p class="text-center mt-3">
                      <img id="outputmain_image" style="max-height: 200px; max-width:100%" />
                    </p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-control-label">Deadline Date</label>
                      <input type="date" name="deadline_date" class="form-control" required>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <label class="form-control-label">Descriptions</label>
                      <textarea name="event_description" class="form-control" rows="5"></textarea>
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

                <div class="row mt-3">
                  <div class="col-12">
                    <input type="submit" class="btn btn-success" name="addNewEventSubmit" value="Add Event">
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
    document.getElementById('eventCategory').addEventListener('change', function () {
      var wrapper = document.getElementById('newCategoryWrapper');
      if (this.value === 'other') {
        wrapper.style.display = 'block';
      } else {
        wrapper.style.display = 'none';
      }
    });

    function loadImageFile(event) {
      var imageDivID = 'output' + event.target.name;
      var image = document.getElementById(imageDivID);
      if (image && event.target.files && event.target.files[0]) {
        image.src = URL.createObjectURL(event.target.files[0]);
      }
    }
  </script>
</body>
</html>

