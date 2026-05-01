<?php
require_once("./classes/class.user.php");
require_once("./classes/class.header.php");
require_once("./classes/edi_password_reset_mail.php");

$userHeader = new HEADER();
$user = new USER();
$conn = $user->getConnection();

function edibear_reset_columns_ready(PDO $conn)
{
    try {
        $s = $conn->query("SHOW COLUMNS FROM `tourists` LIKE 'password_reset_token'");
        return $s && $s->rowCount() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

$resetReady = edibear_reset_columns_ready($conn);
$flash = "";
$flashOk = false;
$view = "request";
$activeToken = "";

if (!$resetReady) {
    $view = "not_configured";
}

if ($resetReady && isset($_GET["token"])) {
    $tokenIn = trim((string) $_GET["token"]);
    if (!preg_match("/^[a-f0-9]{64}$/i", $tokenIn)) {
        $flash = "That reset link is not valid.";
        $flashOk = false;
        $view = "request";
    } else {
        $st = $conn->prepare(
            "SELECT id FROM tourists WHERE password_reset_token = :t AND password_reset_expires > NOW() AND status = 1 LIMIT 1"
        );
        $st->execute([":t" => $tokenIn]);
        if (!$st->fetch(PDO::FETCH_ASSOC)) {
            $flash = "This reset link has expired or was already used. Please request a new one.";
            $flashOk = false;
            $view = "request";
        } else {
            $view = "reset_form";
            $activeToken = $tokenIn;
        }
    }
}

if ($resetReady && isset($_POST["request_reset"])) {
    $email = trim((string) ($_POST["email"] ?? ""));
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $flash = "Please enter a valid email address.";
        $flashOk = false;
        $view = "request";
    } else {
        $st = $conn->prepare(
            "SELECT id, email, name FROM tourists WHERE (email = :e OR username = :e2) AND status = 1 LIMIT 1"
        );
        $st->execute([":e" => $email, ":e2" => $email]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            $tok = bin2hex(random_bytes(32));
            $exp = date("Y-m-d H:i:s", time() + 3600);
            $upd = $conn->prepare(
                "UPDATE tourists SET password_reset_token = :tok, password_reset_expires = :ex WHERE id = :id"
            );
            $upd->execute([":tok" => $tok, ":ex" => $exp, ":id" => (int) $u["id"]]);
            $url = edibear_public_base_url() . "/forgotpassword.php?token=" . rawurlencode($tok);
            $to = trim((string) ($u["email"] ?? ""));
            if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $to = $email;
            }
            $sent = edibear_send_password_reset_email($to, (string) ($u["name"] ?? ""), $url);
            if (!$sent) {
                error_log('edibear: password reset email failed to send for tourist id ' . (int) $u['id']);
            }
        }
        /* Same message whether or not the email exists (avoid account enumeration). */
        $view = "sent";
    }
}

if ($resetReady && isset($_POST["complete_reset"])) {
    $tok = preg_replace("/[^a-f0-9]/i", "", (string) ($_POST["reset_token"] ?? ""));
    $p1 = (string) ($_POST["new_password"] ?? "");
    $p2 = (string) ($_POST["confirm_password"] ?? "");

    if (strlen($tok) !== 64) {
        $flash = "Invalid reset session.";
        $flashOk = false;
        $view = "request";
    } elseif (strlen($p1) < 6) {
        $flash = "Password must be at least 6 characters.";
        $flashOk = false;
        $view = "reset_form";
        $activeToken = $tok;
    } elseif ($p1 !== $p2) {
        $flash = "Passwords do not match.";
        $flashOk = false;
        $view = "reset_form";
        $activeToken = $tok;
    } else {
        $st = $conn->prepare(
            "SELECT id FROM tourists WHERE password_reset_token = :t AND password_reset_expires > NOW() AND status = 1 LIMIT 1"
        );
        $st->execute([":t" => $tok]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $flash = "This reset link has expired or was already used.";
            $flashOk = false;
            $view = "request";
        } else {
            $hash = "pass" . password_hash($p1, PASSWORD_DEFAULT);
            $upd = $conn->prepare(
                "UPDATE tourists SET password = :p, password_reset_token = NULL, password_reset_expires = NULL WHERE id = :id"
            );
            $upd->execute([":p" => $hash, ":id" => (int) $row["id"]]);
            header("Location: login.php?reset=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader("Forgot password"); ?>
    <link rel="stylesheet" href="css/edi-auth.css">
</head>
<body class="edi-auth-page">

<?php echo $userHeader->printUserNav(); ?>
<div class="page-header-bg"></div>

<div class="container-fluid py-5 page-header-content">
    <div class="container">

        <?php if ($view === "not_configured"): ?>
            <div class="edi-auth-card edi-auth-forgot-block">
                <h1>Password reset</h1>
                <p class="edi-auth-lead">Password reset is not available until the database is updated. Please run the SQL script <code>sql/add_tourist_password_reset.sql</code> on your server, then reload this page.</p>
                <p class="text-center mt-3"><a href="login.php">Back to sign in</a></p>
            </div>

        <?php elseif ($view === "sent"): ?>
            <h1 class="edi-auth-hero-title">Check your email</h1>
            <p class="edi-auth-hero-lead">If an account exists for that address, we sent a link to reset your password. The link works for one hour. If you do not see the message, check your spam folder.</p>
            <p class="text-center"><a href="login.php">Return to sign in</a></p>

        <?php elseif ($view === "reset_form"): ?>
            <div class="edi-auth-card edi-auth-forgot-block">
                <h1>Set a new password</h1>
                <p class="edi-auth-lead">Choose a new password for your Edibear account.</p>
                <?php if ($flash !== ""): ?>
                    <div class="edi-auth-alert <?php echo $flashOk ? "ok" : "err"; ?>"><?php echo htmlspecialchars($flash, ENT_QUOTES, "UTF-8"); ?></div>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="hidden" name="reset_token" value="<?php echo htmlspecialchars($activeToken, ENT_QUOTES, "UTF-8"); ?>">
                    <label class="edi-auth-label" for="np">New password</label>
                    <div class="edi-auth-input-wrap">
                        <input class="edi-auth-input" type="password" name="new_password" id="np" required minlength="6" autocomplete="new-password">
                        <button type="button" class="edi-auth-toggle-pw" data-target="np" aria-label="Show password"><i class="fa fa-eye-slash"></i></button>
                    </div>
                    <label class="edi-auth-label" for="cp">Confirm password</label>
                    <div class="edi-auth-input-wrap">
                        <input class="edi-auth-input" type="password" name="confirm_password" id="cp" required minlength="6" autocomplete="new-password">
                        <button type="button" class="edi-auth-toggle-pw" data-target="cp" aria-label="Show password"><i class="fa fa-eye-slash"></i></button>
                    </div>
                    <button type="submit" name="complete_reset" class="edi-auth-btn-primary mt-2">Update password</button>
                </form>
            </div>

        <?php else: ?>
            <?php if ($flash !== ""): ?>
                <div class="edi-auth-alert <?php echo $flashOk ? "ok" : "err"; ?>"><?php echo htmlspecialchars($flash, ENT_QUOTES, "UTF-8"); ?></div>
            <?php endif; ?>

            <div class="edi-auth-card edi-auth-forgot-block">
                <h1>Forgot password?</h1>
                <p class="edi-auth-lead">Please enter your email address. You will receive a link to create a new password via email.</p>
                <form method="post" action="">
                    <label class="edi-auth-label" for="em">E-mail</label>
                    <input class="edi-auth-input" type="email" name="email" id="em" placeholder="E-mail" required autocomplete="email">
                    <button type="submit" name="request_reset" class="edi-auth-btn-primary mt-2">Send</button>
                </form>
                <p class="text-center mt-3"><a href="login.php">Back to sign in</a></p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php echo $userHeader->printUserFooter(); ?>
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
</script>
</body>
</html>
