<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

date_default_timezone_set("Asia/Colombo");

$adminHeader = new HEADER("manage-admins");
$user = new USER();

if (!$user->is_loggedin()) {
    $user->redirect("./index.php");
}

$pdo = $user->getConnection();
$extras = $user->userTableAdminExtrasAvailable();
$sessionAdminId = (int) ($_SESSION["session_tourism"] ?? 0);

$editMode = false;
$currentUserID = 0;
$currentFirstName = "";
$currentLastName = "";
$currentEmail = "";
$currentCity = "";
$currentRole = "administrator";
$currentAdminStatus = 1;
$currentProfilePic = "default.jpg";
$currentProfileUrl = "";

$deleteAdminSubmit = "";

function edi_ma_role_label($role)
{
    $r = strtolower(trim((string) $role));
    return $r === "editor" ? "Editor" : "Administrator";
}

/* ================= EDIT MODE (GET id) ================= */
if (isset($_GET["id"]) && (int) $_GET["id"] > 0) {

    $currentUserID = (int) $_GET["id"];

    if ($user->CountRows("user_table", array("id" => $currentUserID, "delete_status" => "0"))) {

        $editMode = true;
        $cols = array("first_name", "last_name", "login_email", "mobile_number");
        if ($extras) {
            $cols = array_merge($cols, array("admin_role", "city_country", "admin_status", "profile_pic"));
        }
        $admin = $user->fetchAll($cols, array("user_table"), array("id" => $currentUserID))[0];

        $currentFirstName = (string) ($admin["first_name"] ?? "");
        $currentLastName = (string) ($admin["last_name"] ?? "");
        $currentEmail = (string) ($admin["login_email"] ?? "");
        if ($extras) {
            $currentCity = (string) ($admin["city_country"] ?? "");
            $currentRole = strtolower((string) ($admin["admin_role"] ?? "administrator")) === "editor" ? "editor" : "administrator";
            $currentAdminStatus = (int) ($admin["admin_status"] ?? 1);
            $currentProfilePic = basename(str_replace("\\", "/", (string) ($admin["profile_pic"] ?? "default.jpg")));
            if ($currentProfilePic !== "" && $currentProfilePic !== "default.jpg") {
                $currentProfileUrl = "../img/admin-profiles/" . rawurlencode($currentProfilePic);
            }
        } else {
            $currentCity = (string) ($admin["mobile_number"] ?? "");
        }
    } else {
        $user->redirect("./manage-admins");
    }
}

/* ================= LIST + SEARCH + PAGINATION ================= */
$searchQ = trim((string) ($_GET["q"] ?? ""));
$page = max(1, (int) ($_GET["page"] ?? 1));
$perPage = 20;

$where = "delete_status = 0";
$params = array();
if ($searchQ !== "") {
    $like = "%" . $searchQ . "%";
    if ($extras) {
        $where .= " AND (first_name LIKE :slike OR last_name LIKE :slike OR login_email LIKE :slike OR city_country LIKE :slike OR mobile_number LIKE :slike OR CONCAT(first_name,' ',last_name) LIKE :slike)";
    } else {
        $where .= " AND (first_name LIKE :slike OR last_name LIKE :slike OR login_email LIKE :slike OR mobile_number LIKE :slike OR CONCAT(first_name,' ',last_name) LIKE :slike)";
    }
    $params[":slike"] = $like;
}

$totalAdmins = 0;
try {
    $cst = $pdo->prepare("SELECT COUNT(*) AS c FROM user_table WHERE " . $where);
    $cst->execute($params);
    $totalAdmins = (int) ($cst->fetch(PDO::FETCH_ASSOC)["c"] ?? 0);
} catch (Throwable $e) {
    $totalAdmins = 0;
}

$totalPages = max(1, (int) ceil($totalAdmins / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;
$showFrom = $totalAdmins === 0 ? 0 : ($offset + 1);
$showTo = min($offset + $perPage, $totalAdmins);

$listCols = array("id", "first_name", "last_name", "login_email", "register_timestamp", "mobile_number");
if ($extras) {
    $listCols = array_merge($listCols, array("admin_role", "city_country", "admin_status", "profile_pic"));
}

$adminRows = array();
try {
    $sql = "SELECT " . implode(",", $listCols) . " FROM user_table WHERE " . $where . " ORDER BY register_timestamp DESC LIMIT " . (int) $perPage . " OFFSET " . (int) $offset;
    $lst = $pdo->prepare($sql);
    $lst->execute($params);
    $adminRows = $lst->fetchAll(PDO::FETCH_ASSOC) ?: array();
} catch (Throwable $e) {
    $adminRows = array();
}

/* ================= FORM SUBMIT ================= */
if (isset($_POST["addAdminSubmit"]) || isset($_POST["updateAdminSubmit"])) {

    $first = trim(strip_tags((string) ($_POST["inputUserFirstName"] ?? "")));
    $last = trim(strip_tags((string) ($_POST["inputUserLastName"] ?? "")));
    $email = trim((string) ($_POST["inputUserEmail"] ?? ""));
    $city = trim(strip_tags((string) ($_POST["inputUserCity"] ?? "")));
    $role = strtolower(trim((string) ($_POST["inputUserRole"] ?? "administrator")));
    $pass = (string) ($_POST["inputUserPassword"] ?? "");
    $cpass = (string) ($_POST["inputUserConfirmPassword"] ?? "");

    if ($first === "" || $last === "" || $email === "") {
        echo "<script>alert('Please fill in first name, last name, and email.');location.href='./manage-admins'</script>";
        exit;
    }
    if ($extras && $city === "") {
        echo "<script>alert('Please fill in city.');location.href='./manage-admins'</script>";
        exit;
    }
    if (!$extras && $city === "") {
        echo "<script>alert('Please fill in city (stored as contact field on this database version).');location.href='./manage-admins'</script>";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email address.');location.href='./manage-admins'</script>";
        exit;
    }

    $isAdd = isset($_POST["addAdminSubmit"]);
    if ($isAdd) {
        if ($pass === "" || $pass !== $cpass) {
            echo "<script>alert('Passwords must match and cannot be empty for a new admin.');location.href='./manage-admins'</script>";
            exit;
        }
    } else {
        if ($pass !== "" || $cpass !== "") {
            if ($pass !== $cpass) {
                echo "<script>alert('Passwords are not matching');location.href='./manage-admins'</script>";
                exit;
            }
        }
        if (!$extras && $pass === "") {
            echo "<script>alert('Password is required when updating on this database version, or run migration_user_table_admin_ui.sql for optional password.');location.href='./manage-admins'</script>";
            exit;
        }
    }

    $opts = array(
        "role" => $role === "editor" ? "editor" : "administrator",
        "city_country" => $extras ? substr($city, 0, 100) : "",
        "mobile" => $extras ? "" : substr($city, 0, 20),
    );
    if ($extras && !$isAdd) {
        $opts["admin_status"] = (int) ($_POST["inputUserAdminStatus"] ?? 1) === 1 ? 1 : 0;
    }

    if ($isAdd) {
        $dup = $user->CountRows("user_table", array("delete_status" => "0", "login_email" => $email));
        if ($dup) {
            echo "<script>alert('An admin with this email already exists.');location.href='./manage-admins'</script>";
            exit;
        }

        $msg = $user->adminRegister($first, $last, $email, $pass, "", 0, $opts);
        if (strpos((string) $msg, "Successfully") === false) {
            echo "<script>alert(" . json_encode($msg) . ");location.href='./manage-admins'</script>";
            exit;
        }

        $newId = (int) $pdo->lastInsertId();
        $imgDir = __DIR__ . "/../img/admin-profiles/";
        if (!is_dir($imgDir)) {
            @mkdir($imgDir, 0777, true);
        }
        if (!empty($_FILES["inputAdminProfile"]["name"]) && (int) $_FILES["inputAdminProfile"]["error"] === UPLOAD_ERR_OK && $newId > 0) {
            $ext = strtolower(pathinfo($_FILES["inputAdminProfile"]["name"], PATHINFO_EXTENSION));
            if (in_array($ext, array("jpg", "jpeg", "png", "webp", "gif"), true)) {
                $fn = $newId . "." . $ext;
                if (move_uploaded_file($_FILES["inputAdminProfile"]["tmp_name"], $imgDir . $fn)) {
                    if ($extras) {
                        $user->updateTable("user_table", array("profile_pic" => $fn), array("id" => $newId));
                    }
                }
            }
        }

        echo "<script>alert(" . json_encode($msg) . ");location.href='./manage-admins'</script>";
        exit;
    }

    if (isset($_POST["updateAdminSubmit"])) {

        $id = (int) ($_POST["UserHiddenID"] ?? 0);
        if ($id < 1) {
            echo "<script>alert('Invalid admin.');location.href='./manage-admins'</script>";
            exit;
        }

        $dupSt = $pdo->prepare("SELECT COUNT(*) AS c FROM user_table WHERE delete_status = 0 AND login_email = :e AND id <> :id");
        $dupSt->execute(array(":e" => $email, ":id" => $id));
        if ((int) ($dupSt->fetch(PDO::FETCH_ASSOC)["c"] ?? 0) > 0) {
            echo "<script>alert('An admin with this email already exists.');location.href='./manage-admins?id=" . (int) $id . "'</script>";
            exit;
        }

        $msg = $user->adminRegister($first, $last, $email, $pass, "", $id, $opts);
        if (strpos((string) $msg, "Successfully") === false && strpos((string) $msg, "Edited") === false) {
            echo "<script>alert(" . json_encode($msg) . ");location.href='./manage-admins?id=" . (int) $id . "'</script>";
            exit;
        }

        $imgDir = __DIR__ . "/../img/admin-profiles/";
        if (!is_dir($imgDir)) {
            @mkdir($imgDir, 0777, true);
        }
        if ($extras && !empty($_FILES["inputAdminProfile"]["name"]) && (int) $_FILES["inputAdminProfile"]["error"] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES["inputAdminProfile"]["name"], PATHINFO_EXTENSION));
            if (in_array($ext, array("jpg", "jpeg", "png", "webp", "gif"), true)) {
                $fn = $id . "." . $ext;
                foreach ($user->fetchAll(array("profile_pic"), array("user_table"), array("id" => $id)) as $pr) {
                    $old = basename((string) ($pr["profile_pic"] ?? ""));
                    if ($old !== "" && $old !== "default.jpg" && is_file($imgDir . $old)) {
                        @unlink($imgDir . $old);
                    }
                }
                if (move_uploaded_file($_FILES["inputAdminProfile"]["tmp_name"], $imgDir . $fn)) {
                    $user->updateTable("user_table", array("profile_pic" => $fn), array("id" => $id));
                }
            }
        }

        echo "<script>alert(" . json_encode($msg) . ");location.href='./manage-admins'</script>";
        exit;
    }
}

/* ================= DELETE ================= */
if (isset($_POST["deleteAdminSubmit"])) {

    $id = (int) ($_POST["UserHiddenID"] ?? 0);
    if ($id === $sessionAdminId) {
        echo "<script>alert('You cannot delete your own account from here.');location.href='./manage-admins'</script>";
        exit;
    }

    $name = trim((string) ($_POST["inputUserFirstName"] ?? "")) . " " . trim((string) ($_POST["inputUserLastName"] ?? ""));
    $email = (string) ($_POST["inputUserEmail"] ?? "");

    $deleteAdminSubmit = $user->confirmDeleteModal($id, $name, $email, "Confirm delete admin", "manage-admins");
}

if (isset($_POST["confirmDeleteSubmit"])) {

    $id = (int) ($_POST["deleteNameID"] ?? 0);
    if ($id === $sessionAdminId) {
        echo "<script>alert('You cannot delete your own account.');location.href='./manage-admins';</script>";
        exit;
    }

    $user->updateTable("user_table", array("delete_status" => 1), array("id" => $id));

    echo "<script>alert('Admin has been removed from the active list');location.href='./manage-admins';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <style>
    .edi-adm-page-title {
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      color: #dc2626;
      margin: 0;
    }
    .edi-adm-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.25rem;
    }
    .edi-adm-search-group {
      display: flex;
      flex-wrap: wrap;
      align-items: stretch;
      gap: 0.5rem;
    }
    .edi-adm-search-group .form-control {
      min-width: 200px;
      border-radius: 0.5rem;
    }
    .edi-adm-form-title {
      font-size: 1.1rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      color: #1e293b;
      margin-bottom: 1rem;
    }
    .edi-adm-avatar-col {
      text-align: center;
      flex-shrink: 0;
    }
    .edi-adm-avatar-ring {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 3px solid #e2e8f0;
      margin: 0 auto 0.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      background: #f8fafc;
    }
    .edi-adm-avatar-ring img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .edi-adm-user-cell {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .edi-adm-user-cell .edi-adm-avatar-ring {
      width: 44px;
      height: 44px;
      margin: 0;
      border-width: 2px;
      flex-shrink: 0;
    }
    .edi-adm-user-cell .edi-adm-avatar-ring span {
      font-size: 0.65rem;
      color: #94a3b8;
    }
    .edi-adm-section-label {
      font-size: 0.75rem;
      font-weight: 700;
      color: #64748b;
      margin-bottom: 0.5rem;
    }
    .edi-adm-pagination {
      text-align: center;
      margin-top: 1rem;
    }
    .edi-adm-pagination .text-muted {
      font-size: 0.875rem;
    }
    .edi-adm-pagination .page-links a,
    .edi-adm-pagination .page-links span {
      display: inline-block;
      margin: 0 0.15rem;
      padding: 0.25rem 0.5rem;
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
          <div class="edi-adm-toolbar">
            <h1 class="edi-adm-page-title text-uppercase">Admins</h1>
            <form class="edi-adm-search-group" method="get" action="">
              <?php if ($editMode) { ?>
                <input type="hidden" name="id" value="<?php echo (int) $currentUserID; ?>">
              <?php } ?>
              <input type="search" name="q" class="form-control" placeholder="Name, email, or city" value="<?php echo htmlspecialchars($searchQ, ENT_QUOTES, "UTF-8"); ?>" aria-label="Search admins">
              <button type="submit" class="btn btn-success mb-0 font-weight-bold">Search</button>
            </form>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered align-items-center mb-0" id="adminsDataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Create date</th>
                  <th>City / Country</th>
                  <th>Role</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if (empty($adminRows)) {
                    echo '<tr><td colspan="6" class="text-center py-4 text-muted">No admins found.</td></tr>';
                } else {
                    foreach ($adminRows as $row) {
                        $aid = (int) $row["id"];
                        $fn = htmlspecialchars((string) ($row["first_name"] ?? ""), ENT_QUOTES, "UTF-8");
                        $ln = htmlspecialchars((string) ($row["last_name"] ?? ""), ENT_QUOTES, "UTF-8");
                        $em = htmlspecialchars((string) ($row["login_email"] ?? ""), ENT_QUOTES, "UTF-8");
                        $dispName = trim($fn . " " . $ln);
                        if ($dispName === "") {
                            $dispName = $em;
                        }
                        $ts = strtotime((string) ($row["register_timestamp"] ?? ""));
                        $dateStr = $ts ? date("M j, Y, g:i", $ts) : "";
                        if ($extras) {
                            $cityDisp = htmlspecialchars((string) ($row["city_country"] ?? ""), ENT_QUOTES, "UTF-8");
                            $roleKey = strtolower((string) ($row["admin_role"] ?? "administrator"));
                            $roleDisp = htmlspecialchars(edi_ma_role_label($roleKey), ENT_QUOTES, "UTF-8");
                            $st = ((string) ($row["admin_status"] ?? "1")) === "1";
                            $pic = basename(str_replace("\\", "/", (string) ($row["profile_pic"] ?? "")));
                            $picUrl = ($pic !== "" && $pic !== "default.jpg") ? ("../img/admin-profiles/" . rawurlencode($pic)) : "";
                        } else {
                            $cityDisp = htmlspecialchars((string) ($row["mobile_number"] ?? ""), ENT_QUOTES, "UTF-8");
                            $roleDisp = "Administrator";
                            $st = true;
                            $picUrl = "";
                        }
                        $chk = $st ? "checked" : "";
                        $toggleDisabled = !$extras ? " disabled" : "";
                        $innerAvatar = $picUrl !== ""
                            ? "<img src=\"" . htmlspecialchars($picUrl, ENT_QUOTES, "UTF-8") . "\" alt=\"\">"
                            : "<span class=\"text-muted small\">Photo</span>";
                        echo "<tr>
                            <td class=\"align-middle\">
                              <div class=\"edi-adm-user-cell\">
                                <div class=\"edi-adm-avatar-ring\">{$innerAvatar}</div>
                                <div>
                                  <span class=\"font-weight-bold text-dark\">{$dispName}</span><br>
                                  <small class=\"text-muted\">{$em}</small>
                                </div>
                              </div>
                            </td>
                            <td class=\"align-middle text-sm text-muted\">" . htmlspecialchars($dateStr, ENT_QUOTES, "UTF-8") . "</td>
                            <td class=\"align-middle\">{$cityDisp}</td>
                            <td class=\"align-middle\"><span class=\"text-sm\">{$roleDisp}</span></td>
                            <td class=\"align-middle text-center\">
                              <div class=\"d-flex align-items-center justify-content-center gap-2 flex-wrap\">
                                <span class=\"text-xs text-muted\">" . ($st ? "Active" : "Inactive") . "</span>
                                <div class=\"form-check form-switch d-inline-flex justify-content-center mb-0\">
                                  <input class=\"form-check-input\" type=\"checkbox\" name=\"adminSts{$aid}\" value=\"1\" {$chk}{$toggleDisabled} onchange=\"chngAdminSts({$aid}, this)\">
                                </div>
                              </div>
                            </td>
                            <td class=\"align-middle text-center\">
                              <a href=\"./manage-admins?id={$aid}\" class=\"btn btn-link text-success font-weight-bold p-0\">Edit</a>
                            </td>
                          </tr>";
                    }
                }
                ?>
              </tbody>
            </table>
          </div>

          <div class="edi-adm-pagination">
            <p class="text-muted mb-1">Showing <?php echo (int) $showFrom; ?> to <?php echo (int) $showTo; ?> of <?php echo (int) $totalAdmins; ?> admins</p>
            <div class="page-links">
              <?php
              if ($totalPages > 1) {
                  $base = "./manage-admins?page=";
                  if ($searchQ !== "") {
                      $base = "./manage-admins?q=" . rawurlencode($searchQ) . "&page=";
                  }
                  if ($page > 1) {
                      echo '<a href="' . htmlspecialchars($base . ($page - 1), ENT_QUOTES, "UTF-8") . '">&lt;</a> ';
                  } else {
                      echo "<span class=\"text-muted\">&lt;</span> ";
                  }
                  $window = 5;
                  $start = max(1, $page - 2);
                  $end = min($totalPages, $start + $window - 1);
                  if ($end - $start < $window - 1) {
                      $start = max(1, $end - $window + 1);
                  }
                  for ($p = $start; $p <= $end; $p++) {
                      if ($p === $page) {
                          echo "<strong>{$p}</strong> ";
                      } else {
                          echo '<a href="' . htmlspecialchars($base . $p, ENT_QUOTES, "UTF-8") . "\">{$p}</a> ";
                      }
                  }
                  if ($end < $totalPages) {
                      echo "<span class=\"text-muted\">…</span> ";
                      echo '<a href="' . htmlspecialchars($base . $totalPages, ENT_QUOTES, "UTF-8") . "\">{$totalPages}</a> ";
                  }
                  if ($page < $totalPages) {
                      echo '<a href="' . htmlspecialchars($base . ($page + 1), ENT_QUOTES, "UTF-8") . '">&gt;</a>';
                  } else {
                      echo " <span class=\"text-muted\">&gt;</span>";
                  }
              }
              ?>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-4">
          <h2 class="edi-adm-form-title text-uppercase"><?php echo $editMode ? "Edit admin" : "Add new admin"; ?></h2>

          <?php if (!$extras) { ?>
            <p class="text-sm text-warning mb-3">Run <code>sql/migration_user_table_admin_ui.sql</code> for role, city, profile photos, status toggles, and optional password on edit.</p>
          <?php } ?>

          <form method="post" enctype="multipart/form-data" id="ediAdmForm">
            <input type="hidden" name="UserHiddenID" id="UserHiddenID" value="<?php echo $editMode ? (int) $currentUserID : ""; ?>">
            <input type="hidden" name="inputUserAdminStatus" id="inputUserAdminStatus" value="<?php echo (int) $currentAdminStatus; ?>">

            <div class="d-flex flex-wrap gap-4 mb-4">
              <div class="edi-adm-avatar-col">
                <div class="edi-adm-avatar-ring" id="ediAdmAvatarPreview">
                  <?php
                  if ($editMode && $currentProfileUrl !== "") {
                      echo "<img src=\"" . htmlspecialchars($currentProfileUrl, ENT_QUOTES, "UTF-8") . "\" alt=\"\">";
                  } else {
                      echo "<span class=\"text-muted small text-center px-2\" id=\"ediAdmAvatarPlaceholder\">Photo</span>";
                  }
                  ?>
                </div>
                <label class="btn btn-outline-secondary btn-sm mb-0" for="inputAdminProfile">Upload</label>
                <input type="file" name="inputAdminProfile" id="inputAdminProfile" class="d-none" accept=".jpg,.jpeg,.png,.webp,.gif" <?php echo $extras ? "" : "disabled"; ?>>
              </div>

              <div class="flex-grow-1" style="min-width:240px;">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">First name</label>
                    <input class="form-control" name="inputUserFirstName" id="inputUserFirstName" maxlength="50" required value="<?php echo htmlspecialchars($currentFirstName, ENT_QUOTES, "UTF-8"); ?>">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Last name</label>
                    <input class="form-control" name="inputUserLastName" id="inputUserLastName" maxlength="50" required value="<?php echo htmlspecialchars($currentLastName, ENT_QUOTES, "UTF-8"); ?>">
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label font-weight-bold">E-mail</label>
                  <input type="email" class="form-control" name="inputUserEmail" id="inputUserEmail" maxlength="100" required value="<?php echo htmlspecialchars($currentEmail, ENT_QUOTES, "UTF-8"); ?>">
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Role</label>
                    <?php if ($extras) { ?>
                      <select class="form-control" name="inputUserRole" id="inputUserRole">
                        <option value="administrator"<?php echo $currentRole !== "editor" ? " selected" : ""; ?>>Administrator</option>
                        <option value="editor"<?php echo $currentRole === "editor" ? " selected" : ""; ?>>Editor</option>
                      </select>
                    <?php } else { ?>
                      <input type="hidden" name="inputUserRole" value="administrator">
                      <select class="form-control" id="inputUserRoleDisabled" disabled>
                        <option selected>Administrator</option>
                      </select>
                    <?php } ?>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">City</label>
                    <input class="form-control" name="inputUserCity" id="inputUserCity" maxlength="<?php echo $extras ? "100" : "20"; ?>" required value="<?php echo htmlspecialchars($currentCity, ENT_QUOTES, "UTF-8"); ?>" placeholder="e.g. Gampaha">
                  </div>
                </div>

                <p class="edi-adm-section-label">Create password</p>
                <div class="row mb-2">
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Password</label>
                    <input type="password" class="form-control" name="inputUserPassword" id="inputUserPassword" autocomplete="new-password">
                    <small class="text-muted" id="ediAdmPwdHintAdd"<?php echo $editMode ? " style=\"display:none\"" : ""; ?>>Required for new admins.</small>
                    <small class="text-muted" id="ediAdmPwdHintEdit"<?php echo $editMode ? "" : " style=\"display:none\""; ?>><?php echo $extras ? "Leave blank to keep the current password." : "Required on each update (until migration is applied)."; ?></small>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">Confirm password</label>
                    <input type="password" class="form-control" name="inputUserConfirmPassword" id="inputUserConfirmPassword" autocomplete="new-password">
                    <div class="text-danger small" id="passwordMissMatchErr"></div>
                  </div>
                </div>

                <?php if ($extras && $editMode) { ?>
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="ediAdmStatusActive"<?php echo $currentAdminStatus ? " checked" : ""; ?>>
                    <label class="form-check-label font-weight-bold" for="ediAdmStatusActive">Active (can sign in)</label>
                  </div>
                <?php } ?>
              </div>
            </div>

            <div class="edi-admin-form-actions flex-wrap">
              <?php if ($editMode) { ?>
                <input type="submit" class="btn btn-success mb-0 font-weight-bold" name="updateAdminSubmit" id="btnUpdateAdmin" value="Update">
                <input type="submit" class="btn btn-danger mb-0 font-weight-bold" name="deleteAdminSubmit" id="btnDeleteAdmin" value="Delete">
              <?php } else { ?>
                <input type="submit" class="btn btn-success mb-0 font-weight-bold" name="addAdminSubmit" id="btnAddAdmin" value="Add">
              <?php } ?>
              <button type="button" class="btn btn-secondary mb-0 font-weight-bold" id="btnCancelAdmForm">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <?php
      echo $adminHeader->printAdminFooter();
      if ($deleteAdminSubmit !== "") {
          echo $deleteAdminSubmit;
      }
      ?>
    </div>
  </main>

  <div id="chngAdminStsMsg"></div>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script>
    var ediAdmExtras = <?php echo $extras ? "true" : "false"; ?>;
    var ediAdmSessionId = <?php echo (int) $sessionAdminId; ?>;

    $(document).ready(function () {
      $("#ediAdmStatusActive").on("change", function () {
        $("#inputUserAdminStatus").val($(this).is(":checked") ? "1" : "0");
      });

      $("#inputAdminProfile").on("change", function () {
        var f = this.files && this.files[0];
        if (!f || !f.type.match(/^image\//)) return;
        var r = new FileReader();
        r.onload = function (e) {
          $("#ediAdmAvatarPreview").html('<img src="' + e.target.result + '" alt="">');
        };
        r.readAsDataURL(f);
      });

      $("#btnCancelAdmForm").on("click", function () {
        window.location.href = "./manage-admins";
      });

      $("#ediAdmForm").on("submit", function (e) {
        var p = $("#inputUserPassword").val();
        var c = $("#inputUserConfirmPassword").val();
        $("#passwordMissMatchErr").text("");
        if (p !== c) {
          e.preventDefault();
          $("#passwordMissMatchErr").text("Passwords do not match.");
          return false;
        }
      });
    });

    function chngAdminSts(adminID, el) {
      if (!ediAdmExtras) return;
      if (adminID === ediAdmSessionId && !$(el).is(":checked")) {
        alert("You cannot deactivate your own account here.");
        $(el).prop("checked", true);
        return;
      }
      var arr = {
        adminUserID: adminID,
        adminUserStatus: $(el).is(":checked") ? 1 : 0
      };
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: { chngAdminUserSts: arr },
        success: function (html) {
          $("#chngAdminStsMsg").html(html).show();
        }
      });
    }
  </script>
</body>
</html>
