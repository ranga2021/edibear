<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.widgets.php");
    $user = new USER();
    $widgets = new WIDGETS();

    if ( isset($_POST['addMoreTestimonials']) ) {
        $lastTestimonialID = (int)$_POST['addMoreTestimonials'];
        foreach ( $user->fetchAll(array("id","user_id","ratings","one_word","review"), array("testimonials"), array("status"=>1), "id DESC LIMIT 3", "id<$lastTestimonialID") as $testimonialArr ) {
            echo $widgets->displayTestimonial(array_merge($testimonialArr, $user->fetchAll(array("name","profile_pic","country"), array("tourists"), array("id"=>$testimonialArr['user_id']))[0]),$user);
        }
        $lastTestimonialID = $testimonialArr['id'];
        $firstAppovedTestimonialID = $user->MinValue("testimonials", "id", array("status"=>1));
        echo "
            <div id='addMoreTestimonialsDiv$lastTestimonialID'>";
            if ( $firstAppovedTestimonialID < $lastTestimonialID ) {
                echo "
                    <div class='row mt-4 justify-content-center'>
                        <button class='btn btn-primary px-4 rounded' onclick='addMoreTestimonials($lastTestimonialID)'>SEE MORE</button>
                    </div>
                ";
            }
        echo "
            </div>
        ";
    }

    if ( isset($_POST['subscribeNewsletter']) && !empty($_POST['subscribeNewsletter']) ) {
        $emailAddr = $_POST['subscribeNewsletter'];
        $to = "info.edibear@gmail.com";
        $toName = "";
        $ccRecipient = "hello@edibear.com";
        $subject = "Edibear - NEWS LETTER";
        $message = "
            <h1>Edibear - NEWS LETTER</h1>
            <br>
            <table border='0'>
                <tr><td>Email</td><td>: $emailAddr</td></tr>
            </table>
        ";
        require 'mail_inc.php';
        $from = 'system@traveylo.com';
        $fromName = "Edibear";
        sendMail($from, $fromName, $to, $toName, $ccRecipient, $subject, $message);
        echo "<script>alert('Successfully sent the message.');location.reload();</script>";
    }

    if ( $user->is_loggedin("session_tourism_user") ) {
        if ( $user->checkTimeout() ) {

            if ( isset($_POST['editTestimonial']) ) {
                $testimonialID = (int)$_POST['editTestimonial'];
                $testimonialArr = $user->fetchAll(array("ratings","one_word","review"), array("testimonials"), array("id"=>$testimonialID, "user_id"=>$user->sessionUser("session_tourism_user")))[0];
                $testimonialOneWord = $testimonialArr['one_word'];
                $testimonialReview = $testimonialArr['review'];
                $testimonialRatings = $testimonialArr['ratings'];
                echo "
                    <script>
                        $('input[name=inputOneWord]').val('$testimonialOneWord');
                        $('textarea[name=inputReview]').val('$testimonialReview');
                        $('input[name=starRating]').val('$testimonialRatings');
                        for ( var i=1; i<=5; i++ ) {
                            if ( $testimonialRatings >= i ) {
                                $('#star'+i).addClass('text-warning');
                            } else {
                                $('#star'+i).removeClass('text-warning');
                            }
                        }
                    </script>
                ";
                $sessionImageArr = array(array("",0), array("",0), array("",0), array("",0), array("",0), array("",0));
                foreach ( $user->fetchAll(array("image"),array("testimonials_images"),array("testimonial_id"=>$testimonialID)) as $row ) {
                    $image = $widgets->createCachelessImage("./img/testimonials/".$row['image']);
                    $divID = explode(".", explode("-", $row['image'])[1])[0];
                    echo "<script>$('#outputTestimonialImage$divID').attr('src','$image');</script>";
                    $sessionImageArr[$divID-1][0] = $row['image'];
                    $sessionImageArr[$divID-1][1] = 1;
                }
                $_SESSION['sessionImageArr'] = $sessionImageArr;
            }

            if ( isset($_POST['removeTestimonialImage']) ) {
                $imageID = (int)$_POST['removeTestimonialImage'];
                echo "
                    <script>
                        $('input[name=inputTestimonialImage$imageID]').val('');
                        $('#outputTestimonialImage$imageID').removeAttr('src');
                    </script>
                ";
                if ( isset($_SESSION['sessionImageArr']) && $_SESSION['sessionImageArr'][0] != "" ) {
                    $_SESSION['sessionImageArr'][$imageID-1][1] = -1;
                }
            }

            if ( isset($_POST['changeTestimonialImage']) && $_SESSION['sessionImageArr'][0] != "" ) {
                $imageID = (int)$_POST['changeTestimonialImage'];
                if ( isset($_SESSION['sessionImageArr']) ) {
                    $_SESSION['sessionImageArr'][$imageID-1][1] = -1;
                }
            }

            if ( isset($_POST['deleteTestimonial']) ) {
                $testimonialID = (int)$_POST['deleteTestimonial'];
                echo "
                <script>
                    $(function(){
                        $('#deleteTestimonialModal').modal('show');
                    });
                </script>
                <div class='modal fade' id='deleteTestimonialModal' data-backdrop='static' tabindex='-1' role='dialog' aria-labelledby='staticBackdropLabel' aria-hidden='true'>
                    <div class='modal-dialog modal-lg' role='document'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h5 class='modal-title' id='staticBackdropLabel'>Delete Testimonial</h5>
                            </div>
                            <div class='modal-body'>";
                            foreach ( $user->fetchAll(array("id","user_id","ratings","one_word","review","status"), array("testimonials"), array("id"=>$testimonialID)) as $testimonialArr ) {
                                echo $widgets->displayTestimonial(array_merge($testimonialArr, $user->fetchAll(array("name","profile_pic","country"), array("tourists"), array("id"=>$testimonialArr['user_id']))[0]),$user);
                            }
                echo "
                                Are you sure to delete this testimonial
                                <form method='post'>
                                    <input type='hidden' name='deleteTestimonialID' value='$testimonialID'>
                                    <button class='btn btn-sm btn-danger' name='confirmDeleteTestimonial' type='submit'>Delete</button>
                                    <button class='btn btn-sm btn-secondary' type='button' onclick='location.reload()'>Cancel</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                ";
            }

        }
    }
?>