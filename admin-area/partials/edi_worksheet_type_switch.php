<?php
/**
 * Dropdown to switch between add-pdf / add-books / add-homework.
 * Set $ediWorksheetAddCurrent to 'pdf' | 'books' | 'homework' before including.
 */
if (!isset($ediWorksheetAddCurrent)) {
    $ediWorksheetAddCurrent = "pdf";
}
$cur = $ediWorksheetAddCurrent;
?>
<div class="card mb-3 border">
  <div class="card-body py-3">
    <label for="edi-ws-type-select" class="form-label mb-1">Worksheet type</label>
    <select id="edi-ws-type-select" class="form-control form-control-sm" style="max-width: 22rem">
      <option value="./add-pdf.php"<?php echo $cur === "pdf" ? " selected" : ""; ?>>Coloring page (PDF)</option>
      <option value="./add-books.php"<?php echo $cur === "books" ? " selected" : ""; ?>>Book or paper</option>
      <option value="./add-homework.php"<?php echo $cur === "homework" ? " selected" : ""; ?>>Homework</option>
    </select>
    <small class="text-muted d-block mt-1">Changing type opens another form. Save or finish here first if you have unsaved work.</small>
  </div>
</div>
<script>
(function () {
  var s = document.getElementById("edi-ws-type-select");
  if (!s) return;
  s.addEventListener("change", function () {
    var u = this.value;
    if (u) {
      window.location.href = u;
    }
  });
})();
</script>
