<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SESSION FIX (same as add-books)
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("manage-users");
$user = new USER();
$deleteTouristSubmit = "";



/* ================= FORM HANDLING ================= */
if (isset($_POST['newUserSubmit']) || isset($_POST['editUserSubmit']) || isset($_POST['deleteUserSubmit'])) {

    $inputUserUsername = htmlspecialchars($_POST['inputUserUsername'] ?? "");
    $inputUserEmail    = htmlspecialchars($_POST['inputUserEmail'] ?? "");
    $inputUserCountry  = htmlspecialchars($_POST['inputUserCountry'] ?? "");
    $inputUserPassword = $_POST['inputUserPassword'] ?? "";
    $inputUserConfirmPassword = $_POST['inputUserConfirmPassword'] ?? "";

    // ADD + EDIT
    if (isset($_POST['newUserSubmit']) || isset($_POST['editUserSubmit'])) {

        if ($inputUserPassword == $inputUserConfirmPassword) {

            $hashedPassword = "pass".password_hash($inputUserPassword, PASSWORD_DEFAULT);

            // ADD
            if (isset($_POST['newUserSubmit'])) {

                $user->insertTable("tourists", [
                    "username"=>$inputUserUsername,
                    "password"=>$hashedPassword,
                    "email"=>$inputUserEmail,
                    "country"=>$inputUserCountry
                ]);

                $alertString = "Successfully added a new Tourist";
            }

            // EDIT
            if (isset($_POST['editUserSubmit'])) {

                $UserHiddenID = (int)($_POST['UserHiddenID'] ?? 0);

                $user->updateTable("tourists", [
                    "username"=>$inputUserUsername,
                    "password"=>$hashedPassword,
                    "email"=>$inputUserEmail,
                    "country"=>$inputUserCountry
                ], ["id"=>$UserHiddenID]);

                $alertString = "Successfully updated a Tourist";
            }

            echo "<script>alert('$alertString');location.href='./manage-users'</script>";

        } else {
            echo "<script>alert('Passwords are not matching');location.href='./manage-users'</script>";
        }
    }

    // DELETE (modal trigger)
    if (isset($_POST['deleteUserSubmit'])) {

        $UserHiddenID = (int)($_POST['UserHiddenID'] ?? 0);

        $deleteTouristSubmit = $user->confirmDeleteModal(
            $UserHiddenID,
            $inputUserUsername,
            $inputUserEmail,
            "Confirm Delete a Tourist",
            "manage-users"
        );
    }
}

/* ================= CONFIRM DELETE ================= */
if (isset($_POST['confirmDeleteSubmit'])) {

    $deleteNameID = (int)($_POST['deleteNameID'] ?? 0);

    $user->updateTable("tourists", [
        "status"=>0,
        "delete_status"=>1
    ], ["id"=>$deleteNameID]);

    echo "<script>alert('Successfully Deleted a Tourist');location.href='./manage-users';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta property='og:title' content='Kids Coloring Pages, Activity Books & Study Packs'/>
<meta name='description' content='“edibear” is a website that provides a variety of kids coloring pages, activity books, relevant model papers, school related study materials, fun activities for developing the abilities of kids. '/>
<meta name='keywords' content='printable coloring pages for kids, free coloring pages, kids activities, Relevant past papers, model Papers, school related study materials, Fun activities for kids, Developing kids&#8217; abilities, Educational resources for kids, Downloadable kids&#8217; materials, Creative learning for kids, Sinhala Coloring Pages, Tamil Coloring Pages' />
    <!-- Mobiscroll JS and CSS Includes -->
    <link rel="stylesheet" href="./assets/css/mobiscroll.javascript.min.css">
    <script src="./assets/js/mobiscroll.javascript.min.js"></script>
  <?php echo $adminHeader->printAdminHeader(); ?>
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
  </style>
</head>

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav();?>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-header pb-0">
              <h4>Users</h4>
            </div>
            <div class="card-body p-3">
              <div class="table-responsive">
                <table class="table table-bordered" id="usersDataTable" data-order='[[ 3, "desc" ]]' width="100%" cellspacing="0">
                  <thead>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Timestamp</th>
                    <th></th>
                  </thead>
                  <tfoot>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Timestamp</th>
                    <th></th>
                  </tfoot>
                  <tbody>
                    <?php
                      foreach ( $user->fetchAll(array("id", "username", "email", "country", "status", "timestamp"), array("tourists"), array("delete_status"=>"0")) as $rowFetchTourists ) {
                        $touristID = $rowFetchTourists['id'];
                        $touristUsername = $rowFetchTourists['username'];
                        $touristEmail = $rowFetchTourists['email'];
                        $touristCountry = $rowFetchTourists['country'];
                        $touristTimestamp = $rowFetchTourists['timestamp'];
                        $touristStatus = ($rowFetchTourists['status']=='1') ? "checked" : "";
                        echo "
                          <tr>
                            <td class='cursor-pointer' id='touristUsername$touristID' onclick='editTourist($touristID)'>$touristUsername</td>
                            <td id='touristEmail$touristID'>$touristEmail</td>
                            <td id='touristCountry$touristID'>$touristCountry</td>
                            <td>$touristTimestamp</td>
                            <td>
                              <div class='form-check form-switch justify-content-center'>
                                <input class='form-check-input' type='checkbox' name='touristSts$touristID' value='1' $touristStatus onchange='chngTouristSts($touristID)'>
                              </div>
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
      </div>
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-header pb-0">
              <h5 id="addNewUserCardHeader">Add a new Tourist</h5>
            </div>
            <div class="card-body p-3">
              <form action="" method="post">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Username</label>
                      <input class="form-control" type="text" name="inputUserUsername" required>
                      <div style="color:red" id="usernameAlreadyTakenErr"></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Email Address</label>
                      <input class="form-control" type="email" name="inputUserEmail" required>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>
                        Country
                        <input mbsc-input id="demo-country-picker" name="inputUserCountry" data-dropdown="true" data-input-style="box" data-label-style="stacked" placeholder="Please select..." required/>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Password</label>
                      <input class="form-control" type="password" name="inputUserPassword" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="example-text-input" class="form-control-label">Confirm Password</label>
                      <input class="form-control" type="password" name="inputUserConfirmPassword" required>
                      <div style="color:red" id="passwordMissMatchErr"></div>
                    </div>
                  </div>
                </div>
                <div style="float: right;">
                  <input type="hidden" value="" name="UserHiddenID">
                  <input type="submit" class="btn btn-success btn-sm ms-auto" name="newUserSubmit" value="Add">
                  <input type="submit" class="btn btn-primary btn-sm ms-auto" name="editUserSubmit" value="Edit" disabled>
                  <input type="submit" class="btn btn-danger btn-sm ms-auto" name="deleteUserSubmit" value="Delete" disabled>
                  <input type="button" class="btn btn-secondary btn-sm ms-auto" value="Cancel" onclick="location.reload()">
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php 
        echo $adminHeader->printAdminFooter();
        if ($deleteTouristSubmit != "") {
          echo $deleteTouristSubmit;
        }
        
      ?>
    </div>
  </main>
  <div id="checkUsername"></div>
  <div id="chngTouristSts"></div>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script src="./assets/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
  <script src="./assets/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
  <script>
   var passwordStatus = false;
   var usernameStatus = false;
    $(document).ready(function(){
        $('#usersDataTable').DataTable();
    });

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

    $("input[name='inputUserUsername']").keyup(function () {
        usernameStatus = true;
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
  checkTouristUsername: 1,
  username: $("input[name='inputUserUsername']").val()
},
        success: function(html) {
          $("#checkUsername").html(html).show();
        }
      }); 
    });

    function chngTouristSts(touristID) {
      var arr = {
        touristID: touristID,
        touristStatus: ($("input[name='touristSts"+touristID+"']").is(":checked")) ? 1 : 0
      };
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
          chngTouristSts: arr
        },
        success: function(html) {
          $("#chngTouristSts").html(html).show();
        }
      }); 
    }

    $("input[type='password']").on("keyup", function(){
      if ( $("input[name='inputUserPassword']").val() == $("input[name='inputUserConfirmPassword']").val() ) {
        passwordStatus = true;
        enableDisableButton();
        $("#passwordMissMatchErr").text("");
      } else {
        $("#passwordMissMatchErr").text("Not Matching");
        passwordStatus = false;
        enableDisableButton();
      }
    });

    function enableDisableButton() {
      if ( $("#addNewUserCardHeader").text()=="Edit a Tourist" ) {
        if ( passwordStatus && usernameStatus ) {
          $("input[name='editUserSubmit']").prop("disabled",false);
          $("input[name='deleteUserSubmit']").prop("disabled",false);
        } else {
          $("input[name='editUserSubmit']").prop("disabled",true);
          $("input[name='deleteUserSubmit']").prop("disabled",true);
        }
      } else if ( $("#addNewUserCardHeader").text()=="Add a new Tourist" ) {
        if ( passwordStatus && usernameStatus ) {
          $("input[name='newUserSubmit']").prop("disabled",false);
        } else {
          $("input[name='newUserSubmit']").prop("disabled",true);
        }
      }
    }

    function editTourist(touristID) {
      $("#addNewUserCardHeader").text("Edit a Tourist");
      $("input[name='inputUserUsername']").val($("#touristUsername"+touristID).text());
      $("input[name='inputUserEmail']").val($("#touristEmail"+touristID).text());
      $("input[name='inputUserCountry']").val($("#touristCountry"+touristID).text());
      $("input[name='UserHiddenID']").val(touristID);
      $("#usernameAlreadyTakenErr").text("");
      $("input[name='newUserSubmit']").prop("disabled",true);
      $("input[name='editUserSubmit']").prop("disabled",false);
      $("input[name='deleteUserSubmit']").prop("disabled",false);
      usernameStatus = true;
    }
  </script>
</body>

</html>