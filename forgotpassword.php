<?php
session_start();
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");

$userHeader = new HEADER();
$user = new USER();

$showResetForm = false;
$userEmail = "";
$error = "";

// STEP 1: CHECK EMAIL
if(isset($_POST['checkEmail'])){
    $email = $_POST['email'];

    $userData = $user->fetchAll(["id"], ["tourists"], ["email"=>$email]);

    if($userData){
        $showResetForm = true;
        $userEmail = $email;
    } else {
        echo "
        <script>
            alert(\"You don't have an account on this email. Please sign up.\");
            window.location.href='signup.php';
        </script>
        ";
    }
}

// STEP 2: UPDATE PASSWORD
if(isset($_POST['updatePassword'])){
    $email = $_POST['email'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if($newPassword !== $confirmPassword){
        echo "<script>alert('Passwords do not match');</script>";
        $showResetForm = true;
        $userEmail = $email;
    } else {
        $hashed = "pass".password_hash($newPassword, PASSWORD_DEFAULT);

        $user->updateTable(
            "tourists",
            ["password"=>$hashed],
            ["email"=>$email]
        );

        echo "
        <script>
            alert('Password updated successfully');
            window.location.href='login.php';
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php echo $userHeader->printUserHeader("Forgot Password"); ?>
</head>

<body>

<?php echo $userHeader->printUserNav(); ?>

<div class="container-fluid py-5" style="margin-top: 100px;">
<div class="container text-center">

<h2 class="text-danger mb-4">Forgot Password</h2>

<!-- STEP 1 FORM -->
<?php if(!$showResetForm): ?>
<form method="POST" class="col-md-6 mx-auto">
    <input type="email" name="email" placeholder="Enter your email"
        class="form-control mb-3" required>

    <button type="submit" name="checkEmail" class="btn btn-success px-5">
        RESET PASSWORD
    </button>
</form>
<?php endif; ?>

<!-- STEP 2 FORM -->
<?php if($showResetForm): ?>
<form method="POST" class="col-md-6 mx-auto">

    <input type="hidden" name="email" value="<?php echo $userEmail; ?>">

    <input type="password" name="new_password"
        placeholder="New Password"
        class="form-control mb-3" required>

    <input type="password" name="confirm_password"
        placeholder="Confirm Password"
        class="form-control mb-3" required>

    <button type="submit" name="updatePassword"
        class="btn btn-primary px-5">
        UPDATE PASSWORD
    </button>

</form>
<?php endif; ?>

</div>
</div>

<?php echo $userHeader->printUserFooter(); ?>

</body>
</html>