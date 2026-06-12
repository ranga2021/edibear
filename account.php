<?php
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/class.widgets.php");
require_once("./classes/edi_order_line_items.php");

$userHeader = new HEADER();
$user = new USER();
$widgets = new WIDGETS();

$touristID = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

$touristArr = null;
$touristName = '';
$touristCountry = '';
$touristProfilePic = '';
$touristEmail = '';
$touristUsername = '';
$touristMemberSince = '';
$userOrders = array();

if ($touristID > 0 && $user->CountRows("tourists", array("id"=>$touristID)) == 1) {
    $touristArr = $user->fetchAll(
        array("name", "profile_pic", "country", "email", "username", "timestamp"),
        array("tourists"),
        array("id"=>$touristID)
    )[0];

    $touristName = ($touristArr["name"] == NULL || $touristArr["name"] === "\r\n") ? "" : trim($touristArr["name"]);
    $touristCountry = $touristArr["country"] ?? '';
    $touristEmail = trim($touristArr["email"] ?? '');
    $touristUsername = trim($touristArr["username"] ?? '');
    $touristMemberSince = !empty($touristArr["timestamp"]) ? date("F j, Y", strtotime($touristArr["timestamp"])) : "";
    $touristProfilePic = ($touristArr["profile_pic"] == NULL)
        ? ""
        : "src='".$widgets->createCachelessImage("./img/profile-pics/".$touristArr["profile_pic"])."'";

    /* Orders store shopper id in orders.session_id (see order_place.php) */
    $userOrders = $user->fetchAll(
        array(
            "id",
            "order_number", "created_at", "total", "subtotal", "shipping",
            "payment_method", "payment_status", "order_status",
            "first_name", "last_name", "email", "mobile",
            "address_line", "city", "district", "postal_code", "company_name"
        ),
        array("orders"),
        array("session_id" => (string) $touristID),
        "created_at DESC"
    );

    $accountOrderItems = array();
    if (!empty($userOrders)) {
        try {
            $conn = $user->getConnection();
            $chk = $conn->query("SHOW TABLES LIKE " . $conn->quote("order_items"));
            if ($chk && $chk->rowCount() > 0) {
                $oids = array();
                foreach ($userOrders as $uo) {
                    if (!empty($uo["id"])) {
                        $oids[] = (int) $uo["id"];
                    }
                }
                $oids = array_values(array_unique($oids));
                if (!empty($oids)) {
                    $inList = implode(",", $oids);
                    $iq = $conn->query("SELECT * FROM order_items WHERE order_id IN (" . $inList . ") ORDER BY order_id ASC, id ASC");
                    if ($iq) {
                        foreach ($iq->fetchAll(PDO::FETCH_ASSOC) as $li) {
                            $oid = (int) $li["order_id"];
                            if (!isset($accountOrderItems[$oid])) {
                                $accountOrderItems[$oid] = array();
                            }
                            $accountOrderItems[$oid][] = $li;
                        }
                    }
                }
            }
            if (!empty($accountOrderItems)) {
                EdiOrderLineItems::enrichItemsByOrder($conn, $accountOrderItems);
            }
        } catch (Throwable $e) {
            $accountOrderItems = array();
        }
    }
}

// 🔥 HANDLE DELETE TESTIMONIAL
if (isset($_POST['deleteTestimonialConfirm'])) {
    if ($touristID <= 0 || !$touristArr) {
        header("Location: ./login.php");
        exit;
    }
    $tID = (int)$_POST['hiddenTestimonialID'];
    $conn = $user->getConnection();
    
    // 1. Get the image filename from the database first
    $stmtImg = $conn->prepare("SELECT image FROM testimonials_images WHERE testimonial_id = ?");
    $stmtImg->execute([$tID]);
    $imgData = $stmtImg->fetch();

    if ($imgData) {
        $filePath = "./img/testimonials/" . $imgData['image'];
        // 2. Delete the physical file from the server
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // 3. Delete from database (testimonials_images will likely delete via FK or manual query)
    $stmt1 = $conn->prepare("DELETE FROM testimonials_images WHERE testimonial_id = ?");
    $stmt1->execute([$tID]);

    $stmt2 = $conn->prepare("DELETE FROM testimonials WHERE id = ? AND user_id = ?");
    $stmt2->execute([$tID, $touristID]);
    
    
    header("Location: ./account?uid=$touristID");
    exit;
}

// 🔥 HANDLE FORM SUBMIT
if (isset($_POST['addTestimonialSubmit'])) {
    if ($touristID <= 0 || !$touristArr) {
        header("Location: ./login.php");
        exit;
    }

    $inputTouristName = trim($_POST['inputTouristName'] ?? "");
    $inputTouristCountry = trim($_POST['inputTouristCountry'] ?? "");
    $starRating = (int)($_POST['starRating'] ?? 1);
    $inputOneWord = trim($_POST['inputOneWord'] ?? "");
    $inputReview = trim($_POST['inputReview'] ?? "");

    try {
        $conn = $user->getConnection();
        
        // 1. Update Tourist Info (Name and Country)
        $stmt = $conn->prepare("UPDATE tourists SET name=?, country=? WHERE id=?");
        $stmt->execute([$inputTouristName, $inputTouristCountry, $touristID]);

        // 2. Insert the Testimonial text
        $stmt = $conn->prepare("INSERT INTO testimonials (user_id, name, ratings, one_word, review, status) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->execute([$touristID, $inputTouristName, $starRating, $inputOneWord, $inputReview]);
        $testimonialID = $conn->lastInsertId(); // We need this ID for the image table

        // 3. Handle the Image Upload
        if (isset($_FILES['inputProfilePic']) && $_FILES['inputProfilePic']['error'] == 0) {
            $allowedTypes = ['jpg','jpeg','png','webp'];
            $fileExt = strtolower(pathinfo($_FILES['inputProfilePic']['name'], PATHINFO_EXTENSION));

            if (in_array($fileExt, $allowedTypes)) {
                // Ensure directory exists
                if (!is_dir("./img/testimonials")) { mkdir("./img/testimonials", 0777, true); }

                // Create a unique filename (e.g., t_15_1710842000.jpg)
                $newFileName = "t_" . $testimonialID . "_" . time() . "." . $fileExt;
                $destination = "./img/testimonials/" . $newFileName;

                // Move file from temporary folder to your testimonials folder
                if (move_uploaded_file($_FILES['inputProfilePic']['tmp_name'], $destination)) {
                    // 4. INSERT into the image table
                    $stmtImg = $conn->prepare("INSERT INTO testimonials_images (testimonial_id, image) VALUES (?, ?)");
                    $stmtImg->execute([$testimonialID, $newFileName]);
                }
            }
        }

        header("Location: ./account?uid=$touristID");
        exit;
    } catch (PDOException $e) { 
        die("DB ERROR: " . $e->getMessage()); 
    }
}
?>

<script>
const userSession = localStorage.getItem('user_session');

if (!userSession) {
    window.location.replace('./login');
}

const urlParams = new URLSearchParams(window.location.search);
const uid = urlParams.get('uid');

if (userSession && !uid) {
    window.location.replace('./account?uid=' + userSession);
}
</script>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta property='og:title' content='Kids Coloring Pages, Activity Books & Study Packs'/>
    <meta name='description' content='“edibear” is a website that provides a variety of kids coloring pages, activity books, relevant model papers, school related study materials, fun activities for developing the abilities of kids. '/>
    <meta name='keywords' content='printable coloring pages for kids, free coloring pages, kids activities, Relevant past papers, model Papers, school related study materials, Fun activities for kids, Developing kids&#8217; abilities, Educational resources for kids, Downloadable kids&#8217; materials, Creative learning for kids, Sinhala Coloring Pages, Tamil Coloring Pages' />
    <link rel="stylesheet" href="./admin-area/assets/css/mobiscroll.javascript.min.css">
    <script src="./admin-area/assets/js/mobiscroll.javascript.min.js"></script>
    <?php echo $userHeader->printUserHeader("Account") ?>
    <style>
        .md-country-picker-item {
            position: relative;
            line-height: 20px;
            padding: 10px 0 10px 40px;
        }
        .md-country-picker-flag {
            position: absolute;
            left: 0;
            height: 20px;
        }
        .mbsc-scroller-wheel-item-2d .md-country-picker-item {
            transform: scale(1.1);
        }
        .inputRatingStar, td{
            cursor: pointer;
        }
        .edi-account-orders-table { font-size: 0.875rem; }
        .edi-account-order-details summary { user-select: none; outline: none; }
    </style>
</head>

<body>
    <?php
        echo $userHeader->printUserNav(true);        //Topbar
        
    ?>
    <div class="page-header-bg"></div>

    <div class="container-fluid py-3 page-header-content">
        <div class="container">
            <nav class="edi-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Account</li>
                </ol>
            </nav>
            
             <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>Account</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>
        </div>
    </div>
    <div class="container-fluid pt-2 pb-4">
        <div class="container">
            <?php if (!$touristArr): ?>
            <div class="alert alert-warning border-0 shadow-sm" role="alert">
                This account could not be loaded. Please <a href="login.php" class="alert-link">sign in</a> again, or return <a href="./" class="alert-link">home</a>.
            </div>
            <?php else: ?>

            <div class="edi-account-layout">
                <aside class="edi-account-sidebar">
                    <div class="edi-account-sidebar-logo">
                        <a href="./">
                            <img src="./img/Logo.png" alt="edibear">
                        </a>
                    </div>
                    <nav class="edi-account-sidebar-nav">
                        <a href="#edi-account-profile" class="edi-account-nav-item active" data-section="profile">
                            <i class="fa fa-tachometer"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="#edi-account-orders" class="edi-account-nav-item" data-section="orders">
                            <i class="fa fa-shopping-bag"></i>
                            <span>Orders</span>
                        </a>
                        <a href="#edi-account-reviews" class="edi-account-nav-item" data-section="reviews">
                            <i class="fa fa-star"></i>
                            <span>Reviews</span>
                        </a>
                        <a href="./account?uid=<?php echo (int) $touristID; ?>#edi-account-testimonial-form" class="edi-account-nav-item" data-section="edit-profile">
                            <i class="fa fa-user-circle"></i>
                            <span>Edit Profile</span>
                        </a>
                        <a href="./logout.php" class="edi-account-nav-item">
                            <i class="fa fa-sign-out"></i>
                            <span>Logout</span>
                        </a>
                    </nav>
                </aside>

                <div class="edi-account-main">
                    <div class="edi-account-quicklinks">
                        <span class="edi-account-quicklinks-label">Quick Link</span>
                        <div class="edi-account-quicklinks-list">
                            <a href="product_page.php" class="edi-account-quicklink-btn">Continue Shopping</a>
                            <a href="cart.php?uid=<?php echo (int) $touristID; ?>" class="edi-account-quicklink-btn">Honey Cart</a>
                            <a href="testimonials" class="edi-account-quicklink-btn">Community</a>
                        </div>
                    </div>

            <div class="card border-0 shadow-sm mb-3" id="edi-account-profile">
                <div class="card-body py-3">
                    <h5 class="font-weight-bold mb-2">Your profile</h5>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($touristEmail, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mb-1"><strong>Username:</strong> <?php echo htmlspecialchars($touristUsername, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if ($touristName !== ''): ?>
                    <p class="mb-1"><strong>Display name:</strong> <?php echo htmlspecialchars($touristName, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <p class="mb-1"><strong>Country:</strong> <?php echo htmlspecialchars($touristCountry, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if ($touristMemberSince !== ''): ?>
                    <p class="mb-1"><strong>Member since:</strong> <?php echo htmlspecialchars($touristMemberSince, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3" id="edi-account-orders">
                <div class="card-body py-3">
                    <h5 class="font-weight-bold mb-2">Order history</h5>
                    <?php if (empty($userOrders)): ?>
                        <p class="text-muted mb-0">You have not placed any orders yet. When you complete checkout, your orders and their status will show here.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped edi-account-orders-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Order</th>
                                    <th scope="col">Placed</th>
                                    <th scope="col" class="text-right">Total</th>
                                    <th scope="col">Payment</th>
                                    <th scope="col">Pay status</th>
                                    <th scope="col">Order status</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($userOrders as $o):
                                $payMethod = $o['payment_method'] ?? '';
                                if ($payMethod === 'bank_transfer') {
                                    $payLabel = 'Bank transfer';
                                } elseif ($payMethod === 'cod') {
                                    $payLabel = 'Cash on delivery';
                                } else {
                                    $payLabel = (string) $payMethod;
                                }
                                $ps = strtolower((string) ($o['payment_status'] ?? ''));
                                $pillPay = $ps === 'paid' ? 'badge-success' : ($ps === 'failed' ? 'badge-danger' : 'badge-warning');
                                $createdRaw = $o['created_at'] ?? '';
                                $placedDisplay = $createdRaw !== '' ? date('M j, Y', strtotime($createdRaw)) : '—';
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($o['order_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($placedDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-right">Rs. <?php echo number_format((float) ($o['total'] ?? 0), 2); ?></td>
                                    <td><?php echo htmlspecialchars($payLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge <?php echo $pillPay; ?>"><?php echo htmlspecialchars($o['payment_status'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td><?php echo htmlspecialchars($o['order_status'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <details class="edi-account-order-details">
                                            <summary class="text-success font-weight-bold" style="cursor:pointer;">View</summary>
                                            <div class="small text-muted border rounded p-2 mt-2 bg-light">
                                                <p class="mb-1"><strong>Ship to:</strong> <?php echo htmlspecialchars(trim(($o['first_name'] ?? '') . ' ' . ($o['last_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></p>
                                                <?php if (!empty($o['company_name'])): ?>
                                                <p class="mb-1"><?php echo htmlspecialchars($o['company_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                <?php endif; ?>
                                                <p class="mb-1"><?php echo htmlspecialchars($o['address_line'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                                <p class="mb-1"><?php echo htmlspecialchars(trim(($o['city'] ?? '') . ', ' . ($o['district'] ?? '') . ' ' . ($o['postal_code'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></p>
                                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($o['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                                <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($o['mobile'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                                <p class="mb-2"><strong>Subtotal:</strong> Rs. <?php echo number_format((float) ($o['subtotal'] ?? 0), 2); ?> &nbsp; <strong>Shipping:</strong> Rs. <?php echo number_format((float) ($o['shipping'] ?? 0), 2); ?></p>
                                                <?php
                                                $oidAcc = (int) ($o['id'] ?? 0);
                                                $lines = ($oidAcc > 0 && !empty($accountOrderItems[$oidAcc])) ? $accountOrderItems[$oidAcc] : array();
                                                ?>
                                                <?php if (!empty($lines)): ?>
                                                <p class="mb-2 font-weight-bold text-dark">Items</p>
                                                <div class="edi-account-order-lines small">
                                                    <?php foreach ($lines as $li):
                                                        $pn = htmlspecialchars((string) ($li["product_name"] ?? ""), ENT_QUOTES, "UTF-8");
                                                        $qty = (int) ($li["quantity"] ?? 0);
                                                        $lineTot = number_format((float) ($li["line_total"] ?? 0), 2);
                                                        $img = trim((string) ($li["edi_product_image"] ?? ""));
                                                        $imgUrl = $img !== "" ? ("./img/products/" . rawurlencode($img)) : "";
                                                        $lang = htmlspecialchars(trim((string) ($li["edi_product_language"] ?? "")), ENT_QUOTES, "UTF-8");
                                                        $grade = htmlspecialchars(trim((string) ($li["edi_product_grade"] ?? "")), ENT_QUOTES, "UTF-8");
                                                        $cat = htmlspecialchars(trim((string) ($li["edi_product_category"] ?? "")), ENT_QUOTES, "UTF-8");
                                                        $sub = htmlspecialchars(trim((string) ($li["edi_product_subcategory"] ?? "")), ENT_QUOTES, "UTF-8");
                                                        $metaBits = array_filter(array($lang, $grade, $cat, $sub));
                                                        $metaStr = $metaBits !== array() ? implode(" · ", $metaBits) : "—";
                                                        ?>
                                                    <div class="d-flex align-items-start border-bottom py-2" style="gap:10px;">
                                                        <?php if ($imgUrl !== ""): ?>
                                                        <img src="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES, "UTF-8"); ?>" alt="" width="48" height="48" class="rounded" style="object-fit:cover;background:#f1f5f9;flex-shrink:0;" onerror="this.style.visibility='hidden';">
                                                        <?php else: ?>
                                                        <div class="rounded d-flex align-items-center justify-content-center text-muted bg-light" style="width:48px;height:48px;flex-shrink:0;font-size:0.7rem;">—</div>
                                                        <?php endif; ?>
                                                        <div class="flex-grow-1 min-w-0">
                                                            <div class="font-weight-bold text-dark"><?php echo $pn; ?></div>
                                                            <div class="text-muted" style="font-size:0.72rem;"><?php echo $metaStr; ?></div>
                                                            <div class="mt-1">× <?php echo $qty; ?> — Rs. <?php echo $lineTot; ?></div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php else: ?>
                                                <p class="mb-0 text-muted">Item list appears here for orders placed after <code>migration_order_items.sql</code> is applied.</p>
                                                <?php endif; ?>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ( $user->CountRows("testimonials", array("user_id"=>$touristID)) > 0 ) { ?>
            <div class="row mb-3" id="edi-account-reviews">
                <div class="card col-12">
                    <div class="card-body">
                        <h5 class="font-weight-bold">Your Testimonials</h5>
                        <div class="table-responsive">
                            <table class="table align-items-center" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class='text-center'>Short Review</th>
                                        <th class='text-center'>Ratings</th>
                                        <th class='text-center'>Status</th>
                                        <th class='text-center'>Timestamp</th>
                                        <th class='text-center'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ( $user->fetchAll(array("id","one_word","ratings","status","timestamp"), array("testimonials"), array("user_id"=>$touristID), "timestamp DESC") as $tableRow ) {
                                            $testimonialID = $tableRow['id'];
                                            $testimonialStatus = $tableRow['status'];
                                            $testimonialStatus = ($testimonialStatus==1) ? "Approved" : (($testimonialStatus==-1) ? "Rejected" : "Pending");
                                            echo "
                                                <tr>
                                                    <td class='text-center' onclick='editTestimonial($testimonialID)'>".$tableRow['one_word']."</td>
                                                    <td class='text-center' onclick='editTestimonial($testimonialID)'>";
                                                        for ( $i=1; $i<=5; $i++ ) {
                                                            $starColor = ($i<=$tableRow['ratings']) ? "text-warning" : "";
                                                            echo "<span class='fa fa-star $starColor'></span>";
                                                        }
                                            echo "</td>
                    <td class='text-center' onclick='editTestimonial($testimonialID)'>$testimonialStatus</td>
                    <td class='text-center' onclick='editTestimonial($testimonialID)'>".$tableRow['timestamp']."</td>
                    <td class='text-center'>
                        <form method='post' onsubmit='return confirm(\"Delete this testimonial and image?\");'>
                            <input type='hidden' name='hiddenTestimonialID' value='$testimonialID'>
                            <button type='submit' name='deleteTestimonialConfirm' class='btn btn-sm btn-danger'>
                                <i class='fa fa-trash'></i>
                            </button>
                        </form>
                    </td>
                </tr>
            ";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="row" id="edi-account-testimonial-form">
                <div class="card col-12">
                    <div class="card-body">
                        <h5 class="font-weight-bold" id="addEditTestimonialHeading">Add Testimonial</h5>
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        Name<input class='form-control' type='text' name='inputTouristName' value='<?php echo $touristName; ?>' required>
                                    </div>
                                    <div class="form-group">
                                        <label>
                                            Country
                                            <input mbsc-input id="demo-country-picker" name="inputTouristCountry" data-dropdown="true" data-input-style="box" data-label-style="stacked" placeholder="Please select..." required/>
                                        </label>
                                    </div>
                                    Rating &nbsp;
                                    <span class="fa fa-star inputRatingStar text-warning" id="star1"></span>
                                    <span class="fa fa-star inputRatingStar" id="star2"></span>
                                    <span class="fa fa-star inputRatingStar" id="star3"></span>
                                    <span class="fa fa-star inputRatingStar" id="star4"></span>
                                    <span class="fa fa-star inputRatingStar" id="star5"></span>
                                    <input type="hidden" name="starRating" value="1">
                                    <div class="form-group mt-2">
                                        Say your review in one word<input class='form-control' type='text' name='inputOneWord' maxlength="50" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class='col-12 border m-1'>
                                        <div class='form-group'>
                                            <label for='example-text-input' class='form-control-label'>Profile Picture</label>
                                            <input class='form-control' type='file' accept='image/*' onchange='loadImageFile(event)' name='inputProfilePic'>
                                            <p class='text-center my-1'><img id='outputProfilePic' <?php echo $touristProfilePic; ?> style='max-height: 200px; max-width:100%' /></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-12">
                                    Leave a Review<textarea name="inputReview" class="form-control" rows="5" maxlength="500"></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="hiddenTestimonialID" value="0">

<input type="submit" class="btn btn-primary px-4" value="Add Testimonial" name="addTestimonialSubmit">


<a href="./" class="btn btn-secondary px-4">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
                </div><!-- /.edi-account-main -->
            </div><!-- /.edi-account-layout -->
            <?php endif; ?>
        </div>
    </div>
    <div id="editTestimonial"></div>
    <div id="deleteTestimonial"></div>
    <div id="removeTestimonialImage"></div>
    <?php
        echo $userHeader->printUserFooter();
        if ($touristArr) {
            $countryJs = json_encode($touristCountry, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
            echo "<script>$(function(){ $('input[name=inputTouristCountry]').val(" . $countryJs . "); });</script>";
        }
    ?>
    <script>
        mobiscroll.setOptions({
            theme: 'ios',
            themeVariant: 'light'
        });
        var inst = mobiscroll.select('#demo-country-picker', {
            display: 'anchored',
            filter: true,
            itemHeight: 40,
            renderItem: function (item) {
                return '<div class="md-country-picker-item">' +
                    '<img class="md-country-picker-flag" src="https://img.mobiscroll.com/demos/flags/' + item.data.value + '.png" />' +
                    item.display + '</div>';
            }
        });
        mobiscroll.util.http.getJson('https://trial.mobiscroll.com/content/countries.json', function (resp) {
            var countries = [];
            for (var i = 0; i < resp.length; ++i) {
                var country = resp[i];
                countries.push({ text: country.text, value: country.value });
            }
            inst.setOptions({ data: countries });
        });
        $(".inputRatingStar").click(function (event){
            var starNumber = event['target']['id'].substr(4,1);
            $("input[name='starRating']").val(starNumber);
            for ( var i=1; i<=5; i++ ) {
                if ( starNumber >= i ) {
                    $("#star"+i).addClass("text-warning");
                } else {
                    $("#star"+i).removeClass("text-warning");
                }
            }
        });

        function loadImageFile(event, sessionTF=0) { 
            var imageDivID = event.target.name.replace("input", "output");
            var imageDivIdNumber = imageDivID.substr(-1);
            $("#"+imageDivID).addClass("border");
            var image = document.getElementById(imageDivID);
            image.src = URL.createObjectURL(event.target.files[0]);
            if (sessionTF) {
                $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {
                        changeTestimonialImage: imageDivIdNumber
                    },
                    success: function(html) {
                        $("#removeTestimonialImage").html(html).show();
                    }
                }); 
            }
        }

        function editTestimonial(testimonialID) {
            $("#addEditTestimonialHeading").text("Edit a Testimonial");
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {
                    editTestimonial: testimonialID
                },
                success: function(html) {
                    $("input[name='hiddenTestimonialID']").val(testimonialID);
                    $("input[name='addTestimonialSubmit']").prop("disabled",true);
                    $("input[name='updateTestimonialSubmit']").prop("disabled",false);
                    $("input[name='deleteTestimonialSubmit']").prop("disabled",false);
                    $("#editTestimonial").html(html).show();
                }
            }); 
            $('html, body').animate({
                scrollTop: $("#addEditTestimonialHeading").offset().top
            });
        }

        function deleteTestimonial() {
            var testimonialID = $("input[name='hiddenTestimonialID']").val();
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {
                    deleteTestimonial: testimonialID
                },
                success: function(html) {
                    $("#deleteTestimonial").html(html).show();
                }
            }); 
        }

        function removeTestimonialImage(imageID) {
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {
                    removeTestimonialImage: imageID
                },
                success: function(html) {
                    $("#removeTestimonialImage").html(html).show();
                }
            }); 
        }
        
        function clearImageSlot(id) {
    // Clears the file selection
    document.getElementById('inputImage' + id).value = "";
    // Clears the preview image
    const img = document.getElementById('outputTestimonialImage' + id);
    img.src = "";
    img.classList.remove("border");
}

// Modify your existing editTestimonial to enable the delete button
function editTestimonial(testimonialID) {
    $("#addEditTestimonialHeading").text("Edit/Delete Testimonial");
    $("input[name='hiddenTestimonialID']").val(testimonialID);
    
    // Disable Add, Enable Delete
    $("input[name='addTestimonialSubmit']").prop("disabled", true);
    $("#deleteBtn").prop("disabled", false);

    $.ajax({
        type: "POST",
        url: "ajax.php",
        data: { editTestimonial: testimonialID },
        success: function(html) {
            $("#editTestimonial").html(html).show();
        }
    });
    $('html, body').animate({
        scrollTop: $("#addEditTestimonialHeading").offset().top
    });
}

    // Sidebar nav: smooth scroll + active state
    $(document).ready(function() {
        $('.edi-account-nav-item[data-section]').on('click', function(e) {
            var target = $(this).attr('href');
            if (target && target.charAt(0) === '#') {
                e.preventDefault();
                var $el = $(target);
                if ($el.length) {
                    $('html, body').animate({ scrollTop: $el.offset().top - 90 }, 400);
                }
                $('.edi-account-nav-item').removeClass('active');
                $(this).addClass('active');
            }
        });
    });
    </script>
</body>

</html>