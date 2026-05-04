<?php
/**
 * Unified worksheet list: pdf_details + books_details + homework_details on one page.
 * Expects: $user (USER)
 */
if (!isset($user) || !($user instanceof USER)) {
    return;
}
require_once __DIR__ . '/../../classes/edi_worksheet_admin_list.php';
$conn = $user->getConnection();
$ediWsQ = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$ediWsRows = EdiWorksheetAdminList::fetchMergedRows($conn, $ediWsQ);
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
.edi-worksheets-toolbar-tools {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem 1rem;
}
.edi-worksheets-search-form.edi-admin-search-inline .form-control {
  max-width: 24rem;
}
.edi-ws-unified-add-links {
  font-size: 0.8rem;
  color: #64748b;
}
.edi-ws-unified-add-links a {
  font-weight: 600;
  color: #33a675;
  margin-right: 0.65rem;
}
.edi-ws-unified-add-links a:hover {
  text-decoration: underline;
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
      <div class="edi-worksheets-toolbar-tools">
        <form class="edi-worksheets-search-form edi-admin-search-inline" method="get" action="">
          <input type="search" name="q" class="form-control" placeholder="Document Title" value="<?php echo htmlspecialchars($ediWsQ, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
          <button type="submit" class="btn btn-success mb-0">Search</button>
        </form>
        <div class="edi-ws-unified-add-links">
          <span>Add:</span>
          <a href="./add-pdf">Coloring page</a>
          <a href="./add-books">Book / paper</a>
          <a href="./add-homework">Homework</a>
        </div>
      </div>
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
                  $kind = (string) ($ediR['ws_kind'] ?? 'pdf');
                  if (!in_array($kind, array('pdf', 'books', 'homework'), true)) {
                      $kind = 'pdf';
                  }
                  $rid = (int) $ediR['id'];
                  $lang = htmlspecialchars((string) $ediR['lang_title'], ENT_QUOTES, 'UTF-8');
                  $grade = htmlspecialchars((string) $ediR['grade_title'], ENT_QUOTES, 'UTF-8');
                  $sub = htmlspecialchars((string) $ediR['subcat_title'], ENT_QUOTES, 'UTF-8');
                  $tag = htmlspecialchars((string) $ediR['tag'], ENT_QUOTES, 'UTF-8');
                  $title = htmlspecialchars((string) $ediR['title'], ENT_QUOTES, 'UTF-8');
                  $chk = ((string) $ediR['status'] === '1') ? ' checked' : '';
                  $pfx = ($kind === 'pdf') ? 'pdf' : (($kind === 'books') ? 'books' : 'homework');
                  echo '<tr>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm">' . ($lang !== '' ? $lang : '—') . '</span></td>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm">' . ($grade !== '' ? $grade : '—') . '</span></td>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm">' . ($sub !== '' ? $sub : '—') . '</span></td>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm">' . $tag . '</span></td>';
                  echo '<td class="align-middle"><span class="text-secondary text-sm font-weight-bold">' . $title . '</span></td>';
                  echo '<td class="align-middle text-center edi-ws-action-cell">';
                  echo '<div class="form-check form-switch justify-content-center d-inline-flex">';
                  echo '<input class="form-check-input" type="checkbox" data-ws-kind="' . htmlspecialchars($kind, ENT_QUOTES, 'UTF-8') . '" data-ws-id="' . $rid . '" name="ediWsStatus_' . htmlspecialchars($pfx, ENT_QUOTES, 'UTF-8') . '_' . $rid . '" value="1"' . $chk . ' onchange="ediWsUnifiedSts(this)">';
                  echo '</div>';
                  echo '<a href="#" class="edi-ws-edit-link text-success" onclick="ediWsUnifiedEdit(\'' . htmlspecialchars($kind, ENT_QUOTES, 'UTF-8') . '\',' . $rid . ');return false;">Edit</a>';
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
(function () {
  var cfg = {
    pdf: { editUrl: './add-pdf', prefix: 'pdf', ajaxKey: 'chngpdfSts' },
    books: { editUrl: './add-books', prefix: 'books', ajaxKey: 'chngbooksSts' },
    homework: { editUrl: './add-homework', prefix: 'homework', ajaxKey: 'chnghomeworkSts' }
  };
  window.ediWsUnifiedEdit = function (kind, id) {
    var c = cfg[kind];
    if (!c) return;
    location.href = c.editUrl + '?id=' + encodeURIComponent(id);
  };
  window.ediWsUnifiedSts = function (el) {
    var kind = el.getAttribute('data-ws-kind');
    var id = parseInt(el.getAttribute('data-ws-id'), 10);
    var c = cfg[kind];
    if (!c || !id) return;
    var arr = {};
    arr[c.prefix + 'ID'] = id;
    arr[c.prefix + 'Status'] = $(el).is(':checked') ? 1 : 0;
    var payload = {};
    payload[c.ajaxKey] = arr;
    $.ajax({
      type: 'POST',
      url: 'ajax.php',
      data: payload,
      success: function (html) {
        $('#ediWsAjaxOut').html(html).show();
      }
    });
  };
})();
</script>
<div id="ediWsAjaxOut"></div>
