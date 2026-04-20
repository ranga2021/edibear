<?php
declare(strict_types=1);
session_start();

require_once(__DIR__ . "/classes/class.user.php");

// One-time local admin bootstrap script.
// After creating an admin successfully, DELETE this file.

$SETUP_KEY = "myadminkey";

function isLocalhostRequest(): bool {
    $addr = $_SERVER["REMOTE_ADDR"] ?? "";
    return $addr === "127.0.0.1" || $addr === "::1";
}

if (!isLocalhostRequest()) {
    http_response_code(403);
    echo "Forbidden.";
    exit;
}

$key = (string)($_GET["key"] ?? "");
if ($SETUP_KEY === "CHANGE_ME_TO_SOMETHING_RANDOM") {
    http_response_code(500);
    echo "Setup key not configured. Open create-admin.php and set \$SETUP_KEY, then reload.";
    exit;
}

if (!hash_equals($SETUP_KEY, $key)) {
    http_response_code(403);
    echo "Forbidden (bad key).";
    exit;
}

$user = new USER();
$message = "";
$created = false;

// Show current admins (delete_status = 0)
$admins = [];
try {
    $stmt = $user->runQuery("SELECT id, first_name, last_name, login_email, mobile_number FROM user_table WHERE delete_status = 0 ORDER BY id DESC");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $message = "DB error while listing admins: " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first = trim((string)($_POST["first_name"] ?? ""));
    $last = trim((string)($_POST["last_name"] ?? ""));
    $email = trim((string)($_POST["email"] ?? ""));
    $mobile = trim((string)($_POST["mobile"] ?? ""));
    $pass = (string)($_POST["password"] ?? "");
    $pass2 = (string)($_POST["password2"] ?? "");

    if ($first === "" || $last === "" || $email === "" || $mobile === "" || $pass === "") {
        $message = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email.";
    } elseif ($pass !== $pass2) {
        $message = "Passwords do not match.";
    } else {
        try {
            // Prevent duplicates
            $stmt = $user->runQuery("SELECT COUNT(*) AS c FROM user_table WHERE delete_status = 0 AND login_email = :email");
            $stmt->execute([":email" => $email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = (int)($row["c"] ?? 0);

            if ($count > 0) {
                $message = "An admin with this email already exists.";
            } else {
                $result = $user->adminRegister($first, $last, $email, $pass, $mobile);
                $message = (string)$result;
                $created = true;
            }
        } catch (Throwable $e) {
            $message = "Error creating admin: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create Admin (one-time)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f6f7fb; }
        .card { max-width: 760px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 6px 18px rgba(0,0,0,.08); }
        h1 { margin: 0 0 8px; font-size: 20px; }
        p { margin: 6px 0 14px; color: #333; }
        .warn { color: #8a1f11; font-weight: 700; }
        .ok { color: #0b6b2f; font-weight: 700; }
        label { display:block; font-weight: 700; margin: 10px 0 6px; }
        input { width: 100%; padding: 10px; border: 1px solid #d7d9e0; border-radius: 8px; }
        .row { display:flex; gap: 12px; }
        .row > div { flex: 1; }
        button { margin-top: 14px; padding: 10px 14px; border: 0; background: #1b66ff; color: #fff; border-radius: 8px; font-weight: 700; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #fafafa; }
        code { background: #f1f3f8; padding: 2px 6px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Create Admin (one-time)</h1>
        <p class="warn">After it works, delete <code>create-admin.php</code>.</p>

        <?php if ($message !== ""): ?>
            <p class="<?php echo $created ? "ok" : "warn"; ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?></p>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="row">
                <div>
                    <label for="first_name">First name</label>
                    <input id="first_name" name="first_name" required />
                </div>
                <div>
                    <label for="last_name">Last name</label>
                    <input id="last_name" name="last_name" required />
                </div>
            </div>

            <label for="email">Login email</label>
            <input id="email" name="email" type="email" required />

            <label for="mobile">Mobile number</label>
            <input id="mobile" name="mobile" required />

            <div class="row">
                <div>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required />
                </div>
                <div>
                    <label for="password2">Confirm password</label>
                    <input id="password2" name="password2" type="password" required />
                </div>
            </div>

            <button type="submit">Create admin</button>
        </form>

        <h2 style="margin-top: 22px; font-size: 16px;">Existing admins</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($admins) === 0): ?>
                    <tr><td colspan="4">No admins found (or DB error).</td></tr>
                <?php else: ?>
                    <?php foreach ($admins as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string)$a["id"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars(trim(($a["first_name"] ?? "") . " " . ($a["last_name"] ?? "")), ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars((string)($a["login_email"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars((string)($a["mobile_number"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

