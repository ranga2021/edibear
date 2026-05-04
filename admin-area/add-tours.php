<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SESSION FIX
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");

$adminHeader = new HEADER("add-tours");
$user = new USER();
$widgets = new WIDGETS();

$editMode = false;
$currentTourID = 0;

$currentTourNo = "";
$currentTourTitle = "";
$currentTourType = "";
$currentTourMainImage = "";
$currentTourDuration ="";
$currentTourGroup ="";
$currentTourVehicleType ="";
$currentTourGuide ="";
$currentTourPickupDrop ="";
$currentTourHotelType ="";
$currentTourDescription ="";
$currentTourArrivalDepartureLocation ="";
$currentTourDepatureTime ="";
$currentTourMealPlan ="";
$currentTourBedRoom ="";
$currentTourServicesIncluded ="";
$currentTourServicesExcluded ="";
$currentTourMap ="";

// ================= EDIT =================
if (isset($_GET['id']) && $_GET['id'] > 0) {

    $currentTourID = (int)$_GET['id'];

    if ($user->CountRows("tour_details", array("id"=>$currentTourID))) {

        $editMode = true;

        $tour = $user->fetchAll(
            array("*"),
            array("tour_details"),
            array("id"=>$currentTourID)
        )[0];

        extract($tour);

        if (!empty($image_name)) {
            $currentTourMainImage = "src='".$widgets->createCachelessImage("../img/tours/".$image_name)."'";
        }

    } else {
        $user->redirect("./add-tours");
    }
}

// ================= DELETE =================
if (isset($_POST['confirmDeleteTourSubmit'])) {

    $deleteTourID = (int) ($_POST['deleteTourID'] ?? 0);
    if (!$editMode || $deleteTourID < 1 || $deleteTourID !== $currentTourID || !$user->CountRows("tour_details", array("id" => $deleteTourID))) {
        echo "<script>alert('Invalid request.');location.href='./add-tours'</script>";
        exit;
    }

    $trow = $user->fetchAll(array("image_name"), array("tour_details"), array("id" => $deleteTourID));
    if (!empty($trow[0]['image_name'])) {
        $img = basename(str_replace("\\", "/", (string) $trow[0]['image_name']));
        if ($img !== "") {
            $p = "../img/tours/" . $img;
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }

    $user->deleteTableRow("tour_details", array("id" => $deleteTourID));

    echo "<script>alert('Tour deleted successfully');location.href='./createSiteMap?redirect=tours'</script>";
    exit;
}

// ================= SUBMIT =================
if (isset($_POST['addNewTourSubmit']) || isset($_POST['updateTourSubmit'])) {

    $data = [
        "no"=>htmlspecialchars($_POST['inputTourNo'] ?? ""),
        "title"=>htmlspecialchars($_POST['inputTourTitle'] ?? ""),
        "type"=>htmlspecialchars($_POST['inputTourType'] ?? ""),
        "duration"=>htmlspecialchars($_POST['inputTourDuration'] ?? ""),
        "tour_group"=>htmlspecialchars($_POST['inputTourGroup'] ?? ""),
        "vehicle_type"=>htmlspecialchars($_POST['inputTourVehicleType'] ?? ""),
        "guide"=>htmlspecialchars($_POST['inputTourGuide'] ?? ""),
        "pickup_drop"=>htmlspecialchars($_POST['inputTourPickupDrop'] ?? ""),
        "hotel_type"=>htmlspecialchars($_POST['inputTourHotelType'] ?? ""),
        "description"=>strip_tags($_POST['inputTourDescription'] ?? "", "<br>"),
        "arrival_departure_location"=>htmlspecialchars($_POST['inputTourArrivalDepartureLocation'] ?? ""),
        "depature_time"=>htmlspecialchars($_POST['inputTourDepatureTime'] ?? ""),
        "meal_plan"=>htmlspecialchars($_POST['inputTourMealPlan'] ?? ""),
        "bed_room"=>htmlspecialchars($_POST['inputTourBedRoom'] ?? ""),
        "services_included"=>htmlspecialchars($_POST['inputTourServicesIncluded'] ?? ""),
        "services_excluded"=>htmlspecialchars($_POST['inputTourServicesExcluded'] ?? ""),
        "map"=>htmlspecialchars($_POST['inputTourEmbedMap'] ?? "")
    ];

    // ================= ADD =================
    if (isset($_POST['addNewTourSubmit'])) {

        $tourID = $user->insertTable("tour_details", $data, true);

        // IMAGE
        if (!empty($_FILES["inputTourMainImage"]["name"])) {
            $ext = pathinfo($_FILES["inputTourMainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = $tourID.".".$ext;

            move_uploaded_file($_FILES["inputTourMainImage"]["tmp_name"], "../img/tours/".$imageName);

            $user->updateTable("tour_details", ["image_name"=>$imageName], ["id"=>$tourID]);
        }

        echo "<script>alert('Tour added successfully');location.href='./createSiteMap?redirect=tours'</script>";
    }

    // ================= UPDATE =================
    if (isset($_POST['updateTourSubmit'])) {

        $user->updateTable("tour_details", $data, ["id"=>$currentTourID]);

        if (!empty($_FILES["inputTourMainImage"]["name"])) {

            if (!empty($image_name) && file_exists("../img/tours/".$image_name)) {
                unlink("../img/tours/".$image_name);
            }

            $ext = pathinfo($_FILES["inputTourMainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = $currentTourID.".".$ext;

            move_uploaded_file($_FILES["inputTourMainImage"]["tmp_name"], "../img/tours/".$imageName);

            $user->updateTable("tour_details", ["image_name"=>$imageName], ["id"=>$currentTourID]);
        }

        echo "<script>alert('Tour updated successfully');location.href='./createSiteMap?redirect=tours'</script>";
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

<?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit Tour" : "Add Tour"); ?>

<div class="container-fluid py-4">
<div class="card p-3">

<form method="post" enctype="multipart/form-data">

<div class="row">
<?php
echo $widgets->inputGroup("Tour No", "inputTourNo", "col-md-6", $currentTourNo);
echo $widgets->inputGroup("Tour Title", "inputTourTitle", "col-md-6", $currentTourTitle);
?>
</div>

<div class="row mt-3">
<?php
echo $widgets->inputGroup("Type", "inputTourType", "col-md-4", $currentTourType);
echo $widgets->inputGroup("Duration", "inputTourDuration", "col-md-4", $currentTourDuration);
echo $widgets->inputGroup("Group", "inputTourGroup", "col-md-4", $currentTourGroup);
?>
</div>

<div class="row mt-3">
<div class="col-md-6">
<label>Main Image</label>
<input type="file" name="inputTourMainImage" class="form-control" <?php echo !$editMode ? "required" : ""; ?>>
</div>

<div class="col-md-6">
<img <?php echo $currentTourMainImage; ?> style="max-height:200px;">
</div>
</div>

<div class="row mt-3">
<?php
echo $widgets->inputGroup("Vehicle Type", "inputTourVehicleType", "col-md-4", $currentTourVehicleType);
echo $widgets->inputGroup("Guide", "inputTourGuide", "col-md-4", $currentTourGuide);
echo $widgets->inputGroup("Pickup & Drop", "inputTourPickupDrop", "col-md-4", $currentTourPickupDrop);
?>
</div>

<div class="row mt-3">
<?php
echo $widgets->inputGroup("Hotel Type", "inputTourHotelType", "col-md-6", $currentTourHotelType);
echo $widgets->inputGroup("Meal Plan", "inputTourMealPlan", "col-md-6", $currentTourMealPlan);
?>
</div>

<div class="row mt-3">
<div class="col-12">
<label>Description</label>
<textarea name="inputTourDescription" class="form-control"><?php echo $currentTourDescription;?></textarea>
</div>
</div>

<div class="row mt-3">
<?php
echo $widgets->inputGroup("Arrival Location", "inputTourArrivalDepartureLocation", "col-md-6", $currentTourArrivalDepartureLocation);
echo $widgets->inputGroup("Departure Time", "inputTourDepatureTime", "col-md-6", $currentTourDepatureTime);
?>
</div>

<div class="row mt-3">
<?php
echo $widgets->inputGroup("Bedroom", "inputTourBedRoom", "col-md-6", $currentTourBedRoom);
echo $widgets->inputGroup("Map URL", "inputTourEmbedMap", "col-md-6", $currentTourMap);
?>
</div>

<div class="row mt-3">
<div class="col-md-6">
<label>Services Included</label>
<textarea name="inputTourServicesIncluded" class="form-control"><?php echo $currentTourServicesIncluded;?></textarea>
</div>

<div class="col-md-6">
<label>Services Excluded</label>
<textarea name="inputTourServicesExcluded" class="form-control"><?php echo $currentTourServicesExcluded;?></textarea>
</div>
</div>

<div class="mt-4">
<?php
if ($editMode) {
    echo "<button type='submit' name='updateTourSubmit' class='btn btn-primary'>Update</button>";
    echo " <a href='./tours' class='btn btn-secondary'>Cancel</a>";
} else {
    echo "<button type='submit' name='addNewTourSubmit' class='btn btn-success'>Add</button>";
}
?>
</div>

</form>

<?php if ($editMode) { ?>
<form method="post" class="mt-2" onsubmit="return confirm('Delete this tour? This cannot be undone.');">
  <input type="hidden" name="deleteTourID" value="<?php echo (int) $currentTourID; ?>">
  <button type="submit" name="confirmDeleteTourSubmit" value="1" class="btn btn-danger btn-sm">Delete</button>
</form>
<?php } ?>

</div>
</div>

</main>

<?php echo $adminHeader->printAdminFooterJS(); ?>

</body>
</html>