<?php
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");

$userHeader = new HEADER();
$user = new USER();

$signupSuccess = false;
$userExists = false;
$errorOccurred = false;

if ($_POST['password'] !== $_POST['confirm_password']) {
    $errorOccurred = true;
    echo "<script>alert('Passwords do not match');</script>";
    return;
}

$countries = ["Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Palestine", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Samoa", "San Marino", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"];

if (isset($_POST['signupSubmit'])) {
    $username = strip_tags($_POST['username'] ?? "");
    $name     = strip_tags($_POST['name'] ?? "");
    $email    = strip_tags($_POST['email'] ?? "");
    $country  = strip_tags($_POST['country'] ?? "");
    $password = $_POST['password'] ?? "";

    if ($user->CountRows("tourists", array("username" => $username)) > 0) {
        $userExists = true;
    } else {
        $hashedPassword = "pass" . password_hash($password, PASSWORD_DEFAULT);
        $insertData = array(
            "username"      => $username,
            "name"          => $name,
            "email"         => $email,
            "country"       => $country,
            "password"      => $hashedPassword,
            "profile_pic"   => "default.jpg",
            "status"        => 1,
            "delete_status" => 0,
            "timestamp"     => date("Y-m-d H:i:s")
        );

        if ($user->insertTable("tourists", $insertData)) {
            $signupSuccess = true;
            $newUserID = $user->fetchAll(array("id"), array("tourists"), array("username" => $username))[0]['id'];
        } else {
            $errorOccurred = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader("Sign Up") ?>
    <style>
        .modal-content { border: none; border-radius: 15px; }
        .modal-header { border-bottom: none; padding-top: 25px; }
        .modal-footer { border-top: none; padding-bottom: 25px; }
        
        .signup-box {
    max-width: 400px;
    margin: auto;
    padding: 30px;
    border: 1px solid #ddd;
    border-radius: 15px;
    background: #fff;
}

.signup-title {
    color: #F65247;
    font-weight: bold;
    margin-bottom: 20px;
}

.signup-input {
    width: 100%;
    padding: 12px;
    margin-bottom: 12px;
    border: 2px solid #2ecc71;
    border-radius: 8px;
    outline: none;
}

.signup-btn {
    width: 100%;
    background: #33a675;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
}
    </style>
</head>
<body>

<?php echo $userHeader->printUserNav(); ?>
<div class="page-header-bg"></div>

<div class="container-fluid py-5 page-header-content">
    <div class="container">
        <nav class="edi-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-0">
                <li class="breadcrumb-item"><a href="./"><i class="fa fa-home" aria-hidden="true"></i> Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sign Up</li>
            </ol>
        </nav>
        <div class="edi-page-title-row">
            <h1>Create Account</h1>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>

        <div class="row mt-4 justify-content-center">
            <div class="col-md-6">
                <div class="card border border-white shadow-sm">
                    <div class="card-body">
                       <form action="" method="post" class="signup-box">

    <h3 class="text-center signup-title">SIGN UP</h3>

    <input type="text" name="name" class="signup-input" placeholder="Full Name" required>

    <input type="email" name="email" id="emailField" class="signup-input" placeholder="Email Address" required>

    <input type="hidden" name="username" id="usernameField">

    <select name="country" class="signup-input" required>
        <option value="">Select Country</option>
        <?php foreach ($countries as $c): ?>
            <option value="<?php echo $c; ?>" <?php echo ($c == "Sri Lanka") ? "selected" : ""; ?>>
                <?php echo $c; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="password" name="password" id="passwordField" class="signup-input" placeholder="Password" required>

    <input type="password" name="confirm_password" id="confirmPasswordField" class="signup-input" placeholder="Confirm Password" required>

    <button type="submit" name="signupSubmit" class="signup-btn">
        Create Account
    </button>

    <p class="text-center mt-3">
        Already have an account? <a href="login.php">Sign in</a>
    </p>

</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userExistsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-center d-block">
                <i class="fa fa-exclamation-circle text-warning fa-3x mb-3"></i>
                <h5 class="modal-title">Username Taken</h5>
            </div>
            <div class="modal-body text-center">
                That username is already in use. Please try a different one.
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Try Again</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-center d-block">
                <i class="fa fa-check-circle text-success fa-3x mb-3"></i>
                <h5 class="modal-title">Account Created!</h5>
            </div>
            <div class="modal-body text-center">
                Your account has been successfully registered. Welcome to Edibear!
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" id="btnRedirect">Go to Dashboard</button>
            </div>
        </div>
    </div>
</div>

<?php 
echo $userHeader->printUserFooter(); 

if ($userExists) {
    echo "<script>$(function(){ $('#userExistsModal').modal('show'); });</script>";
}
if ($errorOccurred) {
    echo "<script>alert('Error occurred');</script>"; // Fallback
}
if ($signupSuccess) {
    echo "
    <script>
        $(function(){ 
            $('#successModal').modal('show'); 
            $('#btnRedirect').click(function(){
                localStorage.setItem('user_session', '".$newUserID."');
                localStorage.setItem('session_time', Math.floor(Date.now()/1000));
                window.location.replace('./index?uid=' + localStorage.getItem('user_session'));
            });
        });
    </script>
    ";
}
?>

<script>
// 1. Autofill Username with Email
$("#emailField").on("input", function() {
    $("#usernameField").val($(this).val());
});

// 2. Show/Hide Password Toggle
$("#showPassword").click(function (){
    var x = $("#passwordField");
    x.attr("type", x.attr("type") === "password" ? "text" : "password");
});
</script>

</body>
</html>