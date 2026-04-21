<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ SESSION FIX (same as add-books)
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");

$adminHeader = new HEADER("add-ad2");
$user = new USER();
$widgets = new WIDGETS();

$editMode = false;
$currentad2ID = 0;

$currentad2Tag = "";
$currentad2Title = "";
$currentad2MainDescription = "";
$currentad2VideoUrl = "";
$currentad2VideoStatus = "";
$currentad2MainImage = "";
$currentad2adlink = "";

// ================= EDIT MODE =================
if (isset($_GET['id']) && $_GET['id'] > 0) {

    $currentad2ID = (int)$_GET['id'];

    if ($user->CountRows("ad2_details", array("id"=>$currentad2ID))) {

        $editMode = true;

        $ad2DetailsArr = $user->fetchAll(
            array("tag","title","image","description","video","video_status","adlink"),
            array("ad2_details"),
            array("id"=>$currentad2ID)
        )[0];

        $currentad2Tag = $ad2DetailsArr['tag'];
        $currentad2Title = $ad2DetailsArr['title'];
        $currentad2MainDescription = $ad2DetailsArr['description'];
        $currentad2VideoUrl = $ad2DetailsArr['video'];
        $currentad2VideoStatus = ($ad2DetailsArr['video_status']=='1') ? "checked" : "";
        $currentad2adlink = $ad2DetailsArr['adlink'];

        if (!empty($ad2DetailsArr['image'])) {
            $currentad2MainImage = "src='".$widgets->createCachelessImage("../img/ad2/".$ad2DetailsArr['image'])."'";
        }

    } else {
        $user->redirect("./add-ad2");
    }
}

// ================= FORM SUBMIT =================
if (isset($_POST['addNewad2Submit']) || isset($_POST['updatead2Submit'])) {

    $inputad2Tag = htmlspecialchars($_POST['inputad2Tag'] ?? "");
    $inputad2Title = htmlspecialchars($_POST['inputad2Title'] ?? "");
    $inputad2MainDescription = strip_tags($_POST['inputad2MainDescription'] ?? "", "<br>");
    $inputad2VideoUrl = htmlspecialchars($_POST['inputad2VideoUrl'] ?? "");
    $inputad2adlink = htmlspecialchars($_POST['inputad2adlink'] ?? "");
    $ad2VideoStatus = $_POST['ad2VideoStatus'] ?? 0;

    // ================= ADD =================
    if (isset($_POST['addNewad2Submit'])) {

        $imageName = "";
        if (!empty($_FILES["inputad2MainImage"]["name"])) {
            $ext = pathinfo($_FILES["inputad2MainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = time().".".$ext;
            move_uploaded_file($_FILES["inputad2MainImage"]["tmp_name"], "../img/ad2/".$imageName);
        }

        $ad2ID = $user->insertTable("ad2_details", array(
            "tag"=>$inputad2Tag,
            "title"=>$inputad2Title,
            "description"=>$inputad2MainDescription,
            "video"=>$inputad2VideoUrl,
            "video_status"=>$ad2VideoStatus,
            "image"=>$imageName,
            "adlink"=>$inputad2adlink
        ), true);

        echo "<script>alert('Ad2 added successfully');location.href='./createSiteMap?redirect=ad2'</script>";
    }

    // ================= UPDATE =================
    if (isset($_POST['updatead2Submit'])) {

        $user->updateTable("ad2_details", array(
            "tag"=>$inputad2Tag,
            "title"=>$inputad2Title,
            "description"=>$inputad2MainDescription,
            "video"=>$inputad2VideoUrl,
            "video_status"=>$ad2VideoStatus,
            "adlink"=>$inputad2adlink
        ), array("id"=>$currentad2ID));

        if (!empty($_FILES["inputad2MainImage"]["name"])) {

            if (!empty($ad2DetailsArr['image']) && file_exists("../img/ad2/".$ad2DetailsArr['image'])) {
                unlink("../img/ad2/".$ad2DetailsArr['image']);
            }

            $ext = pathinfo($_FILES["inputad2MainImage"]["name"], PATHINFO_EXTENSION);
            $imageName = $currentad2ID.".".$ext;

            move_uploaded_file($_FILES["inputad2MainImage"]["tmp_name"], "../img/ad2/".$imageName);

            $user->updateTable("ad2_details", array("image"=>$imageName), array("id"=>$currentad2ID));
        }

        echo "<script>alert('Ad2 updated successfully');location.href='./createSiteMap?redirect=ad2'</script>";
    }

    // ================= DELETE =================
    if (isset($_POST['confirmDeletead2Submit'])) {

        $deletead2ID = (int)$_POST['deletead2ID'];

        foreach ($user->fetchAll(array("image_01","image_02"), array("ad2_descriptions"), array("ad2_id"=>$deletead2ID)) as $row) {
            if (!empty($row['image_01'])) unlink("../img/ad2/".$row['image_01']);
            if (!empty($row['image_02'])) unlink("../img/ad2/".$row['image_02']);
        }

        $main = $user->fetchAll(array("image"), array("ad2_details"), array("id"=>$deletead2ID));
        if (!empty($main[0]['image'])) {
            unlink("../img/ad2/".$main[0]['image']);
        }

        $user->deleteTableRow("ad2_details", array("id"=>$deletead2ID));

        echo "<script>alert('Ad2 deleted successfully');location.href='./createSiteMap?redirect=ad2'</script>";
    }
}
?>
<script>
    // 1. Check if the localStorage item exists
    const adminSession = localStorage.getItem('admin_session');
    const sessionTime = localStorage.getItem('session_time');
    const currentTime = Math.floor(Date.now() / 1000);

    // 2. If missing OR older than 20 minutes (1200 seconds), kick them out
    if (!adminSession || (currentTime - sessionTime > 1200)) {
        localStorage.removeItem('admin_session');
        window.location.href = 'index.php?error=session_expired';
    }
</script>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <meta property='og:title' content='Kids Coloring Pages, Activity Books & Study Packs'/>
  <meta name='description' content='“edibear” is a website that provides a variety of kids coloring pages, activity books, relevant model papers, school related study materials, fun activities for developing the abilities of kids. '/>
<meta name='keywords' content='printable coloring pages for kids, free coloring pages, kids activities, Relevant past papers, model Papers, school related study materials, Fun activities for kids, Developing kids&#8217; abilities, Educational resources for kids, Downloadable kids&#8217; materials, Creative learning for kids, Sinhala Coloring Pages, Tamil Coloring Pages' />
</head>

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  <main class="main-content position-relative border-radius-lg">
    <!-- Navbar -->
    <?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit ad2" : $adminHeader->getActivePageName()); ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <form accept="" method="post" enctype="multipart/form-data">
                <!-- <div class="row"> -->
                  <?php
                    echo $widgets->inputGroup("ad2 link", "inputad2adlink", "col-md-12", $currentad2adlink);
                    // echo $widgets->inputGroup("ad2 Title", "inputad2Title", "col-md-6", $currentad2Title);
                  ?>
                <!-- </div> -->
                <div class="row border mx-3 mb-2">
                  <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">AD 1 Image</label>
                      <?php
                        if ( $editMode ) {
                          $mainImageRequired = "";
                        } else {
                          $mainImageRequired = "required";
                        }
                        echo "<input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event)' name='inputad2MainImage' $mainImageRequired>";
                      ?>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <p class="text-center mt-3"><img id='outputad2MainImage' <?php echo $currentad2MainImage; ?> style='max-height: 200px; max-width:100%' /></p>
                  </div>
                </div>
                <!-- <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <label for='example-text-input' class='form-control-label'>Main Description</label>
                      <textarea class='form-control' name='inputad2MainDescription' rows="4" required><?php echo $currentad2MainDescription;?></textarea>
                    </div>
                  </div>
                </div> -->
                <!-- Description -->
                <?php
                  // if ( !$editMode ) {
                  //   echo $widgets->addad2DesctiptionDiv(1); 
                  // } else {
                  //   $i=0;
                  //   $sessionad2DescImgArr = array();
                  //   foreach ( $user->fetchAll(array("id","description", "image_01", "image_02"), array("ad2_descriptions"), array("ad2_id"=>$currentad2ID)) as $row ) {
                  //     if ( $i>0 ) echo "<script>$('#addMoread2Description$i').css('display', 'none');</script>";
                  //     $sessionad2DescImgArr[$i] = array(
                  //       $row['id'],
                  //       $row['image_01'],
                  //       ($row['image_01']!= "") ? 1 : 0,
                  //       $row['image_02'],
                  //       ($row['image_02']!= "") ? 1 : 0
                  //     );
                  //     $i++;
                  //     echo $widgets->addad2DesctiptionDiv($i, $row); 
                  //   }
                  //   $_SESSION['sessionad2DescImgArr'] = $sessionad2DescImgArr;
                  //   echo "<script>
                  //   $(document).ready(function () {
                  //     $('input[name=howManyDescriptions]').val('$i');
                  //   })
                  //   </script>";
                  // }
                ?>
                <!-- <div class="row justify-content-center" id="addMoread2DescLoadingImage" style="display: none;">
                  <img src="../img/loading.gif" alt="Loading GIF" style="width: 100px;">
                </div>
                <div class="row">
                  <?php
                    echo $widgets->inputGroup("ad2 Video", "inputad2VideoUrl", "col-md-6", $currentad2VideoUrl);
                    echo "<div class='col-md-6 float-left'>".$widgets->checkboxSwitch("", "ad2VideoStatus", $currentad2VideoStatus, "pt-5")."</div>";
                  ?>
                </div> -->
                <div class="row">
                  <div class="col-12">
                    <input type="hidden" name="howManyDescriptions" value="1">
                    <?php
                      if ( $editMode ) {
                        echo "
                        <input type='submit' class='btn btn-primary' value='Update ad2' name='updatead2Submit'>
                        <input type='button' class='btn btn-danger' value='Delete ad2' onclick='deletead2Submit()'>
                        ";
                      } else {
                        echo "<input type='submit' class='btn btn-success' value='Add ad2' name='addNewad2Submit'>";
                      }
                    ?>
                    <input type="button" class="btn btn-secondary" value="Cancel" onclick="location.href='./add-ad2'">
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
    <div id="removead2DescImage"></div>
  </main>
  <?php 
    echo $adminHeader->printAdminFooterJS(); 
    if ( $editMode ) {
      echo "
      <div class='modal fade' id='confirmDeletead2Modal' data-backdrop='static' tabindex='-1' role='dialog' aria-labelledby='staticBackdropLabel' aria-hidden='true' style='margin-top:200px'>
        <div class='modal-dialog' role='document'>
          <div class='modal-content'>
            <div class='modal-header'>
              <h5 class='modal-title' id='staticBackdropLabel'>Confirm Delete ad2</h5>
            </div>
            <div class='modal-body'>
            <form action='' class='text-center' method='post'>
                ad2 Title : $currentad2Title<br>
                ad2 Tag : $currentad2Tag<br>
                <input type='hidden' name='deletead2ID' value='$currentad2ID'>
              <br>
              <input type='submit' class='btn btn-danger btn-sm' name='confirmDeletead2Submit' value='Delete'>
              <button class='btn btn-sm btn-secondary' type='button' onclick='location.reload()'>Cancel</button>
            </form>
            </div>
          </div>
        </div>
      </div>
      ";
    }
  ?>
  <script>
    function loadImageFile(event, sessionTF=0) { 
      var imageDivID = event.target.name.replace("input", "output");
      var imageDivIdNumber = imageDivID.substr(-1);
      $("#"+imageDivID).addClass("border");
			var image = document.getElementById(imageDivID);
			image.src = URL.createObjectURL(event.target.files[0]);
      if (sessionTF) {
        $.ajax({
          type: "POST",
          url: "ajax.php",
          data: {
              changead2DescImage: imageDivIdNumber,
              changead2DescImageNo: imageDivID.substr(15, 3)
          },
          success: function(html) {
              $("#removead2DescImage").html(html).show();
          }
        }); 
      }
		}

    function updateHowManyDescriptions(val) {
      $("input[name='howManyDescriptions']").val(val);
      console.log( $("input[name='howManyDescriptions']").val() );
    }

    function addMoread2Descriptions(index) {
      $("#addMoread2Description"+index).css("display", "none");
      $("#addMoread2DescLoadingImage").css("display", "flex");
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
          addMoread2Descriptions: index + 1
        },
        success: function(html) {
          $("#addMoread2Descriptions"+index).html(html).show();
          $("#addMoread2DescLoadingImage").css("display", "none");
          index++;
          $("input[name='howManyDescriptions']").val(index);
        }
      }); 
    }

    function removead2DescImage(index, imgNo) {
      $.ajax({
          type: "POST",
          url: "ajax.php",
          data: {
            removead2DescImage: index,
            removead2DescImageNo: imgNo
          },
          success: function(html) {
              $("#removead2DescImage").html(html).show();
          }
      }); 
    }

    function deletead2Submit() {
      $('#confirmDeletead2Modal').modal('show');
    }
  </script>
</body>

</html>