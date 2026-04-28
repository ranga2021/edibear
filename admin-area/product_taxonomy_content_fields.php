<?php
if (empty($ediHasPcat)) {
    return;
}
$ediCurPcat = isset($ediCurPcat) ? (int) $ediCurPcat : 0;
$ediCurPsub = isset($ediCurPsub) ? (int) $ediCurPsub : 0;
?>
<div class="row mt-2">
  <div class="col-md-6">
    <div class="form-group">
      <label class="form-control-label" for="edi_content_product_category">Product category (Honey Market / shop only)</label>
      <select class="form-control" name="edi_content_product_category" id="edi_content_product_category">
        <option value="0">— None —</option>
        <?php foreach ($ediProductCategories as $pc) : ?>
        <option value="<?php echo (int) $pc['id']; ?>"<?php if ((int) $pc['id'] === $ediCurPcat) {
            echo ' selected';
        } ?>><?php echo htmlspecialchars((string) $pc['name'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label class="form-control-label" for="edi_content_product_subcategory">Product subcategory (optional)</label>
      <select class="form-control" name="edi_content_product_subcategory" id="edi_content_product_subcategory">
        <option value="0">— None —</option>
        <?php foreach ($ediProductSubcategories as $s) : ?>
        <option value="<?php echo (int) $s['id']; ?>" data-product-category-id="<?php echo (int) $s['product_category_id']; ?>"<?php if ((int) $s['id'] === $ediCurPsub) {
            echo ' selected';
        } ?>><?php echo htmlspecialchars((string) $s['title'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</div>
<script>
(function () {
  var c = document.getElementById("edi_content_product_category");
  var s = document.getElementById("edi_content_product_subcategory");
  if (!c || !s) return;
  function sync() {
    var cid = c.value;
    for (var i = 0; i < s.options.length; i++) {
      var o = s.options[i];
      if (!o.value) { o.hidden = false; o.disabled = false; continue; }
      var pc = o.getAttribute("data-product-category-id");
      var show = !cid || cid === "0" || !pc || String(pc) === String(cid);
      o.hidden = !show;
      o.disabled = !show;
    }
  }
  c.addEventListener("change", function () { s.value = "0"; sync(); });
  sync();
})();
</script>
