<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/edi_shipping.php");

$adminHeader = new HEADER("shipping-rates");
$user = new USER();
$pdo = $user->getConnection();

$msg = "";
$tablesOk = EdiShipping::weightTiersTableReady($pdo) && EdiShipping::districtsTableReady($pdo);

if ($tablesOk && isset($_POST["edi_add_weight_tier"])) {
    $fee = isset($_POST["fee_lkr"]) ? (float) $_POST["fee_lkr"] : 0.0;
    $mx = trim((string) ($_POST["max_weight_kg"] ?? ""));
    $maxVal = $mx === "" ? null : max(0.0, (float) $mx);
    $sort = (int) ($_POST["sort_order"] ?? 0);
    if ($fee < 0) {
        $fee = 0.0;
    }
    try {
        $st = $pdo->prepare(
            "INSERT INTO edi_shipping_weight_tiers (max_weight_kg, fee_lkr, sort_order) VALUES (:mx, :fee, :so)"
        );
        $st->execute(
            array(
                ":mx" => $maxVal,
                ":fee" => $fee,
                ":so" => $sort,
            )
        );
        $msg = "<div class='alert alert-success'>Weight tier added.</div>";
    } catch (Throwable $e) {
        $msg = "<div class='alert alert-danger'>Could not add tier.</div>";
    }
}

if ($tablesOk && isset($_POST["edi_update_weight_tier"], $_POST["tier_id"])) {
    $tid = (int) $_POST["tier_id"];
    $fee = isset($_POST["fee_lkr"]) ? (float) $_POST["fee_lkr"] : 0.0;
    $mx = trim((string) ($_POST["max_weight_kg"] ?? ""));
    $maxVal = $mx === "" ? null : max(0.0, (float) $mx);
    $sort = (int) ($_POST["sort_order"] ?? 0);
    if ($fee < 0) {
        $fee = 0.0;
    }
    if ($tid > 0) {
        try {
            $st = $pdo->prepare(
                "UPDATE edi_shipping_weight_tiers SET max_weight_kg = :mx, fee_lkr = :fee, sort_order = :so WHERE id = :id"
            );
            $st->execute(
                array(
                    ":mx" => $maxVal,
                    ":fee" => $fee,
                    ":so" => $sort,
                    ":id" => $tid,
                )
            );
            $msg = "<div class='alert alert-success'>Weight tier updated.</div>";
        } catch (Throwable $e) {
            $msg = "<div class='alert alert-danger'>Could not update tier.</div>";
        }
    }
}

if ($tablesOk && isset($_POST["edi_delete_weight_tier"], $_POST["tier_id"])) {
    $tid = (int) $_POST["tier_id"];
    if ($tid > 0) {
        try {
            $st = $pdo->prepare("DELETE FROM edi_shipping_weight_tiers WHERE id = ?");
            $st->execute(array($tid));
            $msg = "<div class='alert alert-success'>Weight tier removed.</div>";
        } catch (Throwable $e) {
            $msg = "<div class='alert alert-danger'>Could not remove tier.</div>";
        }
    }
}

if ($tablesOk && isset($_POST["edi_add_district"])) {
    $name = trim((string) ($_POST["district_name"] ?? ""));
    $fee = isset($_POST["district_fee_lkr"]) ? (float) $_POST["district_fee_lkr"] : 0.0;
    if ($name === "") {
        $msg = "<div class='alert alert-warning'>District name is required.</div>";
    } else {
        try {
            $st = $pdo->prepare("INSERT INTO edi_shipping_districts (name, fee_lkr) VALUES (:n, :f)");
            $st->execute(array(":n" => $name, ":f" => max(0.0, $fee)));
            $msg = "<div class='alert alert-success'>District added.</div>";
        } catch (Throwable $e) {
            $msg = "<div class='alert alert-danger'>Could not add district (duplicate name?).</div>";
        }
    }
}

if ($tablesOk && isset($_POST["edi_update_district"], $_POST["district_id"])) {
    $did = (int) $_POST["district_id"];
    $name = trim((string) ($_POST["district_name_edit"] ?? ""));
    $fee = isset($_POST["district_fee_edit"]) ? (float) $_POST["district_fee_edit"] : 0.0;
    if ($did < 1 || $name === "") {
        $msg = "<div class='alert alert-warning'>District name is required.</div>";
    } else {
        if (function_exists("mb_substr")) {
            $name = mb_substr($name, 0, 128, "UTF-8");
        } else {
            $name = substr($name, 0, 128);
        }
        try {
            $st = $pdo->prepare("UPDATE edi_shipping_districts SET name = :n, fee_lkr = :f WHERE id = :id");
            $st->execute(array(":n" => $name, ":f" => max(0.0, $fee), ":id" => $did));
            $msg = "<div class='alert alert-success'>District updated.</div>";
        } catch (Throwable $e) {
            $msg = "<div class='alert alert-danger'>Could not update district (duplicate name?).</div>";
        }
    }
}

if ($tablesOk && isset($_POST["edi_delete_district"], $_POST["district_id"])) {
    $did = (int) $_POST["district_id"];
    if ($did > 0) {
        try {
            $st = $pdo->prepare("DELETE FROM edi_shipping_districts WHERE id = ?");
            $st->execute(array($did));
            $msg = "<div class='alert alert-success'>District removed.</div>";
        } catch (Throwable $e) {
            $msg = "<div class='alert alert-danger'>Could not remove district.</div>";
        }
    }
}

$tiers = $tablesOk ? EdiShipping::fetchWeightTiers($pdo) : array();
$districts = $tablesOk ? EdiShipping::fetchDistricts($pdo) : array();
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
    .edi-ship-row-editor {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      gap: 0.65rem;
      padding: 0.65rem 0;
      border-bottom: 1px solid #e9ecef;
    }
    .edi-ship-row-editor:last-child { border-bottom: 0; }
    .edi-ship-row-editor .form-label { font-size: 0.7rem; margin-bottom: 0.15rem; color: #64748b; }
    .edi-ship-row-editor .form-control-sm { min-width: 5.5rem; }
    .edi-ship-row-editor .edi-ship-actions { margin-left: auto; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; }
  </style>
</head>
<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg ">
    <?php echo $adminHeader->printAdminNav2("Shipping rates"); ?>

    <div class="container-fluid py-4">
      <?php echo $msg; ?>

      <?php if (!$tablesOk): ?>
        <div class="alert alert-warning">
          Shipping tables are not installed yet. Run <code>sql/migration_shipping_and_weight_kg.sql</code> on your database
          (and add column <code>weight_kg</code> to products). Until then, checkout uses the legacy flat weight fee.
        </div>
      <?php else: ?>
        <div class="row">
          <div class="col-lg-6 mb-4">
            <div class="card">
              <div class="card-header pb-0">
                <h6>Weight tiers</h6>
                <p class="text-sm text-muted mb-0">
                  Rows are read in <strong>sort order</strong>. The <strong>first</strong> tier where <em>cart total kg ≤ max kg</em>
                  wins. Put a final row with <em>empty max</em> to cover all heavier carts (unlimited ceiling).
                </p>
              </div>
              <div class="card-body">
                <div class="mb-2">
                  <div class="text-xs text-uppercase text-muted font-weight-bold px-0 pb-2 border-bottom">Current tiers — edit and save</div>
                  <?php foreach ($tiers as $t): ?>
                    <?php
                    $tid = (int) ($t["id"] ?? 0);
                    $mx = $t["max_weight_kg"] ?? null;
                    $mxVal = ($mx === null || $mx === "") ? "" : htmlspecialchars((string) (0 + (float) $mx), ENT_QUOTES, "UTF-8");
                    $feeVal = htmlspecialchars((string) ((float) ($t["fee_lkr"] ?? 0)), ENT_QUOTES, "UTF-8");
                    $sortVal = (int) ($t["sort_order"] ?? 0);
                    ?>
                    <div class="edi-ship-row-editor">
                      <form method="post" class="d-flex flex-wrap align-items-end gap-2 flex-grow-1">
                        <input type="hidden" name="tier_id" value="<?php echo $tid; ?>">
                        <div>
                          <label class="form-label d-block">Max kg (∞ empty)</label>
                          <input type="number" step="0.0001" min="0" name="max_weight_kg" class="form-control form-control-sm" style="width:7rem" value="<?php echo $mxVal; ?>" placeholder="∞">
                        </div>
                        <div>
                          <label class="form-label d-block">Fee LKR</label>
                          <input type="number" step="0.01" min="0" name="fee_lkr" class="form-control form-control-sm" style="width:7rem" value="<?php echo $feeVal; ?>" required>
                        </div>
                        <div>
                          <label class="form-label d-block">Sort</label>
                          <input type="number" name="sort_order" class="form-control form-control-sm" style="width:4.5rem" value="<?php echo $sortVal; ?>">
                        </div>
                        <button type="submit" name="edi_update_weight_tier" value="1" class="btn btn-sm btn-primary mb-0">Save</button>
                      </form>
                      <div class="edi-ship-actions">
                        <form method="post" class="d-inline m-0" onsubmit="return confirm('Delete this tier?');">
                          <input type="hidden" name="tier_id" value="<?php echo $tid; ?>">
                          <button type="submit" name="edi_delete_weight_tier" class="btn btn-link text-danger btn-sm p-0">Delete</button>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                  <?php if (empty($tiers)): ?>
                    <p class="text-muted text-sm mb-0 py-2">No weight tiers yet. Add one below.</p>
                  <?php endif; ?>
                </div>
                <hr>
                <form method="post" class="row g-2 align-items-end">
                  <div class="col-md-4">
                    <label class="form-label">Max cart kg (optional)</label>
                    <input type="number" step="0.0001" min="0" name="max_weight_kg" class="form-control" placeholder="e.g. 5">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Fee LKR</label>
                    <input type="number" step="0.01" min="0" name="fee_lkr" class="form-control" required>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Sort</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                  </div>
                  <div class="col-md-2">
                    <button type="submit" name="edi_add_weight_tier" class="btn btn-success w-100">Add</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-6 mb-4">
            <div class="card">
              <div class="card-header pb-0">
                <h6>District fees</h6>
                <p class="text-sm text-muted mb-0">
                  <strong>Added</strong> to the weight-tier fee. Names must match the checkout dropdown (case-insensitive).
                </p>
              </div>
              <div class="card-body">
                <div class="mb-2">
                  <div class="text-xs text-uppercase text-muted font-weight-bold px-0 pb-2 border-bottom">Current districts — edit and save</div>
                  <?php foreach ($districts as $d): ?>
                    <?php
                    $did = (int) ($d["id"] ?? 0);
                    $dname = htmlspecialchars((string) ($d["name"] ?? ""), ENT_QUOTES, "UTF-8");
                    $dfee = htmlspecialchars((string) ((float) ($d["fee_lkr"] ?? 0)), ENT_QUOTES, "UTF-8");
                    ?>
                    <div class="edi-ship-row-editor">
                      <form method="post" class="d-flex flex-wrap align-items-end gap-2 flex-grow-1">
                        <input type="hidden" name="district_id" value="<?php echo $did; ?>">
                        <div class="flex-grow-1" style="min-width: 10rem;">
                          <label class="form-label d-block">District name</label>
                          <input type="text" name="district_name_edit" class="form-control form-control-sm" maxlength="128" value="<?php echo $dname; ?>" required>
                        </div>
                        <div>
                          <label class="form-label d-block">Extra fee LKR</label>
                          <input type="number" step="0.01" min="0" name="district_fee_edit" class="form-control form-control-sm" style="width:7rem" value="<?php echo $dfee; ?>" required>
                        </div>
                        <button type="submit" name="edi_update_district" value="1" class="btn btn-sm btn-primary mb-0">Save</button>
                      </form>
                      <div class="edi-ship-actions">
                        <form method="post" class="d-inline m-0" onsubmit="return confirm('Delete this district?');">
                          <input type="hidden" name="district_id" value="<?php echo $did; ?>">
                          <button type="submit" name="edi_delete_district" class="btn btn-link text-danger btn-sm p-0">Delete</button>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                  <?php if (empty($districts)): ?>
                    <p class="text-muted text-sm mb-0 py-2">No districts yet. Add one below.</p>
                  <?php endif; ?>
                </div>
                <hr>
                <form method="post" class="row g-2 align-items-end">
                  <div class="col-md-6">
                    <label class="form-label">District name</label>
                    <input type="text" name="district_name" class="form-control" required maxlength="128" placeholder="e.g. Matara">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Extra fee LKR</label>
                    <input type="number" step="0.01" min="0" name="district_fee_lkr" class="form-control" value="0">
                  </div>
                  <div class="col-md-2">
                    <button type="submit" name="edi_add_district" class="btn btn-success w-100">Add</button>
                  </div>
                </form>
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
