<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  $adminHeader = new HEADER("ad1");
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
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Title</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Ad Link</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $rowNumber = 0;
                      foreach ( $user->fetchAll(array("id","tag","title","status", "adlink"), array("ad1_details"), "", "timestamp DESC") as $rowFetchad1 ) {
                        $ad1ID = $rowFetchad1['id'];
                        $ad1Tag = $rowFetchad1['tag'];
                        $ad1Title = $rowFetchad1['title'];
                        $ad1adlink = $rowFetchad1['adlink'];
                        $ad1Status = ($rowFetchad1['status']=='1') ? "checked" : "";
                        echo "
                        <tr>
                            <td class='align-middle text-center cursorPointer' onclick='editad1($ad1ID)'>
                            <span class='text-secondary text-xs font-weight-bold'>Home Ad 1</span>
                            <!---------
                            <span class='text-secondary text-xs font-weight-bold'>$ad1Title</span>
                            ------------>
                            </td>
                            <td class='align-middle text-center cursorPointer' onclick='editad1($ad1ID)'>
                              <span class='text-secondary text-xs font-weight-bold'>$ad1adlink</span>
                              <!---------
                              <span class='text-secondary text-xs font-weight-bold'>$ad1Tag</span>
                              ------------>
                            </td>
                          <td class='align-middle text-center'>
                            <div class='form-check form-switch justify-content-center'>
                              <input class='form-check-input' type='checkbox' name='ad1Status$ad1ID' value='1' $ad1Status onchange='chngad1Sts($ad1ID)'>
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
  <div id="chngad1Sts"></div>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script>
    function editad1(ad1ID) {
      location.href = "./add-ad1?id="+ad1ID;
    }
    function chngad1Sts(ad1ID) {
      var arr = {
        ad1ID: ad1ID,
        ad1Status: ($("input[name='ad1Status"+ad1ID+"']").is(":checked")) ? 1 : 0
      };
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
          chngad1Sts: arr
        },
        success: function(html) {
          $("#chngad1Sts").html(html).show();
        }
      }); 
    }
  </script>
</body>

</html>