<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    require_once("./classes/edi_content_tags.php");
    require_once("./classes/edi_taxonomy.php");
    
    $userHeader = new HEADER("blogs");
    $user = new USER();
    $widgets = new WIDGETS();
    $ediConn = $user->getConnection();

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

    $searchTag = isset($_GET["tag"]) ? trim(strip_tags((string) $_GET["tag"])) : "";
    $blogLangFilter = isset($_GET["blog_lang"]) ? trim((string) $_GET["blog_lang"]) : "";
    $blogGradeFilter = isset($_GET["blog_grade"]) ? trim((string) $_GET["blog_grade"]) : "";

    $blogListSqlOther = EdiContentTags::buildBlogListingFilterSql($ediConn, $searchTag, $blogLangFilter, $blogGradeFilter);

    $blogPagingQuery = array();
    if ($searchTag !== "") {
        $blogPagingQuery["tag"] = $searchTag;
    }
    if ($blogLangFilter !== "") {
        $blogPagingQuery["blog_lang"] = $blogLangFilter;
    }
    if ($blogGradeFilter !== "") {
        $blogPagingQuery["blog_grade"] = $blogGradeFilter;
    }
    $pagingUrlParm = $blogPagingQuery === array() ? "" : "&" . http_build_query($blogPagingQuery, "", "&", PHP_QUERY_RFC3986);

    $totalBlogPages = (int) ceil(count($user->fetchAll(array("id"), array("blog_details"), array("status" => 1), "", $blogListSqlOther)) / 6);
    if ($totalBlogPages < 1) {
        $totalBlogPages = 1;
    }
    if (isset($_GET["page"])) {
        $blogPageNo = (int) $_GET["page"];
        if ($blogPageNo < 1) {
            $blogPageNo = 1;
        }
        if ($totalBlogPages < $blogPageNo) {
            $redirQ = $blogPagingQuery;
            $redirQ["page"] = (string) $totalBlogPages;
            $user->redirect("./blogs?" . http_build_query($redirQ, "", "&", PHP_QUERY_RFC3986));
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

    <!-- Blog Start — header + full-width 3-column grid (no empty sidebar) -->
    <div class="container mt-5 page-header-content edi-blogs-page">
            <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0 flex-wrap">
                    <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">The Hidden Den</li>
                </ol>
            </nav>
            <div class="edi-page-title-row mt-2 edi-blogs-page-title-row">
                <h1 class="edi-blogs-main-title">EXCITING THINGS</h1>
                <div class="edi-page-title-rule" role="presentation"></div>
            </div>
            <?php
                $blogTagRows = $user->fetchAll(
                    array("tag"),
                    array("blog_details"),
                    array("status" => 1),
                    "",
                    "1=1"
                );
                $blogsAllTags = EdiContentTags::distinctBlogTopicTagsFromRows($blogTagRows);
                $blogTagBarPreserve = array();
                if ($blogLangFilter !== "") {
                    $blogTagBarPreserve["blog_lang"] = $blogLangFilter;
                }
                if ($blogGradeFilter !== "") {
                    $blogTagBarPreserve["blog_grade"] = $blogGradeFilter;
                }
                $langsFromBlogs = EdiContentTags::distinctBlogLanguagesFromRows($blogTagRows);
                $gradesFromBlogs = EdiContentTags::distinctBlogGradesFromRows($blogTagRows);
                $langTitles = array();
                foreach (EdiTaxonomy::loadLanguages($ediConn) as $lr) {
                    $t = trim((string) ($lr["title"] ?? ""));
                    if ($t !== "") {
                        $langTitles[$t] = true;
                    }
                }
                foreach ($langsFromBlogs as $t) {
                    if ($t !== "") {
                        $langTitles[$t] = true;
                    }
                }
                $blogLangOptions = array_keys($langTitles);
                $blogLangOptions = array_values(array_filter($blogLangOptions, function ($v) {
                    $t = trim((string) $v);
                    if ($t === "") {
                        return false;
                    }
                    $low = strtolower($t);
                    if ($low === "all languages" || $low === "all") {
                        return false;
                    }
                    if (strpos($t, "|||") !== false) {
                        return false;
                    }
                    return true;
                }));
                sort($blogLangOptions, SORT_NATURAL | SORT_FLAG_CASE);
                $gradeTitles = array();
                foreach (EdiTaxonomy::loadGrades($ediConn) as $gr) {
                    $t = trim((string) ($gr["title"] ?? ""));
                    if ($t !== "") {
                        $gradeTitles[$t] = true;
                    }
                }
                foreach ($gradesFromBlogs as $t) {
                    if ($t !== "") {
                        $gradeTitles[$t] = true;
                    }
                }
                $blogGradeOptions = array_keys($gradeTitles);
                $blogGradeOptions = array_values(array_filter($blogGradeOptions, function ($v) {
                    $t = trim((string) $v);
                    if ($t === "") {
                        return false;
                    }
                    $low = strtolower($t);
                    if ($low === "all grades" || $low === "all") {
                        return false;
                    }
                    if (strpos($t, "|||") !== false) {
                        return false;
                    }
                    return true;
                }));
                usort($blogGradeOptions, function ($a, $b) {
                    return EdiTaxonomy::gradeSortKey($a) <=> EdiTaxonomy::gradeSortKey($b) ?: strcasecmp($a, $b);
                });

                $ediBlogFilterHref = function ($langOverride, $gradeOverride) use ($searchTag, $blogLangFilter, $blogGradeFilter) {
                    $lang = $langOverride === false ? $blogLangFilter : $langOverride;
                    $grade = $gradeOverride === false ? $blogGradeFilter : $gradeOverride;
                    $q = array();
                    if ($searchTag !== "") {
                        $q["tag"] = $searchTag;
                    }
                    if ($lang !== "") {
                        $q["blog_lang"] = $lang;
                    }
                    if ($grade !== "") {
                        $q["blog_grade"] = $grade;
                    }
                    return "./blogs" . ($q === array() ? "" : "?" . http_build_query($q, "", "&", PHP_QUERY_RFC3986));
                };
            ?>
            <div class="edi-blog-filter-toolbar d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
                <div class="edi-blog-filter-toolbar__tags flex-grow-1" style="min-width:min(100%, 220px);">
                    <?php
                    if ($blogsAllTags !== array()) {
                        echo EdiContentTags::renderBlogsTreasuresStyleTagBar($blogsAllTags, $searchTag, 12, "hidden-den", $blogTagBarPreserve);
                    }
                    ?>
                </div>
                <div class="edi-blog-lang-grade-filters d-flex flex-wrap align-items-end gap-2 mb-0">
                    <div class="edi-blog-filter-dd">
                        <span class="sr-only" id="edi-blog-lang-filter-label">Language</span>
                        <div class="edi-blog-filter-dd__control">
                            <details class="edi-blog-filter-custom" aria-labelledby="edi-blog-lang-filter-label">
                                <summary class="edi-blog-filter-select edi-blog-filter-custom__summary" title="Filter by language">
                                    <?php echo htmlspecialchars($blogLangFilter !== "" ? $blogLangFilter : "All languages", ENT_QUOTES, "UTF-8"); ?>
                                </summary>
                                <div class="edi-blog-filter-custom__panel" role="listbox" aria-label="Languages">
                                    <a class="edi-blog-filter-custom__opt<?php echo $blogLangFilter === "" ? " is-selected" : ""; ?>" role="option" href="<?php echo htmlspecialchars($ediBlogFilterHref("", false), ENT_QUOTES, "UTF-8"); ?>">All languages</a>
                                    <?php foreach ($blogLangOptions as $opt) : ?>
                                    <a class="edi-blog-filter-custom__opt<?php echo $blogLangFilter === $opt ? " is-selected" : ""; ?>" role="option" href="<?php echo htmlspecialchars($ediBlogFilterHref($opt, false), ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($opt, ENT_QUOTES, "UTF-8"); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                            <span class="edi-blog-filter-dd__chev" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="edi-blog-filter-dd">
                        <span class="sr-only" id="edi-blog-grade-filter-label">Grade</span>
                        <div class="edi-blog-filter-dd__control">
                            <details class="edi-blog-filter-custom" aria-labelledby="edi-blog-grade-filter-label">
                                <summary class="edi-blog-filter-select edi-blog-filter-custom__summary" title="Filter by grade">
                                    <?php echo htmlspecialchars($blogGradeFilter !== "" ? $blogGradeFilter : "All grades", ENT_QUOTES, "UTF-8"); ?>
                                </summary>
                                <div class="edi-blog-filter-custom__panel" role="listbox" aria-label="Grades">
                                    <a class="edi-blog-filter-custom__opt<?php echo $blogGradeFilter === "" ? " is-selected" : ""; ?>" role="option" href="<?php echo htmlspecialchars($ediBlogFilterHref(false, ""), ENT_QUOTES, "UTF-8"); ?>">All grades</a>
                                    <?php foreach ($blogGradeOptions as $opt) : ?>
                                    <a class="edi-blog-filter-custom__opt<?php echo $blogGradeFilter === $opt ? " is-selected" : ""; ?>" role="option" href="<?php echo htmlspecialchars($ediBlogFilterHref(false, $opt), ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($opt, ENT_QUOTES, "UTF-8"); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                            <span class="edi-blog-filter-dd__chev" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row edi-blogs-grid pb-3">
                        <?php
                            if ($blogPageNo > 1) {
                                $limit = ($blogPageNo - 1) * 6;
                                $prevSlice = $user->fetchAll(array("id"), array("blog_details"), array("status" => "1"), "id DESC LIMIT $limit", $blogListSqlOther);
                                if (!empty($prevSlice[$limit - 1]["id"])) {
                                    $pivotId = (int) $prevSlice[$limit - 1]["id"];
                                    $other = "id<" . $pivotId . " AND " . $blogListSqlOther;
                                } else {
                                    $other = $blogListSqlOther;
                                }
                            } else {
                                $other = $blogListSqlOther;
                            }
                            foreach ($user->fetchAll(array("id", "tag", "title", "image", "description", "timestamp"), array("blog_details"), array("status" => "1"), "id DESC LIMIT 6", $other) as $row) {
                                echo $widgets->displayBlogBrief($row, "col-sm-6 col-lg-4", 220, "list");
                            }
                        ?>
                        <div class="col-12 mt-2">
                            <?php
                                $previousBtn = $nextBtn = "";
                                if ( $blogPageNo == 1 ) {
                                    $previousBtn = "disabled";
                                } 
                                if ( $blogPageNo == $totalBlogPages ) {
                                    $nextBtn = "disabled";
                                } 
                                echo "
                                    <nav aria-label='Blog pagination'>
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
            var q = new URLSearchParams(window.location.search);
            var parts = [];
            if (q.has("tag") && q.get("tag") !== "") {
                parts.push("tag=" + encodeURIComponent(q.get("tag")));
            }
            if (q.has("blog_lang") && q.get("blog_lang") !== "") {
                parts.push("blog_lang=" + encodeURIComponent(q.get("blog_lang")));
            }
            if (q.has("blog_grade") && q.get("blog_grade") !== "") {
                parts.push("blog_grade=" + encodeURIComponent(q.get("blog_grade")));
            }
            var extra = parts.length ? "&" + parts.join("&") : "";
            if (btn === 0) {
                if (blogPage > 1) {
                    location.href = "./blogs?page=" + (blogPage - 1) + extra;
                }
            } else if (btn === 1) {
                if (totalPages > blogPage) {
                    location.href = "./blogs?page=" + (blogPage + 1) + extra;
                }
            }
        }

        (function () {
            document.querySelectorAll(".edi-blog-filter-custom").forEach(function (d) {
                d.addEventListener("toggle", function () {
                    if (!d.open) {
                        return;
                    }
                    document.querySelectorAll(".edi-blog-filter-custom").forEach(function (o) {
                        if (o !== d) {
                            o.open = false;
                        }
                    });
                });
            });
        })();
    </script>
</body>

</html>