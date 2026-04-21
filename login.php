<?php
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
$userHeader = new HEADER();
$user = new USER();

$loginSuccess = false;
$incorrectUsernamePassword = false;

if (isset($_POST["loginSubmit"])) {
    $loginId = trim(strip_tags($_POST["username"] ?? ""));
    $password = (string) ($_POST["password"] ?? "");

    if ($loginId !== "") {
        $conn = $user->getConnection();
        $stmt = $conn->prepare(
            "SELECT id, password FROM tourists WHERE status = 1 AND (username = :l OR email = :l2) LIMIT 1"
        );
        $stmt->execute([":l" => $loginId, ":l2" => $loginId]);
        $userArr = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userArr && isset($userArr["password"])) {
            $stored = (string) $userArr["password"];
            $hash = strpos($stored, "pass") === 0 ? substr($stored, 4) : $stored;
            if (password_verify($password, $hash)) {
                $loginSuccess = true;
                $userID = (int) $userArr["id"];
            } else {
                $incorrectUsernamePassword = true;
            }
        } else {
            $incorrectUsernamePassword = true;
        }
    } else {
        $incorrectUsernamePassword = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader("Login"); ?>
    <link rel="stylesheet" href="css/edi-auth.css">
</head>
<body class="edi-auth-page">

<?php echo $userHeader->printUserNav(); ?>
<div class="page-header-bg"></div>

<script>
const userSession = localStorage.getItem("user_session");
if (userSession) {
    window.location.replace("./account?uid=" + userSession);
}
</script>

<div class="container-fluid py-5 page-header-content">
    <div class="container">

        <h1 class="edi-auth-hero-title">Welcome Home!</h1>
        <p class="edi-auth-hero-lead">
            Step inside to collect and track your treasures, complete your favorite challenges, and meet Edi’s family!
            You can also share your own adventure stories here. Create your account today to stay connected with
            everything happening in Edibear’s world.
        </p>

        <?php if (isset($_GET["reset"]) && (string) $_GET["reset"] === "1"): ?>
            <div class="edi-auth-alert ok">Your password was updated. You can sign in below.</div>
        <?php endif; ?>

        <div class="edi-auth-card">
            <h2 class="edi-auth-card-title">SIGN IN</h2>
            <form action="" method="post" autocomplete="on">
                <label class="edi-auth-label" for="login_user">E-mail</label>
                <input class="edi-auth-input" type="text" name="username" id="login_user" placeholder="E-mail" required autocomplete="username">

                <label class="edi-auth-label" for="login_pw">Password</label>
                <div class="edi-auth-input-wrap">
                    <input class="edi-auth-input" type="password" name="password" id="login_pw" placeholder="Password" required autocomplete="current-password">
                    <button type="button" class="edi-auth-toggle-pw" data-target="login_pw" aria-label="Show password"><i class="fa fa-eye-slash"></i></button>
                </div>

                <div class="edi-auth-forgot">
                    <a href="forgotpassword.php">Forgot password?</a>
                </div>

                <button type="submit" name="loginSubmit" class="edi-auth-btn-primary" value="1">Login</button>
            </form>

            <p class="edi-auth-switch">Don't have an account? <a href="signup.php">Sign up</a></p>

            <div class="edi-auth-divider"><span>Or</span></div>

            <button type="button" class="edi-auth-btn-social" onclick="alert('Facebook sign-in is coming soon!');">
                <span class="edi-auth-social-icon"><i class="fab fa-facebook-f" style="color:#1877f2;"></i></span>
                Login with Facebook
            </button>

            <script src="https://accounts.google.com/gsi/client" async defer></script>
            <div id="g_id_onload"
                 data-client_id="402778710681-dujccv06k87u7lh0s427016nt97iti99.apps.googleusercontent.com"
                 data-callback="handleGoogleLogin">
            </div>
            <div class="g_id_signin" data-type="standard"></div>
        </div>
    </div>
</div>

<?php echo $userHeader->printUserFooter(); ?>

<?php if ($incorrectUsernamePassword): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (typeof $ !== "undefined" && $("#IncorrectUsernamePasswordModal").modal) {
        $("#IncorrectUsernamePasswordModal").modal("show");
    } else {
        alert("Incorrect email or password. Please try again.");
    }
});
</script>
<?php endif; ?>

<?php if ($loginSuccess): ?>
<script>
    localStorage.setItem("user_session", "<?php echo (int) $userID; ?>");
    localStorage.setItem("session_time", Math.floor(Date.now() / 1000));
    window.location.replace("./index?uid=" + localStorage.getItem("user_session"));
</script>
<?php endif; ?>

<div class="modal fade" id="IncorrectUsernamePasswordModal" data-backdrop="static" tabindex="-1" role="dialog" style="margin-top:200px">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Incorrect email or password.<br>Please try again.</h5>
            </div>
            <div class="modal-body">
                <button type="button" class="btn btn-sm btn-primary" onclick="location.reload()">Okay</button>
            </div>
        </div>
    </div>
</div>

<script>
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

function handleGoogleLogin(response) {
    const data = parseJwt(response.credential);
    fetch("google-login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: data.email, name: data.name })
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            localStorage.setItem("user_session", data.user_id);
            window.location.href = "./index";
        })
        .catch(function (err) { console.error(err); });
}

function parseJwt(token) {
    try {
        return JSON.parse(atob(token.split(".")[1]));
    } catch (e) {
        return {};
    }
}
</script>
</body>
</html>
