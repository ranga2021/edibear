<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");

$adminHeader = new HEADER("bank-details");
$user = new USER();
$pdo = $user->getConnection();

$msg = "";

$tableReady = false;
try {
    $st = $pdo->query("SELECT 1 FROM edi_bank_details LIMIT 1");
    $tableReady = ($st !== false);
} catch (Throwable $e) {
    $tableReady = false;
}

if (!$tableReady) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `edi_bank_details` (
            `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `account_number` VARCHAR(50)  NOT NULL DEFAULT '',
            `account_name`   VARCHAR(150) NOT NULL DEFAULT '',
            `bank_name`      VARCHAR(150) NOT NULL DEFAULT '',
            `branch_name`    VARCHAR(150) NOT NULL DEFAULT '',
            `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $tableReady = true;
    } catch (Throwable $e) {
        $msg = "<div class='alert alert-danger'>Could not create bank details table.</div>";
    }
}

if ($tableReady && isset($_POST["edi_save_bank_details"])) {
    $accNum  = trim((string) ($_POST["account_number"] ?? ""));
    $accName = trim((string) ($_POST["account_name"] ?? ""));
    $bank    = trim((string) ($_POST["bank_name"] ?? ""));
    $branch  = trim((string) ($_POST["branch_name"] ?? ""));

    $existing = $pdo->query("SELECT id FROM edi_bank_details LIMIT 1")->fetch();
    try {
        if ($existing) {
            $st = $pdo->prepare("UPDATE edi_bank_details SET account_number = :an, account_name = :aname, bank_name = :bn, branch_name = :br WHERE id = :id");
            $st->execute(array(
                ":an"    => $accNum,
                ":aname" => $accName,
                ":bn"    => $bank,
                ":br"    => $branch,
                ":id"    => (int) $existing["id"],
            ));
        } else {
            $st = $pdo->prepare("INSERT INTO edi_bank_details (account_number, account_name, bank_name, branch_name) VALUES (:an, :aname, :bn, :br)");
            $st->execute(array(
                ":an"    => $accNum,
                ":aname" => $accName,
                ":bn"    => $bank,
                ":br"    => $branch,
            ));
        }
        $msg = "<div class='alert alert-success'>Bank details saved.</div>";
    } catch (Throwable $e) {
        $msg = "<div class='alert alert-danger'>Could not save bank details.</div>";
    }
}

$bankRow = array("account_number" => "", "account_name" => "", "bank_name" => "", "branch_name" => "");
if ($tableReady) {
    $row = $pdo->query("SELECT * FROM edi_bank_details LIMIT 1")->fetch();
    if ($row) {
        $bankRow = $row;
    }
}
?>
<script>
    const adminSession = localStorage.getItem('admin_session');
    const sessionTime = localStorage.getItem('session_time');
    const currentTime = Math.floor(Date.now() / 1000);
    if (!adminSession || (currentTime - sessionTime > 1200)) {
        localStorage.removeItem('admin_session');
        window.location.href = 'index.php?error=session_expired';
    }
</script>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
</head>
<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2("Bank Details"); ?>

    <div class="container-fluid py-4">
      <?php echo $msg; ?>

      <div class="row">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Bank Transfer Details</h6>
              <p class="text-sm text-muted mb-0">
                These details are shown to customers when they select <strong>Direct bank transfer</strong> at checkout.
              </p>
            </div>
            <div class="card-body">
              <form method="post">
                <div class="mb-3">
                  <label class="form-label">Account Number</label>
                  <input type="text" name="account_number" class="form-control" maxlength="50" value="<?php echo htmlspecialchars((string) $bankRow["account_number"], ENT_QUOTES, "UTF-8"); ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Account Name</label>
                  <input type="text" name="account_name" class="form-control" maxlength="150" value="<?php echo htmlspecialchars((string) $bankRow["account_name"], ENT_QUOTES, "UTF-8"); ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Bank Name</label>
                  <input type="text" name="bank_name" class="form-control" maxlength="150" value="<?php echo htmlspecialchars((string) $bankRow["bank_name"], ENT_QUOTES, "UTF-8"); ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Branch Name</label>
                  <input type="text" name="branch_name" class="form-control" maxlength="150" value="<?php echo htmlspecialchars((string) $bankRow["branch_name"], ENT_QUOTES, "UTF-8"); ?>" required>
                </div>
                <button type="submit" name="edi_save_bank_details" value="1" class="btn btn-success">Save</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php echo $adminHeader->printAdminFooter(); ?>
  </main>
</body>
</html>
