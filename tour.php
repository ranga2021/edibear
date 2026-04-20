<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    $userHeader = new HEADER();
    $user = new USER();
    $widgets = new WIDGETS();

    if ( isset($_POST['quoteSubmit']) ) {
        $quoteName = isset($_POST['quoteName']) ? $_POST['quoteName'] : "";
        $quoteEmail = isset($_POST['quoteEmail']) ? $_POST['quoteEmail'] : "";
        $quoteMobile = isset($_POST['quoteMobile']) ? $_POST['quoteMobile'] : "";
        $quoteCountry = isset($_POST['quoteCountry']) ? $_POST['quoteCountry'] : "";
        $quoteArrivalDate = isset($_POST['quoteArrivalDate']) ? $_POST['quoteArrivalDate'] : "";
        $quoteDepartureDate = isset($_POST['quoteDepartureDate']) ? $_POST['quoteDepartureDate'] : "";
        $quoteAdultsChildren = isset($_POST['quoteAdultsChildren']) ? $_POST['quoteAdultsChildren'] : "";
        $quoteDescription = isset($_POST['quoteDescription']) ? $_POST['quoteDescription'] : "";
        $quoteTourTitle = isset($_POST['quoteTourTitle']) ? $_POST['quoteTourTitle'] : "";
        $to = "hellotraveylo@gmail.com";
        $toName = "";
        $ccRecipient = "hello@traveylo.com";
        $subject = "Traveylo - GET A QUOATE";
        $message = "
        <h1>Traveylo - GET A QUOATE</h1>
        <br>
        <table border='0'>
            <tr><td>Tour Title</td><td>: $quoteTourTitle</td></tr>
            <tr><td>Name</td><td>: $quoteName</td></tr>
            <tr><td>Email</td><td>: $quoteEmail</td></tr>
            <tr><td>Mobile No.</td><td>: $quoteMobile</td></tr>
            <tr><td>Country</td><td>: $quoteCountry</td></tr>
            <tr><td>Arrival Date</td><td>: $quoteArrivalDate</td></tr>
            <tr><td>Deparure Date</td><td>: $quoteDepartureDate</td></tr>
            <tr><td>Adults/Children</td><td>: $quoteAdultsChildren</td></tr>
            <tr><td>Description</td><td>: $quoteDescription</td></tr>
        </table>
        ";
        require 'mail_inc.php';
        $from = 'system@traveylo.com';
        $fromName = "Traveylo";
        sendMail($from, $fromName, $to, $toName, $ccRecipient, $subject, $message);
        echo "<script>alert('Successfully sent the message.')</script>";
    }
    if ( isset($_GET['id']) && $_GET['id'] > 0 ) {
        $tourID = (int)$_GET['id'];
        if ( $user->IsExist("tour_details", "id", $tourID) ) {
            $tourDetailsArr = $user->fetchAll(
                array("no", "title", "image_name", "duration", "tour_group", "vehicle_type", "guide", "pickup_drop", "hotel_type", "description", "arrival_departure_location", "depature_time", "meal_plan", "bed_room", "services_included", "services_excluded", "map"), 
                array("tour_details"), 
                array("id"=>$tourID)
            )[0];
            $tourNo = $tourDetailsArr['no'];
            $tourTitle = $tourDetailsArr['title'];
            $tourDuration = $tourDetailsArr['duration'];
            $tourGroup = $tourDetailsArr['tour_group'];
            $tourVehicleType = $tourDetailsArr['vehicle_type'];
            $tourGuide = $tourDetailsArr['guide'];
            $tourPickupDrop = $tourDetailsArr['pickup_drop'];
            $tourHotelType = $tourDetailsArr['hotel_type'];
            $tourDescription = $tourDetailsArr['description'];
            $tourArrivalDepartureLocation = $tourDetailsArr['arrival_departure_location'];
            $tourDepatureTime = $tourDetailsArr['depature_time'];
            $tourMealPlan = $tourDetailsArr['meal_plan'];
            $tourBedroom = $tourDetailsArr['bed_room'];
            $tourMapUrl = $tourDetailsArr['map'];
            $tourServicesIncluded = explode("-", $tourDetailsArr['services_included']);
            $tourServicesExcluded = explode("-", $tourDetailsArr['services_excluded']);
            $tourMainImage = $widgets->createCachelessImage("./img/tours/".$tourDetailsArr['image_name']);
        } else {
            $user->redirect("./");
        }
    } else {
        $user->redirect("./");
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta property='og:title' content='Traveylo | Sri Lanka Tour Packages | Travel Agent in Sri Lanka'/>
    <meta name='description' content='“Ayubowan!” Traveylo.com provides tour packages covering the most beautiful places 
in Sri Lanka, and you can travel in luxury with your own vehicle around 
our beautiful country. So reserve your tour with us.' />
    <meta name='keywords' content='Travel Agents In Sri Lanka / Sri Lanka Tourism / Sri Lanka Tourist Destinations / Places To Visit In Sri Lanka With Family / How To Travel In Sri Lanka / Sri Lanka Tours & Travels / Tour Packages In Sri Lanka / Sri Lanka Itinerary / Sri Lanka Travel Guide /Sri Lanka HotelsSri Lanka Tour Operators /Sri Lanka Budgets Tours /Small Group Tour In Sri Lanka / Sri Lanka Holiday Packages /Sri Lanka Tour Packages For Couple / Sri Lanka Tour Packages For Family /Sri Lanka Tour Packages Price / What To Do In Sri Lanka /Popular Destinations In Sri Lanka' />
    <link href="./lib/bootstrap.min.css" rel="stylesheet">
    <?php echo $userHeader->printUserHeader($tourNo) ?>
    <link rel="stylesheet" href="./css/image-slider.css">
    <script src='https://www.hCaptcha.com/1/api.js' async defer></script> 
    <style>
        .footerEmailCustom{
            padding: 0 20%;
        }
        @media (max-width: 1024px) {
            .footerEmailCustom{
                padding: 0 10%;
            }
        }
        @media (max-width: 776px) {
            .footerEmailCustom{
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <?php
        echo $userHeader->printUserNav();          //Topbar
    ?>

    <div class="container-fluid py-3 p" style="margin-top: 70px !important;">
        <div class="container py-3">
            <i class="fa fa-home pt-1 pr-2 text-primary"></i><a href="./">Home</a><i class="fa fa-angle-right pt-1 px-2 text-primary"></i><?php echo $tourNo; ?>
            <h4 class="text-warning mt-2 mb-5"><?php echo $tourTitle; ?></h4>
            <div class="row">
                <div class="col-lg-9 text-center">
                    <?php echo $widgets->displayTourCarousel($tourMainImage, $user->fetchAll(array("image_name"), array("tour_sub_images"), array("tour_id"=>$tourID), "image_name")); ?>
                    <div class="row">
                        <?php
                            echo $widgets->displayTourBlock01("fa fa-calendar-alt", $tourDuration, "px-1");
                            echo $widgets->displayTourBlock01("fa fa-users", $tourGroup, "px-1");
                            echo $widgets->displayTourBlock01("fa fa-car", $tourVehicleType, "px-1");
                            echo $widgets->displayTourBlock01("fa fa-user", $tourGuide, "px-1");
                            echo $widgets->displayTourBlock01("fa fa-plane", $tourPickupDrop, "px-1");
                            echo $widgets->displayTourBlock01("fa fa-bed", $tourHotelType, "px-1");
                        ?>
                    </div>
                    <div class="row mb-2 px-1">
                        <div class="col-12 border text-left py-1">
                            <span class="font-weight-bold text-primary">Description</span>
                            <span class="row col mt-1 text-justify"><?php echo $tourDescription; ?></span>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                            echo $widgets->displayTourBlock02("Arrival & Departure Location", $tourArrivalDepartureLocation, "px-1");
                            echo $widgets->displayTourBlock02("Departure Time", $tourDepatureTime, "px-1");
                            echo $widgets->displayTourBlock02("Meal Plan", $tourMealPlan, "px-1");
                            echo $widgets->displayTourBlock02("Bedroom", $tourBedroom, "px-1");
                        ?>
                    </div>
                    <div class="row mb-2">
                        <?php
                            echo $widgets->displayTourServicesBlock("Services Included in the Price", $tourServicesIncluded, "fa fa-check fa-sm", "px-1");
                            echo $widgets->displayTourServicesBlock("Services Excluded in the Price", $tourServicesExcluded, "fa fa-times", "px-1");
                        ?>
                    </div>
                    <div class="row mb-4">
                        <?php
                            echo $widgets->displayTourDayAccordion($user, $tourID);
                        ?>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 text-left">
                            <span class="font-weight-bold text-primary">Tour Map</span><br>
                            <iframe src=<?php echo $tourMapUrl ?> width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <?php echo $widgets->displayGetQuoate($user); ?>
                </div>
            </div>
            <div class="row mt-5 pt-5">
                <div class="text-center">
                    <h1 class="text-primary">RELATED TOUR PACKAGES</h1>
                </div>
                <p class="text-center px-lg-5">
                    Travel has helped us to undestand the meaning of life and it has helped us become better people. 
                    Each time we travel, we see the world with new eyes.
                </p>
                <div class="row justify-content-center mt-4">
                    <?php
                    $totalToursCount = $user->CountRows("tour_details", array("status"=>"1"));
                    if ( $totalToursCount > 0 ) {
                        $tourIdArr = array();
                        foreach( $user->fetchAll(array("id"), array("tour_details"), array("status"=>"1")) as $row ) {
                            array_push($tourIdArr, $row['id']);
                        }
                        $tourIdIndex = array_search($tourID, $tourIdArr);
                        for ( $i=0; $i<3; $i++ ) {
                            $tourIdIndex++;
                            if ( $tourIdIndex >= $totalToursCount ) {
                                $tourIdIndex = 0;
                            }
                            foreach( $user->fetchAll(array("id", "no", "title", "type", "image_name", "description", "duration"), array("tour_details"), array("id"=>$tourIdArr[$tourIdIndex])) as $value ) {
                                echo $widgets->displayToursBriefInHome($value);
                            }
                        }
                    }
                    ?>
            </div>
            </div>
        </div>
    </div>

    <!-- Footer Start -->
    <?php echo $userHeader->printUserFooter(); ?>
    <!-- Footer End -->
    <script src="./lib/bootstrap.bundle(v5).min.js"></script>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        let thumbnails = document.getElementsByClassName('thumbnail')
        let activeImages = document.getElementsByClassName('active')
        for (var i=0; i < thumbnails.length; i++){
            thumbnails[i].addEventListener('mouseover', function(){
                if (activeImages.length > 0){
                    activeImages[0].classList.remove('active')
                }
                this.classList.add('active')
                document.getElementById('featured').src = this.src
            })
        }

        var buttonName = ':input[name="quoteSubmit"]';
        $(buttonName).prop('disabled', true);
        function correctCaptcha() {
            $("form").each(function() {
                $(this).find(buttonName).prop('disabled', false);
            });
        }
    </script>
</body>

</html>