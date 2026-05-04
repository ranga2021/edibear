<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("manage-users");
$user = new USER();
$deleteTouristSubmit = "";

if (!$user->is_loggedin()) {
    $user->redirect("./index.php");
}

function edi_manage_users_unique_username($user, $base)
{
    $base = substr(preg_replace("/[^a-zA-Z0-9_]/", "", (string) $base), 0, 20);
    if ($base === "") {
        $base = "u";
    }
    $candidate = $base;
    for ($i = 0; $i < 50; $i++) {
        if (!$user->CountRows("tourists", array("username" => $candidate))) {
            return $candidate;
        }
        $candidate = substr($base, 0, max(1, 20 - strlen((string) $i))) . $i;
    }
    return "u" . substr(bin2hex(random_bytes(6)), 0, 18);
}

/* ================= FORM HANDLING ================= */
if (isset($_POST["newUserSubmit"]) || isset($_POST["editUserSubmit"]) || isset($_POST["deleteUserSubmit"])) {

    $inputUserName = trim(strip_tags((string) ($_POST["inputUserName"] ?? "")));
    $inputUserEmail = trim((string) ($_POST["inputUserEmail"] ?? ""));
    $inputUserCountry = trim(strip_tags((string) ($_POST["inputUserCountry"] ?? "")));
    $inputUserPassword = (string) ($_POST["inputUserPassword"] ?? "");
    $inputUserConfirmPassword = (string) ($_POST["inputUserConfirmPassword"] ?? "");
    $inputUserUsername = trim((string) ($_POST["inputUserUsername"] ?? ""));

    // ADD + EDIT
    if (isset($_POST["newUserSubmit"]) || isset($_POST["editUserSubmit"])) {

        if ($inputUserName === "" || $inputUserCountry === "" || $inputUserEmail === "") {
            echo "<script>alert('Please fill in name, city\\/country, and email.');location.href='./manage-users'</script>";
            exit;
        }
        if (!filter_var($inputUserEmail, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Invalid email address.');location.href='./manage-users'</script>";
            exit;
        }

        $isAdd = isset($_POST["newUserSubmit"]);
        if ($isAdd) {
            if ($inputUserPassword === "" || $inputUserPassword !== $inputUserConfirmPassword) {
                echo "<script>alert('Passwords must match and cannot be empty for a new user.');location.href='./manage-users'</script>";
                exit;
            }
        } else {
            if ($inputUserPassword !== "" || $inputUserConfirmPassword !== "") {
                if ($inputUserPassword !== $inputUserConfirmPassword) {
                    echo "<script>alert('Passwords are not matching');location.href='./manage-users'</script>";
                    exit;
                }
            }
        }

        $hashedPassword = "";
        if ($isAdd || $inputUserPassword !== "") {
            $hashedPassword = "pass" . password_hash($inputUserPassword, PASSWORD_DEFAULT);
        }
        $imgDir = __DIR__ . "/../img/profile-pics/";
        if (!is_dir($imgDir)) {
            @mkdir($imgDir, 0777, true);
        }

        if (isset($_POST["newUserSubmit"])) {

            $uname = edi_manage_users_unique_username($user, strtolower(explode("@", $inputUserEmail)[0] ?? "user"));

            $newId = $user->insertTable(
                "tourists",
                array(
                    "username" => $uname,
                    "name" => substr($inputUserName, 0, 100),
                    "password" => $hashedPassword,
                    "email" => substr($inputUserEmail, 0, 50),
                    "country" => substr($inputUserCountry, 0, 20),
                    "profile_pic" => "default.jpg",
                    "status" => 1,
                    "delete_status" => 0,
                    "timestamp" => date("Y-m-d H:i:s"),
                ),
                true
            );

            if (!empty($_FILES["inputUserProfile"]["name"]) && (int) $_FILES["inputUserProfile"]["error"] === UPLOAD_ERR_OK && $newId) {
                $ext = strtolower(pathinfo($_FILES["inputUserProfile"]["name"], PATHINFO_EXTENSION));
                if (in_array($ext, array("jpg", "jpeg", "png", "webp", "gif"), true)) {
                    $fn = (int) $newId . "." . $ext;
                    if (move_uploaded_file($_FILES["inputUserProfile"]["tmp_name"], $imgDir . $fn)) {
                        $user->updateTable("tourists", array("profile_pic" => $fn), array("id" => (int) $newId));
                    }
                }
            }

            echo "<script>alert('Successfully added a new user');location.href='./manage-users'</script>";
            exit;
        }

        if (isset($_POST["editUserSubmit"])) {

            $UserHiddenID = (int) ($_POST["UserHiddenID"] ?? 0);
            if ($UserHiddenID < 1) {
                echo "<script>alert('Invalid user.');location.href='./manage-users'</script>";
                exit;
            }

            $update = array(
                "name" => substr($inputUserName, 0, 100),
                "email" => substr($inputUserEmail, 0, 50),
                "country" => substr($inputUserCountry, 0, 20),
            );
            if ($inputUserPassword !== "") {
                $update["password"] = $hashedPassword;
            }
            $user->updateTable("tourists", $update, array("id" => $UserHiddenID));

            if (!empty($_FILES["inputUserProfile"]["name"]) && (int) $_FILES["inputUserProfile"]["error"] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES["inputUserProfile"]["name"], PATHINFO_EXTENSION));
                if (in_array($ext, array("jpg", "jpeg", "png", "webp", "gif"), true)) {
                    $fn = $UserHiddenID . "." . $ext;
                    $dest = $imgDir . $fn;
                    foreach ($user->fetchAll(array("profile_pic"), array("tourists"), array("id" => $UserHiddenID)) as $pr) {
                        $old = basename((string) ($pr["profile_pic"] ?? ""));
                        if ($old !== "" && $old !== "default.jpg" && is_file($imgDir . $old)) {
                            @unlink($imgDir . $old);
                        }
                    }
                    if (move_uploaded_file($_FILES["inputUserProfile"]["tmp_name"], $dest)) {
                        $user->updateTable("tourists", array("profile_pic" => $fn), array("id" => $UserHiddenID));
                    }
                }
            }

            echo "<script>alert('Successfully updated user');location.href='./manage-users'</script>";
            exit;
        }
    }

    if (isset($_POST["deleteUserSubmit"])) {

        $UserHiddenID = (int) ($_POST["UserHiddenID"] ?? 0);

        $deleteTouristSubmit = $user->confirmDeleteModal(
            $UserHiddenID,
            $inputUserUsername !== "" ? $inputUserUsername : ("#" . $UserHiddenID),
            $inputUserEmail,
            "Confirm delete user",
            "manage-users"
        );
    }
}

if (isset($_POST["confirmDeleteSubmit"])) {

    $deleteNameID = (int) ($_POST["deleteNameID"] ?? 0);

    $user->updateTable(
        "tourists",
        array(
            "status" => 0,
            "delete_status" => 1,
        ),
        array("id" => $deleteNameID)
    );

    echo "<script>alert('User has been removed from active list');location.href='./manage-users';</script>";
    exit;
}

$touristRows = $user->fetchAll(
    array("id", "username", "name", "email", "country", "profile_pic", "status", "timestamp"),
    array("tourists"),
    array("delete_status" => "0"),
    "timestamp DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <style>
    .edi-mu-page-title {
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      color: #dc2626;
      margin-bottom: 1rem;
    }
    .edi-mu-section-label {
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      color: #94a3b8;
      text-transform: uppercase;
      margin-bottom: 0.75rem;
    }
    .edi-mu-avatar-ring {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 3px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      flex-shrink: 0;
      background: #f8fafc;
    }
    .edi-mu-avatar-ring img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .edi-mu-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .edi-mu-table-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #334155;
      margin: 0;
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>
    <div class="container-fluid py-4">

      <div class="card mb-4">
        <div class="card-body p-4">
          <div class="edi-mu-toolbar">
            <h2 class="edi-mu-table-title mb-0">Users</h2>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered align-items-center mb-0" id="usersDataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>City / Country</th>
                  <th>Member since</th>
                  <th class="text-center">Active</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if (empty($touristRows)) {
                    echo '<tr><td colspan="6" class="text-center py-4 text-muted">No users found.</td></tr>';
                } else {
                    foreach ($touristRows as $rowFetchTourists) {
                        $touristID = (int) $rowFetchTourists["id"];
                        $touristUsername = htmlspecialchars((string) ($rowFetchTourists["username"] ?? ""), ENT_QUOTES, "UTF-8");
                        $dispName = trim((string) ($rowFetchTourists["name"] ?? ""));
                        if ($dispName === "" || $dispName === "\r\n") {
                            $dispName = (string) ($rowFetchTourists["username"] ?? "");
                        }
                        $dispNameEsc = htmlspecialchars($dispName, ENT_QUOTES, "UTF-8");
                        $touristEmail = htmlspecialchars((string) ($rowFetchTourists["email"] ?? ""), ENT_QUOTES, "UTF-8");
                        $touristCountry = htmlspecialchars((string) ($rowFetchTourists["country"] ?? ""), ENT_QUOTES, "UTF-8");
                        $touristTimestamp = htmlspecialchars((string) ($rowFetchTourists["timestamp"] ?? ""), ENT_QUOTES, "UTF-8");
                        $pic = basename(str_replace("\\", "/", (string) ($rowFetchTourists["profile_pic"] ?? "")));
                        $picUrl = ($pic !== "" && $pic !== "default.jpg") ? ("../img/profile-pics/" . rawurlencode($pic)) : "";
                        $touristStatus = ((string) ($rowFetchTourists["status"] ?? "") === "1") ? "checked" : "";
                        $dataProfile = htmlspecialchars($picUrl, ENT_QUOTES, "UTF-8");
                        echo "<tr>
                            <td class=\"align-middle\"><span class=\"font-weight-bold\">{$dispNameEsc}</span><br><small class=\"text-muted\">@{$touristUsername}</small></td>
                            <td class=\"align-middle\">{$touristEmail}</td>
                            <td class=\"align-middle\">{$touristCountry}</td>
                            <td class=\"align-middle text-sm\">{$touristTimestamp}</td>
                            <td class=\"align-middle text-center\">
                              <div class=\"form-check form-switch d-inline-flex justify-content-center\">
                                <input class=\"form-check-input\" type=\"checkbox\" name=\"touristSts{$touristID}\" value=\"1\" {$touristStatus} onchange=\"chngTouristSts({$touristID})\">
                              </div>
                            </td>
                            <td class=\"align-middle text-center\">
                              <button type=\"button\" class=\"btn btn-link text-success font-weight-bold p-0 edi-mu-edit-btn\" data-id=\"{$touristID}\" data-username=\"" . htmlspecialchars((string) ($rowFetchTourists["username"] ?? ""), ENT_QUOTES, "UTF-8") . "\" data-name=\"" . htmlspecialchars($dispName, ENT_QUOTES, "UTF-8") . "\" data-email=\"" . htmlspecialchars((string) ($rowFetchTourists["email"] ?? ""), ENT_QUOTES, "UTF-8") . "\" data-country=\"" . htmlspecialchars((string) ($rowFetchTourists["country"] ?? ""), ENT_QUOTES, "UTF-8") . "\" data-profile=\"{$dataProfile}\">Edit</button>
                            </td>
                          </tr>";
                    }
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-4">
          <h2 class="edi-mu-page-title text-uppercase mb-1" id="ediMuFormTitle">Manage user</h2>
          <p class="text-sm text-muted mb-4" id="ediMuFormSubtitle">Select a user from the table above, or add a new account below.</p>

          <form action="" method="post" enctype="multipart/form-data" id="ediMuForm">
            <input type="hidden" name="UserHiddenID" id="UserHiddenID" value="">
            <input type="hidden" name="inputUserUsername" id="inputUserUsername" value="">

            <p class="edi-mu-section-label">General information</p>
            <div class="d-flex flex-wrap gap-4 mb-4">
              <div class="edi-mu-avatar-ring" id="ediMuAvatarPreview">
                <span class="text-muted small text-center px-2" id="ediMuAvatarPlaceholder">Photo</span>
              </div>
              <div class="flex-grow-1" style="min-width:240px;">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Name</label>
                    <input class="form-control" type="text" name="inputUserName" id="inputUserName" maxlength="100" required placeholder="Full name">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">City / Country</label>
                    <input class="form-control" type="text" name="inputUserCountry" id="inputUserCountry" maxlength="20" required placeholder="e.g. Gampaha">
                  </div>
                </div>
                <div class="mb-0">
                  <label class="form-label font-weight-bold">E-mail</label>
                  <input class="form-control" type="email" name="inputUserEmail" id="inputUserEmail" maxlength="50" required placeholder="email@example.com">
                </div>
              </div>
            </div>

            <p class="edi-mu-section-label">Change password</p>
            <div class="row mb-4">
              <div class="col-md-6 mb-3">
                <label class="form-label font-weight-bold">Password</label>
                <input class="form-control" type="password" name="inputUserPassword" id="inputUserPassword" autocomplete="new-password" placeholder="">
                <small class="text-muted" id="ediMuPwdHintAdd">Required for new users.</small>
                <small class="text-muted" id="ediMuPwdHintEdit" style="display:none;">Leave blank to keep the current password.</small>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label font-weight-bold">Confirm password</label>
                <input class="form-control" type="password" name="inputUserConfirmPassword" id="inputUserConfirmPassword" autocomplete="new-password" placeholder="">
                <div class="text-danger small" id="passwordMissMatchErr"></div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label font-weight-bold">Profile photo (optional)</label>
              <input class="form-control" type="file" name="inputUserProfile" id="inputUserProfile" accept=".jpg,.jpeg,.png,.webp,.gif">
            </div>

            <div class="edi-admin-form-actions flex-wrap">
              <input type="submit" class="btn btn-success mb-0" name="newUserSubmit" id="btnNewUserSubmit" value="Add">
              <input type="submit" class="btn btn-success mb-0" name="editUserSubmit" id="btnEditUserSubmit" value="Update" style="display:none;">
              <input type="submit" class="btn btn-danger mb-0" name="deleteUserSubmit" id="btnDeleteUserSubmit" value="Delete" style="display:none;">
              <button type="button" class="btn btn-secondary mb-0" id="btnCancelMuForm">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <?php
      echo $adminHeader->printAdminFooter();
      if ($deleteTouristSubmit !== "") {
          echo $deleteTouristSubmit;
      }
      ?>
    </div>
  </main>

  <div id="chngTouristSts"></div>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script src="./assets/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
  <script src="./assets/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
  <script>
    $(document).ready(function () {
      if ($("#usersDataTable tbody tr").length && !$("#usersDataTable td[colspan]").length) {
        $("#usersDataTable").DataTable({ order: [[3, "desc"]] });
      }
    });

    function chngTouristSts(touristID) {
      var arr = {
        touristID: touristID,
        touristStatus: ($("input[name='touristSts" + touristID + "']").is(":checked")) ? 1 : 0
      };
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: { chngTouristSts: arr },
        success: function (html) {
          $("#chngTouristSts").html(html).show();
        }
      });
    }

    function ediMuSetMode(isEdit) {
      $("#ediMuFormTitle").text(isEdit ? "MANAGE USER" : "ADD USER");
      $("#ediMuFormSubtitle").text(isEdit ? "Update profile, email, or password for this account." : "Create a new explorer account.");
      $("#btnNewUserSubmit").toggle(!isEdit);
      $("#btnEditUserSubmit, #btnDeleteUserSubmit").toggle(isEdit);
      $("#ediMuPwdHintAdd").toggle(!isEdit);
      $("#ediMuPwdHintEdit").toggle(isEdit);
      $("#inputUserPassword, #inputUserConfirmPassword").prop("required", !isEdit);
      if (!isEdit) {
        $("#UserHiddenID, #inputUserUsername").val("");
        $("#ediMuForm")[0].reset();
        $("#ediMuAvatarPreview").html('<span class="text-muted small text-center px-2" id="ediMuAvatarPlaceholder">Photo</span>');
      }
      ediMuValidate();
    }

    function ediMuValidate() {
      var isEdit = $("#btnEditUserSubmit").is(":visible");
      var nameOk = $("#inputUserName").val().trim() !== "";
      var emailOk = $("#inputUserEmail").val().trim() !== "";
      var countryOk = $("#inputUserCountry").val().trim() !== "";
      var p1 = $("#inputUserPassword").val();
      var p2 = $("#inputUserConfirmPassword").val();
      var pwdOk;
      if (isEdit) {
        pwdOk = (p1 === "" && p2 === "") || (p1 !== "" && p1 === p2);
      } else {
        pwdOk = p1 !== "" && p1 === p2;
      }
      var basic = nameOk && emailOk && countryOk && pwdOk;
      $("#btnNewUserSubmit").prop("disabled", !basic || isEdit);
      $("#btnEditUserSubmit").prop("disabled", !basic || !isEdit);
      $("#btnDeleteUserSubmit").prop("disabled", !isEdit || !nameOk || !emailOk || !countryOk);
    }

    $("#inputUserName, #inputUserEmail, #inputUserCountry, #inputUserPassword, #inputUserConfirmPassword").on("input keyup", function () {
      var p1 = $("#inputUserPassword").val();
      var p2 = $("#inputUserConfirmPassword").val();
      if (p1 !== "" || p2 !== "") {
        if (p1 !== p2) {
          $("#passwordMissMatchErr").text("Not matching");
        } else {
          $("#passwordMissMatchErr").text("");
        }
      } else {
        $("#passwordMissMatchErr").text("");
      }
      ediMuValidate();
    });

    $(document).on("click", ".edi-mu-edit-btn", function () {
      var $b = $(this);
      $("#UserHiddenID").val($b.data("id"));
      $("#inputUserUsername").val($b.data("username"));
      $("#inputUserName").val($b.data("name"));
      $("#inputUserEmail").val($b.data("email"));
      $("#inputUserCountry").val($b.data("country"));
      $("#inputUserPassword, #inputUserConfirmPassword").val("");
      $("#passwordMissMatchErr").text("");
      var url = $b.data("profile");
      if (url) {
        $("#ediMuAvatarPreview").html('<img src="' + url + '" alt="">');
      } else {
        $("#ediMuAvatarPreview").html('<span class="text-muted small text-center px-2">Photo</span>');
      }
      $("#inputUserProfile").val("");
      ediMuSetMode(true);
    });

    $("#btnCancelMuForm").on("click", function () {
      ediMuSetMode(false);
    });

    $("#inputUserProfile").on("change", function () {
      var f = this.files && this.files[0];
      if (!f) return;
      var r = new FileReader();
      r.onload = function (ev) {
        $("#ediMuAvatarPreview").html('<img src="' + ev.target.result + '" alt="">');
      };
      r.readAsDataURL(f);
    });

    ediMuSetMode(false);
  </script>
</body>
</html>
