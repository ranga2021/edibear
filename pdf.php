<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    
    $userHeader = new HEADER("pdf");
    $user = new USER();
    $widgets = new WIDGETS();

  $language   = $_GET['language'] ?? '';
$grade      = $_GET['grade'] ?? '';
$tag        = $_GET['tag'] ?? '';           // from dynamic category
$sub_cat_id = $_GET['sub_cat_id'] ?? '';

    
    $conditions = ["status" => 1];

    if($language != ""){
        $langData = $user->fetchAll(["id"], ["languages"], ["title"=>$language]);
        if(!empty($langData)){
            $conditions["language_id"] = $langData[0]['id'];
        }
    }

    if($grade != ""){
        $gradeData = $user->fetchAll(["id"], ["grades"], ["title"=>$grade]);
        if(!empty($gradeData)){
            $conditions["grade_id"] = $gradeData[0]['id'];
        }
    }

   // Tag filter (for COLORING, etc.)
$tagFilterLike = "1=1";

// ✅ MAIN CATEGORY FILTER (pdf_details.tag column)
if ($tag !== '') {
    $cleanTag = strip_tags($tag);
    $conditions["tag"] = $cleanTag;
}

// Sub-category filter (Animal, etc.)
if ($sub_cat_id !== '') {
    $conditions["sub_cat_id"] = (int)$sub_cat_id;
}


// For breadcrumb titles (keep your existing code for $languageTitle, $gradeTitle, etc.)
// Just make sure $mainCatTitle shows something nice when tag is used, e.g.:
$mainCatTitle = $tag ? strtoupper($tag) . " Pages" : "Coloring Pages";
    

$languageTitle = $language != "" ? $language : "All Languages";
$gradeTitle = $grade != "" ? $grade : "All Grades";

// main category title
$mainCatTitle = "Category";
if($main_cat_id != ""){
    $mainCat = $user->fetchAll(["title"], ["main_category"], ["id"=>$main_cat_id]);
    if(!empty($mainCat)){
        $mainCatTitle = $mainCat[0]['title'];
    }
}

$titleTag = $_GET['title_tag'] ?? '';

if ($titleTag !== '') {
    $cleanTitleTag = strip_tags($titleTag);
    $tagFilterLike = "title LIKE '%$cleanTitleTag%'";
}

// sub category title
$subCatTitle = "Sub Category";
if($sub_cat_id != ""){
    $subCat = $user->fetchAll(["title"], ["sub_category"], ["id"=>$sub_cat_id]);
    if(!empty($subCat)){
        $subCatTitle = $subCat[0]['title'];
    }
}

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

    $searchTag = isset($_GET['tag']) ? strip_tags($_GET['tag']) : "";

if($searchTag != ""){
    $pagingUrlParm = "&tag=$searchTag";

    
    $conditions["tag"] = $searchTag;

} else {
    $pagingUrlParm = "";
}

    if ( isset($_POST['search']) && !empty($_POST['search'])) {
        $searchKey = strip_tags($_POST['search']);
        $pagingUrlParm .= "&search=$searchKey";
        $searchKeyLike = "title LIKE '%$searchKey%'";
    } else if( isset($_POST['search']) && empty($_POST['search'])){
        $searchKey = "";
        $searchKeyLike = "title LIKE '%%'";
        $pagingUrlParm .= "&search=$searchKey";
    } else if (isset($_GET['search'])) {
        $searchKey = $_GET['search'];
        $pagingUrlParm .= "&search=$searchKey";
        $searchKeyLike = "title LIKE '%$searchKey%'";
    } else {
        $searchKey = "";
        $searchKeyLike = "title LIKE '%%'";
        $pagingUrlParm .= "&search=$searchKey";
    }

    $totalpdfPages = ceil( count($user->fetchAll(
    array("id"), 
    array("pdf_details"), 
    $conditions, 
    "", 
    $tagFilterLike . " AND " . $searchKeyLike
)) / 16);

    if ( isset($_GET['page']) ) {
        $pdfPageNo = (int)$_GET['page'];
        if ( $totalpdfPages < $pdfPageNo ) {
            $user->redirect("./pdf?page=$totalpdfPages");
        }
    } else {
        $pdfPageNo = 1;
    }
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
</head>

<body>
    <?php
        // Match navigation with index.php
        echo $userHeader->printUserNav();       // Navbar
    ?>
    <div class="page-header-bg"></div>

    <!-- pdf Start -->
    <div class="container-fluid py-3 page-header-content" style="margin-top: 0px !important;">
        <div class="container pt-3">

        <div class="d-flex pageheaderdiv">
            
            <div class="col-lg-8 ">
                <i class="fa fa-home pt-1 pr-2 text-primary1"></i>
<a href="./">Home</a>

<i class="fa fa-angle-right pt-1 px-2 text-primary1"></i>
<?php echo $languageTitle; ?>

<i class="fa fa-angle-right pt-1 px-2 text-primary1"></i>
<?php echo $gradeTitle; ?>

<i class="fa fa-angle-right pt-1 px-2 text-primary1"></i>
<?php echo $tag; ?>
                
                    <div class="row mt-3 mt-lg-0">
                        <h4 class="col-lg-6 col-md-12 text-warning mt-2">COLORING PAGES</h4>                      
                    </div>

            </div>
            <div class="col-lg-4 searchcol d-flex align-items-end">
                <div class="search-container">
                        <form method="post" class="d-flex">
                            <input type="text"  name="search">
                            <button type="submit" name="submit"><!--i class="fa fa-search"></i-->Search</button>
                        </form>
                </div>
            </div>

        </div>


            <!-- Tag Cloud -->
            <div class="row d-flex mb-2 mt-3">
                <div class="col-1 pr-0">
                <h5 class="text-warning mb-1" >Tags</h5> 
                </div>
                
                <div class="col-11 tagline">
                <img src="./img/Web pic/tagline.png" alt="tagline" width="100%" height="">
                </div> 
            </div>

            <!-- Navitage to page top -->
            <a id="page-top"></a>


            <?php
            $tagsArr = [];

// ✅ FETCH FILTERED DATA (based on sub_cat_id, language, grade)
$pdfRows = $user->fetchAll(
    ["title"],
    ["pdf_details"],
    $conditions   // 🔥 THIS IS THE FIX
);

foreach ($pdfRows as $row) {

    $words = preg_split('/[\s,]+/', $row['title']);

    foreach ($words as $word) {
        $clean = ucfirst(strtolower(trim($word)));

        // remove small words
        if(strlen($clean) > 2){
            if(!in_array($clean, $tagsArr)){
                $tagsArr[] = $clean;
            }
        }
    }
}

            $totalTags = count($tagsArr);
            $visibleTags = min(15, $totalTags);

            echo "<div style='font-size:15px; color:#666; line-height:1.8;'>";
            for ($i = 0; $i < $visibleTags; $i++) {
    $tagWord = $tagsArr[$i];

    echo "<a href='?title_tag=$tagWord"
    .($tag ? "&tag=$tag" : "")
    .($language ? "&language=$language" : "")
    .($grade ? "&grade=$grade" : "")
    .($sub_cat_id ? "&sub_cat_id=$sub_cat_id" : "")
    ."' style='color:#555; text-decoration:none;'>$tagWord</a>";

    if($i < $visibleTags - 1){
        echo ", ";
    }
}
           

            if ($totalTags > $visibleTags) {
                echo "<button id='seeMoreButton' class='tagfont morebutton mb-1'>More..</button>";
                
                echo "<div id='hiddenTags'  style='display: none; margin-top: 6px;'>";
                
                for ($i = $visibleTags; $i < $totalTags; $i++) {
                     $tagWord = $tagsArr[$i];
                    echo "<a  style='color:#000; border-color:#a7a7a7; border-style:solid;' class='tagfont px-3 py-1 mr-1 mb-2 mt-1' href='./pdf?title_tag=$tagWord' class='btn btn-light m-1'>$tagWord</a>";
                }
                echo "</div>";
                echo "</div>";

                // JavaScript to handle the "See More" button functionality
                echo "
                <script>
                    const seeMoreButton = document.getElementById('seeMoreButton');
                    const hiddenTags = document.getElementById('hiddenTags');

                    seeMoreButton.addEventListener('click', function() {
                        hiddenTags.style.display = 'block';
                        seeMoreButton.style.display = 'none';
                    });
                </script>
                ";
            }
            ?>


            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="row pb-3">
                        <?php
                            if ( $pdfPageNo > 1 ) {
    $limit = ( $pdfPageNo - 1 ) * 16;

    $temp = $user->fetchAll(
        array("id"),
        array("pdf_details"),
        $conditions,
        "id DESC LIMIT $limit",
        $searchKeyLike
    );

    if(!empty($temp)){
        $other = "id<" . $temp[$limit-1]['id'];
    } else {
        $other = "";
    }

} else {
    $other = "";
}

if ( $other != "" ) {
    $other = "$other AND $tagFilterLike AND $searchKeyLike";
} else {
    $other = "$tagFilterLike AND $searchKeyLike";
}
                            
                           // card views have defined by using below code block. if you need to change the card count you may change the DESC LIMIT {number-you-need-to-show} 
                            foreach ( $user->fetchAll(array("id","tag","title","image", "description","timestamp", "pdfupload","download_count"), array("pdf_details"), $conditions, "id DESC LIMIT 16", $other) as $row ) {
                                echo $widgets->displaypdfBrief($row, false, "col-md-3", 200);
                            }
                        ?>

                        <div class="col-12 py-5">
                            <?php 
                                $previousBtn = $nextBtn = "";
                                if ($pdfPageNo == 1) {
                                    $previousBtn = "disabled";
                                } 
                                if ($pdfPageNo == $totalpdfPages) {
                                    $nextBtn = "disabled";
                                } 
                                
                                echo "
                                    <nav aria-label='Page navigation example'>
                                        <ul class='pagination justify-content-center'>
                                            <!-- First Page Button 
                                            <li class='page-item mx-1 $previousBtn'>
                                                <a class='page-link' href='./homework?page=1$pagingUrlParm#page-top' aria-label='First'>
                                                    <span aria-hidden='true'>&laquo;&laquo;</span>
                                                </a>
                                            </li>-->
                                            
                                            <!-- Previous Button 
                                            <li class='page-item mx-1 $previousBtn' onclick='prevNextBtn(0, $pdfPageNo, $totalpdfPages)'>
                                                <a class='page-link' href='#page-top' aria-label='Previous'>
                                                    <span aria-hidden='true'>&laquo;</span>
                                                </a>
                                            </li>-->";
                                            
                                            // Show limited page numbers around current page
                                            $startPage = max(1, $pdfPageNo - 2);
                                            $endPage = min($totalpdfPages, $pdfPageNo + 2);
                                            
                                            // Always show first page if not in initial range
                                            if ($startPage > 1) {
                                                echo "<li class='page-item mx-1'><a class='page-link' href='./pdf?page=1$pagingUrlParm#page-top'>1</a></li>";
                                                if ($startPage > 2) {
                                                    echo "<li class='page-item mx-1 disabled'><a class='page-link' href='#'>...</a></li>";
                                                }
                                            }
                                            
                                            for ($i = $startPage; $i <= $endPage; $i++) {
                                                if ($i == $pdfPageNo) {
                                                    echo "<li class='page-item active mx-1'><a class='page-link' href='#'>$i</a></li>";
                                                } else {
                                                    echo "<li class='page-item mx-1'><a class='page-link' href='./pdf?page=$i$pagingUrlParm#page-top'>$i</a></li>";
                                                }
                                            }
                                            
                                            // Always show last page if not in current range
                                            if ($endPage < $totalpdfPages) {
                                                if ($endPage < $totalpdfPages - 1) {
                                                    echo "<li class='page-item mx-1 disabled'><a class='page-link' href='#'>...</a></li>";
                                                }
                                                echo "<li class='page-item mx-1'><a class='page-link' href='./pdf?page=$totalpdfPages$pagingUrlParm#page-top'>$totalpdfPages</a></li>";
                                            }
                                            
                                echo "
                                            <!-- Next Button 
                                            <li class='page-item mx-1 $nextBtn' onclick='prevNextBtn(1, $pdfPageNo, $totalpdfPages)'>
                                                <a class='page-link' href='#page-top' aria-label='Next'>
                                                    <span aria-hidden='true'>&raquo;</span>
                                                </a>
                                            </li>-->
                                            
                                            <!-- Last Page Button 
                                            <li class='page-item mx-1 $nextBtn'>
                                                <a class='page-link' href='./pdf?page=$totalpdfPages$pagingUrlParm#page-top' aria-label='Last'>
                                                    <span aria-hidden='true'>&raquo;&raquo;</span>
                                                </a>
                                            </li>-->
                                        </ul>
                                    </nav>
                                ";
                            ?>
                        </div>
                        
                        
                    </div>
                </div>
     
            </div>
        </div>
    </div>
    </div>
    <!-- pdf End -->


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
        var buttonName = ':input[name="quoteSubmit"]';
        $(buttonName).prop('disabled', true);
        function correctCaptcha() {
            $("form").each(function() {
                $(this).find(buttonName).prop('disabled', false);
            });
        }

        function prevNextBtn(btn, pdfPage, totalPages) {
            let searchTagParam = new URLSearchParams(window.location.search);
            if ( searchTagParam.has('tag') && searchTagParam.get('tag') !="" ) {
                searchTag = "&tag="+searchTagParam.get('tag');
            }
            if ( btn == 0 ) {
                if ( pdfPage > 1 ) {
                    location.href = "./pdf?page=" + (pdfPage-1) + searchTag;
                }
            } else if ( btn == 1 ) {
                if ( totalPages > pdfPage ) {
                    location.href = "./pdf?page=" + (pdfPage+1) + searchTag;
                }
            }
        }
    </script>
</body>

</html>