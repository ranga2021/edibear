<?php
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");

$userHeader = new HEADER();
$user = new USER();

$signupSuccess = false;
$userExists = false;
$errorOccurred = false;
$pwdMismatch = false;

$countries = ["Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Palestine", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Samoa", "San Marino", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"];

if (isset($_POST["signupSubmit"])) {
    $username = strip_tags($_POST["username"] ?? "");
    $name = strip_tags($_POST["name"] ?? "");
    $email = strip_tags($_POST["email"] ?? "");
    $country = strip_tags($_POST["country"] ?? "");
    $password = (string) ($_POST["password"] ?? "");
    $confirm = (string) ($_POST["confirm_password"] ?? "");

    if ($password !== $confirm) {
        $pwdMismatch = true;
        $errorOccurred = true;
    } elseif (
        $user->CountRows("tourists", array("username" => $username)) > 0
        || $user->CountRows("tourists", array("email" => $email)) > 0
    ) {
        $userExists = true;
    } else {
        $hashedPassword = "pass" . password_hash($password, PASSWORD_DEFAULT);
        $insertData = array(
            "username" => $username,
            "name" => $name,
            "email" => $email,
            "country" => $country,
            "password" => $hashedPassword,
            "profile_pic" => "default.jpg",
            "status" => 1,
            "delete_status" => 0,
            "timestamp" => date("Y-m-d H:i:s"),
        );

        if ($user->insertTable("tourists", $insertData)) {
            $signupSuccess = true;
            $newUserID = $user->fetchAll(array("id"), array("tourists"), array("username" => $username))[0]["id"];
        } else {
            $errorOccurred = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader("Sign Up"); ?>
    <link rel="stylesheet" href="css/edi-auth.css">
</head>
<body class="edi-auth-page">

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

        <h1 class="edi-auth-hero-title">Create your account</h1>
        <p class="edi-auth-hero-lead">Join Edibear to save treasures, join challenges, and hear from Edi’s family.</p>

        <?php if ($pwdMismatch): ?>
            <div class="edi-auth-alert err">Passwords do not match. Please try again.</div>
        <?php endif; ?>

        <div class="edi-auth-card">
            <h2 class="edi-auth-card-title">SIGN UP</h2>
            <form action="" method="post" autocomplete="on">
                <label class="edi-auth-label" for="emailField">E-mail</label>
                <input type="email" name="email" id="emailField" class="edi-auth-input" placeholder="E-mail" required autocomplete="email">

                <input type="hidden" name="username" id="usernameField">

                <label class="edi-auth-label" for="passwordField">Create password</label>
                <div class="edi-auth-input-wrap">
                    <input type="password" name="password" id="passwordField" class="edi-auth-input" placeholder="Create password" required minlength="6" autocomplete="new-password">
                    <button type="button" class="edi-auth-toggle-pw" data-target="passwordField" aria-label="Show password"><i class="fa fa-eye-slash"></i></button>
                </div>

                <label class="edi-auth-label" for="confirmPasswordField">Confirm password</label>
                <div class="edi-auth-input-wrap">
                    <input type="password" name="confirm_password" id="confirmPasswordField" class="edi-auth-input" placeholder="Confirm password" required minlength="6" autocomplete="new-password">
                    <button type="button" class="edi-auth-toggle-pw" data-target="confirmPasswordField" aria-label="Show password"><i class="fa fa-eye-slash"></i></button>
                </div>

                <label class="edi-auth-label" for="nameField">Full name</label>
                <input type="text" name="name" id="nameField" class="edi-auth-input" placeholder="Full name" required autocomplete="name">

                <label class="edi-auth-label" for="countrySel">Country</label>
                <select name="country" id="countrySel" class="edi-auth-input" required style="appearance:auto;">
                    <option value="">Select country</option>
                    <?php foreach ($countries as $c): ?>
                        <option value="<?php echo htmlspecialchars($c, ENT_QUOTES, "UTF-8"); ?>" <?php echo ($c === "Sri Lanka") ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($c, ENT_QUOTES, "UTF-8"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="signupSubmit" class="edi-auth-btn-primary mt-2" value="1">Create account</button>
            </form>

            <p class="edi-auth-switch">Already have an account? <a href="login.php">Sign in</a></p>

            <div class="edi-auth-divider"><span>Or</span></div>

            <button type="button" class="edi-auth-btn-social" onclick="alert('Facebook sign-up is coming soon!');">
                <span class="edi-auth-social-icon"><i class="fab fa-facebook-f" style="color:#1877f2;"></i></span>
                Login with Facebook
            </button>
            <script src="https://accounts.google.com/gsi/client" async defer></script>
            <div id="g_id_onload"
                 data-client_id="402778710681-dujccv06k87u7lh0s427016nt97iti99.apps.googleusercontent.com"
                 data-callback="handleGoogleSignup">
            </div>
            <div class="g_id_signin" data-type="standard"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="userExistsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-center d-block">
                <i class="fa fa-exclamation-circle text-warning fa-3x mb-3"></i>
                <h5 class="modal-title">Account already exists</h5>
            </div>
            <div class="modal-body text-center">
                An account with this email is already registered. Try signing in or use another email.
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Try again</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" data-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-center d-block">
                <i class="fa fa-check-circle text-success fa-3x mb-3"></i>
                <h5 class="modal-title">Account created!</h5>
            </div>
            <div class="modal-body text-center">
                Welcome to Edibear. You can go to your home page whenever you are ready.
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" id="btnRedirect">Continue</button>
            </div>
        </div>
    </div>
</div>

<?php echo $userHeader->printUserFooter(); ?>

<?php if ($userExists): ?>
<script>$(function () { $("#userExistsModal").modal("show"); });</script>
<?php endif; ?>
<?php if ($signupSuccess): ?>
<script>
    $(function () {
        $("#successModal").modal("show");
        $("#btnRedirect").click(function () {
            localStorage.setItem("user_session", "<?php echo (int) $newUserID; ?>");
            localStorage.setItem("session_time", Math.floor(Date.now() / 1000));
            window.location.replace("./index?uid=" + localStorage.getItem("user_session"));
        });
    });
</script>
<?php endif; ?>

<script>
$("#emailField").on("input", function () {
    $("#usernameField").val($(this).val());
});
document.querySelectorAll(".edi-auth-toggle-pw").forEach(function (btn) {
    btn.addEventListener("click", function () {
        var id = this.getAttribute("data-target");
        var inp = document.getElementById(id);
        if (!inp) return;
        var show = inp.getAttribute("type") === "password";
        inp.setAttribute("type", show ? "text" : "password");
        this.innerHTML = show ? "<i class=\"fa fa-eye\"></i>" : "<i class=\"fa fa-eye-slash\"></i>";
    });
});
function handleGoogleSignup(response) {
    try {
        var payload = JSON.parse(atob(response.credential.split(".")[1]));
        fetch("google-login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: payload.email, name: payload.name || "" })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                localStorage.setItem("user_session", data.user_id);
                window.location.href = "./index";
            });
    } catch (e) {}
}
</script>
</body>
</html>
