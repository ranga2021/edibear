<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    $userHeader = new HEADER("testimonials");
    $user = new USER();
    $widgets = new WIDGETS();
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta property='og:title' content='edibear.com | KIDS’ COLORING PAGES, WORKBOOKS & HOMEWORK-RELATED ITEMS'/>
    <meta name='description' content='“edibear” is a website that provides a variety of kids coloring pages, workbooks, relevant 
    model papers, school homework-related items, and fun activities for developing the abilities of kids.' />
    <meta name='keywords' content='Kids coloring pages, Workbooks for kids, Relevant past papers, School 
    homework-related items, Fun activities for kids, Developing kids abilities, Educational resources for kids,
     Downloadable kids materials, Creative learning for kids,' />
    
    <script src='https://www.hCaptcha.com/1/api.js' async defer></script> 
    <link rel="stylesheet" href="css/product_details.css">
    <?php echo $userHeader->printUserHeader() ?>
</head>

<body>
    <?php
        echo $userHeader->printUserNav();        //Topbar
        
    ?>
    <div class="page-header-bg"></div>

    
    <!-- Testimonials -->
    <div class="container-fluid pt-4 pb-5 page-header-content">
        <div class="container pt-5 pb-5">
            <nav class="edi-breadcrumb" aria-label="Breadcrumb">
            <i class="fa fa-home pt-1 pr-2" aria-hidden="true"></i><a href="./">Home</a><i class="fa fa-angle-right pt-1 px-2" aria-hidden="true"></i><span>Testimonials</span>
            </nav>
            
             <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>TRAIL OF TALES</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

            <div class="row mt-3 justify-content-center">
                <div class="col-lg-10 col-md-12">
                <p class="text-justify mb-0">
                It’s time to see what our early buddies and parents think about Edi’s adventure! In a busy life, it can be hard to meet all your child’s learning and entertainment needs, but I am here to help you every step of the way. Nothing makes me happier than seeing my Little Buddies succeed!</p>
                </div>
            </div>

            <div class="row justify-content-center mt-4 testimonial-sec">

                <?php
                    foreach ( $user->fetchAll(array("user_id","ratings","one_word","review"), array("testimonials"), array("status"=>1), "id DESC LIMIT 16") as $testimonialArr ) {
                        echo $widgets->displayTestimonialBrief(array_merge($testimonialArr, $user->fetchAll(array("name","profile_pic","country"), array("tourists"), array("id"=>$testimonialArr['user_id']))[0]));
                    }
                ?>
            </div>
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
    


    <!-- Testimonial End -->

    <!---- ad space start ------->
    <!--<div style="display:flex; justify-content:space-around;" class="mt-5 mb-5">-->
    <!--    <div style="background-color: #fff; border: 1px solid #8c8c8c; color:#000; height: 180px; width: 70%; display:flex; align-items:center; justify-content:space-around;">-->
    <!--        <h4 class="text-center" style="font-size:14px; font-weight:400 !important;"> Advertiesment </h4>-->
    <!--    </div>-->
    <!--</div>-->
    <!---- ad space End------->
    
    
    
    <!-- Footer Start -->
    <?php echo $userHeader->printUserFooter(); ?>
    <!-- Footer End -->
    <script>
        function addMoreTestimonials(lastTestimonialID) {
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {
                    addMoreTestimonials: lastTestimonialID
                },
                success: function(html) {
                    $("#addMoreTestimonialsDiv"+lastTestimonialID).html(html).show();
                }
            }); 
        }
    </script>
</body>

</html>