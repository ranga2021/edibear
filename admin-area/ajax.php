<?php
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.widgets.php");

$user = new USER();
$widgets = new WIDGETS();


/* ================= BASIC ================= */

if (isset($_POST['chngCruslImgDisOrdrAndSts'])) {
    $data = $_POST['chngCruslImgDisOrdrAndSts'];

    $user->updateTable("carousel", [
        "display_order" => (int)$data['carouselDisplayOrder'],
        "status" => (int)$data['carouselStatus']
    ], [
        "id" => (int)$data['carouselImgID']
    ]);
}

if (isset($_POST['chngTourSts'])) {
    $data = $_POST['chngTourSts'];

    $user->updateTable("tour_details", [
        "status" => (int)$data['tourStatus']
    ], [
        "id" => (int)$data['tourID']
    ]);
}

if (isset($_POST['deleteCarouselImage'])) {
    $id = (int)$_POST['deleteCarouselImage'];
    $user->deleteTableRow("carousel", ["id"=>$id]);
    echo "<script>location.reload();</script>";
}

/* ================= DYNAMIC UI ================= */

if (isset($_POST['addMoreTourDayDetails'])) {
    echo $widgets->addTourMainDetailsDiv((int)$_POST['addMoreTourDayDetails']);
}

/* ================= SAFE SESSION HANDLING ================= */

function safeSession(&$arr, $index1, $index2 = null) {
    if (!isset($arr[$index1])) return false;
    if ($index2 !== null && !isset($arr[$index1][$index2])) return false;
    return true;
}

/* ===== TOUR OTHER IMAGE ===== */

if (isset($_POST['removeTourOtherImage'])) {
    $id = (int)$_POST['removeTourOtherImage'];

    echo "<script>
        $('input[name=inputTourOtherImage$id]').val('');
        $('#outputTourOtherImage$id').removeAttr('src');
    </script>";

    if (isset($_SESSION['sessionTourSubImgArr']) &&
        safeSession($_SESSION['sessionTourSubImgArr'], $id-1, 0) &&
        !empty($_SESSION['sessionTourSubImgArr'][$id-1][0])) {

        $_SESSION['sessionTourSubImgArr'][$id-1][1] = -1;
    }
}

if (isset($_POST['changeTourOtherImage'])) {
    $id = (int)$_POST['changeTourOtherImage'];

    if (isset($_SESSION['sessionTourSubImgArr']) &&
        safeSession($_SESSION['sessionTourSubImgArr'], $id-1, 0)) {

        $_SESSION['sessionTourSubImgArr'][$id-1][1] = -1;
    }
}

/* ===== TOUR DAY IMAGE ===== */

if (isset($_POST['removeTourDayImage'])) {
    $id = (int)$_POST['removeTourDayImage'];

    echo "<script>
        $('input[name=inputTourMainDetailsDayImage$id]').val('');
        $('#outputTourMainDetailsDayImage$id').removeAttr('src');
    </script>";

    if (isset($_SESSION['sessionTourDayImgArr']) &&
        safeSession($_SESSION['sessionTourDayImgArr'], $id-1, 1) &&
        !empty($_SESSION['sessionTourDayImgArr'][$id-1][1])) {

        $_SESSION['sessionTourDayImgArr'][$id-1][2] = -1;
    }
}

if (isset($_POST['changeTourDayImage'])) {
    $id = (int)$_POST['changeTourDayImage'];

    if (isset($_SESSION['sessionTourDayImgArr']) &&
        safeSession($_SESSION['sessionTourDayImgArr'], $id-1, 1)) {

        $_SESSION['sessionTourDayImgArr'][$id-1][2] = -1;
    }
}

/* ================= DESCRIPTION ADD ================= */

$descMap = [
    "addMoreBlogDescriptions" => "addBlogDesctiptionDiv",
    "addMoread1Descriptions" => "addad1DesctiptionDiv",
    "addMoread2Descriptions" => "addad2DesctiptionDiv",
    "addMorepdfDescriptions" => "addpdfDesctiptionDiv",
    "addMorehomeworkDescriptions" => "addhomeworkDesctiptionDiv",
    "addMorebooksDescriptions" => "addbooksDesctiptionDiv"
];

foreach ($descMap as $key => $method) {
    if (isset($_POST[$key])) {
        echo $widgets->$method((int)$_POST[$key]);
    }
}

/* ================= GENERIC REMOVE IMAGE ================= */

function removeDescImage($sessionKey, $postKey, $postNoKey, $inputPrefix, $outputPrefix) {

    if (!isset($_POST[$postKey]) || !isset($_POST[$postNoKey])) return;

    $id = (int)$_POST[$postKey];
    $no = (int)$_POST[$postNoKey];

    if (isset($_SESSION[$sessionKey]) && safeSession($_SESSION[$sessionKey], $id-1)) {

        if ($no == 1 && !empty($_SESSION[$sessionKey][$id-1][1])) {
            $_SESSION[$sessionKey][$id-1][2] = -1;
        }

        if ($no == 2 && !empty($_SESSION[$sessionKey][$id-1][3])) {
            $_SESSION[$sessionKey][$id-1][4] = -1;
        }
    }

    $label = ($no == 1) ? "One" : "Two";
    $id = $label . $id;

    echo "<script>
        $('input[name={$inputPrefix}{$id}]').val('');
        $('#{$outputPrefix}{$id}').removeAttr('src');
    </script>";
}

/* APPLY REMOVE */
removeDescImage('sessionBlogDescImgArr','removeBlogDescImage','removeBlogDescImageNo','inputBlogImage','outputBlogImage');
removeDescImage('sessionad1DescImgArr','removead1DescImage','removead1DescImageNo','inputad1Image','outputad1Image');
removeDescImage('sessionad2DescImgArr','removead2DescImage','removead2DescImageNo','inputad2Image','outputad2Image');
removeDescImage('sessionpdfDescImgArr','removepdfDescImage','removepdfDescImageNo','inputpdfImage','outputpdfImage');
removeDescImage('sessionhomeworkDescImgArr','removehomeworkDescImage','removehomeworkDescImageNo','inputhomeworkImage','outputhomeworkImage');
removeDescImage('sessionbooksDescImgArr','removebooksDescImage','removebooksDescImageNo','inputbooksImage','outputbooksImage');

/* ================= USERNAME ================= */

if (isset($_POST['checkTouristUsername']) && isset($_POST['username'])) {

    $id = (int)$_POST['checkTouristUsername'];
    $username = htmlspecialchars($_POST['username']);

    $count = ($id == 0)
        ? $user->CountRows("tourists", ["username"=>$username])
        : (($user->fetchAll(["username"],["tourists"],["id"=>$id])[0]['username'] == $username)
            ? 0
            : $user->CountRows("tourists", ["username"=>$username]));

    echo ($count > 0)
        ? "<script>$('#usernameAlreadyTakenErr').text('This username is already taken');</script>"
        : "<script>$('#usernameAlreadyTakenErr').text('');</script>";
}

/* ================= STATUS ================= */

$statusMap = [
    "chngTouristSts"  => ["table" => "tourists",          "prefix" => "tourist"],
    "chngBlogSts"     => ["table" => "blog_details",      "prefix" => "blog"],
    "chngad1Sts"      => ["table" => "ad1_details",       "prefix" => "ad1"],
    "chngad2Sts"      => ["table" => "ad2_details",       "prefix" => "ad2"],
    "chngpdfSts"      => ["table" => "pdf_details",       "prefix" => "pdf"],
    "chnghomeworkSts" => ["table" => "homework_details",  "prefix" => "homework"],
    "chngbooksSts"    => ["table" => "books_details",     "prefix" => "books"]
];

if (isset($_POST['chngAdminUserSts']) && $user->userTableAdminExtrasAvailable()) {
	$data = $_POST['chngAdminUserSts'];
	$id = (int) (isset($data['adminUserID']) ? $data['adminUserID'] : 0);
	$status = (int) (isset($data['adminUserStatus']) ? $data['adminUserStatus'] : 0);
	if ($id > 0) {
		$user->updateTable("user_table", array(
			"admin_status" => $status,
		), array("id" => $id));
	}
}

foreach ($statusMap as $postKey => $config) {
    if (isset($_POST[$postKey])) {
        $data = $_POST[$postKey];
        $prefix = $config['prefix'];
        
        // Dynamically construct the keys: e.g., pdfID and pdfStatus
        $idKey = $prefix . "ID";
        $statusKey = $prefix . "Status";

        if (isset($data[$idKey])) {
            $id = (int)$data[$idKey];
            $status = (int)$data[$statusKey];

            $user->updateTable($config['table'], [
                "status" => $status
            ], [
                "id" => $id
            ]);
        }
    }
}

/* ================= TESTIMONIAL ================= */

if (isset($_POST['showTestimonial'])) {

    $id = (int)$_POST['showTestimonial'];

    $testimonial = $user->fetchAll(
        ["id","user_id","ratings","one_word","review","status"],
        ["testimonials"],
        ["id"=>$id]
    )[0];

    $userData = $user->fetchAll(
        ["name","profile_pic","country"],
        ["tourists"],
        ["id"=>$testimonial['user_id']]
    )[0];

    $imgRows = $user->fetchAll(["image"], ["testimonials_images"], ["testimonial_id"=>$id]);
    $testimonialPhoto = (!empty($imgRows[0]['image'])) ? $imgRows[0]['image'] : '';

    echo $widgets->displayTestimonial(array_merge($testimonial, $userData, ["testimonial_photo"=>$testimonialPhoto]), $user, true);
}
?>