<?php
  session_start();
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  $adminHeader = new HEADER("testimonials");
  $user = new USER();

  
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