
<?php
    
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    
    require_once("./classes/class.user.php");
    require_once("./classes/edi_taxonomy.php");
    require_once("./classes/edi_ws_taxonomy.php");
    require_once("./classes/edi_explorer_content.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    require_once("./classes/edi_discount_badge.php");
    require_once("./classes/edi_home_section_images.php");
    $userHeader = new HEADER("home");
    $user = new USER();
    $widgets = new WIDGETS();
    $ediHomeExploreBg = EdiHomeSectionImages::cssUrlString(EdiHomeSectionImages::assetUrl($user, EdiHomeSectionImages::TYPE_EXPLORE));
    $ediHomeExploreBgMobile = EdiHomeSectionImages::cssUrlString(EdiHomeSectionImages::assetUrl($user, EdiHomeSectionImages::TYPE_EXPLORE_MOBILE));
    $ediHomeTestimonialBg = EdiHomeSectionImages::cssUrlString(EdiHomeSectionImages::assetUrl($user, EdiHomeSectionImages::TYPE_TESTIMONIAL));
    $ediHomeTestimonialBgMobile = EdiHomeSectionImages::cssUrlString(EdiHomeSectionImages::assetUrl($user, EdiHomeSectionImages::TYPE_TESTIMONIAL_MOBILE));
    
?>
<?php

    $conn = $user->getConnection();

    $explorerLanguages = array();
    $explorerGrades = array();
    $exploreProductCategories = array();
    $exploreProductSubcategories = array();
    $exploreWsCategories = array();
    $exploreWsSubcategories = array();

    if (EdiWsTaxonomy::tableExists($conn, "ws_categories") && EdiWsTaxonomy::tableExists($conn, "ws_subcategories")) {
        EdiWsTaxonomy::ensureWorksheetWsColumns($conn, "pdf_details");
        EdiWsTaxonomy::ensureWorksheetWsColumns($conn, "books_details");
        EdiWsTaxonomy::ensureWorksheetWsColumns($conn, "homework_details");
    }
    $ediHomeExploreUsesWs = EdiExplorerContent::worksheetWsExplorerReady($conn);

    $explorerLanguages = EdiTaxonomy::loadLanguages($conn);
    $explorerGrades = EdiTaxonomy::loadGrades($conn);
    if ($ediHomeExploreUsesWs) {
        $exploreWsCategories = EdiWsTaxonomy::loadCategories($conn);
        $exploreWsSubcategories = EdiWsTaxonomy::loadSubcategories($conn);
    } else {
        // EXPLORE: shop taxonomy, but only categories that have published worksheets (exclude shop-only / treasures-only).
        $exploreWorksheetCategoryIds = EdiExplorerContent::worksheetExplorerProductCategoryIds($conn);
        $exploreProductCategories = EdiExplorerContent::loadProductCategoryOptionsForWorksheetsExplorer($conn);
        $exploreProductSubcategories = EdiExplorerContent::loadProductSubcategoryOptionsForWorksheetsExplorer(
            $conn,
            $exploreWorksheetCategoryIds
        );
    }

    $productQuery = "SELECT * FROM products WHERE status = 1 ORDER BY id DESC LIMIT 4";
    $stmt = $conn->prepare($productQuery);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $braveHeartQuery = "SELECT * FROM braveheart_events WHERE status = 1 ORDER BY id DESC LIMIT 1";
    $braveStmt = $user->getConnection()->prepare($braveHeartQuery);
    $braveStmt->execute();
    $recentChallenge = $braveStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    

    <?php echo $userHeader->printUserHeader() ?>
    <style>
        .explorer-search-area {
            background: url("<?php echo $ediHomeExploreBg; ?>") no-repeat center;
            background-size: 100% 100%;
        }

        .testimonial-bg {
        background: url("<?php echo $ediHomeTestimonialBg; ?>") no-repeat center;
         background-size: 100% 80%;
         padding: 30px 0 0;
    }

    .testimonial-sec .product-card, 
    .testimonial-sec .testimonial-item { 
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
        @media (max-width:768px) {
            .explorer-search-area {
                background-image: url("<?php echo $ediHomeExploreBgMobile; ?>");
            }

            .testimonial-bg{
                background: url("<?php echo $ediHomeTestimonialBgMobile; ?>") no-repeat center;
                background-size: cover;
                padding: 0.75rem 0;
            }
        }
        @media (max-width:650px) {
            .headerLogo, .signInText{
                display: flex;
            }
        }
    </style>

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8382700902937604"
     crossorigin="anonymous"></script>
    
</head>

<body class="index">


<?php //include 'eeeee.php';?>
    <?php 
        echo $userHeader->printUserNav();
        if ( !$user->CountRows("carousel", array("type"=>"video", "status"=>1)) ) {
            $carouselData = $user->fetchAll(array("text1", "text2", "src"), array("carousel"), array("type"=>"img", "status"=>1), "display_order");
            echo $userHeader->printHomeCarousel($carouselData);
        }
    ?>
    <!-- Carousel End -->

    <div class="container-fluid edi-intro-section" id="ayubowan">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-5 col-md-6 edi-intro-text">

                <h4 class="edi-hello">Hello,</h4>

                <h1 class="text-danger">LITTLE BUDDY!</h1>

                <h3 class="edi-welcome">Welcome to <br> my awesome world!</h3>

                <p class="edi-paragraph">
                I’m Edi, your little bear friend. I’m so happy to see you here. 
                <b>It is time to begin your adventure and find new treasures.</b> 
                Now we are going to have so much fun together! Are you ready?
                </p>

                <p class="edi-paragraph">
                I know you love to play, and guess what? Learning is just as exciting! 
                It helps you grow strong and wise so you can tackle any challenge 
                that comes your way.
                </p>

                <p class="edi-paragraph">
                On this journey, you can explore magical resources, discover exciting activities 
                in the den, and finally face the “Brave Heart” challenge. 
                Let’s go explore together!
                </p>

            </div>

            <div class="col-lg-7 col-md-6 text-center edi-intro-image">
                <img src="./img/Web pic/homebg.png" class="img-fluid" alt="Edi hero image">
            </div>

        </div>
    </div>
</div>

    <?php
        /*if ( $user->CountRows("carousel", array("type"=>"main", "status"=>1)) ) {
            $homeMainVideoURL = $user->fetchAll(array("src"), array("carousel"), array("type"=>"main", "status"=>1))[0]['src'];
           echo $widgets->displayHomeMainVideo($homeMainVideoURL);
        } */
    ?>

    <!--How it works-->
    <div class="container-fluid pb-0 edi-learn-section" id="learn-section">
        <div class="container pb-0">
            <div class="text-center">
                <h1 class="text-danger">EXPLORER TRAINING CAMP</h1>
            </div>

            <div class='row mt-3 justify-content-center edi-learn-intro-row'>
                <div class="col-lg-10 col-md-12">
                <p class="edi-home-lead mb-0">
                Congratulations, Explorer! You’ve reached your first destination. 
        Search every corner to find new resources to polish your skills and boost your brainpower.
        Simply <b>select your Language, Grade, and Category</b> to find exactly what you need. 
        Let’s get started!</p>
                </div>
            </div>

            <div class='row justify-content-center edi-learn-cards-row'>
                <div class="col-lg-10 col-md-12 row">
                    <?php
                    echo $widgets->displayHowItWorksBlock3("WORKSHEETS", "Discover a variety of <br>worksheets for learning.", "3.png");
                        echo $widgets->displayHowItWorksBlock("FUN ACTIVITIES", "Explore fun activities for <br>your leisure time.", "1.png");
                        echo $widgets->displayHowItWorksBlock2("BRAIN BOOSTERS", "Secrets to grow your <br>memory and thinking skills.", "2.png");
                        
                    ?>
                </div>
            </div>
        </div>
    </div>
     <!-- SEARCH AREA -->

    <div class="explorer-search-area">
    <div class="container">
        <form method="GET" action="product_page.php" id="searchForm" data-edi-explore-taxonomy="<?php echo $ediHomeExploreUsesWs ? 'ws' : 'product'; ?>">
        <div class="row justify-content-center align-items-end">
            <div class="col-md-3 mb-2">
                <div class="edi-explorer-select-wrap">
                    <select class="explorer-select" name="lang" id="explorer_exp_lang" required>
                        <option value="" disabled selected hidden>Language (Required)</option>
                        <?php foreach ($explorerLanguages as $lr): ?>
                        <option value="<?php echo htmlspecialchars((string) $lr['title'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $lr['title'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-3 mb-2">
                <div class="edi-explorer-select-wrap">
                <select class="explorer-select" name="age" id="explorer_exp_grade" required>
                    <option value="" disabled selected hidden>Grade (Required)</option>
                    <?php foreach ($explorerGrades as $gr): ?>
                    <option value="<?php echo htmlspecialchars((string) $gr['title'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $gr['title'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
                </div>
            </div>

            <div class="col-md-3 mb-2">
                <div class="edi-explorer-select-wrap">
                <?php if ($ediHomeExploreUsesWs) : ?>
                <select class="explorer-select" name="ws_category_id" id="explorer_exp_cat" required>
                    <option value="" disabled selected hidden>Category (Required)</option>
                    <?php foreach ($exploreWsCategories as $mc): ?>
                    <option value="<?php echo (int) $mc['id']; ?>"><?php echo htmlspecialchars((string) $mc['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php else : ?>
                <select class="explorer-select" name="product_category_id" id="explorer_exp_cat" required>
                    <option value="" disabled selected hidden>Category (Required)</option>
                    <?php foreach ($exploreProductCategories as $mc): ?>
                    <option value="<?php echo (int) $mc['id']; ?>"><?php echo htmlspecialchars((string) $mc['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                </div>
            </div>

            <div class="col-md-3 mb-2">
                <div class="edi-explorer-select-wrap">
                    <?php if ($ediHomeExploreUsesWs) : ?>
                    <select class="explorer-select" name="ws_subcategory_id" id="explorer_exp_sub" disabled title="Please select a category first to see subcategories.">
                     <option value="" disabled selected hidden class="edi-exp-sub-need-cat">Subcategory</option>
                     <option value="" hidden class="edi-exp-sub-none">Subcategory (optional)</option>
                    <?php foreach ($exploreWsSubcategories as $sub): ?>
                      <option value="<?php echo (int) $sub['id']; ?>" data-ws-category-id="<?php echo (int) $sub['category_id']; ?>">
                     <?php echo htmlspecialchars((string) $sub['name'], ENT_QUOTES, 'UTF-8'); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                    <?php else : ?>
                    <select class="explorer-select" name="product_subcategory_id" id="explorer_exp_sub" disabled title="Please select a category first to see subcategories.">
                     <option value="" disabled selected hidden class="edi-exp-sub-need-cat">Subcategory </option>
                     <option value="" hidden class="edi-exp-sub-none">Subcategory (optional)</option>
                    <?php foreach ($exploreProductSubcategories as $sub): ?>
                      <option value="<?php echo (int) $sub['id']; ?>" data-product-category-id="<?php echo (int) $sub['product_category_id']; ?>">
                     <?php echo htmlspecialchars((string) $sub['title'], ENT_QUOTES, 'UTF-8'); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="text-center mt-4">
            <button type="submit" class="explore-btn edi-home-section-cta">EXPLORE</button>
        </div>
        </form>
    </div>
</div>

    <div class="container-fluid pb-0" id="honey-market-section">
      <div class="container">

       <div class="text-center">
        <h1 class="text-danger">THE HONEY MARKET</h1>
      </div>
      <div class="row mt-3 justify-content-center">
        <div class="col-lg-10 col-md-12">
      <p class="edi-home-lead mb-0">
        This is your second destination! Explore every trail and collect new treasures
        to sharpen your knowledge and brighten your brave hearts! Discover your favourite things to make this journey truly memorable. It's time to bring the fun home for your next big adventure!
    </p>
        </div>
      </div>

<div class="row mt-4">

<?php 
            // REMOVE the $products = $user->fetchAll(...) line that was here.
            // We now use the $products array we fetched at the top.
            
            if (empty($products)) {
                echo "<div class='col-12 text-center'><p>No treasures found in this trail. Try another search!</p></div>";
            } else {
                foreach($products as $product) {
                    $price = $product['discounted_price'] > 0 ? $product['discounted_price'] : $product['price'];
                    $discountPct = edi_discount_badge_pct($product);
            ?>
                <div class="col-lg-3 col-md-6 text-center mb-4">
                    <div class="product-card">

    <div class="product-card-thumb-wrap">
    <?php if ($discountPct !== null) { ?>
        <span class="edi-discount-hex" aria-label="<?php echo (int) $discountPct; ?> percent off"><?php echo (int) $discountPct; ?>%</span>
    <?php } ?>
    <a href="product_details.php?product_id=<?php echo $product['id']; ?>">
        <img src="./img/products/<?php echo htmlspecialchars((string) $product['image'], ENT_QUOTES, 'UTF-8'); ?>" class="product-img cart-product-image" alt="<?php echo htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?>">
    </a>
    </div>

    <?php $pnameDisplay = htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8'); ?>
    <h6 class="product-card-title">
        <a href="product_details.php?product_id=<?php echo $product['id']; ?>" title="<?php echo $pnameDisplay; ?>" style="text-decoration:none; color:inherit;">
            <?php echo $pnameDisplay; ?>
        </a>
    </h6>
                        <div class="price" style='text-align: left; padding-left:5px;'>
                            <?php
                            if($product['discounted_price'] > 0){
                                echo "<span class='old-price'>LKR ".$product['price']."</span>";
                                echo "<span class='new-price'>LKR ".$product['discounted_price']."</span>";
                            } else {
                                echo "<span class='new-price'>LKR ".$product['price']."</span>";
                            }
                            ?>
                        </div>
                        <div class="product-card-cart-row">
                        <form method="POST" action="add_to_cart.php" class="m-0 p-0">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn newgreen1-btn collect-btn add-to-cart-btn">Collect</button>
                        </form>
                        </div>

                    </div>
                </div>

<?php } 
}?>

</div>

<div class="text-center edi-home-cta-band edi-home-cta-band--products">
<a href="./product_page.php" class="btn mt-3 newgreen1-btn edi-home-section-cta">SEE MORE</a>
</div>

</div>
</div>

    <?php
    $ad1Rows = $user->fetchAll(
        array("id", "tag", "title", "image", "description", "timestamp", "adlink"),
        array("ad1_details"),
        array("status" => "1"),
        "id DESC LIMIT 1"
    );
    
    if (!empty($ad1Rows)) {
        $row = $ad1Rows[0];
        $lastad1ID = "id<" . $row['id'];
        ?>
        <div style="display:flex; justify-content:center; overflow:hidden;" class="edi-home-ad-slot">
            <div style="background-color: #fff; height: auto; width:80%; display:flex; align-items:center; justify-content:center;">
                <h1 class="text-center d-none"> ADD SPACE </h1>
                <div class="row">
                    <?php
                        echo $widgets->displayad1Brief($row, 600, true);
                    ?>
                </div>      
            </div>
        </div>
        <?php
    } else {
        $lastad1ID = "";
    }
    ?>

    <!---- ad space start ------->
    <!--<div style="display:flex; justify-content:space-around;" class="mt-5 mb-5">-->
    <!--    <div style="background-color: #fff; border: 1px solid #8c8c8c; color:#000; height: 180px; width: 70%; display:flex; align-items:center; justify-content:space-around;">-->
    <!--        <h4 class="text-center" style="font-size:14px; font-weight:400 !important;"> Advertiesment </h4>-->
    <!--    </div>-->
    <!--</div>-->
    <!---- ad space End------->

    <!-- Tour Packages (section removed; keep PHP from running broken tour block) -->

    <div class="container-fluid pt-0 px-0 edi-trail-tales-wrap">
        <div class="container pt-0 pb-0">
        <div class="text-center">
            <h1 class="text-danger">TRAIL OF TALES</h1>
        </div>
        <div class='row mt-3 justify-content-center'>
            <div class="col-lg-10 col-md-12">
                <p class="edi-home-lead mb-0">
                    It's time to see what our early buddies and parents think about Edi's adventure! In a busy life, it can be hard to meet all your child's learning and entertainment needs, but I am here to help you every step of the way. Nothing makes me happier than seeing my Little Buddies succeed!
                </p>
            </div>
        </div>
    </div>

    <div class="testimonial-bg">
        <div class="container"> <div class="row justify-content-center testimonial-sec">
                <?php
                    foreach ( $user->fetchAll(array("id","user_id","ratings","one_word","review"), array("testimonials"), array("status"=>1), "id DESC LIMIT 3") as $testimonialArr ) {
                        $touristRow = $user->fetchAll(array("name","profile_pic","country"), array("tourists"), array("id"=>$testimonialArr['user_id']))[0];
                        $imgRows = $user->fetchAll(array("image"), array("testimonials_images"), array("testimonial_id"=>$testimonialArr['id']));
                        $testimonialPhoto = (!empty($imgRows[0]['image'])) ? $imgRows[0]['image'] : '';
                        echo $widgets->displayTestimonialBrief(array_merge($testimonialArr, $touristRow, array("testimonial_photo"=>$testimonialPhoto)));
                    }
                ?>
            </div>
        </div>
    </div>

    <div class="text-center edi-home-cta-band">
        <button class="btn newgreen1-btn edi-home-section-cta" type="button" onclick="location.href='./testimonials'">READ MORE</button>
    </div>
</div>

    <!-- <div class="testimonial-bg">
        <div class="row justify-content-center py-4 px-1 px-md-3 px-lg-5" style="margin: 0;">
            <?php
                // foreach ( $user->fetchAll(array("user_id","ratings","one_word","review"), array("testimonials"), array("status"=>1), "id DESC LIMIT 3") as $testimonialArr ) {
                //     echo $widgets->displayTestimonialBrief(array_merge($testimonialArr, $user->fetchAll(array("name","profile_pic","country"), array("tourists"), array("id"=>$testimonialArr['user_id']))[0]));
                // }
            ?>
        </div>
    </div>
    <div class="text-center mt-2 pb-5">
        <button class="btn btn-primary px-4 rounded" onclick="location.href='./testimonials'">SEE MORE</button>
    </div>
    <div class="pb-4"></div> -->

    <!-- Blog Start -->
    <div class="container-fluid edi-hidden-den-section" id="play-section">
        <div class="container pt-0">
            <div class="text-center">
                <h1 class="text-danger">THE HIDDEN DEN</h1>
            </div>

            <div class="row mt-3 justify-content-center">
                <div class="col-lg-10 col-md-12">
                    <p class="edi-home-lead mb-0">
                Hurrah! You've reached the third destination. This is very special. Inside the Hidden Den, you can discover exciting things that make learning feel just like play! Step inside and explore what you want to learn while Edi guides you step-by-step. Now, you're ready to face any challenge with total confidence!</p>
                </div>
            </div>

            <div class="row edi-hidden-den-grid mt-4 justify-content-center align-items-stretch">
                <div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
                    <div class="edi-hidden-den-featured h-100">
                        <div class="row mx-0">
                        <?php
                            $row = null;
                            $lastBlogID = "";
                            foreach ( $user->fetchAll(array("id","tag","title","image", "description","timestamp"), array("blog_details"), array("status"=>"1"), "id DESC LIMIT 1") as $row ) {
                                echo $widgets->displayBlogBrief($row, "col-12 px-0", 520, "featured");
                            }
                            if ( isset($row['id']) && $row['id'] ) {
                                $lastBlogID = "id<".$row['id'];
                            }
                        ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="row edi-blog-subgrid no-gutters mx-0">
                    <?php
                        foreach ( $user->fetchAll(array("id","tag","title","image", "description","timestamp"), array("blog_details"), array("status"=>"1"), "id DESC LIMIT 4", "$lastBlogID") as $row ) {
                            echo $widgets->displayBlogBrief($row, "col-6", 118, "grid");
                        }
                    ?>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center edi-home-cta-band">
                <div class="col-12 text-center">
                    <button type="button" class="btn newgreen1-btn edi-home-section-cta" onclick="location.href='./blogs'">COME IN</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Blog End -->

    <section id="challenge-section" class="bg-white w-100 clearfix">
    <div class="container">
        <div class="text-center">
            <h1 class="text-danger">BRAVE HEART CHALLENGE</h1>
        </div>

            <div class="row mt-3 justify-content-center">
                <div class="col-lg-10 col-md-12">
                <p class="edi-home-lead mb-0">
                Explorer! We’ve reached the end of the adventure. It’s time to show off your amazing talents and celebrate everything you’ve learned along the way. I’ve set new missions with exciting challenges to test your skills! Grab your tools, join the fun, and you could even win a special prize. Let’s see what you can do!</p>

        <?php if ($recentChallenge): ?>
                <h5 class="text-uppercase mb-3 mt-4" style="font-weight: 700 !important;">
                    UPCOMING – <?php
                    $bhHomeTitle = html_entity_decode((string) ($recentChallenge['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    echo htmlspecialchars($bhHomeTitle, ENT_QUOTES, 'UTF-8');
                    ?>
                </h5>

                <div class="challenge-banner-container mb-4">
                    <img src="./img/braveheart/<?php echo $recentChallenge['main_image']; ?>" 
                         alt="Challenge Banner" 
                         class="img-fluid rounded shadow-sm" 
                         style="max-width: 100%; height: auto;">
                </div>

                <div class="text-center edi-home-cta-band">
                    <a href="./challenges.php" class="btn newgreen1-btn edi-home-section-cta">
                        ACCEPT
                    </a>
                </div>
        <?php else: ?>
                <div class="text-center mt-4">
                    <p class="mb-0">New challenges are coming soon! Stay tuned, Explorer!</p>
                </div>
        <?php endif; ?>
                </div>
            </div>
    </div>
    </section>

    
    <!-- PDF Start -->
    <!-- <div class="container-fluid py-4">
        <div class="container py-5">
            <div class="text-center">
                <h1 class="text-primary">ARTICLE & TRAVEL GUIDINGS</h1>
            </div>
            <div class="row justify-content-center">
                <p class="text-justify col-lg-10 mt-3 px-lg-5">
                    Sri Lanka is an amazing travel destination which offers a wide range of places to visit. So here is some information to make your journey a perfect one.
                </p>
            </div>
            <div class="row mt-3 justify-content-center">
                <div class="col-md-6"> -->
                <?php
                    // foreach ( $user->fetchAll(array("id","tag","title","image", "description","timestamp"), array("pdf_details"), array("status"=>"1"), "id DESC LIMIT 1") as $row ) {
                    //     echo $widgets->displayPdfBrief($row, "col-12", 600);
                    // }
                    // if ( $row['id'] ) {
                    //     $lastpdfID = "id<".$row['id'];
                    // } else {
                    //     $lastpdfID = "";
                    // }
                ?>
                <!-- </div>
                <div class="col-md-6">
                    <div class="row justify-content-center"> -->
                    <?php
                        // foreach ( $user->fetchAll(array("id","tag","title","image", "description","timestamp"), array("pdf_details"), array("status"=>"1"), "id DESC LIMIT 4", "$lastpdfID") as $row ) {
                        //     echo $widgets->displayPdfBrief($row, "col-md-6", 160);
                        // }
                    ?>
                    <!-- </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <button class="btn btn-primary px-4 rounded" onclick="location.href='./pdf'">SEE MORE</button>
            </div>
        </div>
    </div> -->
    <!-- PDF End -->




    <!-- Footer Start -->
    <?php echo $userHeader->printUserFooter(); ?>
    <!-- Footer End -->
    <script>
        // Pass login status to JavaScript
        var isLoggedIn = <?php echo isset($_SESSION['session_tourism_user']) ? 'true' : 'false'; ?>;
    </script>
    <script>
        function goToAyubowan() {
            $('html, body').animate({
                scrollTop: $("#ayubowan").offset().top
            });
        }
    </script>
    <script>
document.querySelectorAll(".add-to-cart-btn").forEach(button => {
    button.addEventListener("click", function(e) {
        e.preventDefault();

        // ✅ USE LOCALSTORAGE (NOT SESSION)
        const userSession = localStorage.getItem('user_session');

        if (!userSession) {
            if (typeof showLoginPopup === "function") {
                showLoginPopup();
            } else {
                window.location.href = './login';
            }
            return;
        }

        const form = this.closest("form");
        const productCard = this.closest(".product-card");
        const productImage = productCard ? productCard.querySelector(".cart-product-image") : null;
        const cartIcon = document.querySelector("#cart-icon");

        // Animation
        if (productImage) {
            const imgClone = productImage.cloneNode(true);
            const rect = productImage.getBoundingClientRect();
            const cartRect = cartIcon ? cartIcon.getBoundingClientRect() : null;

            imgClone.style.position = "fixed";
            imgClone.style.left = rect.left + "px";
            imgClone.style.top = rect.top + "px";
            imgClone.style.width = rect.width + "px";
            imgClone.style.zIndex = 9999;
            imgClone.style.transition = "all 0.8s ease-in-out";

            document.body.appendChild(imgClone);

            setTimeout(() => {
                if (cartRect) {
                    imgClone.style.left = cartRect.left + "px";
                    imgClone.style.top = cartRect.top + "px";
                    imgClone.style.width = "20px";
                    imgClone.style.opacity = "0.3";
                }
            }, 10);

            setTimeout(() => {
                imgClone.remove();
            }, 800);
        }

        // ✅ SEND UID TO BACKEND
        const formData = new FormData(form);
        formData.append('uid', userSession);

        fetch("add_to_cart.php", {
            method: "POST",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (cartIcon) {
                cartIcon.classList.add("bounce");
                setTimeout(() => cartIcon.classList.remove("bounce"), 400);
            }
            // increase cart count
let count = localStorage.getItem('cart_count');
count = count ? parseInt(count, 10) : 0;
localStorage.setItem('cart_count', String(count + 1));
if (typeof window.edibearSyncCartBadge === 'function') {
    window.edibearSyncCartBadge();
}
        })
        .catch(err => console.error(err));
    });
});
</script>


<!-- Edibear Flying Creatures — paste before </body> -->
<style>
.ebi-cr{position:fixed;z-index:9999;pointer-events:none;opacity:0;transition:opacity 0.5s ease;}
</style>
<script>
(function(){
  const MAX_ON_SCREEN = 4;
  let activeCount = 0;

  var GIFS = [
    { src: '/img/creatures/bird.gif',              w: 110 },
    { src: '/img/creatures/butterfly-orange.gif',  w: 95  },
    { src: '/img/creatures/butterfly-blue.gif',    w: 85  }
  ];

  function spawn() {
    if (activeCount >= MAX_ON_SCREEN) return;

    var g = GIFS[Math.floor(Math.random() * GIFS.length)];
    var fl = Math.random() < 0.5;
    var el = document.createElement('img');
    el.className = 'ebi-cr';
    el.src = g.src;
    el.style.width = g.w + 'px';
    el.style.top = (8 + Math.random() * 72) + 'vh';
    el.style.left = fl ? '-140px' : (window.innerWidth + 20) + 'px';
    if (!fl) el.style.transform = 'scaleX(-1)';
    document.body.appendChild(el);
    activeCount++;

    requestAnimationFrame(function(){ requestAnimationFrame(function(){ el.style.opacity='1'; }); });

    var dur = 6000 + Math.random() * 5000;
    var sx = fl ? -140 : window.innerWidth + 20;
    var ex = fl ? window.innerWidth + 140 : -140;
    var sy = parseFloat(el.style.top) / 100 * window.innerHeight;
    var wa = 35 + Math.random() * 55, wo = Math.random() * 6.28, t0 = null;

    (function step(ts){
      if(!t0) t0=ts;
      var p=Math.min((ts-t0)/dur,1), e=p<.5?2*p*p:-1+(4-2*p)*p;
      el.style.left = (sx+(ex-sx)*e)+'px';
      el.style.top  = (sy+Math.sin(p*11+wo)*wa)+'px';
      if(p>.82) el.style.opacity = Math.max(0,1-(p-.82)/.18)+'';
      if(p<1) requestAnimationFrame(step); else { activeCount--; el.remove(); }
    })(0);
  }

  function burst(){
    var n=1+Math.floor(Math.random()*2);
    for(var i=0;i<n;i++) setTimeout(spawn, i*(400+Math.random()*500));
    setTimeout(burst, 7000+Math.random()*10000);
  }
    setTimeout(burst, 2500);
})();
</script>
<script>
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function (e) {
        const target = document.querySelector(this.getAttribute("href"));
        if (target) {
            e.preventDefault();
            window.scrollTo({
                top: target.offsetTop - 80, // adjust for navbar height
                behavior: "smooth"
            });
        }
    });
});
</script>
<script>
(function () {
    var searchForm = document.getElementById("searchForm");
    var tax = searchForm && searchForm.getAttribute("data-edi-explore-taxonomy");
    tax = tax || "product";
    var subAttr = tax === "ws" ? "data-ws-category-id" : "data-product-category-id";
    var catSel = document.getElementById("explorer_exp_cat");
    var subSel = document.getElementById("explorer_exp_sub");
    if (!catSel || !subSel) return;

    var needCat = subSel.querySelector("option.edi-exp-sub-need-cat");
    var noneOpt = subSel.querySelector("option.edi-exp-sub-none");

    function filterSubcategories() {
        var cid = String(catSel.value || "");
        var realOpts = subSel.querySelectorAll("option[" + subAttr + "]");
        if (!cid) {
            subSel.disabled = true;
            if (needCat) {
                needCat.hidden = false;
                needCat.disabled = true;
                needCat.selected = true;
            }
            if (noneOpt) {
                noneOpt.hidden = true;
                noneOpt.disabled = true;
                noneOpt.selected = false;
            }
            for (var i = 0; i < realOpts.length; i++) {
                realOpts[i].hidden = true;
                realOpts[i].disabled = true;
            }
            return;
        }
        subSel.disabled = false;
        if (needCat) {
            needCat.hidden = true;
            needCat.disabled = true;
            needCat.selected = false;
        }
        if (noneOpt) {
            noneOpt.hidden = true;
            noneOpt.disabled = false;
        }
        var current = String(subSel.value || "");
        var stillOk = false;
        for (var j = 0; j < realOpts.length; j++) {
            var o = realOpts[j];
            var pc = o.getAttribute(subAttr);
            var show = String(pc) === String(cid);
            o.hidden = !show;
            o.disabled = !show;
            if (show && o.value === current) {
                stillOk = true;
            }
        }
        if (!stillOk && noneOpt) {
            noneOpt.selected = true;
        }
    }

    catSel.addEventListener("change", filterSubcategories);
    filterSubcategories();
})();
</script>
</body>

</html>