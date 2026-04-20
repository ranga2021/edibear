<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ SESSION FIX
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("manage-admins");
$user = new USER();

$editMode = false;
$currentUserID = 0;

$currentFirstName = "";
$currentLastName = "";
$currentEmail = "";
$currentMobile = "";

$deleteAdminSubmit = "";

/* ================= EDIT MODE ================= */
if (isset($_GET['id']) && $_GET['id'] > 0) {

    $currentUserID = (int)$_GET['id'];

    if ($user->CountRows("user_table", ["id"=>$currentUserID])) {

        $editMode = true;

        $admin = $user->fetchAll(
            ["first_name","last_name","login_email","mobile_number"],
            ["user_table"],
            ["id"=>$currentUserID]
        )[0];

        $currentFirstName = $admin['first_name'];
        $currentLastName  = $admin['last_name'];
        $currentEmail     = $admin['login_email'];
        $currentMobile    = $admin['mobile_number'];

    } else {
        $user->redirect("./manage-admins");
    }
}

/* ================= FORM SUBMIT ================= */
if (isset($_POST['addAdminSubmit']) || isset($_POST['updateAdminSubmit'])) {

    $first  = htmlspecialchars($_POST['inputUserFirstName'] ?? "");
    $last   = htmlspecialchars($_POST['inputUserLastName'] ?? "");
    $email  = htmlspecialchars($_POST['inputUserEmail'] ?? "");
    $mobile = htmlspecialchars($_POST['inputUserMobile'] ?? "");
    $pass   = htmlspecialchars($_POST['inputUserPassword'] ?? "");
    $cpass  = htmlspecialchars($_POST['inputUserConfirmPassword'] ?? "");

    if ($pass !== $cpass) {
        echo "<script>alert('Passwords are not matching');location.href='./manage-admins'</script>";
        exit;
    }

    // ADD
    if (isset($_POST['addAdminSubmit'])) {

        $msg = $user->adminRegister($first,$last,$email,$pass,$mobile);

        echo "<script>alert('$msg');location.href='./manage-admins'</script>";
    }

    // UPDATE
    if (isset($_POST['updateAdminSubmit'])) {

        $id = (int)($_POST['UserHiddenID'] ?? 0);

        $msg = $user->adminRegister($first,$last,$email,$pass,$mobile,$id);

        echo "<script>alert('$msg');location.href='./manage-admins'</script>";
    }
}

/* ================= DELETE ================= */
if (isset($_POST['deleteAdminSubmit'])) {

    $id = (int)($_POST['UserHiddenID'] ?? 0);

    $name = $_POST['inputUserFirstName']." ".$_POST['inputUserLastName'];
    $email = $_POST['inputUserEmail'];

    $deleteAdminSubmit = $user->confirmDeleteModal($id,$name,$email,"Confirm Delete an Admin","manage-admins");
}

if (isset($_POST['confirmDeleteSubmit'])) {

    $id = (int)($_POST['deleteNameID'] ?? 0);

    $user->updateTable("user_table", ["delete_status"=>1], ["id"=>$id]);

    echo "<script>alert('Successfully Deleted an Admin');location.href='./manage-admins';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php echo $adminHeader->printAdminHeader(); ?>
</head>

<body class="g-sidenav-show bg-gray-100">

<?php echo $adminHeader->printAdminNav(); ?>

<main class="main-content position-relative border-radius-lg">

<?php echo $adminHeader->printAdminNav2("Manage Admins"); ?>

<div class="container-fluid py-4">

<!-- ================= TABLE ================= -->
<div class="card mb-4">
<div class="card-header pb-0">
<h4>Admins</h4>
</div>

<div class="card-body p-3">
<div class="table-responsive">

<table class="table align-items-center mb-0">
<thead>
<tr>
<th>#</th>
<th>First Name</th>
<th>Last Name</th>
<th>Email</th>
<th>Mobile</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php
$i = 0;
foreach ($user->fetchAll("",["user_table"],["delete_status"=>"0"]) as $row) {

    $i++;
    $id = $row['id'];

    echo "<tr>
        <td>$i</td>
        <td>{$row['first_name']}</td>
        <td>{$row['last_name']}</td>
        <td>{$row['login_email']}</td>
        <td>{$row['mobile_number']}</td>
        <td>
            <a href='manage-admins?id=$id' class='btn btn-sm btn-primary'>Edit</a>
        </td>
    </tr>";
}
?>

</tbody>
</table>

</div>
</div>
</div>

<!-- ================= FORM ================= -->
<div class="card p-3">

<h5><?php echo $editMode ? "Edit Admin" : "Add Admin"; ?></h5>

<form method="post">

<div class="row">
<div class="col-md-6">
<label>First Name</label>
<input class="form-control" name="inputUserFirstName" value="<?php echo $currentFirstName;?>" required>
</div>

<div class="col-md-6">
<label>Last Name</label>
<input class="form-control" name="inputUserLastName" value="<?php echo $currentLastName;?>" required>
</div>
</div>

<div class="row mt-3">
<div class="col-md-6">
<label>Email</label>
<input type="email" class="form-control" name="inputUserEmail" value="<?php echo $currentEmail;?>" required>
</div>

<div class="col-md-6">
<label>Mobile</label>
<input class="form-control" name="inputUserMobile" value="<?php echo $currentMobile;?>" required>
</div>
</div>

<div class="row mt-3">
<div class="col-md-6">
<label>Password</label>
<input type="password" class="form-control" name="inputUserPassword" required>
</div>

<div class="col-md-6">
<label>Confirm Password</label>
<input type="password" class="form-control" name="inputUserConfirmPassword" required>
</div>
</div>

<input type="hidden" name="UserHiddenID" value="<?php echo $currentUserID; ?>">

<div class="mt-4">

<?php
if ($editMode) {
    echo "<button type='submit' name='updateAdminSubmit' class='btn btn-primary'>Update</button>
          <button type='submit' name='deleteAdminSubmit' class='btn btn-danger'>Delete</button>";
} else {
    echo "<button type='submit' name='addAdminSubmit' class='btn btn-success'>Add</button>";
}
?>

</div>

</form>

</div>

<?php
echo $adminHeader->printAdminFooter();
if ($deleteAdminSubmit != "") echo $deleteAdminSubmit;
?>

</div>
</main>

<?php echo $adminHeader->printAdminFooterJS(); ?>

</body>
</html>