<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SAFE SESSION
if (!is_dir('/tmp')) {
    mkdir('/tmp', 0777, true);
}
session_save_path('/tmp');
session_start();

require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/class.widgets.php");
require_once("../classes/edi_explorer_content.php");
require_once("../classes/edi_taxonomy.php");
require_once("../classes/edi_sitemap.php");

$adminHeader = new HEADER("add-worksheet");
$user = new USER();
$widgets = new WIDGETS();
$ediConn = $user->getConnection();
$ediHasPdfOriginalName = EdiExplorerContent::ensureNullableTextColumn($ediConn, "homework_details", "pdf_original_name");
$ediLanguages = EdiTaxonomy::loadLanguages($ediConn);
$ediGrades = EdiTaxonomy::loadGrades($ediConn);
$ediCurLanguageId = 0;
$ediCurGradeId = 0;
$ediHasPcat = EdiExplorerContent::columnExists($ediConn, "homework_details", "product_category_id");
$ediProductCategories = array();
$ediProductSubcategories = array();
$ediCurPcat = 0;
$ediCurPsub = 0;
if ($ediHasPcat) {
    try {
        $ediProductCategories = $user->fetchAll(array("id", "name"), array("product_categories"), array("status" => 1));
    } catch (Throwable $e) {
        $ediProductCategories = $user->fetchAll(array("id", "name"), array("product_categories"), array());
    }
    try {
        $psc = $ediConn->query("SELECT id, product_category_id, title FROM product_subcategories ORDER BY product_category_id ASC, title ASC");
        if ($psc) {
            $ediProductSubcategories = EdiExplorerContent::dedupeProductSubcategoryRows($psc->fetchAll(PDO::FETCH_ASSOC));
        }
    } catch (Throwable $e) {
        $ediProductSubcategories = array();
    }
}

$editMode = false;
$currenthomeworkTag = "";
$currenthomeworkTitle = "";
$currenthomeworkMainDescription = "";
$currenthomeworkMainImage = "";
$currenthomeworkpdfupload = "";
$currenthomeworkID = 0;

// ================= EDIT MODE =================
if (isset($_GET['id']) && $_GET['id'] > 0) {

    $currenthomeworkID = (int)$_GET['id'];

    if ($user->CountRows("homework_details", ["id"=>$currenthomeworkID])) {

        $editMode = true;

        $data = $user->fetchAll(
            ["tag","title","image","description","pdfupload","language_id","grade_id"],
            ["homework_details"],
            ["id"=>$currenthomeworkID]
        )[0];
        $ediCurLanguageId = (int)($data['language_id'] ?? 0);
        $ediCurGradeId = (int)($data['grade_id'] ?? 0);

        $currenthomeworkTag = $data['tag'];
        $currenthomeworkTitle = $data['title'];
        $currenthomeworkMainDescription = $data['description'];

        if (!empty($data['image'])) {
            $currenthomeworkMainImage = "src='".$widgets->createCachelessImage("../img/homework/".$data['image'])."'";
        }

        if (!empty($data['pdfupload'])) {
            $currenthomeworkpdfupload = "src='".$widgets->createCachelessImage("../img/homework/".$data['pdfupload'])."'";
        }

        if ($ediHasPcat) {
            $r = $ediConn->query("SELECT `product_category_id`, `product_subcategory_id` FROM `homework_details` WHERE `id` = " . (int) $currenthomeworkID);
            if ($r) {
                $ediRow = $r->fetch(PDO::FETCH_ASSOC);
                if ($ediRow) {
                    $ediCurPcat = (int)($ediRow["product_category_id"] ?? 0);
                    $ediCurPsub = (int)($ediRow["product_subcategory_id"] ?? 0);
                }
            }
        }

    } else {
        header("Location: ./add-homework");
        exit;
    }
}

// ================= ADD =================
if (isset($_POST['addNewhomeworkSubmit'])) {

    $tag   = htmlspecialchars($_POST['inputhomeworkTag'] ?? "");
    $title = htmlspecialchars($_POST['inputhomeworkTitle'] ?? "");
    $desc  = strip_tags($_POST['inputhomeworkMainDescription'] ?? "", "<br>");

    $epc = isset($_POST['edi_content_product_category']) ? (int) $_POST['edi_content_product_category'] : 0;
    $eps = isset($_POST['edi_content_product_subcategory']) ? (int) $_POST['edi_content_product_subcategory'] : 0;
    if ($epc > 0 && $eps > 0) {
        $chk = $ediConn->prepare("SELECT `id` FROM `product_subcategories` WHERE `id` = ? AND `product_category_id` = ?");
        $chk->execute(array($eps, $epc));
        if ($chk->fetchColumn() === false) {
            $eps = 0;
        }
    } elseif ($eps > 0 && $epc <= 0) {
        $eps = 0;
    }

    $lg = EdiTaxonomy::contentLanguageGradeFromPost($ediLanguages, $ediGrades);
    if ($lg['language_id'] === null || $lg['grade_id'] === null) {
        echo "<script>alert('Please select a valid language and grade.');history.back();</script>";
        exit;
    }

    $mappedH = array("main_cat_id" => null, "sub_cat_id" => null);
    if ($ediHasPcat) {
        $mappedH = EdiExplorerContent::mapProductSelectionsToContentCategoryIds($ediConn, $epc, $eps);
    }
    $insH = array(
    "tag"=>$tag,
    "title"=>$title,
    "description"=>$desc,
    "image"=>"",          // required
    "video"=>"",          // ✅ FIX
    "video_status"=>0,
    "pdfupload"=>"",
    "download_count"=>0,
    "language_id"=>$lg['language_id'],
    "grade_id"=>$lg['grade_id'],
    "main_cat_id"=>$mappedH["main_cat_id"],
    "sub_cat_id"=>$mappedH["sub_cat_id"]
    );
    if ($ediHasPcat) {
        $insH["product_category_id"] = $epc > 0 ? $epc : null;
        $insH["product_subcategory_id"] = $eps > 0 ? $eps : null;
    }
    $homeworkID = $user->insertTable("homework_details", $insH, true);

    $uploadDir = "../img/homework/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

    // IMAGE
    if (!empty($_FILES["inputhomeworkMainImage"]["name"])) {
        $ext = pathinfo($_FILES["inputhomeworkMainImage"]["name"], PATHINFO_EXTENSION);
        $file = $homeworkID.".".$ext;

        move_uploaded_file($_FILES["inputhomeworkMainImage"]["tmp_name"], $uploadDir.$file);

        $user->updateTable("homework_details", ["image"=>$file], ["id"=>$homeworkID]);
    }

    // PDF
    if (!empty($_FILES["inputhomeworkpdfupload"]["name"])) {
        $file = $homeworkID.".pdf"; // ✅ FIXED SHORT NAME
        $pdfOriginalName = trim((string) $_FILES["inputhomeworkpdfupload"]["name"]);

        move_uploaded_file($_FILES["inputhomeworkpdfupload"]["tmp_name"], $uploadDir.$file);
        $upPdf = ["pdfupload" => $file];
        if ($ediHasPdfOriginalName) {
            $upPdf["pdf_original_name"] = $pdfOriginalName;
        }
        $user->updateTable("homework_details", $upPdf, ["id"=>$homeworkID]);
    }

    edi_regenerate_public_sitemap($user);
    edi_admin_flash_success('Homework added successfully.');
    $user->redirect('./homework');
    exit;
}

// ================= UPDATE =================
if (isset($_POST['updatehomeworkSubmit'])) {

    $tag   = htmlspecialchars($_POST['inputhomeworkTag'] ?? "");
    $title = htmlspecialchars($_POST['inputhomeworkTitle'] ?? "");
    $desc  = strip_tags($_POST['inputhomeworkMainDescription'] ?? "", "<br>");

    $lg = EdiTaxonomy::contentLanguageGradeFromPost($ediLanguages, $ediGrades);
    if ($lg['language_id'] === null || $lg['grade_id'] === null) {
        echo "<script>alert('Please select a valid language and grade.');history.back();</script>";
        exit;
    }
    $upH = [
        "tag"=>$tag,
        "title"=>$title,
        "description"=>$desc,
        "language_id"=>$lg['language_id'],
        "grade_id"=>$lg['grade_id']
    ];
    if ($ediHasPcat) {
        $epc = isset($_POST['edi_content_product_category']) ? (int) $_POST['edi_content_product_category'] : 0;
        $eps = isset($_POST['edi_content_product_subcategory']) ? (int) $_POST['edi_content_product_subcategory'] : 0;
        if ($epc > 0 && $eps > 0) {
            $chk = $ediConn->prepare("SELECT `id` FROM `product_subcategories` WHERE `id` = ? AND `product_category_id` = ?");
            $chk->execute(array($eps, $epc));
            if ($chk->fetchColumn() === false) {
                $eps = 0;
            }
        } elseif ($eps > 0 && $epc <= 0) {
            $eps = 0;
        }
        $upH["product_category_id"] = $epc > 0 ? $epc : null;
        $upH["product_subcategory_id"] = $eps > 0 ? $eps : null;
        $mappedH = EdiExplorerContent::mapProductSelectionsToContentCategoryIds($ediConn, $epc, $eps);
        $upH["main_cat_id"] = $mappedH["main_cat_id"];
        $upH["sub_cat_id"] = $mappedH["sub_cat_id"];
    }
    $user->updateTable("homework_details", $upH, ["id"=>$currenthomeworkID]);

    $uploadDir = "../img/homework/";

    // IMAGE UPDATE
    if (!empty($_FILES["inputhomeworkMainImage"]["name"])) {
        $ext = pathinfo($_FILES["inputhomeworkMainImage"]["name"], PATHINFO_EXTENSION);
        $file = $currenthomeworkID.".".$ext;

        move_uploaded_file($_FILES["inputhomeworkMainImage"]["tmp_name"], $uploadDir.$file);

        $user->updateTable("homework_details", ["image"=>$file], ["id"=>$currenthomeworkID]);
    }

    // PDF UPDATE
    if (!empty($_FILES["inputhomeworkpdfupload"]["name"])) {
        $file = $currenthomeworkID.".pdf";
        $pdfOriginalName = trim((string) $_FILES["inputhomeworkpdfupload"]["name"]);

        move_uploaded_file($_FILES["inputhomeworkpdfupload"]["tmp_name"], $uploadDir.$file);
        $upPdf = ["pdfupload" => $file];
        if ($ediHasPdfOriginalName) {
            $upPdf["pdf_original_name"] = $pdfOriginalName;
        }
        $user->updateTable("homework_details", $upPdf, ["id"=>$currenthomeworkID]);
    }

    edi_regenerate_public_sitemap($user);
    edi_admin_flash_success('Updated successfully.');
    $user->redirect('./homework');
    exit;
}

// ================= DELETE =================
if (isset($_POST['confirmDeletehomeworkSubmit'])) {

    $id = (int) ($_POST['deletehomeworkID'] ?? 0);
    if ($id > 0 && $user->CountRows("homework_details", array("id" => $id))) {
        $dr = $user->fetchAll(array("image", "pdfupload"), array("homework_details"), array("id" => $id));
        if (!empty($dr[0])) {
            $img = (string) ($dr[0]['image'] ?? '');
            $pdfF = (string) ($dr[0]['pdfupload'] ?? '');
            $uploadDir = "../img/homework/";
            if ($img !== '' && is_file($uploadDir . $img)) {
                @unlink($uploadDir . $img);
            }
            if ($pdfF !== '' && is_file($uploadDir . $pdfF)) {
                @unlink($uploadDir . $pdfF);
            }
        }
        $user->deleteTableRow("homework_details", array("id" => $id));
    }

    echo "<script>alert('Deleted successfully');location.href='./homework'</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
  <meta property='og:title' content='Kids Coloring Pages, Activity Books & Study Packs'/>
  <meta name='description' content='“edibear” is a website that provides a variety of kids coloring pages, activity books, relevant model papers, school related study materials, fun activities for developing the abilities of kids. '/>
<meta name='keywords' content='printable coloring pages for kids, free coloring pages, kids activities, Relevant past papers, model Papers, school related study materials, Fun activities for kids, Developing kids&#8217; abilities, Educational resources for kids, Downloadable kids&#8217; materials, Creative learning for kids, Sinhala Coloring Pages, Tamil Coloring Pages' />
</head>

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  <main class="main-content position-relative border-radius-lg">
    <!-- Navbar -->
    <?php echo $adminHeader->printAdminNav2(($editMode) ? "Edit worksheet" : $adminHeader->getActivePageName()); ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
<?php if (!$editMode) { $ediWorksheetAddCurrent = "homework"; require __DIR__ . "/partials/edi_worksheet_type_switch.php"; } ?>
      <div class="card p-4">
              <form method="post" enctype="multipart/form-data" class="edi-add-worksheet-form">
                <?php if ($editMode) { ?>
                <h2 class="text-uppercase text-danger font-weight-bold mb-4">Edit worksheet</h2>
                <?php } else { ?>
                <h2 class="text-uppercase text-danger font-weight-bold mb-4">Add worksheet</h2>
                <?php } ?>
                <?php
                $ediWsTagName = "inputhomeworkTag";
                $ediWsTitleName = "inputhomeworkTitle";
                $ediWsTagValue = $currenthomeworkTag;
                $ediWsTitleValue = $currenthomeworkTitle;
                require __DIR__ . "/partials/edi_worksheet_metadata_form.php";
                ?>
                <?php
                  $mainImageRequired = $editMode ? "" : "required";
                  $pdfuploadRequired = $editMode ? "" : "required";
                ?>
                <div class="row justify-content-center align-items-end mt-4">
                  <div class="col-md-6 col-lg-5">
                    <div class="form-group mb-0">
                      <label class="form-control-label" for="inputhomeworkMainImage">Main Image</label>
                      <input class="form-control" type="file" accept="image/*" id="inputhomeworkMainImage" name="inputhomeworkMainImage" <?php echo $mainImageRequired; ?> onchange="ediWsPreviewImage(event, 'outputhomeworkMainImage')">
                    </div>
                  </div>
                  <div class="col-md-4 col-lg-3 text-center mt-3 mt-md-0">
                    <img id="outputhomeworkMainImage" <?php echo $currenthomeworkMainImage; ?> alt="Preview" class="rounded border bg-light edi-ws-edit-preview-thumb" style="width:100px;height:140px;object-fit:contain;">
                  </div>
                </div>
                <div class="row justify-content-center align-items-end mt-3">
                  <div class="col-md-6 col-lg-5">
                    <div class="form-group mb-0">
                      <label class="form-control-label" for="inputhomeworkpdfupload">Main PDF</label>
                      <input class="form-control" type="file" accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps" id="inputhomeworkpdfupload" name="inputhomeworkpdfupload" <?php echo $pdfuploadRequired; ?>>
                    </div>
                  </div>
                  <div class="col-md-4 col-lg-3 text-center mt-3 mt-md-0">
                    <div class="rounded border bg-light d-inline-flex align-items-center justify-content-center text-muted edi-ws-edit-preview-thumb" style="width:100px;height:140px;" title="PDF">
                      <i class="fas fa-file-pdf text-danger" style="font-size:2rem;" aria-hidden="true"></i>
                    </div>
                  </div>
                </div>
                <div class="row mt-4">
                  <div class="col-12">
                    <div class="form-group mb-0">
                      <label class="form-control-label" for="inputhomeworkMainDescription">Main Description</label>
                      <textarea class="form-control" name="inputhomeworkMainDescription" id="inputhomeworkMainDescription" rows="5" required><?php echo htmlspecialchars($currenthomeworkMainDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                  </div>
                </div>
                <!-- Description -->
                <?php
                  // if ( !$editMode ) {
                  //   echo $widgets->addhomeworkDesctiptionDiv(1); 
                  // } else {
                  //   $i=0;
                  //   $sessionhomeworkDescImgArr = array();
                  //   foreach ( $user->fetchAll(array("id","description", "image_01", "image_02"), array("homework_descriptions"), array("homework_id"=>$currenthomeworkID)) as $row ) {
                  //     if ( $i>0 ) echo "<script>$('#addMorehomeworkDescription$i').css('display', 'none');</script>";
                  //     $sessionhomeworkDescImgArr[$i] = array(
                  //       $row['id'],
                  //       $row['image_01'],
                  //       ($row['image_01']!= "") ? 1 : 0,
                  //       $row['image_02'],
                  //       ($row['image_02']!= "") ? 1 : 0
                  //     );
                  //     $i++;
                  //     echo $widgets->addhomeworkDesctiptionDiv($i, $row); 
                  //   }
                  //   $_SESSION['sessionhomeworkDescImgArr'] = $sessionhomeworkDescImgArr;
                  //   echo "<script>
                  //   $(document).ready(function () {
                  //     $('input[name=howManyDescriptions]').val('$i');
                  //   })
                  //   </script>";
                  // }
                ?>
                <!-- <div class="row justify-content-center" id="addMorehomeworkDescLoadingImage" style="display: none;">
                  <img src="../img/loading.gif" alt="Loading GIF" style="width: 100px;">
                </div>
                <div class="row">
                  <?php
                    echo $widgets->inputGroup("homework Video", "inputhomeworkVideoUrl", "col-md-6", $currenthomeworkVideoUrl);
                    echo "<div class='col-md-6 float-left'>".$widgets->checkboxSwitch("", "homeworkVideoStatus", $currenthomeworkVideoStatus, "pt-5")."</div>";
                  ?>
                </div> -->
                <div class="row mt-4">
                  <div class="col-12">
                    <input type="hidden" name="howManyDescriptions" value="1">
                    <div class="edi-admin-form-actions">
                    <?php
                      if ( $editMode ) {
                        echo "
                        <input type='submit' class='btn btn-success' value='Update' name='updatehomeworkSubmit'>
                        <input type='button' class='btn btn-danger' value='Delete' onclick='deletehomeworkSubmit()'>
                        ";
                      } else {
                        echo "<input type='submit' class='btn btn-success' value='Add' name='addNewhomeworkSubmit'>";
                      }
                    ?>
                    <button type="button" class="btn btn-secondary" onclick="location.href='./homework'">Cancel</button>
                    </div>
                  </div>
                </div>
              </form>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
    <div id="removehomeworkDescImage"></div>
  </main>
  <?php 
    echo $adminHeader->printAdminFooterJS(); 
    if ( $editMode ) {
      $hwDelTitle = htmlspecialchars((string) $currenthomeworkTitle, ENT_QUOTES, 'UTF-8');
      $hwDelTag = htmlspecialchars((string) $currenthomeworkTag, ENT_QUOTES, 'UTF-8');
      echo "
      <div class='modal fade' id='confirmDeletehomeworkModal' data-backdrop='static' tabindex='-1' role='dialog' aria-hidden='true' style='margin-top:200px'>
        <div class='modal-dialog' role='document'>
          <div class='modal-content'>
            <div class='modal-header'>
              <h5 class='modal-title'>Delete worksheet</h5>
            </div>
            <div class='modal-body'>
            <form class='text-center' method='post'>
                <p class='mb-2'>Document title: <strong>$hwDelTitle</strong><br>Tag: <strong>$hwDelTag</strong></p>
                <input type='hidden' name='deletehomeworkID' value='" . (int) $currenthomeworkID . "'>
              <div class='edi-admin-form-actions justify-content-center mt-2'>
              <input type='submit' class='btn btn-danger btn-sm' name='confirmDeletehomeworkSubmit' value='Delete'>
              <button class='btn btn-sm btn-secondary' type='button' data-dismiss='modal'>Cancel</button>
              </div>
            </form>
            </div>
          </div>
        </div>
      </div>
      ";
    }
  ?>
  <script>
    function ediWsPreviewImage(ev, imgId) {
      var f = ev.target.files && ev.target.files[0];
      var el = document.getElementById(imgId);
      if (!el || !f) return;
      if (f.type.indexOf('image/') === 0) {
        el.src = URL.createObjectURL(f);
        el.classList.add('border');
      }
    }
    function loadImageFile(event, sessionTF=0) { 
      var imageDivID = event.target.name.replace("input", "output");
      var imageDivIdNumber = imageDivID.substr(-1);
      $("#"+imageDivID).addClass("border");
			var image = document.getElementById(imageDivID);
			image.src = URL.createObjectURL(event.target.files[0]);
      if (sessionTF) {
        $.ajax({
          type: "POST",
          url: "ajax.php",
          data: {
              changehomeworkDescImage: imageDivIdNumber,
              changehomeworkDescImageNo: imageDivID.substr(15, 3)
          },
          success: function(html) {
              $("#removehomeworkDescImage").html(html).show();
          }
        }); 
      }
		}

    function updateHowManyDescriptions(val) {
      $("input[name='howManyDescriptions']").val(val);
      console.log( $("input[name='howManyDescriptions']").val() );
    }

    function addMorehomeworkDescriptions(index) {
      $("#addMorehomeworkDescription"+index).css("display", "none");
      $("#addMorehomeworkDescLoadingImage").css("display", "flex");
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
          addMorehomeworkDescriptions: index + 1
        },
        success: function(html) {
          $("#addMorehomeworkDescriptions"+index).html(html).show();
          $("#addMorehomeworkDescLoadingImage").css("display", "none");
          index++;
          $("input[name='howManyDescriptions']").val(index);
        }
      }); 
    }

    function removehomeworkDescImage(index, imgNo) {
      $.ajax({
          type: "POST",
          url: "ajax.php",
          data: {
            removehomeworkDescImage: index,
            removehomeworkDescImageNo: imgNo
          },
          success: function(html) {
              $("#removehomeworkDescImage").html(html).show();
          }
      }); 
    }

    function deletehomeworkSubmit() {
      $('#confirmDeletehomeworkModal').modal('show');
    }
  </script>
</body>

</html>