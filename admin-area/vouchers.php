<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/edi_voucher.php");

$adminHeader = new HEADER("vouchers");
$user = new USER();
$pdo = $user->getConnection();

$msg = "";
$tableOk = EdiVoucher::tableReady($pdo);

// --- Add voucher ---
if ($tableOk && isset($_POST["edi_add_voucher"])) {
    $code         = strtoupper(trim($_POST["voucher_code"] ?? ""));
    $description  = trim($_POST["description"] ?? "");
    $discountType = ($_POST["discount_type"] ?? "percentage") === "fixed" ? "fixed" : "percentage";
    $discountVal  = max(0, (float) ($_POST["discount_value"] ?? 0));
    $minOrder     = max(0, (float) ($_POST["min_order_total"] ?? 0));
    $maxUses      = max(0, (int) ($_POST["max_uses"] ?? 0));
    $status       = isset($_POST["status"]) ? 1 : 0;
    $startsAt     = trim($_POST["starts_at"] ?? "") ?: null;
    $expiresAt    = trim($_POST["expires_at"] ?? "") ?: null;

    if ($code === "") {
        $msg = "<div class='alert alert-warning'>Voucher code is required.</div>";
    } elseif ($discountVal <= 0) {
        $msg = "<div class='alert alert-warning'>Discount value must be greater than 0.</div>";
    } elseif ($discountType === "percentage" && $discountVal > 100) {
        $msg = "<div class='alert alert-warning'>Percentage discount cannot exceed 100%.</div>";
    } else {
        try {
            $st = $pdo->prepare(
                "INSERT INTO edi_vouchers (code, description, discount_type, discount_value, min_order_total, max_uses, status, starts_at, expires_at)
                 VALUES (:code, :desc, :dtype, :dval, :minord, :maxu, :st, :sa, :ea)"
            );
            $st->execute(array(
                ":code"   => $code,
                ":desc"   => $description,
                ":dtype"  => $discountType,
                ":dval"   => $discountVal,
                ":minord" => $minOrder,
                ":maxu"   => $maxUses,
                ":st"     => $status,
                ":sa"     => $startsAt,
                ":ea"     => $expiresAt,
            ));
            $msg = "<div class='alert alert-success'>Voucher <strong>" . htmlspecialchars($code, ENT_QUOTES, "UTF-8") . "</strong> created.</div>";
        } catch (Throwable $e) {
            if (strpos($e->getMessage(), "Duplicate") !== false) {
                $msg = "<div class='alert alert-danger'>A voucher with that code already exists.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>Could not add voucher.</div>";
            }
        }
    }
}

// --- Update voucher ---
if ($tableOk && isset($_POST["edi_update_voucher"], $_POST["voucher_id"])) {
    $vid          = (int) $_POST["voucher_id"];
    $code         = strtoupper(trim($_POST["voucher_code"] ?? ""));
    $description  = trim($_POST["description"] ?? "");
    $discountType = ($_POST["discount_type"] ?? "percentage") === "fixed" ? "fixed" : "percentage";
    $discountVal  = max(0, (float) ($_POST["discount_value"] ?? 0));
    $minOrder     = max(0, (float) ($_POST["min_order_total"] ?? 0));
    $maxUses      = max(0, (int) ($_POST["max_uses"] ?? 0));
    $status       = isset($_POST["status"]) ? 1 : 0;
    $startsAt     = trim($_POST["starts_at"] ?? "") ?: null;
    $expiresAt    = trim($_POST["expires_at"] ?? "") ?: null;

    if ($vid < 1 || $code === "") {
        $msg = "<div class='alert alert-warning'>Voucher code is required.</div>";
    } elseif ($discountVal <= 0) {
        $msg = "<div class='alert alert-warning'>Discount value must be greater than 0.</div>";
    } elseif ($discountType === "percentage" && $discountVal > 100) {
        $msg = "<div class='alert alert-warning'>Percentage discount cannot exceed 100%.</div>";
    } else {
        try {
            $st = $pdo->prepare(
                "UPDATE edi_vouchers SET code=:code, description=:desc, discount_type=:dtype, discount_value=:dval,
                 min_order_total=:minord, max_uses=:maxu, status=:st, starts_at=:sa, expires_at=:ea
                 WHERE id=:id"
            );
            $st->execute(array(
                ":code"   => $code,
                ":desc"   => $description,
                ":dtype"  => $discountType,
                ":dval"   => $discountVal,
                ":minord" => $minOrder,
                ":maxu"   => $maxUses,
                ":st"     => $status,
                ":sa"     => $startsAt,
                ":ea"     => $expiresAt,
                ":id"     => $vid,
            ));
            $msg = "<div class='alert alert-success'>Voucher updated.</div>";
        } catch (Throwable $e) {
            if (strpos($e->getMessage(), "Duplicate") !== false) {
                $msg = "<div class='alert alert-danger'>Another voucher with that code already exists.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>Could not update voucher.</div>";
            }
        }
    }
}

// --- Toggle status ---
if ($tableOk && isset($_POST["edi_toggle_voucher"], $_POST["voucher_id"])) {
    $vid = (int) $_POST["voucher_id"];
    $newStatus = (int) $_POST["new_status"];
    if ($vid > 0) {
        try {
            $st = $pdo->prepare("UPDATE edi_vouchers SET status = ? WHERE id = ?");
            $st->execute(array($newStatus, $vid));
            $label = $newStatus ? "activated" : "deactivated";
            $msg = "<div class='alert alert-success'>Voucher {$label}.</div>";
        } catch (Throwable $e) {
            $msg = "<div class='alert alert-danger'>Could not update voucher status.</div>";
        }
    }
}

// --- Delete voucher ---
if ($tableOk && isset($_POST["edi_delete_voucher"], $_POST["voucher_id"])) {
    $vid = (int) $_POST["voucher_id"];
    if ($vid > 0) {
        try {
            $st = $pdo->prepare("DELETE FROM edi_vouchers WHERE id = ?");
            $st->execute(array($vid));
            $msg = "<div class='alert alert-success'>Voucher deleted.</div>";
        } catch (Throwable $e) {
            $msg = "<div class='alert alert-danger'>Could not delete voucher.</div>";
        }
    }
}

$vouchers = $tableOk ? EdiVoucher::fetchAll($pdo) : array();
$editVoucher = null;
if (isset($_GET["edit"])) {
    $editVoucher = $tableOk ? EdiVoucher::findById($pdo, (int) $_GET["edit"]) : null;
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
  <style>
    .voucher-status-active   { color: #2dce89; font-weight: 600; }
    .voucher-status-inactive { color: #f5365c; font-weight: 600; }
    .voucher-table td, .voucher-table th { vertical-align: middle; font-size: 0.85rem; }
  </style>
</head>
<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2("Vouchers"); ?>

    <div class="container-fluid py-4">
      <?php echo $msg; ?>

      <?php if (!$tableOk): ?>
        <div class="alert alert-warning">
          Voucher table is not installed yet. Run <code>sql/migration_vouchers.sql</code> on your database.
        </div>
      <?php else: ?>

        <!-- Add / Edit Voucher Form -->
        <div class="row">
          <div class="col-12 mb-4">
            <div class="card">
              <div class="card-header pb-0">
                <h6><?php echo $editVoucher ? 'Edit Voucher' : 'Add New Voucher'; ?></h6>
              </div>
              <div class="card-body">
                <form method="post">
                  <?php if ($editVoucher): ?>
                    <input type="hidden" name="voucher_id" value="<?php echo (int) $editVoucher['id']; ?>">
                  <?php endif; ?>
                  <div class="row">
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Voucher Code</label>
                      <input type="text" name="voucher_code" class="form-control" required maxlength="50"
                             placeholder="e.g. SAVE20" style="text-transform:uppercase"
                             value="<?php echo $editVoucher ? htmlspecialchars($editVoucher['code'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Description</label>
                      <input type="text" name="description" class="form-control" maxlength="255"
                             placeholder="e.g. 20% off for new customers"
                             value="<?php echo $editVoucher ? htmlspecialchars($editVoucher['description'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Discount Type</label>
                      <select name="discount_type" class="form-control">
                        <option value="percentage" <?php echo ($editVoucher && $editVoucher['discount_type'] === 'percentage') || !$editVoucher ? 'selected' : ''; ?>>Percentage (%)</option>
                        <option value="fixed" <?php echo ($editVoucher && $editVoucher['discount_type'] === 'fixed') ? 'selected' : ''; ?>>Fixed Amount (Rs.)</option>
                      </select>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Discount Value</label>
                      <input type="number" step="0.01" min="0.01" name="discount_value" class="form-control" required
                             placeholder="e.g. 20"
                             value="<?php echo $editVoucher ? htmlspecialchars($editVoucher['discount_value'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Minimum Order Total (Rs.)</label>
                      <input type="number" step="0.01" min="0" name="min_order_total" class="form-control"
                             placeholder="0 = no minimum"
                             value="<?php echo $editVoucher ? htmlspecialchars($editVoucher['min_order_total'], ENT_QUOTES, 'UTF-8') : '0'; ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Max Uses</label>
                      <input type="number" min="0" name="max_uses" class="form-control"
                             placeholder="0 = unlimited"
                             value="<?php echo $editVoucher ? (int) $editVoucher['max_uses'] : '0'; ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Valid From</label>
                      <input type="date" name="starts_at" class="form-control"
                             value="<?php echo $editVoucher && $editVoucher['starts_at'] ? htmlspecialchars($editVoucher['starts_at'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label class="form-label">Expires At</label>
                      <input type="date" name="expires_at" class="form-control"
                             value="<?php echo $editVoucher && $editVoucher['expires_at'] ? htmlspecialchars($editVoucher['expires_at'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    </div>
                  </div>
                  <div class="row align-items-center">
                    <div class="col-md-3 mb-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" id="voucherStatusCheck"
                               <?php echo (!$editVoucher || (int) $editVoucher['status'] === 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="voucherStatusCheck">Active</label>
                      </div>
                    </div>
                    <div class="col-md-9 mb-3 text-right">
                      <?php if ($editVoucher): ?>
                        <a href="vouchers" class="btn btn-secondary btn-sm">Cancel</a>
                        <button type="submit" name="edi_update_voucher" class="btn btn-primary btn-sm">Update Voucher</button>
                      <?php else: ?>
                        <button type="submit" name="edi_add_voucher" class="btn btn-success btn-sm">Add Voucher</button>
                      <?php endif; ?>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Vouchers List -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header pb-0">
                <h6>All Vouchers</h6>
              </div>
              <div class="card-body px-0 pt-0 pb-2">
                <?php if (empty($vouchers)): ?>
                  <p class="text-muted text-sm px-4 py-3 mb-0">No vouchers yet. Add one above.</p>
                <?php else: ?>
                  <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0 voucher-table">
                      <thead>
                        <tr>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Code</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Discount</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Min Order</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Uses</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Valid Period</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($vouchers as $v): ?>
                          <?php
                          $vid = (int) $v['id'];
                          $isActive = (int) $v['status'] === 1;
                          $discLabel = $v['discount_type'] === 'percentage'
                              ? htmlspecialchars($v['discount_value'], ENT_QUOTES, 'UTF-8') . '%'
                              : 'Rs. ' . number_format((float) $v['discount_value'], 2);
                          $maxU = (int) $v['max_uses'];
                          $usedC = (int) $v['used_count'];
                          $usesLabel = $maxU > 0 ? ($usedC . ' / ' . $maxU) : ($usedC . ' / ∞');
                          $sa = $v['starts_at'] ? date('M d, Y', strtotime($v['starts_at'])) : '—';
                          $ea = $v['expires_at'] ? date('M d, Y', strtotime($v['expires_at'])) : '—';
                          ?>
                          <tr>
                            <td class="px-4"><strong><?php echo htmlspecialchars($v['code'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td class="px-2"><?php echo htmlspecialchars($v['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-2"><?php echo $discLabel; ?></td>
                            <td class="px-2"><?php echo (float) $v['min_order_total'] > 0 ? 'Rs. ' . number_format((float) $v['min_order_total'], 2) : '—'; ?></td>
                            <td class="px-2"><?php echo $usesLabel; ?></td>
                            <td class="px-2"><?php echo $sa . ' → ' . $ea; ?></td>
                            <td class="px-2">
                              <span class="<?php echo $isActive ? 'voucher-status-active' : 'voucher-status-inactive'; ?>">
                                <?php echo $isActive ? 'Active' : 'Inactive'; ?>
                              </span>
                            </td>
                            <td class="px-2">
                              <a href="vouchers?edit=<?php echo $vid; ?>" class="btn btn-sm btn-outline-primary mb-1">Edit</a>
                              <form method="post" class="d-inline">
                                <input type="hidden" name="voucher_id" value="<?php echo $vid; ?>">
                                <input type="hidden" name="new_status" value="<?php echo $isActive ? 0 : 1; ?>">
                                <button type="submit" name="edi_toggle_voucher" class="btn btn-sm btn-outline-<?php echo $isActive ? 'warning' : 'success'; ?> mb-1">
                                  <?php echo $isActive ? 'Deactivate' : 'Activate'; ?>
                                </button>
                              </form>
                              <form method="post" class="d-inline" onsubmit="return confirm('Delete this voucher?');">
                                <input type="hidden" name="voucher_id" value="<?php echo $vid; ?>">
                                <button type="submit" name="edi_delete_voucher" class="btn btn-sm btn-outline-danger mb-1">Delete</button>
                              </form>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <?php echo $adminHeader->printAdminFooter(); ?>
  </main>
</body>
</html>
