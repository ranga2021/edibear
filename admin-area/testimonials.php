<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  $adminHeader = new HEADER("testimonials");
  $user = new USER();

      if ( isset($_POST['adminAddTestimonialSubmit']) ) {
        $reviewerName = trim((string)($_POST['reviewerName'] ?? ''));
        $reviewerCountry = trim((string)($_POST['reviewerCountry'] ?? ''));
        $oneWord = trim((string)($_POST['oneWord'] ?? ''));
        $review = trim((string)($_POST['reviewBody'] ?? ''));
        $ratings = (int)($_POST['ratings'] ?? 5);
        $publishNow = isset($_POST['publishNow']) && $_POST['publishNow'] === '1';

        $ratings = max(1, min(5, $ratings));
        $reviewerName = substr(strip_tags($reviewerName), 0, 100);
        $reviewerCountry = substr(strip_tags($reviewerCountry), 0, 20);
        $oneWord = substr(strip_tags($oneWord), 0, 50);
        $review = substr(strip_tags($review), 0, 500);

        if ($reviewerName === '' || $reviewerCountry === '' || $oneWord === '' || $review === '') {
          echo "<script>alert('Please fill in reviewer name, country, headline, and review.');location.href='./testimonials';</script>";
        } else {
          $uniq = bin2hex(random_bytes(4));
          $username = 'u' . substr($uniq, 0, 19);
          $email = 't' . substr($uniq, 0, 6) . '@g.t';
          $hash = 'pass' . password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
          $status = $publishNow ? 1 : 0;

          $user->insertTable('tourists', array(
            'username' => $username,
            'name' => $reviewerName,
            'email' => $email,
            'country' => $reviewerCountry,
            'password' => $hash,
            'profile_pic' => 'default.jpg',
            'status' => 1,
            'delete_status' => 0,
            'timestamp' => date('Y-m-d H:i:s'),
          ));

          $newTouristId = (int) $user->fetchAll(array('id'), array('tourists'), array('username' => $username))[0]['id'];

          $testimonialId = (int) $user->insertTable('testimonials', array(
            'user_id' => $newTouristId,
            'name' => $reviewerName,
            'ratings' => $ratings,
            'one_word' => $oneWord,
            'review' => $review,
            'status' => $status,
          ), true);

          if (!empty($_FILES['adminTestimonialImage']['name']) && (int) $_FILES['adminTestimonialImage']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = array('jpg', 'jpeg', 'png', 'webp');
            $fileExt = strtolower(pathinfo($_FILES['adminTestimonialImage']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, $allowedTypes, true)) {
              $imgDir = __DIR__ . '/../img/testimonials/';
              if (!is_dir($imgDir)) {
                mkdir($imgDir, 0777, true);
              }
              $newFileName = substr(bin2hex(random_bytes(6)), 0, 12) . '.' . $fileExt;
              $dest = $imgDir . $newFileName;
              if (strlen($newFileName) <= 20 && move_uploaded_file($_FILES['adminTestimonialImage']['tmp_name'], $dest)) {
                $user->insertTable('testimonials_images', array(
                  'testimonial_id' => $testimonialId,
                  'image' => $newFileName,
                ));
              }
            }
          }

          echo "<script>alert('Testimonial added successfully');location.href='./testimonials';</script>";
        }
      }

      if ( isset($_POST['changeTestimonialStatusSubmit']) ) {
        $testimonialID = (int)$_POST['testimonialID'];
        $testimonialStatus = $_POST['testimonialStatus'];
        if ( $testimonialStatus != "delete" ) {
          $testimonialStatus = (int)$_POST['testimonialStatus'];
          $user->updateTable("testimonials", array("status"=>$testimonialStatus), array("id"=>$testimonialID));
          
        } else {
          foreach ( $user->fetchAll(array("image"), array("testimonials_images"), array("testimonial_id"=>$testimonialID)) as $row ) {
            unlink("../img/testimonials/".$row['image']);
          }
          $user->deleteTableRow("testimonials_images", array("testimonial_id"=>$testimonialID));
          $user->deleteTableRow("testimonials", array("id"=>$testimonialID));
          echo "<script>alert('Successfully deleted the Testimonial'); location.href='./testimonials';</script>";
        }
      }

?>
<script>
    const adminSession = localStorage.getItem('admin_session');
    const sessionTime = localStorage.getItem('session_time');
    const currentTime = Math.floor(Date.now() / 1000);
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
  
  <style>
    /* CSS for the sleek, animated toggle switches */
    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 26px;
    }

    .switch input { 
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #E2E8F0; /* Off-state from image */
      -webkit-transition: .4s;
      transition: .4s;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 20px;
      width: 20px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      -webkit-transition: .4s;
      transition: .4s;
    }

    input:checked + .slider {
      background-color: #6D74FD; /* On-state from image */
    }

    input:focus + .slider {
      box-shadow: 0 0 1px #6D74FD;
    }

    input:checked + .slider:before {
      -webkit-transform: translateX(24px);
      -ms-transform: translateX(24px);
      transform: translateX(24px);
    }

    /* Rounded sliders */
    .slider.round {
      border-radius: 34px;
    }

    .slider.round:before {
      border-radius: 50%;
    }
  </style>

</head>

<body class="g-sidenav-show    bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card mb-4">
            <div class="card-header pb-0">
              <h4>Add testimonial</h4>
              <p class="text-sm text-muted mb-0">Create a testimonial on behalf of a reviewer (shown on the site when status is approved).</p>
            </div>
            <div class="card-body p-3">
              <form method="post" action="" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Reviewer name</label>
                  <input type="text" name="reviewerName" class="form-control" maxlength="100" required placeholder="e.g. Jane Doe">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Country</label>
                  <input type="text" name="reviewerCountry" class="form-control" maxlength="20" required placeholder="Max 20 characters">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Rating</label>
                  <select name="ratings" class="form-control">
                    <?php for ($r = 5; $r >= 1; $r--) { echo "<option value=\"$r\">$r stars</option>"; } ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Headline (short)</label>
                  <input type="text" name="oneWord" class="form-control" maxlength="50" required placeholder="e.g. Great experience">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Photo (optional)</label>
                  <input type="file" name="adminTestimonialImage" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="col-12">
                  <label class="form-label">Review</label>
                  <textarea name="reviewBody" class="form-control" rows="4" maxlength="500" required placeholder="Full testimonial text (max 500 characters)"></textarea>
                </div>
                <div class="col-12 d-flex align-items-center flex-wrap gap-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="publishNow" value="1" id="publishNow" checked>
                    <label class="form-check-label" for="publishNow">Publish as approved</label>
                  </div>
                  <button type="submit" name="adminAddTestimonialSubmit" value="1" class="btn btn-primary mb-0">Save testimonial</button>
                </div>
              </form>
            </div>
          </div>
          <div class="card">
            <div class="card-header pb-0">
              <h4>Review Testimonials</h4>
            </div>
            <div class="card-body p-3">
              <div class="table-responsive">
                <table class="table table-bordered" id="reviewTestimonialsTable" data-order='[[ 5, "desc" ]]' width="100%" cellspacing="0">
                  <thead>
                    <th>Name</th>
                    <th>Country</th>
                    <th>Ratings</th>
                    <th>Review</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                    <th>ACTION</th>
                  </thead>
                  <tfoot>
                    <th>Name</th>
                    <th>Country</th>
                    <th>Ratings</th>
                    <th>Review</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                    <th>ACTION</th>
                  </tfoot>
                  <tbody>
                    <?php
                      foreach ( $user->fetchAll(array("id", "user_id", "ratings", "one_word", "status","timestamp"), array("testimonials"), "") as $rowFetchTestimonials ) {
                        $testimonialID = $rowFetchTestimonials['id'];
                        $testimonialRatings = $rowFetchTestimonials['ratings'];
                        $testimonialOneWord = $rowFetchTestimonials['one_word'];
                        $rawStatus = $rowFetchTestimonials['status'];
                        $testimonialStatusText = ($rawStatus==1) ? "Approved" : (($rawStatus==-1) ? "Rejected" : "Pending");
                        $testimonialTimestamp = $rowFetchTestimonials['timestamp'];
                        
                        $touristArr = $user->fetchAll(array("name","country"), array("tourists"), array("id"=>$rowFetchTestimonials['user_id']))[0];
                        $touristName = $touristArr['name'];
                        $touristCountry = $touristArr['country'];

                        // Determine the next status for the toggle and its initial state
                        $isApproved = ($rawStatus == 1);
                        $targetStatusForToggle = $isApproved ? -1 : 1;
                        $toggleCheckedState = $isApproved ? "checked" : "";

                        echo "
                          <tr>
                            <td class='cursor-pointer' onclick='showTestimonial($testimonialID)'>$touristName</td>
                            <td>$touristCountry</td>
                            <td>";
                              for ( $i=1; $i<=5; $i++ ) {
                                $starColor = ($i<=$testimonialRatings) ? "text-warning" : "";
                                echo "<span class='fa fa-star $starColor'></span>";
                              }
                        echo "
                            </td>
                            <td>$testimonialOneWord</td>
                            <td>$testimonialStatusText</td>
                            <td>$testimonialTimestamp</td>
                            <td>
                              <div class='d-flex align-items-center'>
                                <form method='POST' action='' id='toggleForm_$testimonialID' class='mr-2'>
                                  <input type='hidden' name='testimonialID' value='$testimonialID'>
                                  <input type='hidden' name='testimonialStatus' value='$targetStatusForToggle'>
                                  <label class='switch mb-0'>
                                    <input type='checkbox' $toggleCheckedState onchange='document.getElementById(\"toggleForm_$testimonialID\").submit()'>
                                    <span class='slider round'></span>
                                  </label>
                                  <input type='hidden' name='changeTestimonialStatusSubmit' value='1'>
                                </form>
                                
                              </div>
                            </td>
                          </tr>
                        ";
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="showTestimonial"></div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script src="./assets/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
  <script src="./assets/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
  <script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    $(document).ready(function(){
        $('#reviewTestimonialsTable').DataTable();
    });
    function showTestimonial(testimonialID) {
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
          showTestimonial: testimonialID
        },
        success: function(html) {
          $("#showTestimonial").html(html).show();
        }
      }); 
      $('html, body').animate({
        scrollTop: $("#showTestimonial").offset().top
      });
    }
  </script>
</body>

</html>