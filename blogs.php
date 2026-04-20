<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    
    $userHeader = new HEADER("blogs");
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
    $totalBlogPages = ceil( count($user->fetchAll(array("id"), array("blog_details"), array("status"=>1), "", $searchTagLike)) / 6);
    if ( isset($_GET['page']) ) {
        $blogPageNo = (int)$_GET['page'];
        if ( $totalBlogPages < $blogPageNo ) {
            $user->redirect("./blogs?page=$totalBlogPages");
        }
    } else {
        $blogPageNo = 1;
    }
?>
<!DOCTYPE html>
<html lang="en">


<head>
   
    <?php echo $userHeader->printUserHeader(); ?>
    <script src='https://www.hCaptcha.com/1/api.js' async defer></script> 
    <link rel="stylesheet" href="css/product_details.css">
</head>

<body>
    <?php
        // Match navigation with index.php
        echo $userHeader->printUserNav();       // Navbar
    ?>
    <div class="page-header-bg"></div>

    <!-- Blog Start -->
    <div class="container-fluid py-3 page-header-content" style="margin-top: 0px !important;">
        <div class="container pt-3">
            <i class="fa fa-home pt-1 pr-2 text-primary1"></i><a href="./">Home</a><i class="fa fa-angle-right pt-1 px-2 text-primary1"></i>The Hidden Den
             <!-- Title + Line -->
        <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
            <h2 style="font-weight:700; margin:0;">EXCITING THINGS</h2>
            <div style="flex:1; height:2px; background:#f4b400;"></div>
        </div>
            
            <h6>Hand crafts, Letter practicing, Drawings</h6>

             <?php 
            //     //echo $widgets->displayGetQuoate($user);
            //     $tagsArr = array();
            //     foreach ( $user->fetchAll(array("tag"), array("blog_details"), array("status"=>1), "", "", "","","", ) as $row ) {
            //         foreach ( explode("/", $row["tag"]) as $value ) {
            //             if ( array_search($value, $tagsArr) === false ) array_push($tagsArr, $value);
            //         }
            //     }
            // ?>
            <!-- Tag Cloud -->
            <!--<div class="mb-5 mt-3">-->
            <!--    <h5 class="text-warning mb-1" >Tags <span style="letter-spacing:-2px;">――――――――――――――――――――――――――――――――――――――――――――――――――――</span>  </h5> <br>-->
            <!--    <div class="d-flex flex-wrap m-n1">-->
                   
            <!--    </div>-->
            <!--</div>-->



            <div class="row mt-4">
                <div class="col-lg-9">
                    <div class="row pb-3">
                        <?php
                            if ( $blogPageNo > 1 ) {
                                $limit = ( $blogPageNo - 1 ) * 6;
                                $other = "id<" . $user->fetchAll(array("id"), array("blog_details"), array("status"=>"1"), "id DESC LIMIT $limit", $searchTagLike)[$limit-1]['id'];
                            } else {
                                $other = "";
                            }
                            if ( $other!="" ) {
                                $other = "$other AND $searchTagLike";
                            } else {
                                $other = "$searchTagLike";
                            }
                            foreach ( $user->fetchAll(array("id","tag","title","image", "description","timestamp"), array("blog_details"), array("status"=>"1"), "id DESC LIMIT 6", $other) as $row ) {
                                echo $widgets->displayBlogBrief($row, "col-md-6", 200, false);
                            }
                        ?>
                        <div class="col-12">
                            <?php
                                $previousBtn = $nextBtn = "";
                                if ( $blogPageNo == 1 ) {
                                    $previousBtn = "disabled";
                                } 
                                if ( $blogPageNo == $totalBlogPages ) {
                                    $nextBtn = "disabled";
                                } 
                                echo "
                                    <nav aria-label='Page navigation example'>
                                        <ul class='pagination justify-content-center'>
                                            <li class='page-item mx-1 $previousBtn' onclick='prevNextBtn(0, $blogPageNo, $totalBlogPages)'>
                                                <a class='page-link' href='#0' aria-label='Previous'>
                                                    <span aria-hidden='true'>&laquo;</span>
                                                </a>
                                            </li>";
                                            for ( $i=1; $i<=$totalBlogPages; $i++ ) {
                                                if ( $i == $blogPageNo ) {
                                                    echo "<li class='page-item active mx-1'><a class='page-link' href='#'>$i</a></li>";
                                                } else {
                                                    echo "<li class='page-item mx-1'><a class='page-link' href='./blogs?page=$i$pagingUrlParm'>$i</a></li>";
                                                }
                                            }
                                echo "
                                            <li class='page-item mx-1 $nextBtn' onclick='prevNextBtn(1, $blogPageNo, $totalBlogPages)'>
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
                        // echo $widgets->displayGetQuoate($user);
                        /*
                        $tagsArr = array();
                        foreach ( $user->fetchAll(array("tag"), array("blog_details"), array("status"=>1), "", "") as $row ) {
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
                                    echo "<a href='./blogs?tag=$tag' class='btn btn-light m-1'>$tag</a>";
                                }*/
                            ?>
                        </div>
                    </div>
                    ------->


                </div>
            </div>
        </div>
    </div>
    <!-- Blog End -->

    <!---- ad space ------->
    
<!--<div style="display:flex; justify-content:space-around;" class="mt-5 mb-5">-->
<!--    <div style="background-color: #fff; border: 1px solid #8c8c8c; color:#000; height: 180px; width: 70%; display:flex; align-items:center; justify-content:space-around;">-->
<!--        <h4 class="text-center" style='font-size:14px; font-weight:400 !important;'> Advertiesment </h4>-->
<!--    </div>-->
<!--</div>-->

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

        function prevNextBtn(btn, blogPage, totalPages) {
            let searchTagParam = new URLSearchParams(window.location.search);
            if ( searchTagParam.has('tag') && searchTagParam.get('tag') !="" ) {
                searchTag = "&tag="+searchTagParam.get('tag');
            }
            if ( btn == 0 ) {
                if ( blogPage > 1 ) {
                    location.href = "./blogs?page=" + (blogPage-1) + searchTag;
                }
            } else if ( btn == 1 ) {
                if ( totalPages > blogPage ) {
                    location.href = "./blogs?page=" + (blogPage+1) + searchTag;
                }
            }
        }
    </script>
</body>

</html>