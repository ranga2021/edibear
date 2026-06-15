<?php
/**
 * Shared worksheet metadata layout (add-pdf / add-books / add-homework).
 *
 * When worksheet taxonomy tables exist (ws_categories / ws_subcategories), only those
 * categories appear — shop product Category / Sub Category are hidden (preserved via
 * hidden inputs on edit when product columns exist).
 *
 * Expects: $ediLanguages, $ediGrades, $ediCurLanguageId, $ediCurGradeId,
 * $ediHasPcat, $ediProductCategories, $ediProductSubcategories, $ediCurPcat, $ediCurPsub,
 * Optional: $ediHasWsTaxonomy, $ediWsCategories, $ediWsSubcategories, $ediCurWsCat, $ediCurWsSub
 * $ediWsTagName, $ediWsTitleName, $ediWsTagValue, $ediWsTitleValue
 */
$ediCurLanguageId = isset($ediCurLanguageId) ? (int) $ediCurLanguageId : 0;
$ediCurGradeId = isset($ediCurGradeId) ? (int) $ediCurGradeId : 0;
$ediCurPcat = isset($ediCurPcat) ? (int) $ediCurPcat : 0;
$ediCurPsub = isset($ediCurPsub) ? (int) $ediCurPsub : 0;
$ediHasWsTaxonomy = !empty($ediHasWsTaxonomy);
$ediHasPcat = !empty($ediHasPcat);
$ediCurWsCat = isset($ediCurWsCat) ? (int) $ediCurWsCat : 0;
$ediCurWsSub = isset($ediCurWsSub) ? (int) $ediCurWsSub : 0;
$ediWsCategories = isset($ediWsCategories) && is_array($ediWsCategories) ? $ediWsCategories : array();
$ediWsSubcategories = isset($ediWsSubcategories) && is_array($ediWsSubcategories) ? $ediWsSubcategories : array();
if (empty($ediLanguages) || !is_array($ediLanguages)) {
    $ediLanguages = array();
}
if (empty($ediGrades) || !is_array($ediGrades)) {
    $ediGrades = array();
}
$ediWsTagValue = isset($ediWsTagValue) ? htmlspecialchars((string) $ediWsTagValue, ENT_QUOTES, 'UTF-8') : '';
$ediWsTitleValue = isset($ediWsTitleValue) ? htmlspecialchars((string) $ediWsTitleValue, ENT_QUOTES, 'UTF-8') : '';
$ediWsTagName = isset($ediWsTagName) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $ediWsTagName) : 'inputpdfTag';
$ediWsTitleName = isset($ediWsTitleName) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $ediWsTitleName) : 'inputpdfTitle';

$ediShowShopProductCategory = $ediHasPcat && !$ediHasWsTaxonomy;
?>
<?php if ($ediHasWsTaxonomy && $ediHasPcat) : ?>
<input type="hidden" name="edi_content_product_category" value="<?php echo (int) $ediCurPcat; ?>">
<input type="hidden" name="edi_content_product_subcategory" value="<?php echo (int) $ediCurPsub; ?>">
<?php endif; ?>
<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-control-label" for="content_language_id">Language</label>
      <select class="form-control" name="content_language_id" id="content_language_id" required>
        <option value="">Select language</option>
        <?php foreach ($ediLanguages as $lng) : ?>
        <option value="<?php echo (int) $lng['id']; ?>"<?php if ((int) $lng['id'] === $ediCurLanguageId) {
            echo ' selected';
        } ?>>
          <?php echo htmlspecialchars((string) $lng['title'], ENT_QUOTES, 'UTF-8'); ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-control-label" for="content_grade_id">Grade</label>
      <div class="d-flex align-items-start" style="gap:6px">
        <select class="form-control" name="content_grade_id" id="content_grade_id" required style="flex:1">
          <option value="">Select grade</option>
          <?php foreach ($ediGrades as $gr) : ?>
          <option value="<?php echo (int) $gr['id']; ?>"<?php if ((int) $gr['id'] === $ediCurGradeId) {
              echo ' selected';
          } ?>>
            <?php echo htmlspecialchars((string) $gr['title'], ENT_QUOTES, 'UTF-8'); ?>
          </option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="btn btn-sm btn-outline-primary" id="ediAddGradeBtn" title="Add new grade" style="white-space:nowrap;height:calc(1.5em + .75rem + 2px);line-height:1">+</button>
        <button type="button" class="btn btn-sm btn-outline-danger" id="ediDeleteGradeBtn" title="Delete selected grade" style="white-space:nowrap;height:calc(1.5em + .75rem + 2px);line-height:1">&minus;</button>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <?php if ($ediHasWsTaxonomy) : ?>
    <div class="form-group">
      <label class="form-control-label" for="edi_ws_category_id">Category</label>
      <select class="form-control" name="edi_ws_category_id" id="edi_ws_category_id">
        <option value="0">— Select —</option>
        <?php foreach ($ediWsCategories as $wcat) : ?>
        <option value="<?php echo (int) $wcat['id']; ?>"<?php if ((int) $wcat['id'] === $ediCurWsCat) {
            echo ' selected';
        } ?>><?php echo htmlspecialchars((string) $wcat['name'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
      </select>
      <small class="form-text text-muted">From <a href="./manage-ws-categories" target="_blank" rel="noopener">Worksheet categories</a>.</small>
    </div>
    <?php elseif ($ediHasPcat) : ?>
    <div class="form-group">
      <label class="form-control-label" for="edi_content_product_category">Category</label>
      <select class="form-control" name="edi_content_product_category" id="edi_content_product_category">
        <option value="0">— Select —</option>
        <?php foreach ($ediProductCategories as $pc) : ?>
        <option value="<?php echo (int) $pc['id']; ?>"<?php if ((int) $pc['id'] === $ediCurPcat) {
            echo ' selected';
        } ?>><?php echo htmlspecialchars((string) $pc['name'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php else : ?>
    <div class="form-group d-none d-md-block" aria-hidden="true">&nbsp;</div>
    <?php endif; ?>
  </div>
</div>
<div class="row mt-2">
  <div class="col-md-4">
    <?php if ($ediHasWsTaxonomy) : ?>
    <div class="form-group">
      <label class="form-control-label" for="edi_ws_subcategory_id">Subcategory</label>
      <select class="form-control" name="edi_ws_subcategory_id" id="edi_ws_subcategory_id">
        <option value="" disabled class="edi-ws-sub-need-cat"<?php echo ($ediCurWsCat < 1) ? ' selected' : ''; ?>>Select a category first</option>
        <option value="0"<?php echo ($ediCurWsCat >= 1 && (int) $ediCurWsSub === 0) ? ' selected' : ''; ?>>— None —</option>
        <?php foreach ($ediWsSubcategories as $ws) : ?>
        <option value="<?php echo (int) $ws['id']; ?>" data-ws-category-id="<?php echo (int) $ws['category_id']; ?>"<?php if ((int) $ws['id'] === $ediCurWsSub) {
            echo ' selected';
        } ?>><?php echo htmlspecialchars((string) $ws['name'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
      </select>
      <small class="form-text text-muted">From <a href="./manage-ws-subcategories" target="_blank" rel="noopener">Worksheet subcategories</a>.</small>
    </div>
    <?php elseif ($ediHasPcat) : ?>
    <div class="form-group">
      <label class="form-control-label" for="edi_content_product_subcategory">Sub Category</label>
      <select class="form-control" name="edi_content_product_subcategory" id="edi_content_product_subcategory">
        <option value="" disabled class="edi-admin-sub-need-cat"<?php echo ($ediCurPcat < 1) ? ' selected' : ''; ?>>Select a category first</option>
        <option value="0"<?php echo ($ediCurPcat >= 1 && (int) $ediCurPsub === 0) ? " selected" : ""; ?>>— None —</option>
        <?php foreach ($ediProductSubcategories as $s) : ?>
        <option value="<?php echo (int) $s['id']; ?>" data-product-category-id="<?php echo (int) $s['product_category_id']; ?>"<?php if ((int) $s['id'] === $ediCurPsub) {
            echo ' selected';
        } ?>><?php echo htmlspecialchars((string) $s['title'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php else : ?>
    <div class="form-group d-none d-md-block" aria-hidden="true">&nbsp;</div>
    <?php endif; ?>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-control-label" for="edi_ws_tag_field">Tag</label>
      <input class="form-control" type="text" name="<?php echo htmlspecialchars($ediWsTagName, ENT_QUOTES, 'UTF-8'); ?>" id="edi_ws_tag_field" value="<?php echo $ediWsTagValue; ?>" placeholder="e.g. Cat">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-control-label" for="edi_ws_doc_title_field">Document Title</label>
      <input class="form-control" type="text" name="<?php echo htmlspecialchars($ediWsTitleName, ENT_QUOTES, 'UTF-8'); ?>" id="edi_ws_doc_title_field" value="<?php echo $ediWsTitleValue; ?>" required>
    </div>
  </div>
</div>
<?php if ($ediHasWsTaxonomy) : ?>
<script>
(function () {
  var c = document.getElementById("edi_ws_category_id");
  var s = document.getElementById("edi_ws_subcategory_id");
  if (!c || !s) return;
  function sync() {
    var cid = String(c.value || "0");
    var needCat = s.querySelector("option.edi-ws-sub-need-cat");
    var realOpts = s.querySelectorAll("option[data-ws-category-id]");
    if (!cid || cid === "0") {
      if (needCat) {
        needCat.hidden = false;
        needCat.disabled = true;
        needCat.selected = true;
      }
      for (var i = 0; i < realOpts.length; i++) {
        realOpts[i].hidden = true;
        realOpts[i].disabled = true;
      }
      return;
    }
    if (needCat) {
      needCat.hidden = true;
      needCat.disabled = true;
      needCat.selected = false;
    }
    var current = String(s.value || "0");
    var stillOk = false;
    for (var j = 0; j < realOpts.length; j++) {
      var o = realOpts[j];
      var pc = o.getAttribute("data-ws-category-id");
      var show = String(pc) === String(cid);
      o.hidden = !show;
      o.disabled = !show;
      if (show && o.value === current) {
        stillOk = true;
      }
    }
    if (!stillOk) {
      s.value = "0";
    }
  }
  c.addEventListener("change", function () { s.value = "0"; sync(); });
  sync();
})();
</script>
<?php endif; ?>
<?php if ($ediShowShopProductCategory) : ?>
<script>
(function () {
  var c = document.getElementById("edi_content_product_category");
  var s = document.getElementById("edi_content_product_subcategory");
  if (!c || !s) return;
  function sync() {
    var cid = String(c.value || "0");
    var needCat = s.querySelector("option.edi-admin-sub-need-cat");
    var realOpts = s.querySelectorAll("option[data-product-category-id]");
    if (!cid || cid === "0") {
      if (needCat) {
        needCat.hidden = false;
        needCat.disabled = true;
        needCat.selected = true;
      }
      for (var i = 0; i < realOpts.length; i++) {
        realOpts[i].hidden = true;
        realOpts[i].disabled = true;
      }
      return;
    }
    if (needCat) {
      needCat.hidden = true;
      needCat.disabled = true;
      needCat.selected = false;
    }
    for (var j = 0; j < realOpts.length; j++) {
      var o = realOpts[j];
      var pc = o.getAttribute("data-product-category-id");
      var show = String(pc) === String(cid);
      o.hidden = !show;
      o.disabled = !show;
    }
  }
  c.addEventListener("change", function () { s.value = "0"; sync(); });
  sync();
})();
</script>
<?php endif; ?>

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

  /* ---------- ADD GRADE ---------- */
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
      .then(function (r) {
        if (!r.ok) throw new Error("Server error (" + r.status + ")");
        return r.text();
      })
      .then(function (text) {
        try { return JSON.parse(text); } catch (e) { throw new Error("Invalid response from server"); }
      })
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
      .catch(function (err) {
        errEl.textContent = err.message || "Network error. Please try again.";
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

  /* ---------- DELETE GRADE ---------- */
  var delBtn = document.getElementById("ediDeleteGradeBtn");
  if (delBtn) {
    delBtn.addEventListener("click", function (e) {
      e.preventDefault();
      var sel = document.getElementById("content_grade_id");
      var id = sel.value;
      if (!id) { alert("Please select a grade to delete."); return; }
      var name = sel.options[sel.selectedIndex].textContent.trim();
      if (!confirm('Delete grade "' + name + '"? This cannot be undone.')) return;
      delBtn.disabled = true;
      var fd = new FormData();
      fd.append("ediDeleteGrade", id);
      fetch("./ajax.php", { method: "POST", body: fd })
        .then(function (r) {
          if (!r.ok) throw new Error("Server error (" + r.status + ")");
          return r.text();
        })
        .then(function (text) {
          try { return JSON.parse(text); } catch (e) { throw new Error("Invalid response from server"); }
        })
        .then(function (data) {
          if (data.ok) {
            for (var i = 0; i < sel.options.length; i++) {
              if (sel.options[i].value == data.id) { sel.remove(i); break; }
            }
            sel.value = "";
          } else {
            alert(data.error || "Failed to delete grade.");
          }
        })
        .catch(function (err) { alert(err.message || "Network error. Please try again."); })
        .finally(function () { delBtn.disabled = false; });
    });
  }
})();
</script>
<?php endif; ?>
