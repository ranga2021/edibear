<?php
/**
 * Worksheet admin list (shared by pdf.php, books.php, homework.php).
 * Expects: $user (USER), $ediWsTable, $ediWsEditUrl (e.g. ./add-pdf), $ediWsAjaxKey, $ediWsPrefix,
 *          $ediWsJsEdit (function name), $ediWsJsSts (function name)
 */
if (!isset($user) || !($user instanceof USER)) {
    return;
}
require_once __DIR__ . '/../../classes/edi_worksheet_admin_list.php';
$conn = $user->getConnection();
$ediWsQ = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$ediWsRows = EdiWorksheetAdminList::fetchRows($conn, $ediWsTable, $ediWsQ);
$ediWsJsCfg = array(
    'editUrl' => $ediWsEditUrl,
    'prefix' => $ediWsPrefix,
    'ajaxKey' => $ediWsAjaxKey,
    'fnEdit' => $ediWsJsEdit,
    'fnSts' => $ediWsJsSts,
);
?>
<style>
.edi-worksheets-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.25rem;
}
.edi-worksheets-title {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  margin: 0;
}
.edi-worksheets-search-form.edi-admin-search-inline .form-control {
  max-width: 24rem;
}
.edi-ws-action-cell {
  white-space: nowrap;
}
.edi-ws-action-cell .form-check.form-switch {
  display: inline-flex;
  vertical-align: middle;
  margin-bottom: 0;
  margin-right: 0.75rem;
}
.edi-ws-edit-link {
  font-size: 0.875rem;
  font-weight: 600;
}
</style>
<div class="card">
  <div class="card-body p-4">
    <div class="edi-worksheets-toolbar">
      <h2 class="edi-worksheets-title text-uppercase text-danger">Worksheets</h2>
      <form class="edi-worksheets-search-form edi-admin-search-inline" method="get" action="">
        <input type="search" name="q" class="form-control" placeholder="Document Title" value="<?php echo htmlspecialchars($ediWsQ, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
        <button type="submit" class="btn btn-success mb-0">Search</button>
      </form>
    </div>
    <div class="table-responsive p-0">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Language</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Grade</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sub Category</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tag</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Document Title</th>
            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (count($ediWsRows) === 0) {
              echo '<tr><td colspan="6" class="text-center text-secondary text-sm py-4">No worksheets found.</td></tr>';
          } else {
              foreach ($ediWsRows as $ediR) {
                  $rid = (int) $ediR['id'];
                  $lang = htmlspecialchars((string) $ediR['lang_title'], ENT_QUOTES, 'UTF-8');
                  $grade = htmlspecialchars((string) $ediR['grade_title'], ENT_QUOTES, 'UTF-8');
                  $sub = htmlspecialchars((string) $ediR['subcat_title'], ENT_QUOTES, 'UTF-8');
                  $tag = htmlspecialchars((string) $ediR['tag'], ENT_QUOTES, 'UTF-8');
                  $title = htmlspecialchars((string) $ediR['title'], ENT_QUOTES, 'UTF-8');
                  $chk = ((string) $ediR['status'] === '1') ? ' checked' : '';
                  $pfx = htmlspecialchars($ediWsPrefix, ENT_QUOTES, 'UTF-8');
                  echo '<tr>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm">' . ($lang !== '' ? $lang : '—') . '</span></td>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm">' . ($grade !== '' ? $grade : '—') . '</span></td>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm">' . ($sub !== '' ? $sub : '—') . '</span></td>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm">' . $tag . '</span></td>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm font-weight-bold">' . $title . '</span></td>';
                  echo '<td class="align-middle text-center edi-ws-action-cell">';
                  echo '<div class="form-check form-switch justify-content-center d-inline-flex">';
                  echo '<input class="form-check-input" type="checkbox" name="' . $pfx . 'Status' . $rid . '" value="1"' . $chk . ' onchange="' . htmlspecialchars($ediWsJsSts, ENT_QUOTES, 'UTF-8') . '(' . $rid . ')">';
                  echo '</div>';
                  echo '<a href="#" class="edi-ws-edit-link text-success" onclick="' . htmlspecialchars($ediWsJsEdit, ENT_QUOTES, 'UTF-8') . '(' . $rid . ');return false;">Edit</a>';
                  echo '</td>';
                  echo '</tr>';
              }
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
(function (cfg) {
  window[cfg.fnEdit] = function (id) {
    var base = cfg.editUrl.replace(/\?.*$/, '');
    location.href = base + '?id=' + encodeURIComponent(id);
  };
  window[cfg.fnSts] = function (id) {
    var arr = {};
    arr[cfg.prefix + 'ID'] = id;
    arr[cfg.prefix + 'Status'] = ($("input[name='" + cfg.prefix + "Status" + id + "']").is(':checked')) ? 1 : 0;
    var payload = {};
    payload[cfg.ajaxKey] = arr;
    $.ajax({
      type: 'POST',
      url: 'ajax.php',
      data: payload,
      success: function (html) {
        $('#ediWsAjaxOut').html(html).show();
      }
    });
  };
})(<?php echo json_encode($ediWsJsCfg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>);
</script>
<div id="ediWsAjaxOut"></div>
