<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  $adminHeader = new HEADER("books");
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
  <meta property='og:title' content='Traveylo | Sri Lanka Tour Packages | Travel Agent in Sri Lanka'/>
  <meta name='description' content='“Ayubowan!” Traveylo.com provides tour packages covering the most beautiful places 
in Sri Lanka, and you can travel in luxury with your own vehicle around 
our beautiful country. So reserve your tour with us.' />
<meta name='keywords' content='Travel Agents In Sri Lanka / Sri Lanka Tourism / Sri Lanka Tourist Destinations / Places To Visit In Sri Lanka With Family / How To Travel In Sri Lanka / Sri Lanka Tours & Travels / Tour Packages In Sri Lanka / Sri Lanka Itinerary / Sri Lanka Travel Guide /Sri Lanka HotelsSri Lanka Tour Operators /Sri Lanka Budgets Tours /Small Group Tour In Sri Lanka / Sri Lanka Holiday Packages /Sri Lanka Tour Packages For Couple / Sri Lanka Tour Packages For Family /Sri Lanka Tour Packages Price / What To Do In Sri Lanka /Popular Destinations In Sri Lanka' />
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
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">books Title</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">books Tag</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $rowNumber = 0;
                      foreach ( $user->fetchAll(array("id","tag","title","status"), array("books_details"), "", "timestamp DESC") as $rowFetchbooks ) {
                        $booksID = $rowFetchbooks['id'];
                        $booksTag = $rowFetchbooks['tag'];
                        $booksTitle = $rowFetchbooks['title'];
                        $booksStatus = ($rowFetchbooks['status']=='1') ? "checked" : "";
                        echo "
                        <tr>
                          <td class='align-middle text-center cursorPointer' onclick='editbooks($booksID)'>
                            <span class='text-secondary text-xs font-weight-bold'>$booksTitle</span>
                          </td>
                          <td class='align-middle text-center cursorPointer' onclick='editbooks($booksID)'>
                            <span class='text-secondary text-xs font-weight-bold'>$booksTag</span>
                          </td>
                          <td class='align-middle text-center'>
                            <div class='form-check form-switch justify-content-center'>
                              <input class='form-check-input' type='checkbox' name='booksStatus$booksID' value='1' $booksStatus onchange='chngbooksSts($booksID)'>
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
  <div id="chngbooksSts"></div>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script>
    function editbooks(booksID) {
      location.href = "./add-books?id="+booksID;
    }
    function chngbooksSts(booksID) {
      var arr = {
        booksID: booksID,
        booksStatus: ($("input[name='booksStatus"+booksID+"']").is(":checked")) ? 1 : 0
      };
      $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
          chngbooksSts: arr
        },
        success: function(html) {
          $("#chngbooksSts").html(html).show();
        }
      }); 
    }
  </script>
</body>

</html>