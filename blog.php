<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");
    require_once("./classes/edi_blog_extra_media.php");
require_once("./classes/edi_blog_story_sections.php");
    require_once("./classes/edi_content_tags.php");

    $userHeader = new HEADER("blogs");
    $user = new USER();
    $widgets = new WIDGETS();

    if (!isset($_GET['id']) || (int) $_GET['id'] <= 0) {
        $user->redirect("./blogs");
    }

    $blogID = (int) $_GET['id'];

    if (!$user->IsExist("blog_details", "id", $blogID)) {
        $user->redirect("./blogs");
    }

    $blogDetailsArr = $user->fetchAll(
        array("tag", "title", "description", "image", "video", "video_status", "timestamp"),
        array("blog_details"),
        array("id" => $blogID)
    )[0];

    $blogTag = $blogDetailsArr['tag'];
    $blogTitle = $blogDetailsArr['title'];
    $blogMainDescription = $blogDetailsArr['description'];
    $blogVideoUrl = $blogDetailsArr['video'];
    $blogVideoStatus = (int) ($blogDetailsArr['video_status'] ?? 0);

    $blogDate = date("d M Y", strtotime(substr($blogDetailsArr['timestamp'], 0, 10)));
    $blogMainImage = $widgets->createCachelessImage("./img/blogs/" . $blogDetailsArr['image']);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '/blog.php';
    $dir = dirname($script);
    $base = ($dir === '/' || $dir === '.' || $dir === '') ? '' : rtrim($dir, '/');
    $blogPath = ($base === '' ? '' : $base) . '/blog';
    $blogPageUrl = ($host !== '') ? ($scheme . '://' . $host . $blogPath . '?id=' . $blogID) : ('./blog?id=' . $blogID);

    $shareUrl = rawurlencode($blogPageUrl);
    $shareTitle = rawurlencode((string) $blogTitle);
    $shareText = rawurlencode((string) $blogTitle . ' — ');

    $fbShare = "https://www.facebook.com/sharer/sharer.php?u=" . $shareUrl;
    $twShare = "https://twitter.com/intent/tweet?url=" . $shareUrl . "&text=" . $shareTitle;
    $pinShare = "https://pinterest.com/pin/create/button/?url=" . $shareUrl . "&description=" . $shareTitle;
    $waShare = "https://api.whatsapp.com/send?text=" . $shareText . $shareUrl;

    $blogExtraMedia = EdiBlogExtraMedia::fetchForBlog($user->getConnection(), $blogID);
$blogPostTags = EdiContentTags::blogTopicTagsFromCell($blogTag);
$blogMetaTagText = "";
if (!empty($blogPostTags)) {
    $blogMetaTagText = implode(", ", $blogPostTags);
}
if ($blogMetaTagText === "") {
    $blogMetaTagText = EdiContentTags::blogCategoryDisplayLabel($blogTag);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <?php echo $userHeader->printUserHeader($blogTitle, str_replace("'", "", strip_tags($blogMainDescription)), substr($blogMainImage, 2)) ?>
    <link rel="stylesheet" href="css/product_details.css">
</head>

<body class="edi-blog-single-page">
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v16.0" nonce="ndpmmHDo"></script>
    <?php
        echo $userHeader->printUserNav();
    ?>
    <div class="page-header-bg"></div>

    <div class="container-fluid mt-5 page-header-content pb-5 edi-blog-single-outer px-0">
        <div class="container edi-blog-single-inner px-lg-4">
            <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0 flex-wrap">
                    <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="./blogs">The Hidden Den</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Exciting Things</li>
                </ol>
            </nav>

            <div class="edi-page-title-row edi-blogs-page-title-row mt-2">
                <h1 class="edi-blogs-main-title"><?php echo htmlspecialchars(strtoupper((string) $blogTitle), ENT_QUOTES, "UTF-8"); ?></h1>
                <div class="edi-page-title-rule" role="presentation"></div>
            </div>

            <div class="edi-blog-single-featured mt-3 mb-3">
                <img src="<?php echo htmlspecialchars((string) $blogMainImage, ENT_QUOTES, "UTF-8"); ?>" class="edi-blog-single-featured__img img-fluid" alt="<?php echo htmlspecialchars((string) $blogTitle, ENT_QUOTES, "UTF-8"); ?>">
            </div>

            <div class="edi-blog-single-meta-share mb-3">
                <div class="edi-blog-single-meta">
                    <i class="fa fa-tag fa-sm text-warning p-1" aria-hidden="true"></i>
                    <span class="text-warning"><?php echo htmlspecialchars((string) $blogMetaTagText, ENT_QUOTES, "UTF-8"); ?></span>
                </div>
                <div class="edi-blog-single-share" aria-label="Share this post">
                    <span class="edi-blog-share-label">SHARE</span>
                    <div class="edi-blog-share-icons">
                        <a class="edi-blog-share-btn" href="<?php echo htmlspecialchars($fbShare, ENT_QUOTES, "UTF-8"); ?>" target="_blank" rel="noopener noreferrer" title="Facebook" aria-label="Share on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                        <a class="edi-blog-share-btn" href="<?php echo htmlspecialchars($twShare, ENT_QUOTES, "UTF-8"); ?>" target="_blank" rel="noopener noreferrer" title="Twitter" aria-label="Share on Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                        <a class="edi-blog-share-btn" href="<?php echo htmlspecialchars($pinShare, ENT_QUOTES, "UTF-8"); ?>" target="_blank" rel="noopener noreferrer" title="Pinterest" aria-label="Share on Pinterest"><i class="fab fa-pinterest-p" aria-hidden="true"></i></a>
                        <a class="edi-blog-share-btn" href="<?php echo htmlspecialchars($waShare, ENT_QUOTES, "UTF-8"); ?>" target="_blank" rel="noopener noreferrer" title="WhatsApp" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>

            <article class="edi-blog-single-article">
                <div class="edi-blog-single-prose text-justify">
                    <?php echo $blogMainDescription; ?>
                </div>

                <?php
                foreach (EdiBlogStorySections::fetchForBlog($user->getConnection(), $blogID) as $blogSubArr) {
                    $img01 = trim((string) ($blogSubArr['image_01'] ?? ''));
                    $img02 = trim((string) ($blogSubArr['image_02'] ?? ''));
                    $img01Cap = trim((string) ($blogSubArr['image_01_caption'] ?? ''));
                    $img02Cap = trim((string) ($blogSubArr['image_02_caption'] ?? ''));
                    $blogImg01 = $img01 !== '' ? $widgets->createCachelessImage("./img/blogs/" . $img01) : '';
                    $blogImg02 = $img02 !== '' ? $widgets->createCachelessImage("./img/blogs/" . $img02) : '';
                    ?>
                    <div class="edi-blog-single-section mt-4">
                        <?php if ($blogImg01 !== '' || $blogImg02 !== ''): ?>
                        <div class="row edi-blog-single-img-row">
                            <?php if ($blogImg01 !== ''): ?>
                            <div class="<?php echo $blogImg02 !== '' ? 'col-sm-6' : 'col-12'; ?> mb-2 mb-sm-0">
                                <figure class="edi-blog-single-figure mb-0">
                                    <img src="<?php echo htmlspecialchars($blogImg01, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded edi-blog-inline-img" alt="">
                                    <?php if ($img01Cap !== ''): ?>
                                    <figcaption class="edi-blog-extra-caption mt-2 text-muted small"><?php echo htmlspecialchars($img01Cap, ENT_QUOTES, 'UTF-8'); ?></figcaption>
                                    <?php endif; ?>
                                </figure>
                            </div>
                            <?php endif; ?>
                            <?php if ($blogImg02 !== ''): ?>
                            <div class="<?php echo $blogImg01 !== '' ? 'col-sm-6' : 'col-12'; ?>">
                                <figure class="edi-blog-single-figure mb-0">
                                    <img src="<?php echo htmlspecialchars($blogImg02, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded edi-blog-inline-img" alt="">
                                    <?php if ($img02Cap !== ''): ?>
                                    <figcaption class="edi-blog-extra-caption mt-2 text-muted small"><?php echo htmlspecialchars($img02Cap, ENT_QUOTES, 'UTF-8'); ?></figcaption>
                                    <?php endif; ?>
                                </figure>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (trim((string) ($blogSubArr['description'] ?? '')) !== ''): ?>
                        <div class="edi-blog-single-prose text-justify mt-3">
                            <?php echo $blogSubArr['description']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                ?>

                <?php
                foreach ($blogExtraMedia as $em) {
                    $mt = strtolower((string) ($em['media_type'] ?? 'image'));
                    $cap = trim((string) ($em['caption'] ?? ''));
                    if ($mt === 'video') {
                        $vurl = trim((string) ($em['path'] ?? ''));
                        if ($vurl !== '' && $vurl !== '.') {
                            echo $widgets->displayHomeMainVideo($vurl, 'edi-blog-single-video-wrap blog-video');
                        }
                        continue;
                    }
                    $fn = basename(str_replace('\\', '/', (string) ($em['path'] ?? '')));
                    if ($fn === '') {
                        continue;
                    }
                    $src = $widgets->createCachelessImage("./img/blogs/" . $fn);
                    ?>
                    <figure class="edi-blog-extra-figure mt-4 mb-0">
                        <img src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded edi-blog-inline-img" alt="<?php echo htmlspecialchars($cap !== '' ? $cap : $blogTitle, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($cap !== ''): ?>
                        <figcaption class="edi-blog-extra-caption mt-2 text-muted small"><?php echo htmlspecialchars($cap, ENT_QUOTES, 'UTF-8'); ?></figcaption>
                        <?php endif; ?>
                    </figure>
                    <?php
                }
                ?>

                <?php
                if ($blogVideoStatus === 1 && $blogVideoUrl !== '' && $blogVideoUrl !== '.') {
                    echo $widgets->displayHomeMainVideo($blogVideoUrl, 'edi-blog-single-video-wrap blog-video');
                }
                ?>
            </article>
        </div>
    </div>

    <!-- Footer Start -->
    <?php echo $userHeader->printUserFooter(); ?>
    <!-- Footer End -->
</body>

</html>
