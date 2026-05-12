<?php
// session_start(); // Can be removed if not using PHP sessions elsewhere
require_once("./classes/class.user.php");
require_once("./classes/edi_taxonomy.php");
require_once("./classes/class.header.php");
require_once("./classes/class.widgets.php");
require_once("./classes/edi_discount_badge.php");
require_once("./classes/edi_explorer_content.php");
require_once("./classes/edi_content_tags.php");

$userHeader = new HEADER("shop");
$user = new USER();
$widgets = new WIDGETS();

$conn = $user->getConnection();

$hasProductSubcategoryColumn = false;
try {
    $colStmt = $conn->query("SHOW COLUMNS FROM products LIKE 'product_subcategory_id'");
    $hasProductSubcategoryColumn = $colStmt && $colStmt->rowCount() > 0;
} catch (Throwable $e) {
    $hasProductSubcategoryColumn = false;
}

// --- 1. Fetch Filter Options from DB ---
$categories = array();
try {
    $catStmt = $conn->query(
        "SELECT DISTINCT c.id, c.name
         FROM product_categories c
         INNER JOIN products p ON p.category_id = c.id
         WHERE p.status = 1
         ORDER BY c.name ASC"
    );
    if ($catStmt) {
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $categories = array();
}
$ageGroups = $user->fetchAll(array("DISTINCT age_group"), array("products"), array("status" => 1));
$brands = $user->fetchAll(array("DISTINCT brand"), array("products"), array("status" => 1));
$shopGrades = EdiTaxonomy::loadGrades($conn);

$productSubcategoriesAll = array();
try {
    $pscStmt = $conn->query("SELECT id, product_category_id, title FROM product_subcategories ORDER BY product_category_id ASC, title ASC");
    if ($pscStmt) {
        $productSubcategoriesAll = EdiExplorerContent::dedupeProductSubcategoryRows($pscStmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Throwable $e) {
    $productSubcategoriesAll = array();
}

$ageOptionTitles = array();
foreach ($shopGrades as $gr) {
    $t = trim((string) ($gr["title"] ?? ""));
    if ($t !== "") {
        $ageOptionTitles[$t] = true;
    }
}
foreach ($ageGroups as $row) {
    $t = trim((string) ($row["age_group"] ?? ""));
    if ($t !== "") {
        $ageOptionTitles[$t] = true;
    }
}
$ageOptionList = array_keys($ageOptionTitles);
$ageOptionList = array_values(array_filter($ageOptionList, function ($t) {
    return !EdiTaxonomy::isNumericGradeAboveFive($t);
}));
usort($ageOptionList, function ($a, $b) {
    return EdiTaxonomy::gradeSortKey($a) <=> EdiTaxonomy::gradeSortKey($b) ?: strcasecmp($a, $b);
});

// --- 2. Handle Filtering Logic ---
$catF = isset($_GET["category"]) ? trim((string) $_GET["category"]) : "";
$ageF = isset($_GET["age"]) ? trim((string) $_GET["age"]) : "";
$brandF = isset($_GET["brand"]) ? trim((string) $_GET["brand"]) : "";
$priceF = isset($_GET["price"]) ? trim((string) $_GET["price"]) : "";
$offerF = isset($_GET["offers"]) ? trim((string) $_GET["offers"]) : "";
$langF = isset($_GET["lang"]) ? trim((string) $_GET["lang"]) : "";
$subF = isset($_GET["sub"]) ? (int) $_GET["sub"] : 0;
$mcatF = isset($_GET["main_cat_id"]) ? (int) $_GET["main_cat_id"] : 0;
$scatF = isset($_GET["sub_cat_id"]) ? (int) $_GET["sub_cat_id"] : 0;

// Homepage EXPLORE: worksheet ws_* taxonomy (preferred when enabled) or Honey Market product_category ids.
$exploreProductCatId = isset($_GET["product_category_id"]) ? (int) $_GET["product_category_id"] : 0;
$exploreProductSubId = isset($_GET["product_subcategory_id"]) ? (int) $_GET["product_subcategory_id"] : 0;
$exploreWsCatId = isset($_GET["ws_category_id"]) ? (int) $_GET["ws_category_id"] : 0;
$exploreWsSubId = isset($_GET["ws_subcategory_id"]) ? (int) $_GET["ws_subcategory_id"] : 0;
$forceExplorerWs = EdiExplorerContent::worksheetWsExplorerReady($conn) && $exploreWsCatId > 0;
$forceExplorer = false;
if ($forceExplorerWs) {
    $forceExplorer = true;
} elseif ($exploreProductCatId > 0) {
    $forceExplorer = true;
    $mapped = EdiExplorerContent::mapProductSelectionsToContentCategoryIds($conn, $exploreProductCatId, $exploreProductSubId);
    $mcatF = isset($mapped["main_cat_id"]) && $mapped["main_cat_id"] ? (int) $mapped["main_cat_id"] : 0;
    $scatF = isset($mapped["sub_cat_id"]) && $mapped["sub_cat_id"] ? (int) $mapped["sub_cat_id"] : 0;
}

// Homepage EXPLORE (content category): free resources only — no Honey Market products.
$products = array();
if ($mcatF === 0 && !$forceExplorer) {
    $query = "SELECT * FROM products WHERE status = 1";
    $params = array();

    if ($catF !== "") {
        $query .= " AND category_id = :cat";
        $params[":cat"] = $catF;
    }
    if ($ageF !== "") {
        $query .= " AND TRIM(COALESCE(age_group, '')) = :age";
        $params[":age"] = $ageF;
    }
    if ($brandF !== "") {
        $query .= " AND brand = :brand";
        $params[":brand"] = $brandF;
    }
    if ($langF !== "") {
        $query .= " AND LOWER(TRIM(COALESCE(language, ''))) = LOWER(:lang)";
        $params[":lang"] = $langF;
    }
    if ($subF > 0 && $hasProductSubcategoryColumn) {
        $query .= " AND product_subcategory_id = :psub";
        $params[":psub"] = $subF;
    }

    if ($offerF === "available") {
        $query .= " AND discount_percentage > 0";
    }

    if ($priceF === "low") {
        $query .= " ORDER BY discounted_price ASC";
    } elseif ($priceF === "high") {
        $query .= " ORDER BY discounted_price DESC";
    } else {
        $query .= " ORDER BY id DESC";
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$explorerMergedItems = array();
$explorerMergedTotal = 0;
$ediExplorerPerPage = EdiExplorerContent::explorerWorksPerPage();
$ediExplorerPageNum = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
if ($forceExplorerWs) {
    $explorerMergedTotal = EdiExplorerContent::countMergedExplorerByWsTaxonomy($conn, $langF, $ageF, $exploreWsCatId, $exploreWsSubId);
    $ediExplorerMaxPage = max(1, (int) ceil($explorerMergedTotal / $ediExplorerPerPage));
    if ($ediExplorerPageNum > $ediExplorerMaxPage) {
        $ediExplorerPageNum = $ediExplorerMaxPage;
    }
    $explorerMergedItems = EdiExplorerContent::fetchMergedExplorerPageByWsTaxonomy(
        $conn,
        $langF,
        $ageF,
        $exploreWsCatId,
        $exploreWsSubId,
        $ediExplorerPageNum,
        $ediExplorerPerPage
    );
} elseif ($forceExplorer && $exploreProductCatId > 0) {
    $explorerMergedTotal = EdiExplorerContent::countMergedExplorerByProductTaxonomy($conn, $langF, $ageF, $exploreProductCatId, $exploreProductSubId);
    $ediExplorerMaxPage = max(1, (int) ceil($explorerMergedTotal / $ediExplorerPerPage));
    if ($ediExplorerPageNum > $ediExplorerMaxPage) {
        $ediExplorerPageNum = $ediExplorerMaxPage;
    }
    $explorerMergedItems = EdiExplorerContent::fetchMergedExplorerPageByProductTaxonomy(
        $conn,
        $langF,
        $ageF,
        $exploreProductCatId,
        $exploreProductSubId,
        $ediExplorerPageNum,
        $ediExplorerPerPage
    );
} elseif ($mcatF > 0) {
    $explorerMergedTotal = EdiExplorerContent::countMergedExplorerByContentTaxonomy($conn, $langF, $ageF, $mcatF, $scatF);
    $ediExplorerMaxPage = max(1, (int) ceil($explorerMergedTotal / $ediExplorerPerPage));
    if ($ediExplorerPageNum > $ediExplorerMaxPage) {
        $ediExplorerPageNum = $ediExplorerMaxPage;
    }
    $explorerMergedItems = EdiExplorerContent::fetchMergedExplorerPageByContentTaxonomy(
        $conn,
        $langF,
        $ageF,
        $mcatF,
        $scatF,
        $ediExplorerPageNum,
        $ediExplorerPerPage
    );
}
$noExplorerMerged = ($explorerMergedTotal === 0);

$explorerPdfTags = array();
$explorerBookTags = array();
$explorerHomeworkTags = array();
if ($forceExplorerWs) {
    $explorerPdfTags = EdiContentTags::distinctFromRows(EdiExplorerContent::fetchMatchingTagRowsByWsTaxonomy($conn, "pdf_details", $langF, $ageF, $exploreWsCatId, $exploreWsSubId, 0));
    $explorerBookTags = EdiContentTags::distinctFromRows(EdiExplorerContent::fetchMatchingTagRowsByWsTaxonomy($conn, "books_details", $langF, $ageF, $exploreWsCatId, $exploreWsSubId, 0));
    $explorerHomeworkTags = EdiContentTags::distinctFromRows(EdiExplorerContent::fetchMatchingTagRowsByWsTaxonomy($conn, "homework_details", $langF, $ageF, $exploreWsCatId, $exploreWsSubId, 0));
} elseif ($forceExplorer) {
    $explorerPdfTags = EdiContentTags::distinctFromRows(EdiExplorerContent::fetchMatchingTagRowsByProductTaxonomy($conn, "pdf_details", $langF, $ageF, $exploreProductCatId, $exploreProductSubId, 0));
    $explorerBookTags = EdiContentTags::distinctFromRows(EdiExplorerContent::fetchMatchingTagRowsByProductTaxonomy($conn, "books_details", $langF, $ageF, $exploreProductCatId, $exploreProductSubId, 0));
    $explorerHomeworkTags = EdiContentTags::distinctFromRows(EdiExplorerContent::fetchMatchingTagRowsByProductTaxonomy($conn, "homework_details", $langF, $ageF, $exploreProductCatId, $exploreProductSubId, 0));
} elseif ($mcatF > 0) {
    $explorerPdfTags = EdiContentTags::distinctFromRows(EdiExplorerContent::fetchMatchingTagRows($conn, "pdf_details", $langF, $ageF, $mcatF, $scatF, 500));
    $explorerBookTags = EdiContentTags::distinctFromRows(EdiExplorerContent::fetchMatchingTagRows($conn, "books_details", $langF, $ageF, $mcatF, $scatF, 500));
    $explorerHomeworkTags = EdiContentTags::distinctFromRows(EdiExplorerContent::fetchMatchingTagRows($conn, "homework_details", $langF, $ageF, $mcatF, $scatF, 500));
}

$explorerListQuery = array();
if ($langF !== "") {
    $explorerListQuery["language"] = $langF;
}
if ($ageF !== "") {
    $explorerListQuery["grade"] = $ageF;
}
if ($forceExplorerWs && $exploreWsCatId > 0) {
    $explorerListQuery["ws_category_id"] = (string) $exploreWsCatId;
    if ($exploreWsSubId > 0) {
        $explorerListQuery["ws_subcategory_id"] = (string) $exploreWsSubId;
    }
} elseif ($forceExplorer && $exploreProductCatId > 0) {
    $explorerListQuery["product_category_id"] = (string) $exploreProductCatId;
    if ($exploreProductSubId > 0) {
        $explorerListQuery["product_subcategory_id"] = (string) $exploreProductSubId;
    }
} elseif ($mcatF > 0) {
    $explorerListQuery["main_cat_id"] = (string) $mcatF;
}
if ($scatF > 0) {
    $explorerListQuery["sub_cat_id"] = (string) $scatF;
}

// Breadcrumb trail: Home > explorer filters (no Honey Market segment on this page)
$shopExtraParams = array();
if ($brandF !== "") {
    $shopExtraParams["brand"] = $brandF;
}
if ($priceF !== "") {
    $shopExtraParams["price"] = $priceF;
}
if ($offerF !== "") {
    $shopExtraParams["offers"] = $offerF;
}

$exploreCategoryName = "";
if ($forceExplorer && $exploreProductCatId > 0) {
    foreach ($categories as $cRow) {
        if ((int) $cRow["id"] === (int) $exploreProductCatId) {
            $exploreCategoryName = (string) $cRow["name"];
            break;
        }
    }
    if ($exploreCategoryName === "") {
        try {
            $pcNameStmt = $conn->prepare("SELECT `name` FROM `product_categories` WHERE `id` = ? LIMIT 1");
            $pcNameStmt->execute(array($exploreProductCatId));
            $exploreCategoryName = trim((string) $pcNameStmt->fetchColumn());
        } catch (Throwable $e) {
            $exploreCategoryName = "";
        }
    }
    if ($exploreCategoryName === "") {
        $exploreCategoryName = "Category";
    }
}

$exploreSubcategoryTitle = "";
if ($forceExplorer && $exploreProductSubId > 0) {
    foreach ($productSubcategoriesAll as $sRow) {
        if ((int) $sRow["id"] === (int) $exploreProductSubId) {
            $exploreSubcategoryTitle = (string) $sRow["title"];
            break;
        }
    }
    if ($exploreSubcategoryTitle === "") {
        try {
            $psTitleStmt = $conn->prepare("SELECT `title` FROM `product_subcategories` WHERE `id` = ? LIMIT 1");
            $psTitleStmt->execute(array($exploreProductSubId));
            $exploreSubcategoryTitle = trim((string) $psTitleStmt->fetchColumn());
        } catch (Throwable $e) {
            $exploreSubcategoryTitle = "";
        }
    }
    if ($exploreSubcategoryTitle === "") {
        $exploreSubcategoryTitle = "Subcategory";
    }
}

$exploreWsCategoryName = "";
$exploreWsSubName = "";
if ($forceExplorerWs && $exploreWsCatId > 0) {
    try {
        $wsn = $conn->prepare("SELECT `name` FROM `ws_categories` WHERE `id` = ? LIMIT 1");
        $wsn->execute(array($exploreWsCatId));
        $exploreWsCategoryName = trim((string) $wsn->fetchColumn());
    } catch (Throwable $e) {
        $exploreWsCategoryName = "";
    }
    if ($exploreWsCategoryName === "") {
        $exploreWsCategoryName = "Category";
    }
    if ($exploreWsSubId > 0) {
        try {
            $wsn2 = $conn->prepare("SELECT `name` FROM `ws_subcategories` WHERE `id` = ? LIMIT 1");
            $wsn2->execute(array($exploreWsSubId));
            $exploreWsSubName = trim((string) $wsn2->fetchColumn());
        } catch (Throwable $e) {
            $exploreWsSubName = "";
        }
        if ($exploreWsSubName === "") {
            $exploreWsSubName = "Subcategory";
        }
    }
}

$categoryNameForCrumb = "";
if ($catF !== "") {
    foreach ($categories as $cRow) {
        if ((string) $cRow["id"] === (string) $catF) {
            $categoryNameForCrumb = (string) $cRow["name"];
            break;
        }
    }
    if ($categoryNameForCrumb === "") {
        if (ctype_digit((string) $catF)) {
            try {
                $pcCrumbStmt = $conn->prepare("SELECT `name` FROM `product_categories` WHERE `id` = ? LIMIT 1");
                $pcCrumbStmt->execute(array((int) $catF));
                $categoryNameForCrumb = trim((string) $pcCrumbStmt->fetchColumn());
            } catch (Throwable $e) {
                $categoryNameForCrumb = "";
            }
        }
    }
    if ($categoryNameForCrumb === "") {
        $categoryNameForCrumb = "Category";
    }
}

$subTitleForCrumb = "";
if ($subF > 0) {
    foreach ($productSubcategoriesAll as $sRow) {
        if ((int) $sRow["id"] === $subF) {
            $subTitleForCrumb = (string) $sRow["title"];
            break;
        }
    }
    if ($subTitleForCrumb === "") {
        $subTitleForCrumb = "Subcategory";
    }
}

$contentMainTitle = "";
if ($mcatF > 0) {
    try {
        $mst = $conn->prepare("SELECT `title` FROM `main_category` WHERE `id` = ?");
        $mst->execute(array($mcatF));
        $contentMainTitle = trim((string) $mst->fetchColumn());
    } catch (Throwable $e) {
        $contentMainTitle = "";
    }
    if ($contentMainTitle === "") {
        $contentMainTitle = "Category";
    }
}
$contentSubTitle = "";
if ($scatF > 0) {
    try {
        $sst = $conn->prepare("SELECT `title` FROM `sub_category` WHERE `id` = ?");
        $sst->execute(array($scatF));
        $contentSubTitle = trim((string) $sst->fetchColumn());
    } catch (Throwable $e) {
        $contentSubTitle = "";
    }
    if ($contentSubTitle === "") {
        $contentSubTitle = "Subcategory";
    }
}

$explorerSegments = array();
if ($forceExplorerWs) {
    if ($langF !== "") {
        $explorerSegments[] = array("key" => "lang", "value" => $langF, "label" => $langF);
    }
    if ($ageF !== "") {
        $explorerSegments[] = array("key" => "age", "value" => $ageF, "label" => $ageF);
    }
    $explorerSegments[] = array("key" => "ws_category_id", "value" => (string) $exploreWsCatId, "label" => $exploreWsCategoryName);
    if ($exploreWsSubId > 0) {
        $explorerSegments[] = array("key" => "ws_subcategory_id", "value" => (string) $exploreWsSubId, "label" => $exploreWsSubName);
    }
} elseif ($forceExplorer) {
    // For homepage EXPLORE: breadcrumb should follow selected search criteria.
    if ($langF !== "") {
        $explorerSegments[] = array("key" => "lang", "value" => $langF, "label" => $langF);
    }
    if ($ageF !== "") {
        $explorerSegments[] = array("key" => "age", "value" => $ageF, "label" => $ageF);
    }
    $explorerSegments[] = array("key" => "product_category_id", "value" => (string) $exploreProductCatId, "label" => $exploreCategoryName);
    if ($exploreProductSubId > 0) {
        $explorerSegments[] = array("key" => "product_subcategory_id", "value" => (string) $exploreProductSubId, "label" => $exploreSubcategoryTitle);
    }
} else {
    if ($langF !== "") {
        $explorerSegments[] = array("key" => "lang", "value" => $langF, "label" => $langF);
    }
    if ($ageF !== "") {
        $explorerSegments[] = array("key" => "age", "value" => $ageF, "label" => $ageF);
    }
    if ($mcatF > 0) {
        $explorerSegments[] = array("key" => "main_cat_id", "value" => (string) $mcatF, "label" => $contentMainTitle);
    } elseif ($catF !== "") {
        $explorerSegments[] = array("key" => "category", "value" => $catF, "label" => $categoryNameForCrumb);
    }
    if ($scatF > 0) {
        $explorerSegments[] = array("key" => "sub_cat_id", "value" => (string) $scatF, "label" => $contentSubTitle);
    } elseif ($subF > 0) {
        $explorerSegments[] = array("key" => "sub", "value" => (string) $subF, "label" => $subTitleForCrumb);
    }
}

$treasuresBreadcrumbs = array(
    array("label" => "Home", "href" => "./", "current" => false),
);
if (count($explorerSegments) === 0) {
    $treasuresBreadcrumbs[] = array("label" => "Treasures", "href" => null, "current" => true);
} else {
    $built = array();
    $n = count($explorerSegments);
    for ($i = 0; $i < $n; $i++) {
        $seg = $explorerSegments[$i];
        $built[$seg["key"]] = $seg["value"];
        $q = array_merge($built, $shopExtraParams);
        $isLast = ($i === $n - 1);
        $href = "product_page.php?" . http_build_query($q, "", "&", PHP_QUERY_RFC3986);
        $treasuresBreadcrumbs[] = array(
            "label" => $seg["label"],
            "href" => $isLast ? null : $href,
            "current" => $isLast,
        );
    }
}
$treasuresPageHeading = "TREASURES";
if ($forceExplorerWs && $exploreWsSubId > 0 && $exploreWsSubName !== "") {
    $treasuresPageHeading = $exploreWsSubName;
} elseif ($forceExplorerWs && $exploreWsCategoryName !== "") {
    $treasuresPageHeading = strtoupper($exploreWsCategoryName);
} elseif ($forceExplorer && $exploreProductSubId > 0 && $exploreSubcategoryTitle !== "") {
    $treasuresPageHeading = $exploreSubcategoryTitle;
} elseif ($forceExplorer && $exploreCategoryName !== "") {
    $treasuresPageHeading = strtoupper($exploreCategoryName);
} elseif ($mcatF > 0 && $contentMainTitle !== "") {
    $treasuresPageHeading = strtoupper($contentMainTitle);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader() ?>
    <link rel="stylesheet" href="css/product_style.css">
</head>
<body>
    <?php echo $userHeader->printUserNav(); ?>
    <div class="page-header-bg"></div>
   

    <div class="container mt-5 page-header-content">
                <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-0 flex-wrap">
                        <?php foreach ($treasuresBreadcrumbs as $bc): ?>
                            <?php
                            $rawLabel = (string) $bc["label"];
                            $bcLabel = htmlspecialchars($rawLabel, ENT_QUOTES, "UTF-8");
                            $bcHref = isset($bc["href"]) && $bc["href"] !== null ? htmlspecialchars((string) $bc["href"], ENT_QUOTES, "UTF-8") : "";
                            $isCurrent = !empty($bc["current"]);
                            ?>
                            <?php if ($isCurrent): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $bcLabel; ?></li>
                            <?php elseif ($rawLabel === "Home"): ?>
                                <li class="breadcrumb-item"><a href="<?php echo $bcHref; ?>"><i class="fa fa-home" aria-hidden="true"></i> <?php echo $bcLabel; ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?php echo $bcHref; ?>"><?php echo $bcLabel; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                 
                  <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1><?php echo htmlspecialchars($treasuresPageHeading, ENT_QUOTES, "UTF-8"); ?></h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>
        <?php
        $explorerMergedTagsForBar = array();
        if ($forceExplorer) {
            $explorerMergedTagsForBar = array_values(array_unique(array_merge($explorerPdfTags, $explorerBookTags, $explorerHomeworkTags)));
            sort($explorerMergedTagsForBar, SORT_NATURAL | SORT_FLAG_CASE);
        }
        ?>
        <?php if ($forceExplorer && $explorerMergedTagsForBar !== array()): ?>
        <div class="mt-2 mb-3">
            <?php echo EdiContentTags::renderExplorerCommaTagBarHtml($explorerMergedTagsForBar, "product_page.php", $explorerListQuery, 12, "all", "#edi-explorer-region-unified"); ?>
        </div>
        <?php endif; ?>

        <?php if (!$forceExplorer): ?>
        <form method="GET" action="" class="treasures-filters-form" id="treasures-filters-form" aria-label="Filter treasures">
            <?php if ($mcatF > 0): ?>
            <input type="hidden" name="main_cat_id" value="<?php echo (int) $mcatF; ?>">
            <?php if ($scatF > 0): ?><input type="hidden" name="sub_cat_id" value="<?php echo (int) $scatF; ?>"><?php endif; ?>
            <?php if ($langF !== ""): ?><input type="hidden" name="lang" value="<?php echo htmlspecialchars($langF, ENT_QUOTES, "UTF-8"); ?>"><?php endif; ?>
            <?php if ($ageF !== ""): ?><input type="hidden" name="age" value="<?php echo htmlspecialchars($ageF, ENT_QUOTES, "UTF-8"); ?>"><?php endif; ?>
            <?php else: ?>
            <div class="treasures-filters-row">
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-category">Category</label>
                    <select id="filter-category" name="category" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Category</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= htmlspecialchars((string) $c['id'], ENT_QUOTES, 'UTF-8') ?>" <?= ($catF == (string) $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-age">Grade</label>
                    <select id="filter-age" name="age" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Grade</option>
                        <?php foreach ($ageOptionList as $ageTitle): ?>
                            <option value="<?= htmlspecialchars($ageTitle, ENT_QUOTES, 'UTF-8') ?>" <?= ($ageF === $ageTitle) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ageTitle, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-brand">Brands</label>
                    <select id="filter-brand" name="brand" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Brands</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= htmlspecialchars((string) $b['brand'], ENT_QUOTES, 'UTF-8') ?>" <?= ($brandF === $b['brand']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $b['brand'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-price">Price</label>
                    <select id="filter-price" name="price" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Price</option>
                        <option value="low" <?= ($priceF === 'low') ? 'selected' : '' ?>>Low to High</option>
                        <option value="high" <?= ($priceF === 'high') ? 'selected' : '' ?>>High to Low</option>
                    </select>
                </div>
                <div class="treasures-filter-cell">
                    <label class="sr-only" for="filter-offers">Offers</label>
                    <select id="filter-offers" name="offers" class="form-control treasures-filter-select" onchange="this.form.submit()">
                        <option value="">Offers</option>
                        <option value="available" <?= ($offerF === 'available') ? 'selected' : '' ?>>Available</option>
                    </select>
                </div>
            </div>
            <?php endif; ?>
        </form>

        <div class="row treasures-product-grid mt-2">
            <?php
            $noExplorer = $noExplorerMerged;
            $noProducts = empty($products);
            $filteredNoResult = $noProducts && ( ($mcatF > 0 && $noExplorer) || ($mcatF === 0 && $catF !== "") );
            ?>
            <?php if ($filteredNoResult): ?>
                <div class="col-12 text-center py-5"><h4>No treasures found for these filters.</h4></div>
            <?php elseif ($noProducts && $mcatF === 0 && $catF === ""): ?>
                <div class="col-12 text-center py-5"><h4>No treasures found!</h4></div>
            <?php endif; ?>
            <?php if (!empty($products)) : foreach($products as $p): ?>
                <?php
                $pid = (int) $p['id'];
                $pname = htmlspecialchars((string) $p['product_name'], ENT_QUOTES, 'UTF-8');
                $pimg = htmlspecialchars((string) $p['image'], ENT_QUOTES, 'UTF-8');
                $discountPct = edi_discount_badge_pct($p);
                ?>
                <div class="col-lg-3 col-md-6 text-center mb-4">
                    <div class="product-card">
                        <div class="product-card-thumb-wrap">
                        <?php if ($discountPct !== null) { ?>
                            <span class="edi-discount-hex" aria-label="<?= (int) $discountPct; ?> percent off"><?= (int) $discountPct; ?>%</span>
                        <?php } ?>
                        <a href="product_details.php?product_id=<?= $pid ?>">
                            <img src="./img/products/<?= $pimg ?>" class="product-img cart-product-image" alt="<?= $pname ?>">
                        </a>
                        </div>
                        <h6 class="mt-3" style="text-align: left; padding-left: 5px;">
                            <a href="product_details.php?product_id=<?= $pid ?>" style="text-decoration: none; color: inherit;"><?= $pname ?></a>
                        </h6>
                        <div class="price" style="text-align: left; padding-left: 5px;">
                            <?php if ((float) $p['discounted_price'] > 0): ?>
                                <span class="old-price">LKR <?= number_format((float) $p['price'], 2, '.', '') ?></span>
                                <span class="new-price">LKR <?= number_format((float) $p['discounted_price'], 2, '.', '') ?></span>
                            <?php else: ?>
                                <span class="new-price">LKR <?= number_format((float) $p['price'], 2, '.', '') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-cart-row">
                            <form class="cart-form m-0 p-0">
                                <input type="hidden" name="product_id" value="<?= $pid ?>">
                                <button type="button" class="btn newgreen1-btn collect-btn add-to-cart-btn">Collect</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <?php endif; ?>

        <?php
        if ($forceExplorer && $noExplorerMerged):
        ?>
            <div class="col-12 text-center py-5"><h4>No resources found for these filters.</h4></div>
        <?php
        endif;
        if (($forceExplorer || $mcatF > 0) && !$noExplorerMerged):
            $ediExplorerPagBase = array();
            foreach (array('lang', 'age', 'ws_category_id', 'ws_subcategory_id', 'product_category_id', 'product_subcategory_id', 'main_cat_id', 'sub_cat_id', 'category', 'sub', 'brand', 'price', 'offers') as $ediPagKey) {
                if (!isset($_GET[$ediPagKey])) {
                    continue;
                }
                $ediPagVal = $_GET[$ediPagKey];
                if ($ediPagVal === '' || $ediPagVal === null) {
                    continue;
                }
                $ediExplorerPagBase[$ediPagKey] = is_scalar($ediPagVal) ? (string) $ediPagVal : '';
            }
            $ediExplorerTotalPages = max(1, (int) ceil($explorerMergedTotal / $ediExplorerPerPage));
        ?>
        <div class="mt-3">
            <div class="mb-2">
                <div id="edi-explorer-region-unified" class="edi-explorer-filter-region">
                <div class="row">
                    <?php
                    foreach ($explorerMergedItems as $ediExItem) {
                        $ediK = (string) ($ediExItem['ws_kind'] ?? '');
                        $ediRow = isset($ediExItem['row']) && is_array($ediExItem['row']) ? $ediExItem['row'] : array();
                        if ($ediK === 'pdf') {
                            echo $widgets->displaypdfBrief($ediRow, false, "col-md-3", 200, $forceExplorer);
                        } elseif ($ediK === 'books') {
                            echo $widgets->displaybooksBrief(false, $ediRow, "col-md-3", 200, $forceExplorer);
                        } elseif ($ediK === 'homework') {
                            echo $widgets->displayhomeworkBrief($ediRow, false, "col-md-3", 200, $forceExplorer);
                        }
                    }
                    ?>
                </div>
                </div>
            </div>
            <?php if ($ediExplorerTotalPages > 1): ?>
            <nav class="edi-explorer-pager mt-4 d-flex flex-wrap justify-content-center align-items-center gap-2" aria-label="Worksheet pages">
                <?php
                if ($ediExplorerPageNum > 1) {
                    $ediPq = $ediExplorerPagBase;
                    $ediPrev = $ediExplorerPageNum - 1;
                    if ($ediPrev <= 1) {
                        unset($ediPq['page']);
                    } else {
                        $ediPq['page'] = (string) $ediPrev;
                    }
                    $ediPrevHref = 'product_page.php?' . http_build_query($ediPq, '', '&', PHP_QUERY_RFC3986);
                    echo '<a class="btn btn-sm btn-outline-secondary" href="' . htmlspecialchars($ediPrevHref, ENT_QUOTES, 'UTF-8') . '">Previous</a>';
                }
                $ediWindow = 5;
                $ediStart = max(1, $ediExplorerPageNum - (int) floor($ediWindow / 2));
                $ediEnd = min($ediExplorerTotalPages, $ediStart + $ediWindow - 1);
                if ($ediEnd - $ediStart + 1 < $ediWindow) {
                    $ediStart = max(1, $ediEnd - $ediWindow + 1);
                }
                for ($ediPi = $ediStart; $ediPi <= $ediEnd; $ediPi++) {
                    $ediPq = $ediExplorerPagBase;
                    if ($ediPi <= 1) {
                        unset($ediPq['page']);
                    } else {
                        $ediPq['page'] = (string) $ediPi;
                    }
                    $ediPHref = 'product_page.php?' . http_build_query($ediPq, '', '&', PHP_QUERY_RFC3986);
                    $ediIsCur = ($ediPi === $ediExplorerPageNum);
                    if ($ediIsCur) {
                        echo '<span class="btn btn-sm btn-success" aria-current="page">' . (int) $ediPi . '</span>';
                    } else {
                        echo '<a class="btn btn-sm btn-outline-secondary" href="' . htmlspecialchars($ediPHref, ENT_QUOTES, 'UTF-8') . '">' . (int) $ediPi . '</a>';
                    }
                }
                if ($ediExplorerPageNum < $ediExplorerTotalPages) {
                    $ediPq = $ediExplorerPagBase;
                    $ediPq['page'] = (string) ($ediExplorerPageNum + 1);
                    $ediNextHref = 'product_page.php?' . http_build_query($ediPq, '', '&', PHP_QUERY_RFC3986);
                    echo '<a class="btn btn-sm btn-outline-secondary" href="' . htmlspecialchars($ediNextHref, ENT_QUOTES, 'UTF-8') . '">Next</a>';
                }
                ?>
            </nav>
            <p class="text-center text-muted small mt-2 mb-0">
                Page <?php echo (int) $ediExplorerPageNum; ?> of <?php echo (int) $ediExplorerTotalPages; ?>
                (<?php echo (int) $explorerMergedTotal; ?> worksheets)
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php echo $userHeader->printUserFooter(); ?>

    <script>
        document.querySelectorAll(".add-to-cart-btn").forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();

                // 1. Check LocalStorage for User Session
                const userSession = localStorage.getItem('user_session');

                if (!userSession) {
                    // If your system uses a function showLoginPopup(), call it. 
                    // Otherwise, redirect to login.
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

                // --- Animation Logic ---
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

                    setTimeout(() => { imgClone.remove(); }, 800);
                }

                // 2. Prepare Data with LocalStorage UID
                const formData = new FormData(form);
                formData.append('uid', userSession); // Add the user ID to the request

                // 3. Send Ajax Request to add_to_cart.php
                fetch("add_to_cart.php", {
                    method: "POST",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    // increase cart count
let count = localStorage.getItem('cart_count');
count = count ? parseInt(count, 10) : 0;
localStorage.setItem('cart_count', String(count + 1));
if (typeof window.edibearSyncCartBadge === 'function') {
    window.edibearSyncCartBadge();
}
                    /* CART BOUNCE EFFECT */
                    if (cartIcon) {
                        cartIcon.classList.add("bounce");
                        setTimeout(() => cartIcon.classList.remove("bounce"), 400);
                    }
                });
            });
        });
    </script>
    <?php if ($forceExplorer): ?>
    <script>
    (function () {
        function norm(s) {
            return String(s || "").trim().toLowerCase();
        }
        function applyFilter(bar) {
            var targetSel = bar.getAttribute("data-edi-filter-target");
            if (!targetSel) return;
            var region = document.querySelector(targetSel);
            if (!region) return;
            var active = bar.querySelector(".edi-explorer-tag-btn.is-selected");
            var filterTag = active ? (active.getAttribute("data-edi-tag") || "") : "";
            var ft = norm(filterTag);
            region.querySelectorAll(".edi-explorer-filter-card").forEach(function (card) {
                var ok = !ft;
                if (ft) {
                    ok = false;
                    var raw = card.getAttribute("data-edi-tags");
                    if (raw) {
                        try {
                            var tags = JSON.parse(raw);
                            if (Array.isArray(tags)) {
                                ok = tags.some(function (t) { return norm(t) === ft; });
                            }
                        } catch (e) { ok = false; }
                    }
                }
                card.classList.toggle("d-none", !ok);
            });
        }
        document.addEventListener("click", function (ev) {
            var btn = ev.target.closest && ev.target.closest(".edi-explorer-tag-bar--filter .edi-explorer-tag-btn");
            if (!btn) return;
            ev.preventDefault();
            var bar = btn.closest(".edi-explorer-tag-bar--filter");
            if (!bar) return;
            if (btn.classList.contains("is-selected")) {
                btn.classList.remove("is-selected");
                btn.setAttribute("aria-pressed", "false");
            } else {
                bar.querySelectorAll(".edi-explorer-tag-btn.is-selected").forEach(function (b) {
                    b.classList.remove("is-selected");
                    b.setAttribute("aria-pressed", "false");
                });
                btn.classList.add("is-selected");
                btn.setAttribute("aria-pressed", "true");
            }
            applyFilter(bar);
        });
    })();
    </script>
    <?php endif; ?>
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
</body>
</html>