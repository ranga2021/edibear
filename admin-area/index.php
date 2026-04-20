<?php
require_once("../classes/session_config.php");

require_once("../classes/class.user.php");
$login = new USER();

if(isset($_SESSION['session_tourism'])){
    // Use an absolute-style path if relative fails
    header("Location: dashboard.php");
    exit;
}
$incorrectUsernamePassword = false;

// Helper to resolve friendly page ids (e.g. "dashboard" or "ad1") to actual files when mod_rewrite is not available.
function resolveAdminPageRedirect(string $page): string {
    $page = trim($page);
    if ($page === '') {
        return 'dashboard.php';
    }

    // If caller already provided a file extension or a full path, trust it.
    if (preg_match('/\.(php|html?)$/i', $page) || preg_match('#^(https?://|/|\.\/)#i', $page)) {
        return $page;
    }

    $candidate = __DIR__ . DIRECTORY_SEPARATOR . $page . '.php';
    if (file_exists($candidate)) {
        return $page . '.php';
    }

    // Fallback: keep legacy behaviour (e.g. ?page=dashboard)
    return $page;
}


//if(isset($_POST['loginSubmit'])){
   // if(isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response'])){
        // $secret = 'ES_eaaedab7ac1040b593545138aef2e3bc';
        // $verifyResponse = file_get_contents('https://api.hcaptcha.com/siteverify?secret='.$secret.'&response='.$_POST['h-captcha-response'].'&remoteip='.$_SERVER['REMOTE_ADDR']);
        // echo "verify Responce: " . $verifyResponse;
        // $responseData = json_decode($verifyResponse);    
        
        // $SECRET_KEY = "ES_eaaedab7ac1040b593545138aef2e3bc";
        // $VERIFY_URL = "https://api.hcaptcha.com/siteverify";
        
        // // Retrieve token from POST data with key 'h-captcha-response'
        // $token = $_POST['h-captcha-response'];
        
        // // Build payload with secret key and token
        // $data = array(
        // 'secret' => $SECRET_KEY,
        // 'response' => $token
        // );
        
        // // Make POST request with data payload to hCaptcha API endpoint
        // $options = array(
        // 'http' => array(
        //     'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        //     'method' => 'POST',
        //     'content' => http_build_query($data)
        // )
        // );
        // $context = stream_context_create($options);
        // $response = file_get_contents($VERIFY_URL, false, $context);
        
        // //echo $response;
         
        // // Parse JSON from response. Check for success or error codes
        // $response_json = json_decode($response, true);
        // $success = $response_json['success'];
        

        //echo "Captcha Responce: " . $success;
        //if($success){
        
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submitted']) && $_POST['form_submitted'] === 'true') {
    
          $umail = strip_tags(isset($_POST['inputEmail']) ? $_POST['inputEmail'] : "");
          $upass = strip_tags(isset($_POST['inputPassword']) ? $_POST['inputPassword'] : "");
          
          // Verify reCAPTCHA token
          $recaptcha_token = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
          $secret_key = '6LccC4wqAAAAAEpDGD7q1dVvHZzJ8rxdmVYFLz7B'; // Replace with your actual secret key
          $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
          
          // cPanel commonly disables allow_url_fopen; avoid wrapper warnings by preferring cURL.
          // (Current codebase doesn't enforce captcha success for admin login, so we just attempt verification when possible.)
          if (!empty($recaptcha_token)) {
              $response = false;
              if (function_exists('curl_init')) {
                  $ch = curl_init();
                  curl_setopt($ch, CURLOPT_URL, $verify_url);
                  curl_setopt($ch, CURLOPT_POST, true);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                      'secret' => $secret_key,
                      'response' => $recaptcha_token,
                  ]));
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                  $response = curl_exec($ch);
                  curl_close($ch);
              } else if (ini_get('allow_url_fopen')) {
                  $response = file_get_contents($verify_url, false, stream_context_create([
                      'http' => [
                          'method' => 'POST',
                          'header' => 'Content-type: application/x-www-form-urlencoded',
                          'content' => http_build_query(['secret' => $secret_key, 'response' => $recaptcha_token])
                      ]
                  ]));
              }
              
              $result = $response ? json_decode($response) : null;
          }
          
          
            if($login->doLogin($umail,$upass)){
    // Get the user ID to store it
    $userId = $_SESSION['session_tourism']; 
    
    echo "<script>
        localStorage.setItem('admin_session', '$userId');
        localStorage.setItem('session_time', " . time() . ");
        window.location.href = 'dashboard.php';
    </script>";
    exit;
} else {
    $incorrectUsernamePassword = true;
}
          
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../img/Favicon.png">
  <title>Admin Login - Edibear</title>
  <meta name="Title" content="Traveylo | Sri Lanka Tour Packages | Travel Agent in Sri Lanka" />
  <meta property='og:title' content='Traveylo | Sri Lanka Tour Packages | Travel Agent in Sri Lanka'/>
  <meta name='description' content='“Ayubowan!” Traveylo.com provides tour packages covering the most beautiful places 
in Sri Lanka, and you can travel in luxury with your own vehicle around 
our beautiful country. So reserve your tour with us.' />
  <meta name='keywords' content='Travel Agents In Sri Lanka / Sri Lanka Tourism / Sri Lanka Tourist Destinations / Places To Visit In Sri Lanka With Family / How To Travel In Sri Lanka / Sri Lanka Tours & Travels / Tour Packages In Sri Lanka / Sri Lanka Itinerary / Sri Lanka Travel Guide /Sri Lanka HotelsSri Lanka Tour Operators /Sri Lanka Budgets Tours /Small Group Tour In Sri Lanka / Sri Lanka Holiday Packages /Sri Lanka Tour Packages For Couple / Sri Lanka Tour Packages For Family /Sri Lanka Tour Packages Price / What To Do In Sri Lanka /Popular Destinations In Sri Lanka' />
		
    <!-- for Facebook -->
    <meta property="og:title" content="Traveylo | Sri Lanka Tour Packages | Travel Agent in Sri Lanka"/>
    <meta property="og:site_name" content="Traveylo Website"/>
    <meta property="og:image" content="https://traveylo.com/img/Logo-Footer.png" />
    <meta property="og:url" content="https://traveylo.com" />
    <meta property="og:description" content='"Ayubowan!” Traveylo.com provides tour packages covering the most beautiful places 
in Sri Lanka, and you can travel in luxury with your own vehicle around 
our beautiful country. So reserve your tour with us.'>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="./assets/css/argon-dashboard.css?v=2.0.4" rel="stylesheet" />
  <style>
    body.admin-login-body {
      background-color: #f9fafb;
      font-family: "Open Sans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
        sans-serif;
    }

    .admin-login-main {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-page-wrapper {
      width: 100%;
      max-width: 420px;
      padding: 24px 16px;
      margin: 0 auto;
      text-align: center;
    }

    .login-page-title {
      font-size: 20px;
      font-weight: 600;
      color: #111827;
      margin-bottom: 16px;
    }

    .login-card {
      background-color: #ffffff;
      border-radius: 14px;
      box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
      padding: 32px 28px 28px;
    }

    .login-card-title {
      font-size: 18px;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #ef4444;
      margin-bottom: 24px;
    }

    .login-label {
      display: block;
      text-align: left;
      font-size: 12px;
      font-weight: 600;
      color: #6b7280;
      margin-bottom: 6px;
    }

    .login-input-group .input-group-text {
      background-color: #f9fafb;
      border-radius: 10px 0 0 10px;
      border-color: #e5e7eb;
      color: #9ca3af;
      font-size: 14px;
    }

    .login-input-group .form-control {
      border-radius: 0 10px 10px 0;
      border-color: #e5e7eb;
      font-size: 14px;
      padding: 10px 12px;
      box-shadow: none;
    }

    .login-input-group .form-control:focus {
      border-color: #33a675;
      box-shadow: 0 0 0 1px #bbf7d0;
    }

    .login-input-group.password-group .form-control {
      border-radius: 0;
      border-left: 0;
      border-right: 0;
    }

    .login-input-group.password-group .input-group-text:first-child {
      border-radius: 10px 0 0 10px;
    }

    .login-input-group.password-group .input-group-text.password-toggle {
      border-radius: 0 10px 10px 0;
      cursor: pointer;
      border-left: 0;
      color: #9ca3af;
      transition: color 0.15s ease;
    }

    .login-input-group.password-group .input-group-text.password-toggle:hover {
      color: #111827;
    }

    .login-forgot-row {
      display: flex;
      justify-content: flex-end;
      margin-top: 4px;
      margin-bottom: 20px;
    }

    .login-forgot-link {
      font-size: 12px;
      color: #6b7280;
      text-decoration: none;
    }

    .login-forgot-link:hover {
      color: #33a675;
      text-decoration: underline;
    }

    .login-submit-btn {
      background-color: #33a675;
      border-color: #33a675;
      border-radius: 999px;
      font-weight: 600;
      font-size: 14px;
      padding: 10px 0;
      box-shadow: 0 10px 20px rgba(22, 163, 74, 0.35);
    }

    .login-submit-btn:hover {
      background-color: #2a8f61;
      border-color: #2a8f61;
    }

    .login-logo {
      margin-bottom: 10px;
    }

    .login-logo img {
      max-width: 140px;
      height: auto;
    }

    @media (max-width: 576px) {
      .login-card {
        padding: 24px 20px 22px;
      }

      .login-page-wrapper {
        padding-top: 40px;
        padding-bottom: 40px;
      }
    }
  </style>
  <!--hCaptcha
  <script src='https://www.hCaptcha.com/1/api.js' async defer></script> -->
   <!--reCaptcha-->
  
  <script src="./assets/js/plugins/jquery.min.js"></script>
</head>

<body class="admin-login-body">
  <main class="admin-login-main">
    <div class="login-page-wrapper">
      <div class="login-logo">
        <img src="../img/Logo.png" alt="Logo">
      </div>
      <h2 class="login-page-title">Admin Login</h2>

      <div class="login-card">
        <h3 class="login-card-title">Sign In</h3>

        <form id="loginForm" action="" method="POST">
          <div class="mb-3">
            <label class="login-label" for="inputEmail">E-mail</label>
            <div class="input-group login-input-group">
              <span class="input-group-text">
                <i class="ni ni-email-83"></i>
              </span>
              <input
                type="email"
                id="inputEmail"
                class="form-control"
                name="inputEmail"
                placeholder="E-mail"
                aria-label="Email"
                required
              >
            </div>
          </div>

          <div class="mb-1">
            <label class="login-label" for="inputPassword">Password</label>
            <div class="input-group login-input-group password-group">
              <span class="input-group-text">
                <i class="ni ni-lock-circle-open"></i>
              </span>
              <input
                type="password"
                id="inputPassword"
                class="form-control"
                name="inputPassword"
                placeholder="Password"
                aria-label="Password"
                required
              >
              <span class="input-group-text password-toggle" id="passwordToggle">
                <i class="ni ni-eye-17"></i>
              </span>
            </div>
          </div>

          <div class="login-forgot-row">
            <a href="#" class="login-forgot-link">Forgot password?</a>
          </div>

          <input type="hidden" name="form_submitted" value="true">
          <button
            id="loginSubmit"
            name="loginSubmit"
            class="g-recaptcha btn login-submit-btn w-100 mt-1"
            type="submit"
          >
            Login
          </button>
        </form>
      </div>
    </div>
  </main>
  <?php
    if ($incorrectUsernamePassword) {
      echo "
        <script>
            $(function(){
                $('#IncorrectUsernamePasswordModal').modal('show');
            });
        </script>
      ";
    }
  ?>
  <!--   Core JS Files   -->
  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
   <script>
   
 </script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    
  </script>
  <!-- Github buttons -->
  <script async defer src="./assets/js/plugins/buttons.js"></script>

    <!--IncorrectUsernamePassword Modal-->
    <div class="modal fade" id="IncorrectUsernamePasswordModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true" style="margin-top:200px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Incorrect Username or Password.<br>Please Try again</h5>
                </div>
                <div class="modal-body">
                  <button class="btn btn-sm btn-primary" type="button" onclick="location.reload()">Okay</button>
                </div>
            </div>
        </div>
    </div>
    <!--Modal End-->

    <script>
document.getElementById("loginForm").addEventListener("submit", function(e) {
    const email = document.querySelector("input[name='inputEmail']").value;
    const password = document.querySelector("input[name='inputPassword']").value;
    // Basic front-end presence checks; main validation is handled on the server.
    if (!email || !password) {
        e.preventDefault();
    }
});

const passwordInput = document.getElementById("inputPassword");
const passwordToggle = document.getElementById("passwordToggle");

if (passwordInput && passwordToggle) {
  passwordToggle.addEventListener("click", function () {
    const isPassword = passwordInput.getAttribute("type") === "password";
    passwordInput.setAttribute("type", isPassword ? "text" : "password");
    this.classList.toggle("active", isPassword);
  });
}
</script>

</body>

</html>