<?php

require_once __DIR__ . "/edi_content_tags.php";

class WIDGETS{
    private $bootstrapColWidth;

    public function setBootstrapColWidth( $bootstrapColWidth ){
        $this->bootstrapColWidth = $bootstrapColWidth;
    }

    public function userHeaderImage() {
        return "
            <div class='container-fluid page-header' style='margin-top: -200px;'>
                <div class='container'>
                    <div class='d-flex flex-column align-items-center justify-content-center page-header-height headerImageHeight'>
                    </div>
                </div>
            </div>
        ";
    }

    public function displayHomeMainVideo($homeMainVideoURL, $outerClass = null) {
    // 1. Extract the Video ID using Regex to ensure a clean ID
    // This handles: youtube.com/watch?v=ID, youtu.be/ID, and youtube.com/embed/ID
    $videoId = '';
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $homeMainVideoURL, $match)) {
        $videoId = $match[1];
    }

    // 2. Only build the iframe if we found a valid ID
    if (!empty($videoId)) {
        $embedURL = "https://www.youtube.com/embed/" . $videoId;
        $wrap = ($outerClass !== null && $outerClass !== '')
            ? preg_replace('/[^a-zA-Z0-9_\- ]/', '', (string) $outerClass)
            : 'container-fluid py-5 blog-video';
        if ($wrap === '') {
            $wrap = 'container-fluid py-5 blog-video';
        }

        return "
            <div class='" . $wrap . "'>
                <div class='container pt-1'>
                    <div class='row justify-content-center'>
                        <div class='col-lg-10'>
                            <div class='embed-responsive embed-responsive-16by9 text-center'>
                                <iframe class='embed-responsive-item'
                                        src='$embedURL'
                                        allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture'
                                        allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ";
    }
    return "";
}
    

    public function createCachelessImage($imageURL) {
        if ($imageURL === '' || $imageURL === null) {
            return '';
        }
        $candidates = array($imageURL);
        if (strpos($imageURL, './') === 0) {
            $candidates[] = dirname(__DIR__) . '/' . substr($imageURL, 2);
        }
        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return $imageURL . '?' . filemtime($path);
            }
        }
        return $imageURL;
    }

    /**
     * Home "How it works" card body: HTML from index.php; only <br> is kept (safe for static strings).
     */
    private function formatHowItWorksSummaryHtml($txt)
    {
        $decoded = html_entity_decode((string) $txt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = strip_tags($decoded, '<br>');
        return str_ireplace(array('<br />', '<br/>'), '<br>', $clean);
    }

    /**
     * Safe filename for testimonial / profile image paths (basename only).
     */
    private function isSafeImageFilename($name) {
        $name = (string) $name;
        return $name !== '' && preg_match('/^[A-Za-z0-9._-]+$/', $name) === 1;
    }

    /**
     * Public URL for testimonial avatar: uploaded testimonial image, then profile pic on disk,
     * then an inline SVG placeholder (missing files / empty DB values).
     */
    private function resolveTestimonialAvatarSrc($testiArr, $admin = false) {
        $rootFs = dirname(__DIR__);
        $urlPrefix = $admin ? '../' : './';

        $uploaded = isset($testiArr['testimonial_photo']) ? trim((string) $testiArr['testimonial_photo']) : '';
        if ($this->isSafeImageFilename($uploaded)) {
            $fs = $rootFs . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'testimonials' . DIRECTORY_SEPARATOR . $uploaded;
            if (is_file($fs) && is_readable($fs)) {
                $rel = $urlPrefix . 'img/testimonials/' . $uploaded;
                return $rel . '?' . filemtime($fs);
            }
        }

        $prof = isset($testiArr['profile_pic']) ? trim((string) $testiArr['profile_pic']) : '';
        if ($this->isSafeImageFilename($prof)) {
            $fs = $rootFs . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'profile-pics' . DIRECTORY_SEPARATOR . $prof;
            if (is_file($fs) && is_readable($fs)) {
                $rel = $urlPrefix . 'img/profile-pics/' . $prof;
                return $rel . '?' . filemtime($fs);
            }
        }

        return 'data:image/svg+xml,' . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="65" height="65" viewBox="0 0 65 65">'
            . '<defs><linearGradient id="ta" x1="0%" y1="0%" x2="100%" y2="100%">'
            . '<stop offset="0%" style="stop-color:#cbd5e1"/><stop offset="100%" style="stop-color:#94a3b8"/></linearGradient></defs>'
            . '<circle cx="32.5" cy="32.5" r="32" fill="url(#ta)"/>'
            . '<circle cx="32.5" cy="24" r="11" fill="#f8fafc"/>'
            . '<path d="M14 54c4-14 10-19 18.5-19s14.5 5 18.5 19" fill="#f8fafc"/>'
            . '</svg>'
        );
    }

    public function displayHowItWorksBlock($topic, $txt, $img) {
        $img = $this->createCachelessImage("./img/Web pic/$img");
        $topicEsc = htmlspecialchars((string) $topic, ENT_QUOTES, 'UTF-8');
        $summaryHtml = $this->formatHowItWorksSummaryHtml($txt);
        $html = "
            <div class='col-md-4 mb-2 px-xl- text-center'>
                <div class='pt-5 pb-3 border blog-item'>
                    <img class='img-fluid' src='$img' alt='$topicEsc' style='height: 180px;'><br>
                    <h5 class='font-weight-bold coloring-pages text-uppercase pt-4 mb-2'>$topicEsc</h5>
                    <div class='edi-howitworks-card-desc'>$summaryHtml</div>
                    <div class='pt-md-3 pt-sm-1'>
                   
                    </div>
                </div>
            </div>
        ";
        return $html;
    }

    public function displayHowItWorksBlock2($topic, $txt, $img) {
        $img = $this->createCachelessImage("./img/Web pic/$img");
        $topicEsc = htmlspecialchars((string) $topic, ENT_QUOTES, 'UTF-8');
        $summaryHtml = $this->formatHowItWorksSummaryHtml($txt);
        $html = "
            <div class='col-md-4 mb-2 px-xl- text-center'>
                <div class='pt-5 pb-3 border blog-item'>
                    <img class='img-fluid' src='$img' alt='$topicEsc' style='height: 180px;'><br>
                    <h5 class='font-weight-bold books-paper text-uppercase pt-4 mb-2'>$topicEsc</h5>
                    <div class='edi-howitworks-card-desc'>$summaryHtml</div>
                    <div class='pt-md-3 pt-sm-1'>
                    
                    </div>
                </div>
            </div>
        ";
        return $html;
    }

    public function displayHowItWorksBlock3($topic, $txt, $img) {
        $img = $this->createCachelessImage("./img/Web pic/$img");
        $topicEsc = htmlspecialchars((string) $topic, ENT_QUOTES, 'UTF-8');
        $summaryHtml = $this->formatHowItWorksSummaryHtml($txt);
        $html = "
            <div class='col-md-4 mb-2 px-xl- text-center'>
                <div class='pt-5 pb-3 border blog-item'>
                    <img class='img-fluid' src='$img' alt='$topicEsc' style='height: 180px;'><br>
                    <h5 class='font-weight-bold homeworks text-uppercase pt-4 mb-2'>$topicEsc</h5>
                    <div class='edi-howitworks-card-desc'>$summaryHtml</div>
                    <div class='pt-md-3 pt-sm-1'>
                    
                    </div>
                </div>
            </div>
        ";
        return $html;
    }

    

    public function displayToursBriefInHome($dataArr) {
        $imageName = $this->createCachelessImage("./img/tours/".$dataArr['image_name']);
        $redirectURL = "./tour?id=".$dataArr['id'];
        $tourDescription = $dataArr['description'];
        if ( strlen($tourDescription) > 220 ) {
            $tourDescription = substr($tourDescription, 0, 220);
            if ( substr($tourDescription, -1) == " " ) {
                $tourDescription = substr($tourDescription, 0, -1) . "...";
            } else {
                $tourDescription .= "...";
            }
        }
        $html = "
            <div class='col-lg-4 col-md-6 mb-3' style='cursor:pointer;' onclick=location.href='$redirectURL'>
                <div class='package-item bg-white mb-2'>
                    <img class='img-fluid mb-1' src='$imageName' alt=''>
                    <div class='p-1' style='line-height: 1.25;'>
                        <span class='text-primary' style='line-height: 1.8;'>".$dataArr['no']."</span><br>
                        <span class='text-warning'><b>".$dataArr['title']."</b></span><br>
                        <small>".$dataArr['type']."</small>
                    </div>
                    <p class='px-1 my-3 text-justify'>$tourDescription <a href='$redirectURL'><br>Read More</a></p>
                    <div class='col-12 py-1 pl-3 text-white bg-primary'>
                        <small><i class='fa fa-calendar-alt mr-2'></i> ".$dataArr['duration']."</small>
                    </div>
                </div>
            </div>
        ";
        return $html;
    }

    public function displayTourCarousel($mainImage, $subImgsArr) {
        $html = "
            <img id=featured src='$mainImage'>
            <div id='slide-wrapper'>
                <div id='slider' class='justify-content-center'>
                    <img class='thumbnail active' src='$mainImage'>";
                    foreach ( $subImgsArr as $row ) {
                        $imageName = $this->createCachelessImage("./img/tours/".$row['image_name']);
                        $html .= "<img class='thumbnail' src='$imageName'>";
                    }
        $html .= "
                </div>
            </div>
        ";
        return $html;
    }

    public function displayTourBlock01($icon, $text, $class) {
        $html = "
            <div class='col-md-4 text-left mb-2 $class'>
                <div class='col-12 border py-2'>
                    <i class='$icon text-primary pr-3'></i>$text
                </div>
            </div>
        ";
        return $html;
    }

    public function displayTourBlock02($topic, $text, $class) {
        $html = "
            <div class='col-md-6 text-left mb-2 $class'>
                <div class='col-12 border py-1'>
                    <span class='font-weight-bold text-primary'>$topic</span>
                    <span class='row col mt-1'>$text</span>
                </div>
            </div>
        ";
        return $html;
    }

    public function displayTourServicesBlock($topic, $dataArr, $icon, $class) {
        $html = "
            <div class='col-md-6 text-left mb-2 $class'>
                <div class='col-12 border py-1'>
                    <span class='font-weight-bold text-primary'>$topic</span><br>";
                    foreach ( $dataArr as $val ) {
                        $html .= "<span><i class='$icon text-warning px-1'></i>$val</span><br>";
                    }
        $html .= "
                </div>
            </div>
        ";
        return $html;
    }

    public function displayTourDayAccordion($user, $tourID) {
        $html = "<div class='accordion px-1' id='accordionExample'>";
        $show = "show";
        $collapsed = "";
        $ariaExpanded = "true";
        $i = 1;
        foreach ( $user->fetchAll(array("title", "description", "image_name", "accommodation", "room", "meal_plan", "travel_time"), array("tour_day_details"), array("tour_id"=>$tourID), "id") as $row ) {
            $tourDayTitle = $row['title'];
            $tourDayDescription = $row['description'];
            $tourDayAccommodation = $row['accommodation'];
            $tourDayRoom = $row['room'];
            $tourDayMealPlan = $row['meal_plan'];
            $tourDayTravelTime = $row['travel_time'];
            $imageName = ($row['image_name']!="") ? $this->createCachelessImage("./img/tours/".$row['image_name']) : "";
            $html .= "
                <div class='accordion-item'>
                    <h2 class='accordion-header' id='heading$i'>
                        <button class='accordion-button $collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse$i' aria-expanded='$ariaExpanded' aria-controls='collapse$i'>
                            <b>Day $i - $tourDayTitle</b>
                        </button>
                    </h2>
                    <div id='collapse$i' class='accordion-collapse collapse $show' aria-labelledby='heading$i' data-bs-parent='#accordionExample'>
                        <div class='accordion-body'>
                            <p class='text-justify'>$tourDayDescription</p>
                            <img src='$imageName' style='width: 100%;' alt='Tour Day $i Image'>
                            <div class='row mt-2'>
                                <div class='col-12 text-left py-1'>
                                    <div class='border p-2'>
                                        <span class='font-weight-bold text-primary'>Highlights</span><br>
                                        <span><i class='fa fa-bed fa-sm text-warning px-2'></i>Accommodation - $tourDayAccommodation</span><br>
                                        <span><i class='fa fa-bed fa-sm text-warning px-2'></i>Room - $tourDayRoom</span><br>
                                        <span><i class='fa fa-bed fa-sm text-warning px-2'></i>Meal Plan - $tourDayMealPlan</span><br>
                                        <span><i class='fa fa-bed fa-sm text-warning px-2'></i>Travel Time - $tourDayTravelTime</span><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            ";
            if ( $i == 1 ) {
                $show = "";
                $collapsed = "collapsed";
                $ariaExpanded = "false";
            }
            $i++;
        }
        $html .= "</div>";
        return $html;
    }

    public function testimonialData($testiArr, $admin=false) {
        $imgPrefix = $admin ? '../' : './';
        $profilePic = $this->resolveTestimonialAvatarSrc($testiArr, $admin);
        $ratings = $testiArr['ratings'];
        $name = $testiArr['name'];
        $html = "
            <img src='{$imgPrefix}img/Web pic/4.png' width='35px' alt='Testimonial' style='padding-bottom:20px; margin-top: -25px;'>
            <div class='text-center'>
                <img src='$profilePic' class='rounded-circle' width='65px' height='65px' alt=''>
            </div>
            <h6 class='text-primary text-center text-uppercase font-weight-bold mt-2 mb-1' >".$testiArr['one_word']."</h6>
            <p class='text-justify mb-0 mt-2'>".$testiArr['review']."</p> <div class='d-flex justify-content-left'>";
            
            for ( $i=1; $i<=5; $i++ ) {
                $starColor = ($i<=$ratings) ? "text-warning1" : "";
                $html .= "<span class='text-center fa fa-star $starColor'></span>";
            }
        $html .= "
        </div>
            <div class='testimonial-name-tag'>
                <h6 class='text-center text-uppercase font-weight-bold mt-2 mb-1'>".$testiArr['name']."</h6>
                <center style='margin-top:-10px;'><span class='text-primary' style='font-size: 12px;'>".$testiArr['country']."</span></center>
            </div>"
        ;
            
        return $html;
    }

    public function displayTestimonialBrief($testiArr) {
        $html = "
            <div class='col-md-4 mb-2 mb-md-0'>
                <div class='card blog-item'>
                    <div class='card-body p-3'>";
        $html .= $this->testimonialData($testiArr);
        $html .= "
                    </div>
                </div>
            </div>
        ";
        return $html;
    }

    public function displayTestimonial($testiArr, $user, $admin=false) {
        $html = "

        
        <div class='d-flex'>
            <div class='col-md-12 col-sm-12 justify-content-center'>
                <div class='col-12 card blog-item'>
                    <div class='card-body p-3'>
                        <div class='row'>
                            <div class='col-md-12'>".$this->testimonialData($testiArr, $admin)."</div>
                        </div>
                    </div>
                </div>
            </div>    
        </div>
        <br>
        ";
        return $html;
    }

  
    public function displayBlogBrief($row, $col = "col-md-6", $charLimit = 160, $layout = "list") {
        if ($layout === true || $layout === false || $layout === null) {
            $layout = "list";
        }
        if (!in_array($layout, array("list", "featured", "grid"), true)) {
            $layout = "list";
        }

        $blogId = (int) $row["id"];
        $imageName = $this->createCachelessImage("./img/blogs/" . $row["image"]);
        $tagPlain = EdiContentTags::blogCategoryDisplayLabel((string) ($row["tag"] ?? ""));
        $tag = htmlspecialchars($tagPlain, ENT_QUOTES, "UTF-8");
        $title = htmlspecialchars((string) $row["title"], ENT_QUOTES, "UTF-8");

        $plain = strip_tags((string) $row["description"]);
        $plain = preg_replace("/\s+/u", " ", $plain);
        $plain = trim($plain);
        $charLimit = (int) $charLimit;
        if ($charLimit < 40) {
            $charLimit = 40;
        }
        if (strlen($plain) > $charLimit) {
            $plain = rtrim(substr($plain, 0, $charLimit));
            if ($plain !== "" && !preg_match("/\s$/u", $plain)) {
                $plain = preg_replace("/\s+\S*$/u", "", $plain);
            }
            $plain .= "…";
        }

        $blogUrl = "./blog?id=" . $blogId;
        $mbClass = ($layout === "grid" || $layout === "featured") ? "mb-0" : "mb-3";

        $tagRow = "";
        if ($tagPlain !== "") {
            $tagRow = "<div class='edi-blog-tag'><i class='fa fa-tag' aria-hidden='true'></i><span>" . $tag . "</span></div>";
        }

        $html = "
            <div class='" . $col . " edi-blog-col " . $mbClass . "'>
                <article class='edi-blog-card edi-blog-card--" . $layout . "'>
                    <a class='edi-blog-thumb-link' href='" . $blogUrl . "' aria-label='" . $title . "'>
                        <div class='edi-blog-thumb'>
                            <img src='" . $imageName . "' alt='' width='640' height='360' loading='lazy'>
                        </div>
                    </a>
                    <div class='edi-blog-body'>
                        " . $tagRow . "
                        <h3 class='edi-blog-title'><a href='" . $blogUrl . "'>" . $title . "</a></h3>
                        <div class='edi-blog-title-rule' role='presentation'></div>
                        <p class='edi-blog-excerpt'>" . htmlspecialchars($plain, ENT_QUOTES, "UTF-8") . "</p>
                        <a href='" . $blogUrl . "' class='edi-blog-readmore'>Read More</a>
                    </div>
                </article>
            </div>
        ";
        return $html;
    }

    
public function displayad1Brief($row, $col="col-md-12", $wordCount=200) {
    $imageName = $this->createCachelessImage("./img/ad1/".$row['image']);
    $ad1Date = date("d M y", strtotime(substr($row['timestamp'], 0, 10)));
    $ad1Description = $row['description'];
    $ad1adlink = $row['adlink'];
    if ( strlen($ad1Description) > $wordCount ) {
        $ad1Description = substr($ad1Description, 0, $wordCount);
        if ( substr($ad1Description, -1) == " " ) {
            $ad1Description = substr($ad1Description, 0, -1) . "...";
        } else {
            $ad1Description .= "...";
        }
    }
    $html = "
        <div class='$col mb-0 pb-0'>
            <div class='blog-item'>
                <div class='position-relative'>
                    <img class='cursor-pointer img-fluid w-100' src='$imageName' alt='' onclick=location.href='$ad1adlink'>

                </div>
<!------------
                <div class='bg-white p-2'>
                    <div class='d-flex mb-2' style='font-size:12px;'>
                        <i class='fa fa-tag fa-sm text-warning p-1'></i>
                        <span class='text-warning'>".$row['tag']."</span>
                    </div>
                    <h5 class='text-primary text-uppercase font-weight-bold mb-0'>".$row['title']."</h5>
                    <hr class='border-warning mt-1 mb-3'>
                    <p class='m-0 text-justify text-decoration-none'>$ad1Description <a href='./ad1?id=".$row['id']."'><br>Read More</a></p>
                </div>
                ------->
            </div>
        </div>
    ";
    return $html;
}

public function displayad2Brief($row, $col="col-md-12", $wordCount=200) {
    $imageName = $this->createCachelessImage("./img/ad2/".$row['image']);
    $ad2Date = date("d M y", strtotime(substr($row['timestamp'], 0, 10)));
    $ad2Description = $row['description'];
    $ad2adlink = $row['adlink'];
    if ( strlen($ad2Description) > $wordCount ) {
        $ad2Description = substr($ad2Description, 0, $wordCount);
        if ( substr($ad2Description, -1) == " " ) {
            $ad2Description = substr($ad2Description, 0, -1) . "...";
        } else {
            $ad2Description .= "...";
        }
    }
    $html = "
        <div class='$col mb-0 pb-0'>
            <div class='blog-item'>
                <div class='position-relative'>
                    <img class='cursor-pointer img-fluid w-100' src='$imageName' alt='' onclick=location.href='$ad2adlink'>
                </div>
<!------------
                <div class='bg-white p-2'>
                    <div class='d-flex mb-2' style='font-size:12px;'>
                        <i class='fa fa-calendar-alt fa-sm text-warning p-1'></i>
                        <span class='text-warning pr-4'>$ad2Date</span>
                        <i class='fa fa-tag fa-sm text-warning p-1'></i>
                        <span class='text-warning'>".$row['tag']."</span>
                    </div>
                    <h5 class='text-primary text-uppercase font-weight-bold mb-0'>".$row['title']."</h5>
                    <hr class='border-warning mt-1 mb-3'>
                    <p class='m-0 text-justify text-decoration-none'>$ad2Description <a href='./ad2?id=".$row['id']."'><br>Read More</a></p>
                </div>
                ------->
            </div>
        </div>
    ";
    return $html;
}


    
    
public function displaypdfBrief($row, $isHome, $col="col-md-6", $wordCount=200, $ediExplorerTagFilter = false) {

    $imageName = $this->createCachelessImage("./img/pdf/".$row['image']);
    $pdfDate = date("d M y", strtotime(substr($row['timestamp'], 0, 10)));
    $pdfDescription = $row['description'];
    $uploadpdf = $row['pdfupload'];
    $pdfId = $row['id'];
    $buttonId = 'downloadButton_' . $pdfId;
    $countId = 'downloadCount_' . $pdfId;
    $downloadcount = $row['download_count'];
    if ( strlen($pdfDescription) > $wordCount ) {
        $pdfDescription = substr($pdfDescription, 0, $wordCount);
        if ( substr($pdfDescription, -1) == " " ) {
            $pdfDescription = substr($pdfDescription, 0, -1) . "...";
        } else {
            $pdfDescription .= "...";
        }
    }
    if ($isHome){
        $html = "
        <div class='$col mb-4 pb-2'>
            <div class='blog-item'>
                <div class='position-relative'>
                    <img class='cursor-pointer img-fluid w-100' src='$imageName' alt='' onclick=location.href='./pdf?id=".$row['id']."'>
                </div>
                <div class='bg-white p-2'>
                    <div class='d-flex mb-2' style='font-size:12px;'>
    <i class='fa fa-tag fa-sm p-1' style='color:#FFC107;'></i>
    <span style='color:#FFC107; font-weight:500;'>". $this->getTitleTag($row['title']) ."</span>
</div>
                    <h5 class='text-primary text-uppercase font-weight-bold mb-0' >".$row['title']."</h5>
                    <hr class='border-warning mt-1 mb-3'>

                
                    <p class='m-0 text-justify text-decoration-none'>$pdfDescription <a href='./pdf?id=".$row['id']."'><br>Read More</a></p>
                    
                    </div>
            </div>
        </div>
    ";
    }
    else{
        $cardTag = htmlspecialchars($this->getTitleTag($row['title']) ?: trim($row['tag'] ?? ''), ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($row['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $dlA = $this->buildContentDownloadAnchor("./img/pdf", $uploadpdf, $countId, $pdfId, "pdf");
        $colClass = $col . " mb-4 pb-2";
        $explorerAttr = "";
        if ($ediExplorerTagFilter) {
            $toks = EdiContentTags::splitTags(isset($row['tag']) ? (string) $row['tag'] : "");
            $colClass .= " edi-explorer-filter-card";
            $explorerAttr = ' data-edi-tags="' . htmlspecialchars(json_encode($toks, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") . '"';
        }
        $html = "
        <div class='$colClass'$explorerAttr>
            <div class='blog-item edi-pdf-result-card' style='border-radius:6px; overflow:hidden; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.04);'>
                <div class='imageframe position-relative' style='border:2px solid #2d8a54; border-radius:4px; margin:8px;'>
                    <img class='img-fluid w-100' src='$imageName' alt='' style='display:block;'>
                </div>
                <div class='bg-white px-2 pb-3 pt-0'>
                    <div class='d-flex align-items-center mb-1' style='font-size:12px;'>
                        <i class='fa fa-tag fa-sm p-1' style='color:#0b0b0b;' aria-hidden='true'></i>
                        <span class='text-uppercase' style='color:#0b0b0b; font-weight:600; letter-spacing:.02em;'>$cardTag</span>
                    </div>
                    <h5 class='text-danger text-uppercase font-weight-bold mb-1' style='font-size:1rem; line-height:1.2;'>$safeTitle</h5>
                    <p class='mb-2 text-muted' style='font-size: 13px; line-height:1.35;'>$pdfDescription</p>
                    <div class='d-flex align-items-center justify-content-between border-top pt-2 mt-1'>
                    <div class='d-flex align-items-center flex-wrap'> 
                    ".$dlA."
                    <div id='$countId' class='pl-2 mb-0' style='font-size:0.9rem; font-weight:600;'>($downloadcount)</div>
                    </div>
                    <button type='button' class='btn btn-link p-0 edi-fav-tgl' data-fav-type='pdf' data-fav-id='$pdfId' aria-pressed='false' title='Save' style='line-height:1;'><i class='fa fa-heart-o text-secondary' style='font-size:1.15rem' aria-hidden='true'></i></button>
                    </div>
                </div>
            </div>
        </div>
    ";
    }
    

    return $html;
}

/**
 * Direct file download; bumps server count via update_download_count.php.
 * $path like "./img/pdf/filename.pdf"; $type: pdf|book|homework
 */
private function buildContentDownloadAnchor($pathRel, $uploadName, $countId, $contentId, $type, $classBtn = "btn newgreen1-btn btn-sm")
    {
        $id = (int) $contentId;
        $uploadName = trim((string) $uploadName);
        if ($id < 1 || $uploadName === "") {
            return "<span class=\"text-muted small\">—</span>";
        }
        $t = "pdf";
        if ($type === "book") {
            $t = "book";
        } elseif ($type === "homework") {
            $t = "homework";
        }
        $fileBase = str_replace("..", "", $uploadName);
        $fileBase = basename($fileBase);
        $href = rtrim($pathRel, "/") . "/" . $fileBase;
        $on = "fetch('update_download_count.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'downloadButton=1&pdfId=" . $id . "&type=" . $t . "'}).then(function(r){return r.text()}).then(function(c){var n=document.getElementById('" . $countId . "');if(n){n.textContent='('+c+')';}});return true;";
        $escH = htmlspecialchars($href, ENT_QUOTES, "UTF-8");
        $escD = htmlspecialchars($fileBase, ENT_QUOTES, "UTF-8");
        $escC = htmlspecialchars($classBtn, ENT_QUOTES, "UTF-8");
        $escOn = htmlspecialchars($on, ENT_COMPAT, "UTF-8");
        return "<a href=\"" . $escH . "\" class=\"" . $escC . "\" download=\"" . $escD . "\" onclick=\"" . $escOn . "\">Download</a>";
    }

private function getTitleTag($title) {
    $words = preg_split('/[\s,]+/', $title);

    foreach ($words as $word) {
        $clean = ucfirst(strtolower(trim($word)));

        if(strlen($clean) > 2){
            return $clean; // return first meaningful word
        }
    }

    return '';
}

public function displayhomeworkBrief($row, $isHome, $col="col-md-6", $wordCount=200, $ediExplorerTagFilter = false) {
    $imageName = $this->createCachelessImage("./img/homework/".$row['image']);
    // $pdfupload = $this->createCachelessImage("./img/pdf/".$row['pdfupload']);
    $homeworkDate = date("d M y", strtotime(substr($row['timestamp'], 0, 10)));
    $homeworkDescription = $row['description'];
    $uploadpdf = $row['pdfupload'];
    $downloadcount = $row['download_count'];
    $pdfId = $row['id'];
    $buttonId = 'downloadButton_' . $pdfId;
    $countId = 'downloadCount_' . $pdfId;
    if ( strlen($homeworkDescription) > $wordCount ) {
        $homeworkDescription = substr($homeworkDescription, 0, $wordCount);
        if ( substr($homeworkDescription, -1) == " " ) {
            $homeworkDescription = substr($homeworkDescription, 0, -1) . "...";
        } else {
            $homeworkDescription .= "...";
        }
    }
    if ($isHome){

        $html = "
        <div class='$col mb-4 pb-2'>
            <div class='blog-item'>
                <div class='imageframe position-relative'>
                    <img class='cursor-pointer img-fluid w-100' src='$imageName' alt='' onclick=location.href='./homework?id=".$row['id']."'>
                </div>
                <div class='bg-white p-2'>
                    <div class='d-flex mb-2' style='font-size:12px;'>
                        <i class='fa fa-calendar-alt fa-sm text-warning p-1'></i>
                        <span class='text-warning pr-4'>$homeworkDate</span>
                        <i class='fa fa-tag fa-sm text-warning p-1'></i>
                        <span class='text-warning'>".$row['tag']."</span>
                    </div>
                    <h5 class='text-primary text-uppercase font-weight-bold mb-0'>".$row['title']."</h5>
                    <hr class='border-warning mt-1 mb-3'>

                
                    <p class='m-0 text-justify text-decoration-none'>$homeworkDescription <a href='./homework?id=".$row['id']."'><br>Read More</a></p>
                    
                    </div>
            </div>
        </div>
    ";
    }
    else{
        $dlA = $this->buildContentDownloadAnchor("./img/homework", $uploadpdf, $countId, $pdfId, "homework");
        $colClass = $col . " mb-4 pb-2";
        $explorerAttr = "";
        if ($ediExplorerTagFilter) {
            $toks = EdiContentTags::splitTags(isset($row['tag']) ? (string) $row['tag'] : "");
            $colClass .= " edi-explorer-filter-card";
            $explorerAttr = ' data-edi-tags="' . htmlspecialchars(json_encode($toks, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") . '"';
        }
        $html = "
        <div class='$colClass'$explorerAttr>
            <div class='blog-item'>
                <div class='imageframe position-relative'>
                    <img class='img-fluid w-100' src='$imageName' alt=''>
                </div>
                <div class='bg-white p-2'>
                    <div class='d-flex mb-2' style='font-size:12px;'>
                        <i class='fa fa-calendar-alt fa-sm text-warning p-1'></i>
                        <span class='text-warning pr-4'>$homeworkDate</span>
                        <i class='fa fa-tag fa-sm text-warning p-1'></i>
                        <span class='text-warning'>".$row['tag']."</span>
                    </div>
                    <h5 class='text-primary text-uppercase font-weight-bold mb-0' style='font-size: 18px;'>".$row['title']."</h5>
                    <p class='mb-2' style='font-size: 12px;'>$homeworkDescription</p>
                    <hr class='border-warning mt-1 mb-3'>
                    <div class='d-flex align-items-center flex-wrap'>".$dlA."
                        <div id='$countId' class='pl-2 mb-0'>($downloadcount)</div>
                    </div>
                </div>
            </div>
        </div>
    ";
    }

    return $html;
}


public function displaybooksBrief($isHome, $row, $col="col-md-6", $wordCount=200, $ediExplorerTagFilter = false) {
    $imageName = $this->createCachelessImage("./img/books/".$row['image']);
    // $pdfupload = $this->createCachelessImage("./img/pdf/".$row['pdfupload']);
    $booksDate = date("d M y", strtotime(substr($row['timestamp'], 0, 10)));
    $booksDescription = $row['description'];
    $uploadpdf = $row['pdfupload'];
    $pdfId = $row['id'];
    $buttonId = 'downloadButton_' . $pdfId;
    $countId = 'downloadCount_' . $pdfId;
    $downloadcount = $row['download_count'];
    if ( strlen($booksDescription) > $wordCount ) {
        $booksDescription = substr($booksDescription, 0, $wordCount);
        if ( substr($booksDescription, -1) == " " ) {
            $booksDescription = substr($booksDescription, 0, -1) . "...";
        } else {
            $booksDescription .= "...";
        }
    }
    if ($isHome){

        $html = "
        <div class='$col mb-4 pb-2'>
            <div class='blog-item'>
                <div class=' position-relative'>
                    <img class='cursor-pointer img-fluid w-100' src='$imageName' alt='' onclick=location.href='./books?id=".$row['id']."'>
                </div>
                <div class='bg-white p-2'>
                    <div class='d-flex mb-2' style='font-size:12px;'>
                        <i class='fa fa-calendar-alt fa-sm text-warning p-1'></i>
                        <span class='text-warning pr-4'>$booksDate</span>
                        <i class='fa fa-tag fa-sm text-warning p-1'></i>
                        <span class='text-warning'>".$row['tag']."</span>
                    </div>
                    <h5 class='text-primary text-uppercase font-weight-bold mb-0'>".$row['title']."</h5>
                    <hr class='border-warning mt-1 mb-3'>

                
                    <p class='m-0 text-justify text-decoration-none'>$booksDescription <a href='./books?id=".$row['id']."'><br>Read More</a></p>
                    
                    </div>
            </div>
        </div>
    ";
    }
    else{
        $dlA = $this->buildContentDownloadAnchor("./img/books", $uploadpdf, $countId, $pdfId, "book");
        $colClass = $col . " mb-4 pb-2";
        $explorerAttr = "";
        if ($ediExplorerTagFilter) {
            $toks = EdiContentTags::splitTags(isset($row['tag']) ? (string) $row['tag'] : "");
            $colClass .= " edi-explorer-filter-card";
            $explorerAttr = ' data-edi-tags="' . htmlspecialchars(json_encode($toks, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") . '"';
        }
        $html = "
        <div class='$colClass'$explorerAttr>
            <div class='blog-item'>
                <div class='imageframe position-relative'>
                    <img class='img-fluid w-100' src='$imageName' alt=''>
                </div>
                <div class='bg-white p-2'>
                    <div class='d-flex mb-2' style='font-size:12px;'>
                        <i class='fa fa-calendar-alt fa-sm text-warning p-1'></i>
                        <span class='text-warning pr-4'>$booksDate</span>
                        <i class='fa fa-tag fa-sm text-warning p-1'></i>
                        <span class='text-warning'>".$row['tag']."</span>
                    </div>
                    <h5 class='text-primary text-uppercase font-weight-bold mb-0' style='font-size: 18px;'>".$row['title']."</h5>
                    <p class='mb-2' style='font-size: 12px;'>$booksDescription</p>
                    <hr class='border-warning mt-1 mb-3'>
                    <div class='d-flex align-items-center flex-wrap'>".$dlA."
                    <div id='$countId' class='pl-2 mb-0'>($downloadcount)</div>
                    </div>
                </div>
            </div>
        </div>
    ";
    }

    return $html;
}




    public function displayGetQuoate($user) {
        $html = "
        <!------
            <div class='col-12 border py-5'>
            
                <h4 class='text-warning text-center mb-3'>GET A QUOATE</h4>
                <form action='' method='POST'>
                    <select name='quoteTourTitle' class='form-control mb-1'>
                        <option value=''>Select Tour</option>";
                            foreach ( $user->fetchAll(array("title"), array("tour_details"), array("status"=>"1"), "id") as $row ) {
                                $html .= "<option value='".$row['title']."'>".$row['title']."</option>";
                            }
        $html .= "
                    </select>
                    <input type='text' class='form-control mb-1' name='quoteName' placeholder='Name' required>
                    <input type='email' class='form-control mb-1' name='quoteEmail' placeholder='Email' required>
                    <input type='tel' class='form-control mb-1' name='quoteMobile' placeholder='Mobile' required>
                    <input type='text' class='form-control mb-1' name='quoteCountry' placeholder='Living Country' required>
                    <input type='text' class='form-control mb-1' name='quoteArrivalDate' placeholder='Arrival Date' onfocus=(this.type='date')>
                    <input type='text' class='form-control mb-1' name='quoteDepartureDate' placeholder='Departure Date' onfocus=(this.type='date')>
                    <input type='text' class='form-control mb-1' name='quoteAdultsChildren' placeholder='Adults/Children'>
                    <textarea name='quoteDescription' rows='5' class='form-control mb-3' placeholder='Message'></textarea>
                    <center>
                        <span>This data will only be used by our team to contact you</span>
                        <div class='my-3'>
                           
                            <div class='h-captcha' data-sitekey='cfe150fa-234b-4633-9582-b974082cbc2f' data-callback='correctCaptcha' data-size='compact'></div>
                        </div>
                        <input type='submit' class='btn btn-primary text-center' name='quoteSubmit' value='Submit'>
                    </center>
                </form>
                
            </div>
            ------>
            

            <br>
            <div class='col-12 bg-boxgray text-center py-5'>
                <small class='text-black' style='font-size:14px; font-weight:400 !important;'>Advertiesment</small> 
            </div>

            <br>
            <div class='col-12 bg-boxgray text-center py-5'>
                <small class='text-black' style='font-size:14px; font-weight:400 !important;'>Advertiesment</small> 
            </div>

            <br>
           <div class='col-12 bg-boxgray text-center py-5'>
                <small class='text-black' style='font-size:14px; font-weight:400 !important;'>Advertiesment</small> 
            </div>

            <br>
            <div class='col-12 bg-boxgray text-center py-5'>
                <small class='text-black' style='font-size:14px; font-weight:400 !important;'>Advertiesment</small> 
            </div>
            <br>
            <div class='col-12 bg-boxgray text-center py-5'>
                <small class='text-black' style='font-size:14px; font-weight:400 !important;'>Advertiesment</small> 
            </div>
            <br>
            <div class='col-12 bg-boxgray text-center py-5'>
                <small class='text-black' style='font-size:14px; font-weight:400 !important;'>Advertiesment</small> 
            </div>
        ";
        return $html;
    }

    public function inputGroup($lable, $name, $class="", $value="", $type="text", $required="required") {
        if ( $class!="" ) $this->bootstrapColWidth = $class;
        $html = "
            <div class='$this->bootstrapColWidth'>
                <div class='form-group'>
                    <label for='example-text-input' class='form-control-label'>$lable</label>
                    <input class='form-control' type='$type' name='$name' value='$value' $required>
                </div>
            </div>
        ";
        return $html;
    }

    public function checkboxSwitch($lable, $nameID, $checked="", $align="justify-content-center") {
        return "
            <div class='form-check form-switch $align'>
                <input class='form-check-input text-danger' type='checkbox' name='$nameID' id='$nameID' value='1' $checked>
                <label class='form-check-label font-weight-bold' for='$nameID'>$lable</label>
            </div>
        ";
    }

    function addTourMainDetailsDiv($day, $valuesArr=array()) {
        $imageName = (!empty($valuesArr['image_name'])) ? "../img/tours/".$valuesArr['image_name'] : "";
        $html = "
            <div class='row border mx-3 mb-3'>
                <center>";
            $html .= $this->inputGroup('Day Title ('.$day.')', 'inputTourDayTitle'.$day, 'col-md-6', $valuesArr['title'], "text", "");
        $html .= "
                </center>
                <div class='row'>
                    <div class='col-md-6'>
                        <div class='form-group'>
                            <label for='example-text-input' class='form-control-label'>Description</label>
                            <textarea class='form-control' name='inputTourDayDescription$day' rows='5'>".$valuesArr['description']."</textarea>
                        </div>
                    </div>
                    <div class='col-md-6 d-flex align-items-center justify-content-center'>
                        <div class='form-group'>
                            <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 2)' name='inputTourMainDetailsDayImage$day'>
                            <span onclick='removeTourDayImage($day)' class='text-danger cursor-pointer'>remove</span>
                            <p class='text-center my-1'><img id='outputTourMainDetailsDayImage$day' src='$imageName' style='max-height: 115px; max-width:100%' /></p>
                        </div>
                    </div>
                </div>
                <div class='row'>";
                    $html .= $this->inputGroup('Accommodation', 'inputTourDayAccommodation'.$day, "", $valuesArr['accommodation'], "text", "");
                    $html .= $this->inputGroup('Room', 'inputTourDayRoom'.$day, "", $valuesArr['room'], "text", "");
                    $html .= $this->inputGroup('Meal Plan', 'inputTourDayMealPlan'.$day, "", $valuesArr['meal_plan'], "text", "");
                    $html .= $this->inputGroup('Travel Time', 'inputTourDayTravelTime'.$day, "", $valuesArr['travel_time'], "text", "");
        $html .= "
                </div>
                <div class='row text-center' id='addMoreTourDetails$day'>
                    <i class='fa fa-plus-circle fa-lg cursor-pointer' onclick='addMoreTourDayDetails($day)' aria-hidden='true'></i><br>
                    <small style='font-size: 10px;'>Add More</small>
                </div>
            </div>
            <div id='addMoreTourDayDetails$day'></div>
        ";
        return $html;
    }

    public function addBlogDesctiptionDiv($index, $valuesArr=array()) {
        $image01 = (!empty($valuesArr['image_01'])) ? "../img/blogs/".$valuesArr['image_01'] : "";
        $image02 = (!empty($valuesArr['image_02'])) ? "../img/blogs/".$valuesArr['image_02'] : "";
        $html = "
            <div class='row border mx-3 mb-3'>
                <b>0$index</b>
                <div class='col-12'>
                    <div class='form-group mt-2'>
                        <textarea class='form-control' name='inputBlogDescription$index' rows='5'>".$valuesArr['description']."</textarea>
                    </div>
                </div>
                <div class='row'>
                    <div class='col-md-6 d-flex align-items-center justify-content-center'>
                        <div class='form-group'>
                            <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputBlogImageOne$index'>
                            <span onclick='removeBlogDescImage($index,1)' class='text-danger cursor-pointer'>remove</span>
                            <p class='text-center my-1'><img id='outputBlogImageOne$index' src='$image01' style='max-height: 115px; max-width:100%' /></p>
                        </div>
                    </div>
                    <div class='col-md-6 d-flex align-items-center justify-content-center'>
                        <div class='form-group'>
                            <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputBlogImageTwo$index'>
                            <span onclick='removeBlogDescImage($index,2)' class='text-danger cursor-pointer'>remove</span>
                            <p class='text-center my-1'><img id='outputBlogImageTwo$index' src='$image02' style='max-height: 115px; max-width:100%' /></p>
                        </div>
                    </div>
                </div>
                <div class='row text-center' id='addMoreBlogDescription$index'>
                    <i class='fa fa-plus-circle fa-lg cursor-pointer' onclick='addMoreBlogDescriptions($index)' aria-hidden='true'></i><br>
                    <small style='font-size: 10px;'>Add More</small>
                </div>
            </div>
            <div id='addMoreBlogDescriptions$index'></div>
        ";
        return $html;
    }

    
public function addad1DesctiptionDiv($index, $valuesArr=array()) {
    $image01 = (!empty($valuesArr['image_01'])) ? "../img/ad1/".$valuesArr['image_01'] : "";
    $image02 = (!empty($valuesArr['image_02'])) ? "../img/ad1/".$valuesArr['image_02'] : "";
    $html = "
        <div class='row border mx-3 mb-3'>
            <b>0$index</b>
            <div class='col-12'>
                <div class='form-group mt-2'>
                    <textarea class='form-control' name='inputad1Description$index' rows='5'>".$valuesArr['description']."</textarea>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 d-flex align-items-center justify-content-center'>
                    <div class='form-group'>
                        <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputad1ImageOne$index'>
                        <span onclick='removead1DescImage($index,1)' class='text-danger cursor-pointer'>remove</span>
                        <p class='text-center my-1'><img id='outputad1ImageOne$index' src='$image01' style='max-height: 115px; max-width:100%' /></p>
                    </div>
                </div>
                <div class='col-md-6 d-flex align-items-center justify-content-center'>
                    <div class='form-group'>
                        <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputad1ImageTwo$index'>
                        <span onclick='removead1DescImage($index,2)' class='text-danger cursor-pointer'>remove</span>
                        <p class='text-center my-1'><img id='outputad1ImageTwo$index' src='$image02' style='max-height: 115px; max-width:100%' /></p>
                    </div>
                </div>
            </div>
            <div class='row text-center' id='addMoread1Description$index'>
                <i class='fa fa-plus-circle fa-lg cursor-pointer' onclick='addMoread1Descriptions($index)' aria-hidden='true'></i><br>
                <small style='font-size: 10px;'>Add More</small>
            </div>
        </div>
        <div id='addMoread1Descriptions$index'></div>
    ";
    return $html;
}

public function addad2DesctiptionDiv($index, $valuesArr=array()) {
    $image01 = (!empty($valuesArr['image_01'])) ? "../img/ad2/".$valuesArr['image_01'] : "";
    $image02 = (!empty($valuesArr['image_02'])) ? "../img/ad2/".$valuesArr['image_02'] : "";
    $html = "
        <div class='row border mx-3 mb-3'>
            <b>0$index</b>
            <div class='col-12'>
                <div class='form-group mt-2'>
                    <textarea class='form-control' name='inputad2Description$index' rows='5'>".$valuesArr['description']."</textarea>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 d-flex align-items-center justify-content-center'>
                    <div class='form-group'>
                        <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputad2ImageOne$index'>
                        <span onclick='removead2DescImage($index,1)' class='text-danger cursor-pointer'>remove</span>
                        <p class='text-center my-1'><img id='outputad2ImageOne$index' src='$image01' style='max-height: 115px; max-width:100%' /></p>
                    </div>
                </div>
                <div class='col-md-6 d-flex align-items-center justify-content-center'>
                    <div class='form-group'>
                        <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputad2ImageTwo$index'>
                        <span onclick='removead2DescImage($index,2)' class='text-danger cursor-pointer'>remove</span>
                        <p class='text-center my-1'><img id='outputad2ImageTwo$index' src='$image02' style='max-height: 115px; max-width:100%' /></p>
                    </div>
                </div>
            </div>
            <div class='row text-center' id='addMoread2Description$index'>
                <i class='fa fa-plus-circle fa-lg cursor-pointer' onclick='addMoread2Descriptions($index)' aria-hidden='true'></i><br>
                <small style='font-size: 10px;'>Add More</small>
            </div>
        </div>
        <div id='addMoread2Descriptions$index'></div>
    ";
    return $html;
}

    
    public function addpdfDesctiptionDiv($index, $valuesArr=array()) {
        $image01 = (!empty($valuesArr['image_01'])) ? "../img/pdf/".$valuesArr['image_01'] : "";
        $image02 = (!empty($valuesArr['image_02'])) ? "../img/pdf/".$valuesArr['image_02'] : "";
        $html = "
            <div class='row border mx-3 mb-3'>
                <b>0$index</b>
                <div class='col-12'>
                    <div class='form-group mt-2'>
                        <textarea class='form-control' name='inputpdfDescription$index' rows='5'>".$valuesArr['description']."</textarea>
                    </div>
                </div>
                <div class='row'>
                    <div class='col-md-6 d-flex align-items-center justify-content-center'>
                        <div class='form-group'>
                            <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputpdfImageOne$index'>
                            <span onclick='removepdfDescImage($index,1)' class='text-danger cursor-pointer'>remove</span>
                            <p class='text-center my-1'><img id='outputpdfImageOne$index' src='$image01' style='max-height: 115px; max-width:100%' /></p>
                        </div>
                    </div>
                    <div class='col-md-6 d-flex align-items-center justify-content-center'>
                        <div class='form-group'>
                            <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputpdfImageTwo$index'>
                            <span onclick='removepdfDescImage($index,2)' class='text-danger cursor-pointer'>remove</span>
                            <p class='text-center my-1'><img id='outputpdfImageTwo$index' src='$image02' style='max-height: 115px; max-width:100%' /></p>
                        </div>
                    </div>
                </div>
                <div class='row text-center' id='addMorepdfDescription$index'>
                    <i class='fa fa-plus-circle fa-lg cursor-pointer' onclick='addMorepdfDescriptions($index)' aria-hidden='true'></i><br>
                    <small style='font-size: 10px;'>Add More</small>
                </div>
            </div>
            <div id='addMorepdfDescriptions$index'></div>
        ";
        return $html;
    }

    
public function addhomeworkDesctiptionDiv($index, $valuesArr=array()) {
    $image01 = (!empty($valuesArr['image_01'])) ? "../img/homework/".$valuesArr['image_01'] : "";
    $image02 = (!empty($valuesArr['image_02'])) ? "../img/homework/".$valuesArr['image_02'] : "";
    $html = "
        <div class='row border mx-3 mb-3'>
            <b>0$index</b>
            <div class='col-12'>
                <div class='form-group mt-2'>
                    <textarea class='form-control' name='inputhomeworkDescription$index' rows='5'>".$valuesArr['description']."</textarea>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 d-flex align-items-center justify-content-center'>
                    <div class='form-group'>
                        <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputhomeworkImageOne$index'>
                        <span onclick='removehomeworkDescImage($index,1)' class='text-danger cursor-pointer'>remove</span>
                        <p class='text-center my-1'><img id='outputhomeworkImageOne$index' src='$image01' style='max-height: 115px; max-width:100%' /></p>
                    </div>
                </div>
                <div class='col-md-6 d-flex align-items-center justify-content-center'>
                    <div class='form-group'>
                        <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputhomeworkImageTwo$index'>
                        <span onclick='removehomeworkDescImage($index,2)' class='text-danger cursor-pointer'>remove</span>
                        <p class='text-center my-1'><img id='outputhomeworkImageTwo$index' src='$image02' style='max-height: 115px; max-width:100%' /></p>
                    </div>
                </div>
            </div>
            <div class='row text-center' id='addMorehomeworkDescription$index'>
                <i class='fa fa-plus-circle fa-lg cursor-pointer' onclick='addMorehomeworkDescriptions($index)' aria-hidden='true'></i><br>
                <small style='font-size: 10px;'>Add More</small>
            </div>
        </div>
        <div id='addMorehomeworkDescriptions$index'></div>
    ";
    return $html;
}

    
public function addbooksDesctiptionDiv($index, $valuesArr=array()) {
    $image01 = (!empty($valuesArr['image_01'])) ? "../img/books/".$valuesArr['image_01'] : "";
    $image02 = (!empty($valuesArr['image_02'])) ? "../img/books/".$valuesArr['image_02'] : "";
    $html = "
        <div class='row border mx-3 mb-3'>
            <b>0$index</b>
            <div class='col-12'>
                <div class='form-group mt-2'>
                    <textarea class='form-control' name='inputbooksDescription$index' rows='5'>".$valuesArr['description']."</textarea>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6 d-flex align-items-center justify-content-center'>
                    <div class='form-group'>
                        <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputbooksImageOne$index'>
                        <span onclick='removebooksDescImage($index,1)' class='text-danger cursor-pointer'>remove</span>
                        <p class='text-center my-1'><img id='outputbooksImageOne$index' src='$image01' style='max-height: 115px; max-width:100%' /></p>
                    </div>
                </div>
                <div class='col-md-6 d-flex align-items-center justify-content-center'>
                    <div class='form-group'>
                        <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event, 1)' name='inputbooksImageTwo$index'>
                        <span onclick='removebooksDescImage($index,2)' class='text-danger cursor-pointer'>remove</span>
                        <p class='text-center my-1'><img id='outputbooksImageTwo$index' src='$image02' style='max-height: 115px; max-width:100%' /></p>
                    </div>
                </div>
            </div>
            <div class='row text-center' id='addMorebooksDescription$index'>
                <i class='fa fa-plus-circle fa-lg cursor-pointer' onclick='addMorebooksDescriptions($index)' aria-hidden='true'></i><br>
                <small style='font-size: 10px;'>Add More</small>
            </div>
        </div>
        <div id='addMorebooksDescriptions$index'></div>
    ";
    return $html;
}

}