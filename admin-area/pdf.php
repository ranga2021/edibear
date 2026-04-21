<?php
  
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  $adminHeader = new HEADER("pdf");
  $user = new USER();

  
?>
<script>
    // 1. Check if the localStorage item exists
    const adminSession = localStorage.getItem('admin_session');
    const sessionTime = localStorage.getItem('session_time');
    const currentTime = Math.floor(Date.now() / 1000);

    // 2. If missing OR older than 20 minutes (1200 seconds), kick them out
    if (!adminSession || (currentTime - sessionTime > 1200)) {
        localStorage.removeItem('admin_session');
        window.location.href = 'index.php?error=session_expired';
    }
</script>
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
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">PDF Title</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">PDF Tag</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $rowNumber = 0;
                      foreach ( $user->fetchAll(array("id","tag","title","status"), array("pdf_details"), "", "timestamp DESC") as $rowFetchpdf ) {
                        $pdfID = $rowFetchpdf['id'];
                        $pdfTag = $rowFetchpdf['tag'];
                        $pdfTitle = $rowFetchpdf['title'];
                        $pdfStatus = ($rowFetchpdf['status']=='1') ? "checked" : "";
                        echo "
                        <tr>
                          <td class='align-middle text-center cursorPointer' onclick='editpdf($pdfID)'>
                            <span class='text-secondary text-xs font-weight-bold'>$pdfTitle</span>
                          </td>
                          <td class='align-middle text-center cursorPointer' onclick='editpdf($pdfID)'>
                            <span class='text-secondary text-xs font-weight-bold'>$pdfTag</span>
                          </td>
                          <td class='align-middle text-center'>
                            <div class='form-check form-switch justify-content-center'>
                              <input class='form-check-input' type='checkbox' name='pdfStatus$pdfID' value='1' $pdfStatus onchange='chngpdfSts($pdfID)'>
                            </div>
                          </td>
                        </tr>
                        ";
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>
  <div id="chngpdfSts"></div>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script>
    function editpdf(pdfID) {
      location.href = "./add-pdf?id="+pdfID;
    }
    function chngpdfSts(pdfID) {
      var arr = {
        pdfID: pdfID,
        pdfStatus: ($("input[name='pdfStatus"+pdfID+"']").is(":checked")) ? 1 : 0
      };
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
          chngpdfSts: arr
        },
        success: function(html) {
          $("#chngpdfSts").html(html).show();
        }
      }); 
    }
  </script>
</body>

</html>