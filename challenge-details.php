<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");

    $user = new USER();
    $userHeader = new HEADER("challenges");

    $eventId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    $eventQuery = "SELECT e.*, c.name as cat_name 
                   FROM braveheart_events e 
                   LEFT JOIN braveheart_categories c ON e.category_id = c.id 
                   WHERE e.id = :id AND e.status = 1";
    $stmt = $user->getConnection()->prepare($eventQuery);
    $stmt->execute(array('id' => $eventId));
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event || !is_array($event)) {
        header("Location: challenges.php");
        exit;
    }

    $winnerQuery = "SELECT * FROM braveheart_winners WHERE event_id = :id ORDER BY id ASC";
    $stmtWin = $user->getConnection()->prepare($winnerQuery);
    $stmtWin->execute(array('id' => $eventId));
    $winners = $stmtWin->fetchAll(PDO::FETCH_ASSOC);

    $today = date('Y-m-d');
    $isUpcoming = (isset($event['deadline_date']) && (string) $event['deadline_date'] >= $today);

    $eventTitle = (string) ($event['title'] ?? '');
    $catName = trim((string) ($event['cat_name'] ?? ''));
    $mainImage = (string) ($event['main_image'] ?? '');
    $appFile = trim((string) ($event['application_file'] ?? ''));
    $descRaw = (string) ($event['description'] ?? '');

    /* Admin/DB may store HTML entities; decode once before htmlspecialchars to avoid literal "&#039;" on screen */
    $eventTitlePlain = html_entity_decode($eventTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $catNamePlain = html_entity_decode($catName, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $descPlain = html_entity_decode($descRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '/challenge-details.php';
    $dir = dirname($script);
    $base = ($dir === '/' || $dir === '.' || $dir === '') ? '' : rtrim($dir, '/');
    $detailPath = ($base === '' ? '' : $base) . '/challenge-details.php';
    $pageUrl = ($host !== '') ? ($scheme . '://' . $host . $detailPath . '?id=' . $eventId) : ('./challenge-details.php?id=' . $eventId);

    $shareUrl = rawurlencode($pageUrl);
    $shareTitle = rawurlencode($eventTitlePlain);
    $shareText = rawurlencode($eventTitlePlain . ' — ');

    $fbShare = "https://www.facebook.com/sharer/sharer.php?u=" . $shareUrl;
    $twShare = "https://twitter.com/intent/tweet?url=" . $shareUrl . "&text=" . $shareTitle;
    $pinShare = "https://pinterest.com/pin/create/button/?url=" . $shareUrl . "&description=" . $shareTitle;
    $waShare = "https://api.whatsapp.com/send?text=" . $shareText . $shareUrl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader($eventTitlePlain, strip_tags($descPlain), $mainImage !== '' ? ('img/braveheart/' . $mainImage) : ''); ?>
</head>
<body class="challenge-details-page">
    <?php echo $userHeader->printUserNav(); ?>

    <div class="page-header-bg"></div>

    <div class="container-fluid mt-5 page-header-content pb-5 edi-challenge-detail-outer px-0">
        <div class="container edi-challenge-detail-inner px-lg-4">
            <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0 flex-wrap">
                    <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="./challenges.php">Brave Heart Challenge</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Challenge</li>
                </ol>
            </nav>

            <div class="edi-page-title-row mt-2">
                <h1>CHALLENGE DETAILS</h1>
                <div class="edi-page-title-rule" role="presentation"></div>
            </div>

            <?php if ($mainImage !== ''): ?>
            <div class="edi-challenge-hero mt-3 mb-3">
                <img src="./img/braveheart/<?php echo htmlspecialchars($mainImage, ENT_QUOTES, 'UTF-8'); ?>"
                     class="edi-challenge-hero__img img-fluid"
                     alt="<?php echo htmlspecialchars($eventTitlePlain, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <?php endif; ?>

            <div class="edi-blog-single-meta-share mb-3">
                <div class="edi-blog-single-meta">
                    <?php if ($catName !== ''): ?>
                    <i class="fa fa-tag fa-sm text-warning p-1" aria-hidden="true"></i>
                    <span class="text-warning"><?php echo htmlspecialchars($catNamePlain, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="edi-blog-single-share" aria-label="Share this challenge">
                    <span class="edi-blog-share-label">SHARE</span>
                    <div class="edi-blog-share-icons">
                        <a class="edi-blog-share-btn" href="<?php echo htmlspecialchars($fbShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" title="Facebook" aria-label="Share on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                        <a class="edi-blog-share-btn" href="<?php echo htmlspecialchars($twShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" title="Twitter" aria-label="Share on Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                        <a class="edi-blog-share-btn" href="<?php echo htmlspecialchars($pinShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" title="Pinterest" aria-label="Share on Pinterest"><i class="fab fa-pinterest-p" aria-hidden="true"></i></a>
                        <a class="edi-blog-share-btn" href="<?php echo htmlspecialchars($waShare, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" title="WhatsApp" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>

            <h2 class="edi-challenge-detail-title"><?php echo htmlspecialchars(strtoupper($eventTitlePlain), ENT_QUOTES, 'UTF-8'); ?></h2>

            <article class="edi-blog-single-article">
                <div class="edi-blog-single-prose text-justify edi-challenge-detail-body">
                    <?php
                    $ediBhAllowed = '<p><br><br/><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><h5><h6><span><div><blockquote>';
                    $ediBhHasTags = ($descPlain !== '' && preg_match('/<[a-z][\s\S]*>/i', $descPlain));
                    if ($ediBhHasTags) {
                        echo strip_tags($descPlain, $ediBhAllowed);
                    } else {
                        echo nl2br(htmlspecialchars($descPlain, ENT_QUOTES, 'UTF-8'));
                    }
                    ?>
                </div>
            </article>

            <?php if ($appFile !== ''): ?>
            <div class="edi-challenge-app-row" role="region" aria-label="Application download">
                <div class="edi-challenge-app-label">
                    <h3 class="edi-challenge-app-heading mb-0">Application &amp; Details</h3>
                    <?php if (!$isUpcoming): ?>
                    <small class="text-muted d-block mt-1">This challenge has ended.</small>
                    <?php endif; ?>
                </div>
                <div class="edi-challenge-app-actions">
                    <?php if ($isUpcoming): ?>
                    <a href="./img/braveheart/<?php echo htmlspecialchars($appFile, ENT_QUOTES, 'UTF-8'); ?>"
                       class="btn edi-challenge-download-btn"
                       download>Download</a>
                    <?php else: ?>
                    <button type="button" class="btn edi-challenge-download-btn edi-challenge-download-btn--disabled" disabled>Application closed</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!$isUpcoming): ?>
            <h3 class="edi-challenge-winners-title mt-5">Winner's Details</h3>
            <div class="row mt-3 edi-challenge-winners-row">
                <?php if (!empty($winners)): ?>
                    <?php foreach ($winners as $winner): ?>
                        <?php
                        $wImg = (string) ($winner['image'] ?? '');
                        $wTitle = (string) ($winner['title'] ?? '');
                        $wTitlePlain = html_entity_decode($wTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        ?>
                <div class="col-md-4 mb-3">
                    <div class="edi-challenge-winner-card text-center h-100">
                        <div class="edi-challenge-winner-thumb">
                            <img src="./img/braveheart/<?php echo htmlspecialchars($wImg, ENT_QUOTES, 'UTF-8'); ?>"
                                 class="edi-challenge-winner-img"
                                 alt=""
                                 onerror="this.src='./img/placeholder-winner.jpg';">
                        </div>
                        <div class="edi-challenge-winner-caption">
                            <?php echo htmlspecialchars($wTitlePlain, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="col-12">
                    <p class="text-muted mb-0">Winners will be announced soon!</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php echo $userHeader->printUserFooter(); ?>
</body>
</html>
