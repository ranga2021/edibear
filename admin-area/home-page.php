<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  $adminHeader = new HEADER("home-page");
  $user = new USER();
  
      if ( isset($_POST['carouselAddSubmit']) || isset($_POST['carouselUpdateSubmit']) ) {
        $carouselText1 = strip_tags((isset($_POST['inputCarouselText1']))?$_POST['inputCarouselText1']:"","<br>");
        $carouselText2 = strip_tags((isset($_POST['inputCarouselText2']))?$_POST['inputCarouselText2']:"","<br>");
        $carouselImgStatus = (int)(isset($_POST['carouselImgStatus']))?$_POST['carouselImgStatus']:0;
        $fieldsArr = array("type"=>"img","text1"=>$carouselText1,"text2"=>$carouselText2,"status"=>$carouselImgStatus);
        if ( $_FILES["inputCarouselImage"]["name"] != "" ) {
          $carouselImage =$_FILES["inputCarouselImage"]["name"];
          move_uploaded_file($_FILES["inputCarouselImage"]["tmp_name"], "../img/carousel/" . $carouselImage);
          $fieldsArr['src'] = $carouselImage;
        }
        if ( isset($_POST['carouselAddSubmit']) ) {
          $nextDisplayNo = (int)$user->MaxValue("carousel", "display_order",array("type"=>"img")) + 1;
          $fieldsArr['display_order'] = $nextDisplayNo;
          if ($user->insertTable("carousel",$fieldsArr)) {
            echo "<script>alert('Successfully Added a new Carousel Image');location.href='./home-page'</script>";
          } else {
            echo "<script>alert('Failed to add Carousel Image');location.href='./home-page'</script>";
          }
        } else if ( isset($_POST['carouselUpdateSubmit']) ) {
          $hiddenCarouselID = (int)$_POST['hiddenCarouselID'];
          if ( $user->updateTable("carousel", $fieldsArr, array("id"=>$hiddenCarouselID)) ) {
            echo "<script>alert('Successfully updated a Carousel Image');location.href='./home-page'</script>";
          } else {
            echo "<script>alert('Failed to update Carousel Image');location.href='./home-page'</script>";
          }
        }
      } else if ( isset($_POST['homeMainVideoSaveSubmit']) ) {
        $inputMainVideoID = (int)(isset($_POST['hiddenMainVideoID']))?$_POST['hiddenMainVideoID']:0;
        $inputMainVideoURL = strip_tags((isset($_POST['homeMainVideoURL']))?$_POST['homeMainVideoURL']:"");
        $inputMainVideoStatus = (int)(isset($_POST['homeMainVideoStatus']))?$_POST['homeMainVideoStatus']:0;
        $user->updateTable("carousel", array("src"=>$inputMainVideoURL, "status"=>$inputMainVideoStatus), array("id"=>$inputMainVideoID));
        echo "<script>alert('Successfully updated the Main Video');location.href='./home-page'</script>";
      }
      $homeMainVideoURL = "";
      $homeMainVideoStatus = "";
      $homeMainVideoID = 0;
      foreach ( $user->fetchAll(array("id","src","status"), array("carousel"), array("type"=>"main")) as $homeMainVideoData ) {
        $homeMainVideoID = $homeMainVideoData['id'];
        $homeMainVideoURL = $homeMainVideoData['src'];
        $homeMainVideoStatus = ($homeMainVideoData['status']=='1') ? "checked" : "";
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
  <meta property='og:title' content='Traveylo | Sri Lanka Tour Packages | Travel Agent in Sri Lanka'/>
  <meta name='description' content='“Ayubowan!” Traveylo.com provides tour packages covering the most beautiful places 
in Sri Lanka, and you can travel in luxury with your own vehicle around 
our beautiful country. So reserve your tour with us.' />
<meta name='keywords' content='Travel Agents In Sri Lanka / Sri Lanka Tourism / Sri Lanka Tourist Destinations / Places To Visit In Sri Lanka With Family / How To Travel In Sri Lanka / Sri Lanka Tours & Travels / Tour Packages In Sri Lanka / Sri Lanka Itinerary / Sri Lanka Travel Guide /Sri Lanka HotelsSri Lanka Tour Operators /Sri Lanka Budgets Tours /Small Group Tour In Sri Lanka / Sri Lanka Holiday Packages /Sri Lanka Tour Packages For Couple / Sri Lanka Tour Packages For Family /Sri Lanka Tour Packages Price / What To Do In Sri Lanka /Popular Destinations In Sri Lanka' />
</head>

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-header p-2">
              <h5>Carousel</h5>
            </div>
            <div class="card-body p-3">
              <div class="row mb-4">
                <div class="col-12 table-responsive p-0" style="overflow-x: auto;">
                  <table class="table align-items-center text-center mb-0" style="width: 100%;">
                    <thead>
                      <tr>
                        <th class="text-secondary text-xs font-weight-bolder opacity-7">IMAGE</th>
                        <th class="text-secondary text-xs font-weight-bolder opacity-7">TEXT 1</th>
                        <th class="text-secondary text-xs font-weight-bolder opacity-7">TEXT 2</th>
                        <th class="text-secondary text-xs font-weight-bolder opacity-7">DISPLAY ORDER</th>
                        <th class="text-secondary text-xs font-weight-bolder opacity-7">STATUS</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                        $responseArr = $user->fetchAll("", array("carousel"), array("type"=>"img"), "display_order"); 
                        if ( count($responseArr) > 0 ) {
                            foreach ( $responseArr as $tableRow ) {
                              $carouselID = $tableRow['id'];
                              $carouselSrc = "../img/carousel/".$tableRow['src'];
                              $carouselSrc = $carouselSrc . "?" . filemtime("$carouselSrc");
                              $carouselText1 = $tableRow['text1'];
                              $carouselText2 = $tableRow['text2'];
                              $carouselDisplayOrder = $tableRow['display_order'];
                              $carouselStatus = ($tableRow['status']=='1') ? "checked" : "";
                              echo "
                                <tr>
                                  <td class='align-middle text-center'>
                                    <img src='$carouselSrc' width='130px' class='cursor-pointer' id='carouselImg$carouselID' onclick='editCarouselDetails($carouselID)'>
                                  </td>
                                  <td class='align-middle text-center'>
                                    <span class='text-secondary text-xs font-weight-bold cursor-pointer' id='carouselText1$carouselID' onclick='editCarouselDetails($carouselID)'>$carouselText1</span>
                                  </td>
                                  <td class='align-middle text-center'>
                                    <span class='text-secondary text-xs font-weight-bold cursor-pointer' id='carouselText2$carouselID' onclick='editCarouselDetails($carouselID)'>$carouselText2</span>
                                  </td>
                                  <td class='align-middle text-center'>
                                    <input type='number' class='form-control' name='carouselDisplayOrder$carouselID' min='1' value='$carouselDisplayOrder' onchange='chngCruslImgDisOrdrAndSts($carouselID)' onkeyup='chngCruslImgDisOrdrAndSts($carouselID)'>
                                  </td>
                                  <td class='align-middle text-center'>
                                    <div class='form-check form-switch'>
                                      <input class='form-check-input' type='checkbox' name='carouselImageStatus$carouselID' value='1' $carouselStatus onchange='chngCruslImgDisOrdrAndSts($carouselID)'>
                                    </div>
                                  </td>
                                  <td class='align-middle text-center'>
                                    <button class='btn btn-danger btn-xs' type='button' onclick='deleteCarouselImage($carouselID)'>Delete</button>
                                  </td>
                                </tr>
                              ";
                          }
                        } else {
                          echo "<tr><td class='align-middle text-center' colspan='6'><span class='text-secondary text-xs font-weight-bold'>No data to show</span></td></tr>";
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 mb-4">
          <div class="card">
          <div class="card-header text-center p-1">
              <h6>Main Video</h6>
            </div>
            <div class="card-body p-1">
              <div class="row justify-content-center">
                <div class="col-md-6">
                  <div class="embed-responsive embed-responsive-16by9 text-center">
                    <iframe class="embed-responsive-item" src="<?php echo $homeMainVideoURL;?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                  </div>
                </div>
                <div class="col-md-6">
                  <form action="" method="post">
                    <div class="col-12">
                      <div class="form-group">
                        <label for="example-text-input" class="form-control-label">Video URL</label>
                        <input class="form-control" type="text" name="homeMainVideoURL" value=<?php echo $homeMainVideoURL;?> required>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" name="homeMainVideoStatus" id="homeMainVideoStatus" value="1" <?php echo $homeMainVideoStatus;?>>
                          <label class="form-check-label font-weight-bold" for="homeMainVideoStatus">Status</label>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <input type="hidden" name="hiddenMainVideoID" value=<?php echo $homeMainVideoID?>>
                          <input type="submit" class="btn btn-success btn-xs" value="Save" name="homeMainVideoSaveSubmit">
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-header text-center p-1">
              <h6 id="addEditCarousel">Add a new Carousel Image</h6>
            </div>
            <div class="card-body p-1">
              <form action="" method="post" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Text 1</label>
                      <input class="form-control" type="text" name="inputCarouselText1">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Text 2</label>
                      <input class="form-control" type="text" name="inputCarouselText2">
                    </div>
                  </div>
                </div>
                <div class="row justify-content-center">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Image</label>
                      <input type='file' class='form-control' accept='image/*' name='inputCarouselImage' id='inputCarouselImage' onchange='loadFile(event)' required>
                      <p class="text-center mt-1"><img id='outputCarouselImage' style='max-height: 200px; max-width:100%' /></p>
                    </div>
                  </div>
                </div>
                <div class="row justify-content-center">
                  <div class="col-md-3 col-6">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" name="carouselImgStatus" id="carouselImgStatus" value="1">
                      <label class="form-check-label font-weight-bold" for="carouselImgStatus">Status</label>
                    </div>
                  </div>
                  <div class="col-md-6 col-6 text-center">
                    <input type="hidden" name="hiddenCarouselID" value="">
                    <input type="submit" class="btn btn-success btn-sm" name="carouselAddSubmit" value="Add">
                    <input type="submit" class="btn btn-primary btn-sm" name="carouselUpdateSubmit" value="Update" disabled>
                    <input type="submit" class="btn btn-secondary btn-sm" onclick="location.reload()" value="Cancel">
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
  <div id="chngCruslImgDisOrdrAndSts"></div>
  <div id="deleteCarouselImage"></div>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script>
    function loadFile(event) {
        $("#outputCarouselImage").addClass("border");
        var image = document.getElementById('outputCarouselImage');
        image.src = URL.createObjectURL(event.target.files[0]);
      }
      function editCarouselDetails(carouselImgID) {
        $('html, body').animate({
          scrollTop: $("#addEditCarousel").offset().top
        });
        $("#addEditCarousel").text("Edit Carousel Image");
        $("input[name='hiddenCarouselID']").val(carouselImgID);
        $("input[name='inputCarouselText1']").val($("#carouselText1"+carouselImgID).text());
        $("input[name='inputCarouselText2']").val($("#carouselText2"+carouselImgID).text());
        $("#outputCarouselImage").attr("src",$("#carouselImg"+carouselImgID).attr("src"));
        if ( $("input[name='carouselImageStatus"+carouselImgID+"']").is(":checked") ) {
          $("input[name='carouselImgStatus']").attr("checked", true)
        } else {
          $("input[name='carouselImgStatus']").attr("checked", false)
        }
        $("input[name='inputCarouselImage']").removeAttr("required");
        $("input[name='carouselAddSubmit']").attr("disabled", true);
        $("input[name='carouselUpdateSubmit']").attr("disabled", false);
      }
      function chngCruslImgDisOrdrAndSts(carouselImgID) {
        var arr = {
          carouselImgID: carouselImgID,
          carouselDisplayOrder: $("input[name='carouselDisplayOrder"+carouselImgID+"']").val(),
          carouselStatus: ($("input[name='carouselImageStatus"+carouselImgID+"']").is(":checked")) ? 1 : 0
        };
        $.ajax({
          type: "POST",
          url: "ajax.php",
          data: {
            chngCruslImgDisOrdrAndSts: arr
          },
          success: function(html) {
            $("#chngCruslImgDisOrdrAndSts").html(html).show();
          }
        }); 
      }
      function deleteCarouselImage(carouselImgID) {
        $.ajax({
          type: "POST",
          url: "ajax.php",
          data: {
            deleteCarouselImage: carouselImgID
          },
          success: function(html) {
            $("#deleteCarouselImage").html(html).show();
          }
        }); 
      }
  </script>
</body>

</html>