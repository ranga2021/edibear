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
    <div class="container-fluid py-5 page-header-content">
        <div class="container">
            <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edi's Story</li>
                </ol>
            </nav>
           <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>Edi's Story</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                <h1 class="text-primary mb-3">HI THERE !</h1>
                <p class="text-justify px-lg-5 mx-lg-5">
                You already know who I am, so I don't need to introduce myself again. It’s a joyful moment for all of us, because
                after a lot of hard work, we’ve finally found a way to stay close and help our little buddies. ''Edibear's World'' has a
                long history, this isn't the right time to tell it all, but I look forward to the moment I can share that adventure with
                you. I also want to say a special thank you to the four friends who have stayed by my side through every moment.
                    <br>
                    <br>
                I believe that learning shouldn't feel like a chore; it should feel like the most exciting adventure you’ve ever been
                on! Every kid has their own path, so my mission is to help them become the smartest, bravest, and most creative
                versions of themselves while having a blast along the way
                    <br>
                    <br>
                Here is a quick summary of the treasures I’ve hidden inside my world for you:
                    <br>
                    <br>
<b>EXPLORER TRAINING CAMP</b><br>
This is where the magic happens! In the Training Camp, you can find everything you need to power up your brain.
It’s home to my Worksheets (your secret maps for studying), Fun Activities (for hands-on creative play), and
Brain Boosters (to sharpen your memory and thinking skills). Whenever you want to discover something new, just
head to the camp and pick your path.
<br><br>

<b>THE HONEY MARKET</b><br>
Every great explorer needs the right gear, but the world is a big place, and it’s not always easy to find the best
things in one spot. That’s why I’ve done the searching for you and gathered the best treasures in The Honey
Market. I’ve hand-picked these items to ensure my Little Buddies have everything they need to tackle any
challenge with confidence!
<br><br>

<b>TRAIL OF TALES</b><br>
I’m not the only one who loves learning! Our "Trail of Tales" section is where moms and dads share their stories.
They tell us how their little explorers are overcoming challenges and growing stronger every day.
<br><br>

<b>THE HIDDEN DEN</b><br>
This is a special place. Step into The Hidden Den, where I share helpful guides, exciting stories, and extra tips to
spark your curiosity and keep the fun going between your quests.
<br><br>

<b>BRAVE HEART CHALLENGE</b><br>
Are you ready to be brave? The Brave Heart Challenge is where the real action happens! It’s packed with missions
that help you practice what you’ve learned. It’s tough, it’s fun, and it’s where you prove you’re a true explorer!
<br><br>

<b>JOIN MY FAMILY</b><br>
It’s family time! Discuss everything related to your kids' education and entertainment, share knowledge, swap
activity ideas, and get support from other family members.
<br><br>

So, let’s start the journey! Grab your gear and get ready for an amazing adventure. I am here to support you every
step of the way. Let’s have a blast together and make learning the most exciting thing ever!
<br><br>

Thank you for choosing Edibear-where learning is an awesome adventure!"
<br><br>

With love Edi 
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