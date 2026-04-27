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
      <select class="form-control" name="content_grade_id" id="content_grade_id" required>
        <option value="">Select grade</option>
        <?php foreach ($ediGrades as $gr) : ?>
        <option value="<?php echo (int) $gr['id']; ?>"<?php if ((int) $gr['id'] === $ediCurGradeId) { echo ' selected'; } ?>>
          <?php echo htmlspecialchars((string) $gr['title'], ENT_QUOTES, 'UTF-8'); ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</div>
