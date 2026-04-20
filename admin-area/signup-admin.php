<?php
/**
 * Create the first administrator (no login required) when there are zero active admins.
 * After at least one admin exists, signed-in users are redirected to manage-admins.php to add more.
 *
 * Optional env: ADMIN_BOOTSTRAP_TOKEN — if set, the form must include this token (first-time setup only).
 */
declare(strict_types=1);

if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');

require_once(__DIR__ . '/../classes/session_config.php');
require_once(__DIR__ . '/../classes/class.user.php');
require_once(__DIR__ . '/../classes/class.header.php');

$user = new USER();
$adminHeader = new HEADER('signup-admin');

$bootstrapTokenEnv = trim((string) getenv('ADMIN_BOOTSTRAP_TOKEN'));
$error = '';
$info = '';

$activeAdminCount = -1;
try {
    $stmt = $user->getConnection()->query('SELECT COUNT(*) AS c FROM user_table WHERE delete_status = 0');
    $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    $activeAdminCount = (int) ($row['c'] ?? 0);
} catch (Throwable $e) {
    $error = 'Cannot reach the database. Check DB_* environment variables.';
    $activeAdminCount = -1;
}

$loggedIn = isset($_SESSION['session_tourism']);

if ($activeAdminCount > 0 && $loggedIn) {
    header('Location: manage-admins.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $activeAdminCount === 0) {
    if ($bootstrapTokenEnv !== '' && !hash_equals($bootstrapTokenEnv, (string) ($_POST['bootstrap_token'] ?? ''))) {
        $error = 'Invalid or missing setup token. Set ADMIN_BOOTSTRAP_TOKEN in the server environment and enter the same value here.';
    } else {
        $first = trim((string) ($_POST['first_name'] ?? ''));
        $last = trim((string) ($_POST['last_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $mobile = trim((string) ($_POST['mobile'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        $pass2 = (string) ($_POST['password2'] ?? '');

        if ($first === '' || $last === '' || $email === '' || $mobile === '' || $pass === '') {
            $error = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($pass !== $pass2) {
            $error = 'Passwords do not match.';
        } elseif (strlen($pass) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            try {
                $chk = $user->runQuery('SELECT COUNT(*) FROM user_table WHERE delete_status = 0 AND login_email = :e');
                $chk->execute([':e' => $email]);
                if ((int) $chk->fetchColumn() > 0) {
                    $error = 'An admin with this email already exists. Sign in at the login page.';
                } else {
                    $msg = $user->adminRegister($first, $last, $email, $pass, $mobile);
                    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Admin created</title></head><body>';
                    echo '<script>alert(' . json_encode($msg) . ');window.location.href="./index.php";</script>';
                    echo '</body></html>';
                    exit;
                }
            } catch (Throwable $e) {
                $error = 'Could not create admin: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

$pageTitle = $activeAdminCount === 0
    ? 'Create first administrator'
    : 'Administrator signup';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="../img/Favicon.png">
  <title><?php echo htmlspecialchars($pageTitle); ?> - Edibear</title>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <style>
    body.admin-login-body { background-color: #f9fafb; }
    .signup-wrap { max-width: 520px; margin: 0 auto; padding: 24px 16px; }
    .signup-card {
      background: #fff; border-radius: 14px; box-shadow: 0 18px 45px rgba(15,23,42,.12);
      padding: 28px 24px 24px; text-align: left;
    }
    .signup-card h2 { font-size: 1.1rem; font-weight: 700; color: #f65247; margin-bottom: 8px; text-align: center; }
    .signup-card .sub { font-size: 13px; color: #606062; text-align: center; margin-bottom: 20px; }
    .signup-card label { font-size: 12px; font-weight: 600; color: #6b7280; }
    .alert-mini { font-size: 13px; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; }
    .alert-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .alert-info { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
  </style>
</head>
<body class="admin-login-body">
<main class="admin-login-main">
  <div class="signup-wrap">
    <div class="text-center mb-3">
      <a href="./index.php"><img src="../img/Logo.png" alt="Edibear" style="max-width:120px;height:auto;"></a>
    </div>
    <div class="signup-card">

<?php if ($activeAdminCount < 0): ?>
      <h2>Database error</h2>
      <p class="sub"><?php echo htmlspecialchars($error); ?></p>
      <p class="text-center"><a href="./index.php">Back to login</a></p>

<?php elseif ($activeAdminCount > 0 && !$loggedIn): ?>
      <h2>Sign in required</h2>
      <p class="sub">An administrator already exists. New accounts can only be created by a signed-in admin under <strong>Admins (add / edit)</strong>.</p>
      <p class="text-center mt-3"><a href="./index.php" class="btn btn-primary">Go to login</a></p>

<?php else: ?>
      <h2>Create first administrator</h2>
      <p class="sub">There are no active admins yet. Create one account, then sign in at the login page.</p>
      <?php if ($bootstrapTokenEnv !== ''): ?>
        <p class="sub" style="font-size:12px;">This server requires a setup token (environment variable <code>ADMIN_BOOTSTRAP_TOKEN</code>).</p>
      <?php endif; ?>

      <?php if ($error !== ''): ?>
        <div class="alert-mini alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <?php if ($bootstrapTokenEnv !== ''): ?>
        <div class="mb-3">
          <label for="bootstrap_token">Setup token</label>
          <input type="password" class="form-control" id="bootstrap_token" name="bootstrap_token" required autocomplete="off">
        </div>
        <?php endif; ?>

        <div class="row">
          <div class="col-md-6 mb-2">
            <label for="first_name">First name</label>
            <input class="form-control" id="first_name" name="first_name" required maxlength="50"
              value="<?php echo htmlspecialchars((string) ($_POST['first_name'] ?? '')); ?>">
          </div>
          <div class="col-md-6 mb-2">
            <label for="last_name">Last name</label>
            <input class="form-control" id="last_name" name="last_name" required maxlength="50"
              value="<?php echo htmlspecialchars((string) ($_POST['last_name'] ?? '')); ?>">
          </div>
        </div>
        <div class="mb-2">
          <label for="email">Email (login)</label>
          <input type="email" class="form-control" id="email" name="email" required maxlength="100"
            value="<?php echo htmlspecialchars((string) ($_POST['email'] ?? '')); ?>">
        </div>
        <div class="mb-2">
          <label for="mobile">Mobile</label>
          <input class="form-control" id="mobile" name="mobile" required maxlength="20"
            value="<?php echo htmlspecialchars((string) ($_POST['mobile'] ?? '')); ?>">
        </div>
        <div class="row">
          <div class="col-md-6 mb-2">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
          </div>
          <div class="col-md-6 mb-2">
            <label for="password2">Confirm password</label>
            <input type="password" class="form-control" id="password2" name="password2" required minlength="8" autocomplete="new-password">
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-2">Create administrator</button>
      </form>
      <p class="text-center mt-3 mb-0" style="font-size:13px;"><a href="./index.php">Back to login</a></p>
<?php endif; ?>

    </div>
  </div>
</main>
<?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>
