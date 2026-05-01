<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    $userHeader = new HEADER("blogs");
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
            $tagTrim = trim((string) $blogTag);
            $tagParts = array_values(array_filter(array_map("trim", explode("/", $tagTrim)), function ($s) {
                return $s !== "";
            }));
            $breadcrumbTopic = !empty($tagParts) ? strtoupper($tagParts[0]) : "BLOG";
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
    <link rel="stylesheet" href="css/product_details.css">
</head>

<body class="edi-blog-single-page">
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v16.0" nonce="ndpmmHDo"></script>
    <?php
        echo $userHeader->printUserNav();        //Topbar
              //Header Image
    ?>
    <div class="page-header-bg"></div>

    <div class="container mt-5 page-header-content pb-5 edi-blog-single-inner px-lg-4">
            <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0 flex-wrap">
                    <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="./blogs">The Hidden Den</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($breadcrumbTopic, ENT_QUOTES, "UTF-8"); ?></li>
                </ol>
            </nav>

            <div class="edi-page-title-row edi-blogs-page-title-row mt-2">
                <h1 class="edi-blogs-main-title"><?php echo htmlspecialchars(strtoupper((string) $blogTitle), ENT_QUOTES, "UTF-8"); ?></h1>
                <div class="edi-page-title-rule" role="presentation"></div>
            </div>

            <div class="edi-blog-single-featured mt-3 mb-4">
                <img src="<?php echo htmlspecialchars((string) $blogMainImage, ENT_QUOTES, "UTF-8"); ?>" class="edi-blog-single-featured__img img-fluid" alt="<?php echo htmlspecialchars((string) $blogTitle, ENT_QUOTES, "UTF-8"); ?>">
            </div>

            <div class="row">
                <div class="col-lg-10">
                    <div class="row mb-2 pt-3">
                        <div class="col-12 text-left">
                            <i class='fa fa-calendar-alt fa-sm text-warning p-1'></i>
                            <span class='text-warning pr-4'><?php echo htmlspecialchars($blogDate, ENT_QUOTES, "UTF-8"); ?></span>
                            <i class='fa fa-tag fa-sm text-warning p-1'></i>
                            <span class='text-warning'><?php echo htmlspecialchars($blogTag, ENT_QUOTES, "UTF-8"); ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-left">
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
                <div class="col-lg-2 mt-3 text-center edi-blog-single-share">
                    <div class='justify-content-center'>
                        <a class='btn btn-outline-primary btn-square m-1' href="<?php echo htmlspecialchars((string) $fbShare, ENT_QUOTES, "UTF-8"); ?>" target="_blank" rel="noopener noreferrer"><i class='fab fa-facebook-f'></i></a>
                        <a class='btn btn-outline-primary btn-square m-1' href='https://instagram.com/edibearsworld?utm_source=qr&igshid=MzNlNGNkZWQ4Mg%3D%3D' target="_blank" rel="noopener noreferrer"><i class='fab fa-instagram'></i></a><br>
                        <a class='btn btn-outline-primary btn-square m-1' href='https://www.youtube.com/channel/UCEMob_TpTUErMEKeK9jiz_w' target="_blank" rel="noopener noreferrer"><i class='fab fa-youtube'></i></a>
                        <a class='btn btn-outline-primary btn-square m-1' target='_blank' rel="noopener noreferrer" href='https://www.pinterest.com/edibearsworld/'><i class='fab fa-pinterest'></i></a>
                    </div>
                    <span class="font-weight-bold text-warning">SHARE</span><br>
                </div>
            </div>
    </div>

    <!-- Footer Start -->
    <?php echo $userHeader->printUserFooter(); ?>
    <!-- Footer End -->
</body>

</html>