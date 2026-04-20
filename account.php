<?php
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/class.widgets.php");

$userHeader = new HEADER();
$user = new USER();
$widgets = new WIDGETS();

$touristID = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

$touristArr = null;

if ($touristID > 0 && $user->CountRows("tourists", array("id"=>$touristID)) == 1) {
    $touristArr = $user->fetchAll(
        array("name", "profile_pic", "country"),
        array("tourists"),
        array("id"=>$touristID)
    )[0];

    $touristName = ($touristArr["name"] == NULL) ? "" : $touristArr["name"];
    $touristCountry = $touristArr["country"];
    $touristProfilePic = ($touristArr["profile_pic"] == NULL)
        ? ""
        : "src='".$widgets->createCachelessImage("./img/profile-pics/".$touristArr["profile_pic"])."'";
}

// 🔥 HANDLE DELETE TESTIMONIAL
if (isset($_POST['deleteTestimonialConfirm'])) {
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
    if ($touristID <= 0) { die("Invalid User ID"); }

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
    <meta property='og:title' content='Traveylo | Sri Lanka Tour Packages | Travel Agent in Sri Lanka'/>
    <meta name='description' content='“Ayubowan!” Traveylo.com provides tour packages covering the most beautiful places 
in Sri Lanka, and you can travel in luxury with your own vehicle around 
our beautiful country. So reserve your tour with us.' />
    <meta name='keywords' content='Travel Agents In Sri Lanka / Sri Lanka Tourism / Sri Lanka Tourist Destinations / Places To Visit In Sri Lanka With Family / How To Travel In Sri Lanka / Sri Lanka Tours & Travels / Tour Packages In Sri Lanka / Sri Lanka Itinerary / Sri Lanka Travel Guide /Sri Lanka HotelsSri Lanka Tour Operators /Sri Lanka Budgets Tours /Small Group Tour In Sri Lanka / Sri Lanka Holiday Packages /Sri Lanka Tour Packages For Couple / Sri Lanka Tour Packages For Family /Sri Lanka Tour Packages Price / What To Do In Sri Lanka /Popular Destinations In Sri Lanka' />
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
    </style>
</head>

<body>
    <?php
        echo $userHeader->printUserNav(true);        //Topbar
        
    ?>
    <div class="page-header-bg"></div>

    <div class="container-fluid py-4 page-header-content">
        <div class="container">
            <nav class="edi-breadcrumb" aria-label="Breadcrumb">
            <i class="fa fa-home pt-1 pr-2" aria-hidden="true"></i><a href="./">Home</a><i class="fa fa-angle-right pt-1 px-2" aria-hidden="true"></i><span>Account</span>
            </nav>
            
             <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h1>Account</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>
            <div class="text-right mb-3">
                <a href="./logout.php" class="btn btn-danger">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>
        </div>
    </div>
    <div class="container-fluid pt-3 pb-5">
        <div class="container">
            <?php if ( $user->CountRows("testimonials", array("user_id"=>$touristID)) > 0 ) { ?>
            <div class="row mb-4">
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
            <div class="row">
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
        </div>
    </div>
    <div id="editTestimonial"></div>
    <div id="deleteTestimonial"></div>
    <div id="removeTestimonialImage"></div>
    <?php 
        echo $userHeader->printUserFooter(); 
        echo "
        <script>
            $( document ).ready(function() {
                $('input[name=inputTouristCountry]').val('$touristCountry')
            }); 
        </script>";
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
    </script>
</body>

</html>