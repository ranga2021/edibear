<?php
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/class.widgets.php");

$userHeader = new HEADER();
$user = new USER();
$widgets = new WIDGETS();

$loginSuccess = false;
$incorrectUsernamePassword = false;

if (isset($_POST['loginSubmit'])) {

    $username = strip_tags($_POST['username'] ?? "");
    $password = strip_tags($_POST['password'] ?? "");

    if ($user->CountRows("tourists", array("username"=>$username, "status"=>1)) == 1) {

        $userArr = $user->fetchAll(
            array("id","password"),
            array("tourists"),
            array("username"=>$username, "status"=>1)
        )[0];

        if (password_verify($password, substr($userArr["password"],4))) {
            $loginSuccess = true;
            $userID = $userArr['id'];
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
<?php echo $userHeader->printUserHeader("Login") ?>
</head>

<body>

<?php echo $userHeader->printUserNav(); ?>
<div class="page-header-bg"></div>

<script>
// ✅ CHECK LOGIN (redirect if already logged)
const userSession = localStorage.getItem('user_session');

if (userSession) {
    window.location.replace('./account?uid=' + userSession);
}
</script>

<div class="container-fluid py-5 page-header-content">
<div class="container text-center">

        <!-- TITLE -->
        <h2>Welcome Home!</h2>

        <p style="max-width:800px; margin:15px auto; color:#666; text-align:left;">
            Step inside to collect and track your treasures, complete your favorite challenges,and meet Edi’s family! 
            You can also share your own adventure stories with here.Create your account today to stay connected with 
            everything happening in Edibear’s world.
        </p>

        <!-- LOGIN CARD -->
        <div style="
            max-width:420px;
            margin:30px auto;
            background:#fff;
            border-radius:12px;
            padding:30px;
            box-shadow:0 5px 20px rgba(0,0,0,0.1);
            border:1px solid #eee;
        ">

            <h4>SIGN IN</h4>

            <form action="" method="post">

                <input type="text" name="username" placeholder="E-mail" required
                style="
                    width:100%;
                    padding:12px;
                    border:2px solid #33a675;
                    border-radius:6px;
                    margin-top:15px;
                ">

                <input type="password" name="password" placeholder="Password" required
                style="
                    width:100%;
                    padding:12px;
                    border:2px solid #33a675;
                    border-radius:6px;
                    margin-top:10px;
                ">

                <div style="text-align:center; margin:10px 0; font-size:13px;">
                    <a href="./forgotpassword" style="color:#666;">Forgot password?</a>
                </div>

                <input type="submit" name="loginSubmit" value="Login"
                style="
                    width:100%;
                    background:#33a675;
                    color:#fff;
                    border:none;
                    padding:12px;
                    border-radius:6px;
                    font-weight:600;
                    margin-top:10px;
                ">

            </form>

            <p style="margin-top:15px; font-size:14px;">
                Don't have an account? 
                <a href="signup.php" style="color:#2e7d32; font-weight:600;">Sign up</a>
            </p>

            <!-- DIVIDER -->
            <div style="display:flex; align-items:center; margin:20px 0;">
                <div style="flex:1; height:1px; background:#ccc;"></div>
                <span style="margin:0 10px; font-size:13px;">Or</span>
                <div style="flex:1; height:1px; background:#ccc;"></div>
            </div>

            <!-- SOCIAL BUTTONS (UI ONLY) -->

            <script src="https://accounts.google.com/gsi/client" async defer></script>

<div id="g_id_onload"
     data-client_id="402778710681-dujccv06k87u7lh0s427016nt97iti99.apps.googleusercontent.com"
     data-callback="handleGoogleLogin">
</div>

<div class="g_id_signin" data-type="standard"></div>

        </div>

    </div>

</div>

<?php 
echo $userHeader->printUserFooter(); 

// ❌ WRONG LOGIN
if ($incorrectUsernamePassword) {
    echo "
    <script>
        $(function(){
            $('#IncorrectUsernamePasswordModal').modal('show');
        });
    </script>
    ";
}

// ✅ SUCCESS LOGIN → SAVE LOCAL STORAGE
if ($loginSuccess) {
    echo "
    <script>
        localStorage.setItem('user_session', '".$userID."');
        localStorage.setItem('session_time', Math.floor(Date.now()/1000));
        window.location.replace('./index?uid=' + localStorage.getItem('user_session'));
    </script>
    ";
}
?>

<script>
var x = $("input[name='password']");
$("#showPassword").click(function (){
    if ( x.attr("type") == "password" ) {
        x.attr("type", "text");
    } else {
        x.attr("type", "password");
    }
});
</script>

<div class="modal fade" id="IncorrectUsernamePasswordModal" data-backdrop="static" tabindex="-1" role="dialog" style="margin-top:200px">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Incorrect Username or Password.<br>Please Try again</h5>
</div>
<div class="modal-body">
<button class="btn btn-sm btn-primary" onclick="location.reload()">Okay</button>
</div>
</div>
</div>
</div>
<script>
function handleGoogleLogin(response) {
    console.log("Google response:", response); // DEBUG

    const data = parseJwt(response.credential);

    console.log("Decoded:", data); // DEBUG

    fetch("google-login.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            email: data.email,
            name: data.name
        })
    })
    .then(res => res.json())
    .then(data => {
        console.log("Backend:", data);

        localStorage.setItem('user_session', data.user_id);
        window.location.href = './index';
    })
    .catch(err => console.error(err));
}

function parseJwt(token) {
    try {
        return JSON.parse(atob(token.split('.')[1]));
    } catch (e) {
        console.error("JWT decode error", e);
        return {};
    }
}
</script>

</body>
</html>