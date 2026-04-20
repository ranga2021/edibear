<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    $userHeader = new HEADER("about");
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
    <?php echo $userHeader->printUserHeader(); ?>
    <script src='https://www.hCaptcha.com/1/api.js' async defer></script> 
     <link rel="stylesheet" href="css/product_details.css">
</head>

<body>
    <?php
        echo $userHeader->printUserNav();        //Topbar
        
    ?>
    <div class="page-header-bg"></div>

    <!-- About Start -->
    <div class="container-fluid py-5 page-header-content" style="margin-top: 0px !important;">
        <div class="container">
            <i class="fa fa-home pt-1 pr-2" style="color:#8bc34a;"></i>
            <a href="./">Home</a>
            <i class="fa fa-angle-right pt-1 px-2" style="color:#8bc34a;"></i>
            Edi's Story
           <!-- Title + Line -->
        <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
            <h2 style="font-weight:700; margin:0;">Edi's Story</h2>
            <div style="flex:1; height:2px; background:#f4b400;"></div>
        </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                <h1 class="text-primary mb-3">HI THERE !</h1>
                <p class="text-justify px-lg-5 mx-lg-5">
                "EdiBear" is a super awesome website made just for kids like you! Our goal is to make learning super fun and exciting, and we have a ton of cool stuff for you to explore!
                    <br>
                    <br>
                    Here's what you can find on our website: 
                    <br>
                    <br>
                    
<b>Coloring Pages</b><br>
Get ready to unleash your creativity with our amazing coloring pages. We have a wide variety of colouring pages in different categories. You can choose from lots of fun themes, characters, and objects to colour and bring them to life with your own unique style.
<br><br>

<b>Activity books and Model papers</b><br>
We have unique activity books and model papers help you to become a super smart student. These activities help you to develop your subject skills and make learning a breeze. And the Model papers are like practice tests that help you to get ready for the exams to score more.
<br><br>

<b>Study Packs </b><br>
We know homework can sometimes be tough, but don't worry; we've got your back! Our collection of resources will make your school assignments easier and more enjoyable. We have handy reference materials, study guides, and helpful templates to support you and make your homework super impressive.
<br><br>

<b>Fun Activities</b><br>
Get ready for some serious fun with our awesome activities. We have handpicked the coolest brain teasers, puzzles, arts and crafts projects, and more. These activities are not only super entertaining, but they also help you become a super thinker, problem-solver, and creative genius.
<br><br>

We also want to give a huge shout out and say a big "Thank you" to <a href="https://www.freepik.com/" target="_blank">Freepik.com</a>, <a href="https://www.unsplash.com/" target="_blank">Unsplash.com</a>, <a href="https://www.pixabay.com/" target="_blank">Pixabay.com</a>, <a href="https://www.pexels.com/" target="_blank">Pexels.com</a>, and other incredible resources that have helped make our website even more awesome. Their contributions have made our website a truly magical place for you to explore and learn.
<br><br>

So get ready for an amazing adventure at edibear! We're here to support you on your learning journey and help you become the best version of yourself. Let's have a blast together and make learning the most exciting thing ever!
<br><br>

Thank you for choosing edibear, where learning is an awesome adventure!
<br> 
<br>

                </p>
                <div class="px-lg-5 mx-lg-5">
                    <img class="img-fluid " src="./img/Web pic/aboutpage.jpg" alt="About">
                </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Footer Start -->
    <?php echo $userHeader->printUserFooter(); ?>
    <!-- Footer End -->
</body>

</html>