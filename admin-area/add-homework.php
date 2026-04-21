<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SAFE SESSION
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");

$adminHeader = new HEADER("add-homework");
$user = new USER();
$widgets = new WIDGETS();

$editMode = false;
$currenthomeworkTag = "";
$currenthomeworkTitle = "";
$currenthomeworkMainDescription = "";
$currenthomeworkMainImage = "";
$currenthomeworkpdfupload = "";
$currenthomeworkID = 0;

// ================= EDIT MODE =================
if (isset($_GET['id']) && $_GET['id'] > 0) {

    $currenthomeworkID = (int)$_GET['id'];

    if ($user->CountRows("homework_details", ["id"=>$currenthomeworkID])) {

        $editMode = true;

        $data = $user->fetchAll(
            ["tag","title","image","description","pdfupload"],
            ["homework_details"],
            ["id"=>$currenthomeworkID]
        )[0];

        $currenthomeworkTag = $data['tag'];
        $currenthomeworkTitle = $data['title'];
        $currenthomeworkMainDescription = $data['description'];

        if (!empty($data['image'])) {
            $currenthomeworkMainImage = "src='".$widgets->createCachelessImage("../img/homework/".$data['image'])."'";
        }

        if (!empty($data['pdfupload'])) {
            $currenthomeworkpdfupload = "src='".$widgets->createCachelessImage("../img/homework/".$data['pdfupload'])."'";
        }

    } else {
        header("Location: ./add-homework");
        exit;
    }
}

// ================= ADD =================
if (isset($_POST['addNewhomeworkSubmit'])) {

    $tag   = htmlspecialchars($_POST['inputhomeworkTag'] ?? "");
    $title = htmlspecialchars($_POST['inputhomeworkTitle'] ?? "");
    $desc  = strip_tags($_POST['inputhomeworkMainDescription'] ?? "", "<br>");

    $homeworkID = $user->insertTable("homework_details", [
    "tag"=>$tag,
    "title"=>$title,
    "description"=>$desc,
    "image"=>"",          // required
    "video"=>"",          // ✅ FIX
    "video_status"=>0,
    "pdfupload"=>"",
    "download_count"=>0,
    "language_id"=>0,
    "grade_id"=>0,
    "main_cat_id"=>0,
    "sub_cat_id"=>0
    // ✅ FIX
], true);

    $uploadDir = "../img/homework/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

    // IMAGE
    if (!empty($_FILES["inputhomeworkMainImage"]["name"])) {
        $ext = pathinfo($_FILES["inputhomeworkMainImage"]["name"], PATHINFO_EXTENSION);
        $file = $homeworkID.".".$ext;

        move_uploaded_file($_FILES["inputhomeworkMainImage"]["tmp_name"], $uploadDir.$file);

        $user->updateTable("homework_details", ["image"=>$file], ["id"=>$homeworkID]);
    }

    // PDF
    if (!empty($_FILES["inputhomeworkpdfupload"]["name"])) {
        $file = $homeworkID.".pdf"; // ✅ FIXED SHORT NAME

        move_uploaded_file($_FILES["inputhomeworkpdfupload"]["tmp_name"], $uploadDir.$file);

        $user->updateTable("homework_details", ["pdfupload"=>$file], ["id"=>$homeworkID]);
    }

    echo "<script>alert('Homework added successfully');location.href='./createSiteMap?redirect=homework'</script>";
    exit;
}

// ================= UPDATE =================
if (isset($_POST['updatehomeworkSubmit'])) {

    $tag   = htmlspecialchars($_POST['inputhomeworkTag'] ?? "");
    $title = htmlspecialchars($_POST['inputhomeworkTitle'] ?? "");
    $desc  = strip_tags($_POST['inputhomeworkMainDescription'] ?? "", "<br>");

    $user->updateTable("homework_details", [
        "tag"=>$tag,
        "title"=>$title,
        "description"=>$desc
    ], ["id"=>$currenthomeworkID]);

    $uploadDir = "../img/homework/";

    // IMAGE UPDATE
    if (!empty($_FILES["inputhomeworkMainImage"]["name"])) {
        $ext = pathinfo($_FILES["inputhomeworkMainImage"]["name"], PATHINFO_EXTENSION);
        $file = $currenthomeworkID.".".$ext;

        move_uploaded_file($_FILES["inputhomeworkMainImage"]["tmp_name"], $uploadDir.$file);

        $user->updateTable("homework_details", ["image"=>$file], ["id"=>$currenthomeworkID]);
    }

    // PDF UPDATE
    if (!empty($_FILES["inputhomeworkpdfupload"]["name"])) {
        $file = $currenthomeworkID.".pdf";

        move_uploaded_file($_FILES["inputhomeworkpdfupload"]["tmp_name"], $uploadDir.$file);

        $user->updateTable("homework_details", ["pdfupload"=>$file], ["id"=>$currenthomeworkID]);
    }

    echo "<script>alert('Updated successfully');location.href='./createSiteMap?redirect=homework'</script>";
    exit;
}

// ================= DELETE =================
if (isset($_POST['confirmDeletehomeworkSubmit'])) {

    $id = (int)$_POST['deletehomeworkID'];

    $user->deleteTableRow("homework_details", ["id"=>$id]);

    echo "<script>alert('Deleted successfully');location.href='./createSiteMap?redirect=homework'</script>";
    exit;
}
?>
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
    <?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit homework" : $adminHeader->getActivePageName()); ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <form accept="" method="post" enctype="multipart/form-data">
                <div class="row">
                  <?php
                    echo $widgets->inputGroup("homework Tag", "inputhomeworkTag", "col-md-6", $currenthomeworkTag);
                    echo $widgets->inputGroup("homework Title", "inputhomeworkTitle", "col-md-6", $currenthomeworkTitle);
                  ?>
                </div>
                <div class="row border mx-3 mb-2">
                  <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Main Image</label>
                      <?php
                        if ( $editMode ) {
                          $mainImageRequired = "";
                        } else {
                          $mainImageRequired = "required";
                        }
                        echo "<input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event)' name='inputhomeworkMainImage' $mainImageRequired>";
                      ?>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <p class="text-center mt-3"><img id='outputhomeworkMainImage' <?php echo $currenthomeworkMainImage; ?> style='max-height: 200px; max-width:100%' /></p>
                  </div>

                  <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Main PDF</label>
                      <?php
                        if ( $editMode ) {
                          $pdfuploadRequired = "";
                        } else {
                          $pdfuploadRequired = "required";
                        }
                        echo "<input class='form-control' type='file' accept='image/jpeg,image/gif,image/png,application/pdf,image/x-eps' onchange='loadImageFile(event)' name='inputhomeworkpdfupload' $pdfuploadRequired>";
                      ?>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <p class="text-center mt-3"><img id='outputhomeworkpdfupload' <?php echo $currenthomeworkpdfupload; ?> style='max-height: 200px; max-width:100%' /></p>
                  </div>


                </div>
                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <label for='example-text-input' class='form-control-label'>Main Description</label>
                      <textarea class='form-control' name='inputhomeworkMainDescription' rows="4" required><?php echo $currenthomeworkMainDescription;?></textarea>
                    </div>
                  </div>
                </div>
                <!-- Description -->
                <?php
                  // if ( !$editMode ) {
                  //   echo $widgets->addhomeworkDesctiptionDiv(1); 
                  // } else {
                  //   $i=0;
                  //   $sessionhomeworkDescImgArr = array();
                  //   foreach ( $user->fetchAll(array("id","description", "image_01", "image_02"), array("homework_descriptions"), array("homework_id"=>$currenthomeworkID)) as $row ) {
                  //     if ( $i>0 ) echo "<script>$('#addMorehomeworkDescription$i').css('display', 'none');</script>";
                  //     $sessionhomeworkDescImgArr[$i] = array(
                  //       $row['id'],
                  //       $row['image_01'],
                  //       ($row['image_01']!= "") ? 1 : 0,
                  //       $row['image_02'],
                  //       ($row['image_02']!= "") ? 1 : 0
                  //     );
                  //     $i++;
                  //     echo $widgets->addhomeworkDesctiptionDiv($i, $row); 
                  //   }
                  //   $_SESSION['sessionhomeworkDescImgArr'] = $sessionhomeworkDescImgArr;
                  //   echo "<script>
                  //   $(document).ready(function () {
                  //     $('input[name=howManyDescriptions]').val('$i');
                  //   })
                  //   </script>";
                  // }
                ?>
                <!-- <div class="row justify-content-center" id="addMorehomeworkDescLoadingImage" style="display: none;">
                  <img src="../img/loading.gif" alt="Loading GIF" style="width: 100px;">
                </div>
                <div class="row">
                  <?php
                    echo $widgets->inputGroup("homework Video", "inputhomeworkVideoUrl", "col-md-6", $currenthomeworkVideoUrl);
                    echo "<div class='col-md-6 float-left'>".$widgets->checkboxSwitch("", "homeworkVideoStatus", $currenthomeworkVideoStatus, "pt-5")."</div>";
                  ?>
                </div> -->
                <div class="row">
                  <div class="col-12">
                    <input type="hidden" name="howManyDescriptions" value="1">
                    <?php
                      if ( $editMode ) {
                        echo "
                        <input type='submit' class='btn btn-primary' value='Update homework' name='updatehomeworkSubmit'>
                        <input type='button' class='btn btn-danger' value='Delete homework' onclick='deletehomeworkSubmit()'>
                        ";
                      } else {
                        echo "<input type='submit' class='btn btn-success' value='Add homework' name='addNewhomeworkSubmit'>";
                      }
                    ?>
                    <input type="button" class="btn btn-secondary" value="Cancel" onclick="location.href='./add-homework'">
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
    <div id="removehomeworkDescImage"></div>
  </main>
  <?php 
    echo $adminHeader->printAdminFooterJS(); 
    if ( $editMode ) {
      echo "
      <div class='modal fade' id='confirmDeletehomeworkModal' data-backdrop='static' tabindex='-1' role='dialog' aria-labelledby='staticBackdropLabel' aria-hidden='true' style='margin-top:200px'>
        <div class='modal-dialog' role='document'>
          <div class='modal-content'>
            <div class='modal-header'>
              <h5 class='modal-title' id='staticBackdropLabel'>Confirm Delete a homework</h5>
            </div>
            <div class='modal-body'>
            <form action='' class='text-center' method='post'>
                homework Title : $currenthomeworkTitle<br>
                homework Tag : $currenthomeworkTag<br>
                <input type='hidden' name='deletehomeworkID' value='$currenthomeworkID'>
              <br>
              <input type='submit' class='btn btn-danger btn-sm' name='confirmDeletehomeworkSubmit' value='Delete'>
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
              changehomeworkDescImage: imageDivIdNumber,
              changehomeworkDescImageNo: imageDivID.substr(15, 3)
          },
          success: function(html) {
              $("#removehomeworkDescImage").html(html).show();
          }
        }); 
      }
		}

    function updateHowManyDescriptions(val) {
      $("input[name='howManyDescriptions']").val(val);
      console.log( $("input[name='howManyDescriptions']").val() );
    }

    function addMorehomeworkDescriptions(index) {
      $("#addMorehomeworkDescription"+index).css("display", "none");
      $("#addMorehomeworkDescLoadingImage").css("display", "flex");
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
          addMorehomeworkDescriptions: index + 1
        },
        success: function(html) {
          $("#addMorehomeworkDescriptions"+index).html(html).show();
          $("#addMorehomeworkDescLoadingImage").css("display", "none");
          index++;
          $("input[name='howManyDescriptions']").val(index);
        }
      }); 
    }

    function removehomeworkDescImage(index, imgNo) {
      $.ajax({
          type: "POST",
          url: "ajax.php",
          data: {
            removehomeworkDescImage: index,
            removehomeworkDescImageNo: imgNo
          },
          success: function(html) {
              $("#removehomeworkDescImage").html(html).show();
          }
      }); 
    }

    function deletehomeworkSubmit() {
      $('#confirmDeletehomeworkModal').modal('show');
    }
  </script>
</body>

</html>