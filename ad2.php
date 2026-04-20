<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    
    $userHeader = new HEADER("ad2");
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

    if ( isset($_GET['tag']) ) {
        $searchTag = strip_tags($_GET['tag']);
        $pagingUrlParm = "&tag=$searchTag";
        $searchTagLike = "tag LIKE '%$searchTag%'";
    } else {
        $searchTag = "";
        $searchTagLike = "tag LIKE '%%'";
        $pagingUrlParm = "";
    }
    $totalad2Pages = ceil( count($user->fetchAll(array("id"), array("ad2_details"), array("status"=>1), "", $searchTagLike)) / 6);
    if ( isset($_GET['page']) ) {
        $ad2PageNo = (int)$_GET['page'];
        if ( $totalad2Pages < $ad2PageNo ) {
            $user->redirect("./ad2?page=$totalad2Pages");
        }
    } else {
        $ad2PageNo = 1;
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
    <?php echo $userHeader->printUserHeader(); ?>
    <script src='https://www.hCaptcha.com/1/api.js' async defer></script> 
</head>

<body>
    <?php
        echo $userHeader->printUserNav();            //Topbar
        
    ?>

    <!-- ad2 Start -->
    <div class="container-fluid py-3" style="margin-top: 70px !important;">
        <div class="container pt-3">
            <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ad2</li>
                </ol>
            </nav>
            <h4 class="text-warning mt-2">Fun Activities</h4>

            <?php 
                //echo $widgets->displayGetQuoate($user);
                $tagsArr = array();
                foreach ( $user->fetchAll(array("tag"), array("ad2_details"), array("status"=>1), "", "", "","","", ) as $row ) {
                    foreach ( explode("/", $row["tag"]) as $value ) {
                        if ( array_search($value, $tagsArr) === false ) array_push($tagsArr, $value);
                    }
                }
            ?>
            <!-- Tag Cloud -->
            <div class="mb-5 mt-3">
                <h5 class="text-warning mb-1" >Tags <span style="letter-spacing:-2px;">――――――――――――――――――――――――――――――――――――――――――――――――――――</span>  </h5> 
                <div class="d-flex flex-wrap m-n1">
                    <?php
                        foreach ( $tagsArr as $tag ) {
                            echo "<a href='./ad2?tag=$tag' class='btn btn-light m-1'>$tag</a>";
                            
                        }
                    ?>
                </div>
            </div>



            <div class="row mt-4">
                <div class="col-lg-9">
                    <div class="row pb-3">
                        <?php
                            if ( $ad2PageNo > 1 ) {
                                $limit = ( $ad2PageNo - 1 ) * 6;
                                $other = "id<" . $user->fetchAll(array("id"), array("ad2_details"), array("status"=>"1"), "id DESC LIMIT $limit", $searchTagLike)[$limit-1]['id'];
                            } else {
                                $other = "";
                            }
                            if ( $other!="" ) {
                                $other = "$other AND $searchTagLike";
                            } else {
                                $other = "$searchTagLike";
                            }
                            foreach ( $user->fetchAll(array("id","tag","title","image", "description","timestamp"), array("ad2_details"), array("status"=>"1"), "id DESC LIMIT 6", $other) as $row ) {
                                echo $widgets->displayad2Brief($row, "col-md-6", 200, false);
                            }
                        ?>
                        <div class="col-12">
                            <?php
                                $previousBtn = $nextBtn = "";
                                if ( $ad2PageNo == 1 ) {
                                    $previousBtn = "disabled";
                                } 
                                if ( $ad2PageNo == $totalad2Pages ) {
                                    $nextBtn = "disabled";
                                } 
                                echo "
                                    <nav aria-label='Page navigation example'>
                                        <ul class='pagination justify-content-center'>
                                            <li class='page-item mx-1 $previousBtn' onclick='prevNextBtn(0, $ad2PageNo, $totalad2Pages)'>
                                                <a class='page-link' href='#0' aria-label='Previous'>
                                                    <span aria-hidden='true'>&laquo;</span>
                                                </a>
                                            </li>";
                                            for ( $i=1; $i<=$totalad2Pages; $i++ ) {
                                                if ( $i == $ad2PageNo ) {
                                                    echo "<li class='page-item active mx-1'><a class='page-link' href='#'>$i</a></li>";
                                                } else {
                                                    echo "<li class='page-item mx-1'><a class='page-link' href='./ad2?page=$i$pagingUrlParm'>$i</a></li>";
                                                }
                                            }
                                echo "
                                            <li class='page-item mx-1 $nextBtn' onclick='prevNextBtn(1, $ad2PageNo, $totalad2Pages)'>
                                                <a class='page-link' href='#0' aria-label='Next'>
                                                    <span aria-hidden='true'>&raquo;</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                ";
                            ?>
                            
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mt-5 mt-lg-0">
                    
                    <?php 
                        echo $widgets->displayGetQuoate($user);
                        /*
                        $tagsArr = array();
                        foreach ( $user->fetchAll(array("tag"), array("ad2_details"), array("status"=>1), "", "") as $row ) {
                            foreach ( explode("/", $row["tag"]) as $value ) {
                                if ( array_search($value, $tagsArr) === false ) array_push($tagsArr, $value);
                            }
                        }*/
                    ?>
                    <!-- Tag Cloud -->
                    <!----
                    <div class="my-5">
                        <h5 class="text-warning mb-1">Tags</h5>
                        <div class="d-flex flex-wrap m-n1">
                            <?php
                               /* foreach ( $tagsArr as $tag ) {
                                    echo "<a href='./ad2?tag=$tag' class='btn btn-light m-1'>$tag</a>";
                                }*/
                            ?>
                        </div>
                    </div>
                    ------->


                </div>
            </div>
        </div>
    </div>
    <!-- ad2 End -->

    <!---- ad space ------->
    
<div style="display:flex; justify-content:space-around;" class="mt-5 mb-5">
    <div style="background-color: #a1a1a1; height: 420px; width: 70%; display:flex; align-items:center; justify-content:space-around;">
        <h1 class="text-center"> ADD SPACE </h1>
    </div>
</div>

    <!-- Footer Start -->
    <?php echo $userHeader->printUserFooter(); ?>
    <!-- Footer End -->
    <script>
        var buttonName = ':input[name="quoteSubmit"]';
        $(buttonName).prop('disabled', true);
        function correctCaptcha() {
            $("form").each(function() {
                $(this).find(buttonName).prop('disabled', false);
            });
        }

        function prevNextBtn(btn, ad2Page, totalPages) {
            let searchTagParam = new URLSearchParams(window.location.search);
            if ( searchTagParam.has('tag') && searchTagParam.get('tag') !="" ) {
                searchTag = "&tag="+searchTagParam.get('tag');
            }
            if ( btn == 0 ) {
                if ( ad2Page > 1 ) {
                    location.href = "./ad2?page=" + (ad2Page-1) + searchTag;
                }
            } else if ( btn == 1 ) {
                if ( totalPages > ad2Page ) {
                    location.href = "./ad2?page=" + (ad2Page+1) + searchTag;
                }
            }
        }
    </script>
</body>

</html>