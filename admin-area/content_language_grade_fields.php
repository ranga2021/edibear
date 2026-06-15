<?php
$ediCurLanguageId = isset($ediCurLanguageId) ? (int) $ediCurLanguageId : 0;
$ediCurGradeId = isset($ediCurGradeId) ? (int) $ediCurGradeId : 0;
if (empty($ediLanguages) || !is_array($ediLanguages)) {
    $ediLanguages = array();
}
if (empty($ediGrades) || !is_array($ediGrades)) {
    $ediGrades = array();
}
?>
<div class="row mt-2">
  <div class="col-md-6">
    <div class="form-group">
      <label class="form-control-label" for="content_language_id">Language</label>
      <select class="form-control" name="content_language_id" id="content_language_id" required>
        <option value="">Select language</option>
        <?php foreach ($ediLanguages as $lng) : ?>
        <option value="<?php echo (int) $lng['id']; ?>"<?php if ((int) $lng['id'] === $ediCurLanguageId) { echo ' selected'; } ?>>
          <?php echo htmlspecialchars((string) $lng['title'], ENT_QUOTES, 'UTF-8'); ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label class="form-control-label" for="content_grade_id">Grade</label>
      <div class="d-flex align-items-start" style="gap:6px">
        <select class="form-control" name="content_grade_id" id="content_grade_id" required style="flex:1">
          <option value="">Select grade</option>
          <?php foreach ($ediGrades as $gr) : ?>
          <option value="<?php echo (int) $gr['id']; ?>"<?php if ((int) $gr['id'] === $ediCurGradeId) { echo ' selected'; } ?>>
            <?php echo htmlspecialchars((string) $gr['title'], ENT_QUOTES, 'UTF-8'); ?>
          </option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="btn btn-sm btn-outline-primary" id="ediAddGradeBtn" title="Add new grade" style="white-space:nowrap;height:calc(1.5em + .75rem + 2px);line-height:1">+</button>
      </div>
    </div>
  </div>
</div>

<?php if (!isset($ediAddGradeModalRendered)) : $ediAddGradeModalRendered = true; ?>
<!-- Add Grade Modal -->
<div class="modal fade" id="ediAddGradeModal" tabindex="-1" role="dialog" aria-labelledby="ediAddGradeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ediAddGradeModalLabel">Add New Grade</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group mb-0">
          <label for="ediNewGradeTitle">Grade Name</label>
          <input type="text" class="form-control" id="ediNewGradeTitle" placeholder="e.g. Grade 6">
          <div id="ediAddGradeError" class="text-danger mt-1" style="display:none"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-sm" id="ediAddGradeSave">Add Grade</button>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  var btn = document.getElementById("ediAddGradeBtn");
  if (!btn) return;
  btn.addEventListener("click", function (e) {
    e.preventDefault();
    var errEl = document.getElementById("ediAddGradeError");
    if (errEl) { errEl.style.display = "none"; errEl.textContent = ""; }
    document.getElementById("ediNewGradeTitle").value = "";
    $("#ediAddGradeModal").modal("show");
  });
  document.getElementById("ediAddGradeSave").addEventListener("click", function () {
    var title = (document.getElementById("ediNewGradeTitle").value || "").trim();
    var errEl = document.getElementById("ediAddGradeError");
    if (!title) {
      errEl.textContent = "Please enter a grade name.";
      errEl.style.display = "block";
      return;
    }
    var saveBtn = this;
    saveBtn.disabled = true;
    saveBtn.textContent = "Saving...";
    var fd = new FormData();
    fd.append("ediAddGrade", title);
    fetch("./ajax.php", { method: "POST", body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.ok) {
          var sel = document.getElementById("content_grade_id");
          var opt = document.createElement("option");
          opt.value = data.id;
          opt.textContent = data.title;
          sel.appendChild(opt);
          sel.value = data.id;
          $("#ediAddGradeModal").modal("hide");
        } else {
          errEl.textContent = data.error || "Failed to add grade.";
          errEl.style.display = "block";
        }
      })
      .catch(function () {
        errEl.textContent = "Network error. Please try again.";
        errEl.style.display = "block";
      })
      .finally(function () {
        saveBtn.disabled = false;
        saveBtn.textContent = "Add Grade";
      });
  });
  document.getElementById("ediNewGradeTitle").addEventListener("keydown", function (e) {
    if (e.key === "Enter") { e.preventDefault(); document.getElementById("ediAddGradeSave").click(); }
  });
})();
</script>
<?php endif; ?>
