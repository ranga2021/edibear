<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  require_once("../classes/edi_home_section_images.php");
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
      } else if ( isset($_POST['homeExploreBgSubmit']) ) {
        $err = EdiHomeSectionImages::saveUploaded($user, EdiHomeSectionImages::TYPE_EXPLORE, "homeExploreBgFile");
        if ($err !== null) {
          echo "<script>alert(" . json_encode($err) . ");location.href='./home-page'</script>";
        } else {
          echo "<script>alert('Explore area background image updated.');location.href='./home-page'</script>";
        }
      } else if ( isset($_POST['homeTestimonialBgSubmit']) ) {
        $err = EdiHomeSectionImages::saveUploaded($user, EdiHomeSectionImages::TYPE_TESTIMONIAL, "homeTestimonialBgFile");
        if ($err !== null) {
          echo "<script>alert(" . json_encode($err) . ");location.href='./home-page'</script>";
        } else {
          echo "<script>alert('Testimonial area background image updated.');location.href='./home-page'</script>";
        }
      } else if ( isset($_POST['homeFooterBgSubmit']) ) {
        $err = EdiHomeSectionImages::saveUploaded($user, EdiHomeSectionImages::TYPE_FOOTER, "homeFooterBgFile");
        if ($err !== null) {
          echo "<script>alert(" . json_encode($err) . ");location.href='./home-page'</script>";
        } else {
          echo "<script>alert('Footer area image updated.');location.href='./home-page'</script>";
        }
      }
      $homeMainVideoURL = "";
      $homeMainVideoStatus = "";
      $homeMainVideoID = 0;
      foreach ( $user->fetchAll(array("id","src","status"), array("carousel"), array("type"=>"main")) as $homeMainVideoData ) {
        $homeMainVideoID = $homeMainVideoData['id'];
        $homeMainVideoURL = $homeMainVideoData['src'];
        $homeMainVideoStatus = ($homeMainVideoData['status']=='1') ? "checked" : "";
      }

      $ediAdminRel = function ($webPath) {
        if (strpos($webPath, "./") === 0) {
          return ".." . substr($webPath, 1);
        }
        return $webPath;
      };
      $homeExplorePreview = $ediAdminRel(EdiHomeSectionImages::assetUrl($user, EdiHomeSectionImages::TYPE_EXPLORE));
      $homeTestimonialPreview = $ediAdminRel(EdiHomeSectionImages::assetUrl($user, EdiHomeSectionImages::TYPE_TESTIMONIAL));
      $homeFooterPreview = $ediAdminRel(EdiHomeSectionImages::assetUrl($user, EdiHomeSectionImages::TYPE_FOOTER));
      $homeExplorePreview .= (strpos($homeExplorePreview, "?") === false ? "?" : "&") . "v=" . (string) time();
      $homeTestimonialPreview .= (strpos($homeTestimonialPreview, "?") === false ? "?" : "&") . "v=" . (string) time();
      $homeFooterPreview .= (strpos($homeFooterPreview, "?") === false ? "?" : "&") . "v=" . (string) time();
    
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
            <div class="card-header p-2">
              <h5>Home section images</h5>
              <p class="text-xs text-secondary mb-0">Update the Explorer search strip background, Testimonials strip background, and site footer illustration. Run <code>sql/migration_home_section_backgrounds.sql</code> once if uploads fail (column <code>carousel.type</code> must allow longer values).</p>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <div class="col-lg-4 col-md-12 mb-4">
                  <h6 class="mb-3">Explore area background</h6>
                  <p class="text-xs text-muted mb-2">Behind the language / grade / category search on the home page.</p>
                  <div class="text-center mb-3 p-2 border border-radius-lg" style="min-height:120px;background:#f6f6f6;">
                    <img src="<?php echo htmlspecialchars($homeExplorePreview, ENT_QUOTES, 'UTF-8'); ?>" alt="Explore preview" style="max-width:100%;max-height:140px;object-fit:contain;">
                  </div>
                  <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                      <label class="form-control-label">Image</label>
                      <input type="file" class="form-control" accept="image/*" name="homeExploreBgFile" required>
                    </div>
                    <input type="submit" class="btn btn-success btn-sm" name="homeExploreBgSubmit" value="Update image">
                  </form>
                </div>
                <div class="col-lg-4 col-md-12 mb-4">
                  <h6 class="mb-3">Testimonial area background</h6>
                  <p class="text-xs text-muted mb-2">Behind the home page testimonial cards.</p>
                  <div class="text-center mb-3 p-2 border border-radius-lg" style="min-height:120px;background:#f6f6f6;">
                    <img src="<?php echo htmlspecialchars($homeTestimonialPreview, ENT_QUOTES, 'UTF-8'); ?>" alt="Testimonial preview" style="max-width:100%;max-height:140px;object-fit:contain;">
                  </div>
                  <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                      <label class="form-control-label">Image</label>
                      <input type="file" class="form-control" accept="image/*" name="homeTestimonialBgFile" required>
                    </div>
                    <input type="submit" class="btn btn-success btn-sm" name="homeTestimonialBgSubmit" value="Update image">
                  </form>
                </div>
                <div class="col-lg-4 col-md-12 mb-4">
                  <h6 class="mb-3">Footer area image</h6>
                  <p class="text-xs text-muted mb-2">Illustration along the bottom of the footer site-wide.</p>
                  <div class="text-center mb-3 p-2 border border-radius-lg" style="min-height:120px;background:#f6f6f6;">
                    <img src="<?php echo htmlspecialchars($homeFooterPreview, ENT_QUOTES, 'UTF-8'); ?>" alt="Footer preview" style="max-width:100%;max-height:140px;object-fit:contain;">
                  </div>
                  <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                      <label class="form-control-label">Image</label>
                      <input type="file" class="form-control" accept="image/*" name="homeFooterBgFile" required>
                    </div>
                    <input type="submit" class="btn btn-success btn-sm" name="homeFooterBgSubmit" value="Update image">
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