<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    $userHeader = new HEADER();
    $user = new USER();
    $widgets = new WIDGETS();
    if ( isset($_GET['id']) && $_GET['id'] > 0 ) {
        $blogID = (int)$_GET['id'];
        if ( $user->IsExist("blog_details", "id", $blogID) ) {
            $blogDetailsArr = $user->fetchAll(
                array("tag", "title", "description", "image", "video", "timestamp"), 
                array("blog_details"), 
                array("id"=>$blogID)
            )[0];
            $blogTag = $blogDetailsArr['tag'];
            $blogTitle = $blogDetailsArr['title'];
            $blogMainDescription = $blogDetailsArr['description'];
            $blogVideoUrl = $blogDetailsArr['video'];
           
            $blogDate = date("d M Y", strtotime(substr($blogDetailsArr['timestamp'], 0, 10)));
            $blogMainImage = $widgets->createCachelessImage("./img/blogs/".$blogDetailsArr['image']);
            $fbShare = "https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fedibear.com%2Fblog%3Fid%3D".$blogID."&amp;src=sdkpreparse";
        } else {
            $user->redirect("./blogs");
        }
    } else {
        $user->redirect("./blogs");
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
   
    <?php echo $userHeader->printUserHeader($blogTitle, str_replace("'", "", $blogMainDescription), substr($blogMainImage, 2)) ?>
</head>

<body>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v16.0" nonce="ndpmmHDo"></script>
    <?php
        echo $userHeader->printUserNav();        //Topbar
              //Header Image
    ?>
    <div class="page-header-bg"></div>

    <div class="container-fluid py-3 p page-header-content" style="margin-top: 0px !important;">
        <div class="container pt-3">
            <i class="fa fa-home pt-1 pr-2 text-primary1"></i><a href="./">Home</a><i class="fa fa-angle-right pt-1 px-2 text-primary1"></i><a href="./blogs">Blog</a><i class="fa fa-angle-right pt-1 px-2 text-primary"></i><?php echo $blogTag; ?><i class="fa fa-angle-right pt-1 px-2 text-primary"></i><?php echo $blogTitle; ?>
            <h4 class="text-warning mt-2"><?php echo $blogTitle; ?></h4>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <img src=<?php echo $blogMainImage ?> class="img-fluid" alt="Blog Main Image" style="width:60%;">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-2 mt-3 text-center">
                    <div class='justify-content-center'>
                        <a class='btn btn-outline-primary btn-square m-1' href=<?php echo $fbShare ?> target="_blank" ><i class='fab fa-facebook-f'></i></a>
                        <a class='btn btn-outline-primary btn-square m-1' href='https://instagram.com/edibearsworld?utm_source=qr&igshid=MzNlNGNkZWQ4Mg%3D%3D' target="_blank" ><i class='fab fa-instagram'></i></a><br>
                        <a class='btn btn-outline-primary btn-square m-1' href='https://www.youtube.com/channel/UCEMob_TpTUErMEKeK9jiz_w'><i class='fab fa-youtube' target="_blank" ></i></a>
                        <a class='btn btn-outline-primary btn-square m-1' target='_blank' href='https://www.pinterest.com/edibearsworld/'><i class='fab fa-pinterest'></i></a>
                    </div>
                    <span class="font-weight-bold text-warning">SHARE</span><br>
                </div>
                <div class="col-lg-10 text-center">
                    <div class="row mb-2 pt-3">
                        <div class="col-12 text-left">
                            <i class='fa fa-calendar-alt fa-sm text-warning p-1'></i>
                            <span class='text-warning pr-4'><?php echo $blogDate; ?></span>
                            <i class='fa fa-tag fa-sm text-warning p-1'></i>
                            <span class='text-warning'><?php echo $blogTag; ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-left">
                            <span class="font-weight-bold text-primary"><?php echo $blogTitle; ?></span><br>
                            <p class="text-justify mt-2"><?php echo $blogMainDescription; ?></p>
                        </div>
                    </div>
                    <?php
                        foreach ( $user->fetchAll(array("description","image_01","image_02"), array("blog_descriptions"), array("blog_id"=>$blogID)) as $blogSubArr ) {
                            $blogImg01 = $widgets->createCachelessImage("./img/blogs/".$blogSubArr['image_01']);
                            $blogImg02 = $widgets->createCachelessImage("./img/blogs/".$blogSubArr['image_02']);
                            echo "
                                <div class='row mb-2'>
                                    <div class='col-sm-6 mb-1'>
                                        <img src='$blogImg01' class='img-fluid' alt='Blog Image'>
                                    </div>
                                    <div class='col-sm-6'>
                                        <img src='$blogImg02' class='img-fluid' alt='Blog Image'>
                                    </div>
                                    <div class='col-12 mt-2 text-justify'>
                                        <p>".$blogSubArr['description']."</p>
                                    </div>

                                </div>

                               


                            ";
                        }
                    ?>
                    <?php
                        if ( true) {
                            echo $widgets->displayHomeMainVideo($blogVideoUrl);
                        }

                        echo "

                              


                            ";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Start -->
    <?php echo $userHeader->printUserFooter(); ?>
    <!-- Footer End -->
</body>

</html>