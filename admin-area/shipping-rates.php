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
                <div class="table-responsive">
                  <table class="table table-sm align-items-center mb-0">
                    <thead>
                      <tr>
                        <th>Max kg (empty = ∞)</th>
                        <th>Fee (LKR)</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($tiers as $t): ?>
                        <?php
                        $tid = (int) ($t["id"] ?? 0);
                        $mx = $t["max_weight_kg"] ?? null;
                        $mxDisp = ($mx === null || $mx === "") ? "—" : htmlspecialchars((string) $mx, ENT_QUOTES, "UTF-8");
                        ?>
                        <tr>
                          <td><?php echo $mxDisp; ?></td>
                          <td><?php echo number_format((float) ($t["fee_lkr"] ?? 0), 2); ?></td>
                          <td class="text-end">
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this tier?');">
                              <input type="hidden" name="tier_id" value="<?php echo $tid; ?>">
                              <button type="submit" name="edi_delete_weight_tier" class="btn btn-link text-danger btn-sm p-0">Delete</button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
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
                <div class="table-responsive">
                  <table class="table table-sm mb-0">
                    <thead>
                      <tr>
                        <th>District</th>
                        <th>Extra fee (LKR)</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($districts as $d): ?>
                        <?php $did = (int) ($d["id"] ?? 0); ?>
                        <tr>
                          <td><?php echo htmlspecialchars((string) ($d["name"] ?? ""), ENT_QUOTES, "UTF-8"); ?></td>
                          <td><?php echo number_format((float) ($d["fee_lkr"] ?? 0), 2); ?></td>
                          <td class="text-end">
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this district?');">
                              <input type="hidden" name="district_id" value="<?php echo $did; ?>">
                              <button type="submit" name="edi_delete_district" class="btn btn-link text-danger btn-sm p-0">Delete</button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
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
