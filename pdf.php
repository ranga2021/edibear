<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    require_once("./classes/edi_content_tags.php");
    require_once("./classes/edi_explorer_content.php");
    
    $userHeader = new HEADER("pdf");
    $user = new USER();
    $widgets = new WIDGETS();
    $conn = $user->getConnection();

  $language   = $_GET['language'] ?? $_GET['lang'] ?? '';
$grade      = $_GET['grade'] ?? $_GET['age'] ?? '';
$sub_cat_id = $_GET['sub_cat_id'] ?? '';
$main_cat_id = $_GET['main_cat_id'] ?? '';
$product_category_id = $_GET['product_category_id'] ?? '';
$product_subcategory_id = $_GET['product_subcategory_id'] ?? '';
$searchTag  = isset($_GET['tag']) ? strip_tags((string) $_GET['tag']) : "";

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

$tagFilterLike = "1=1";

// Sub-category filter
if ($sub_cat_id !== '') {
    $conditions["sub_cat_id"] = (int) $sub_cat_id;
}
if ($main_cat_id !== '') {
    $conditions["main_cat_id"] = (int) $main_cat_id;
}
// If using Honey Market taxonomy params (from homepage EXPLORE), filter by those columns when present.
if ($main_cat_id === '' && $product_category_id !== '' && EdiExplorerContent::columnExists($conn, "pdf_details", "product_category_id")) {
    $conditions["product_category_id"] = (int) $product_category_id;
    if ($product_subcategory_id !== '' && EdiExplorerContent::columnExists($conn, "pdf_details", "product_subcategory_id")) {
        $conditions["product_subcategory_id"] = (int) $product_subcategory_id;
    }
}

$mainCatTitle = "Category";
if ($main_cat_id != "") {
    $mainCat = $user->fetchAll(array("title"), array("main_category"), array("id" => $main_cat_id));
    if (!empty($mainCat)) {
        $mainCatTitle = $mainCat[0]["title"];
    }
} elseif ($product_category_id !== "") {
    $pc = $user->fetchAll(array("name"), array("product_categories"), array("id" => (int) $product_category_id));
    if (!empty($pc) && isset($pc[0]["name"])) {
        $mainCatTitle = (string) $pc[0]["name"];
    }
} elseif ($searchTag !== "") {
    $mainCatTitle = strtoupper($searchTag) . " Pages";
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
} elseif ($product_subcategory_id !== "") {
    $ps = $user->fetchAll(array("title"), array("product_subcategories"), array("id" => (int) $product_subcategory_id));
    if (!empty($ps) && isset($ps[0]["title"])) {
        $subCatTitle = (string) $ps[0]["title"];
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

    $pagingUrlParm = "";

if ( isset($_POST['search']) && !empty($_POST['search'])) {
        $searchKey = strip_tags($_POST['search']);
        $pagingUrlParm .= "&search=$searchKey";
        $searchKeyLike = "title LIKE '%$searchKey%'";
    } else if( isset($_POST['search']) && empty($_POST['search'])){
        $searchKey = "";
        $searchKeyLike = "title LIKE '%%'";
        $pagingUrlParm .= "&search=$searchKey";
    } else if (isset($_GET['search'])) {
        $searchKey = strip_tags(trim((string) $_GET['search']));
        $pagingUrlParm .= "&search=" . rawurlencode($searchKey);
        $searchKeyLike = "title LIKE '%$searchKey%'";
    } else {
        $searchKey = "";
        $searchKeyLike = "title LIKE '%%'";
        $pagingUrlParm .= "&search=$searchKey";
    }

    if (isset($_GET['main_cat_id']) && (int) $_GET['main_cat_id'] > 0) {
        $pagingUrlParm .= "&main_cat_id=" . (int) $_GET['main_cat_id'];
    }
    if (isset($_GET['sub_cat_id']) && (int) $_GET['sub_cat_id'] > 0) {
        $pagingUrlParm .= "&sub_cat_id=" . (int) $_GET['sub_cat_id'];
    }
    if ($language !== "") {
        $pagingUrlParm .= "&language=" . rawurlencode($language);
    }
    if ($grade !== "") {
        $pagingUrlParm .= "&grade=" . rawurlencode($grade);
    }
    if ($searchTag !== "") {
        $pagingUrlParm .= "&tag=" . rawurlencode($searchTag);
    }

    $searchTagLike = ($searchTag !== "") ? ("tag LIKE '%" . str_replace(array("\\", "'", "%", "_"), array("\\\\", "''", "\\%", "\\_"), $searchTag) . "%'") : "1=1";
    $listComboOther = "(" . $tagFilterLike . ") AND (" . $searchKeyLike . ") AND (" . $searchTagLike . ")";
    $listComboForTagCloud = "(" . $tagFilterLike . ") AND (" . $searchKeyLike . ")";

    $pdfPreserveParams = EdiContentTags::preserveListParams($language, $grade, $main_cat_id, $sub_cat_id);

    if ($main_cat_id != "" && $mainCatTitle !== "" && $mainCatTitle !== "Category") {
        $pageHeroTitleForPdf = strtoupper($mainCatTitle);
    } elseif (isset($subCatTitle) && $subCatTitle !== "" && $subCatTitle !== "Sub Category") {
        $pageHeroTitleForPdf = strtoupper(trim($subCatTitle));
    } elseif (isset($titleTag) && $titleTag !== "") {
        $pageHeroTitleForPdf = strtoupper(strip_tags($titleTag));
    } elseif ($searchTag !== "") {
        $pageHeroTitleForPdf = strtoupper(trim($searchTag));
    } else {
        $pageHeroTitleForPdf = "COLORING PAGES";
    }

    $ediPdfListParams = array();
    if ($language !== "") {
        $ediPdfListParams["language"] = $language;
    }
    if ($grade !== "") {
        $ediPdfListParams["grade"] = $grade;
    }
    if (!empty($searchTag)) {
        $ediPdfListParams["tag"] = $searchTag;
    }
    if ($sub_cat_id !== "") {
        $ediPdfListParams["sub_cat_id"] = (string) (int) $sub_cat_id;
    }
    if ($main_cat_id !== "") {
        $ediPdfListParams["main_cat_id"] = (string) (int) $main_cat_id;
    }
    if (isset($searchKey) && $searchKey !== "") {
        $ediPdfListParams["search"] = $searchKey;
    }
    if (isset($titleTag) && (string) $titleTag !== "") {
        $ediPdfListParams["title_tag"] = (string) $titleTag;
    }

    $totalpdfPages = ceil( count($user->fetchAll(
    array("id"), 
    array("pdf_details"), 
    $conditions, 
    "", 
    $listComboOther
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
    <div class="container mt-5 page-header-content">

                <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0 flex-wrap">
                    <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                    <?php if ($language !== ""): ?>
                        <li class="breadcrumb-item"><?php echo htmlspecialchars($language, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endif; ?>
                    <?php if ($grade !== ""): ?>
                        <li class="breadcrumb-item"><?php echo htmlspecialchars($grade, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endif; ?>
                    <?php if ($mainCatTitle !== "" && $mainCatTitle !== "Category"): ?>
                        <li class="breadcrumb-item"><?php echo htmlspecialchars($mainCatTitle, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endif; ?>
                    <?php if ($subCatTitle !== "" && $subCatTitle !== "Sub Category" && $subCatTitle !== "Subcategory"): ?>
                        <li class="breadcrumb-item"><?php echo htmlspecialchars($subCatTitle, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endif; ?>
                    <?php if (!empty($searchTag)): ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($searchTag, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php elseif ((string) $titleTag !== ""): ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars((string) $titleTag, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php elseif ($subCatTitle !== "" && $subCatTitle !== "Sub Category" && $subCatTitle !== "Subcategory"): ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($subCatTitle, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php elseif ($mainCatTitle !== "" && $mainCatTitle !== "Category"): ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($mainCatTitle, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars("All", ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endif; ?>
                </ol>
            </nav>
                
            <div class="edi-page-title-row mt-2">
                        <h1><?php echo htmlspecialchars($pageHeroTitleForPdf, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <div class="edi-page-title-rule" role="presentation"></div>
            </div>


            <!-- Navitage to page top -->
            <a id="page-top"></a>


            <?php
            $pdfTagRows = $user->fetchAll(
                array("tag"),
                array("pdf_details"),
                $conditions,
                "id DESC LIMIT 500",
                $listComboForTagCloud
            );
            $tagsArr = EdiContentTags::distinctFromRows($pdfTagRows);

            $totalTags = count($tagsArr);
            $visibleTags = min(20, $totalTags);

            echo "<div class=\"edi-pdf-tag-chips text-dark\" style=\"font-size:15px; line-height:2.1; margin-left:15px;\">";
            for ($i = 0; $i < $visibleTags; $i++) {
                $tagWord = $tagsArr[$i];
                $qNext = array_merge($pdfPreserveParams, array("tag" => $tagWord));
                if (isset($searchKey) && $searchKey !== "") {
                    $qNext["search"] = $searchKey;
                }
                $href = "pdf.php?" . http_build_query($qNext, "", "&", PHP_QUERY_RFC3986);
                $safeWord = htmlspecialchars($tagWord, ENT_QUOTES, "UTF-8");
                echo "<a href=\"" . htmlspecialchars($href, ENT_QUOTES, "UTF-8") . "\" class=\"edi-pdf-topic-link\" style=\"color:#f57c00; text-decoration:none; font-weight:600; border-bottom:1px solid rgba(245,124,0,.35);\">" . $safeWord . "</a>";
                if ($i < $visibleTags - 1) {
                    echo "<span class=\"text-muted\" style=\"margin:0 5px 0 2px;\">,</span> ";
                }
            }
            if ($totalTags > $visibleTags) {
                echo " <span class=\"text-muted\">&hellip;</span> ";
                echo "<button type=\"button\" id=\"seeMoreButton\" class=\"tagfont morebutton mb-1 text-warning font-weight-bold\" style=\"background:none;border:0;cursor:pointer;\">See more</button>";
                echo "<div id=\"hiddenTags\" style=\"display: none; margin-top: 10px;\">";
                for ($i = $visibleTags; $i < $totalTags; $i++) {
                    $tagWord = $tagsArr[$i];
                    $qNext = array_merge($pdfPreserveParams, array("tag" => $tagWord));
                    if (isset($searchKey) && $searchKey !== "") {
                        $qNext["search"] = $searchKey;
                    }
                    $href = "pdf.php?" . http_build_query($qNext, "", "&", PHP_QUERY_RFC3986);
                    $safeWord = htmlspecialchars($tagWord, ENT_QUOTES, "UTF-8");
                    echo " <a href=\"" . htmlspecialchars($href, ENT_QUOTES, "UTF-8") . "\" class=\"btn btn-sm btn-light border px-2 py-0 mb-1 mr-1 text-dark\">" . $safeWord . "</a>";
                }
                echo "</div>";
                echo "
                <script>
                (function() {
                    var b = document.getElementById('seeMoreButton');
                    var h = document.getElementById('hiddenTags');
                    if (b && h) b.addEventListener('click', function() { h.style.display = 'block'; b.style.display = 'none'; });
                })();
                </script>";
            }
            echo "</div>";
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
        $listComboOther
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
    $other = "$other AND $listComboOther";
} else {
    $other = $listComboOther;
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
    (function () {
        function fKey() { return "edibear_fav_pdf"; }
        function read() { try { return JSON.parse(localStorage.getItem(fKey()) || "[]"); } catch (e) { return []; } }
        function write(a) { localStorage.setItem(fKey(), JSON.stringify(a)); }
        function paint(btn, on) {
            var ic = btn.querySelector("i");
            if (ic) ic.className = on ? "fa fa-heart text-danger" : "fa fa-heart-o text-secondary";
            btn.setAttribute("aria-pressed", on ? "true" : "false");
        }
        document.addEventListener("click", function (e) {
            var t = e.target && e.target.closest && e.target.closest(".edi-fav-tgl");
            if (!t) return;
            e.preventDefault();
            var id = parseInt(t.getAttribute("data-fav-id"), 10);
            if (!id) return;
            var a = read();
            var i = a.indexOf(id);
            if (i >= 0) { a.splice(i, 1); paint(t, false); } else { a.push(id); paint(t, true); }
            write(a);
        });
        document.addEventListener("DOMContentLoaded", function () {
            var a = read();
            document.querySelectorAll(".edi-fav-tgl").forEach(function (btn) {
                var id = parseInt(btn.getAttribute("data-fav-id"), 10);
                if (a.indexOf(id) >= 0) paint(btn, true);
            });
        });
    })();
    </script>
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