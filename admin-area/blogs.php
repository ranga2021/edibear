<?php
require_once("../classes/session_config.php");
require_once("../classes/class.user.php");
require_once("../classes/class.header.php");
require_once("../classes/edi_content_tags.php");

$adminHeader = new HEADER("blogs");
$user = new USER();

$search = isset($_GET["search"]) ? trim((string) $_GET["search"]) : "";
$rows = array();

try {
    $pdo = $user->getConnection();
    if ($search === "") {
        $st = $pdo->query("SELECT `id`, `tag`, `title`, `status` FROM `blog_details` ORDER BY `timestamp` DESC");
        $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : array();
    } else {
        $st = $pdo->prepare("SELECT `id`, `tag`, `title`, `status` FROM `blog_details` WHERE `title` LIKE :q ORDER BY `timestamp` DESC");
        $st->execute(array(":q" => "%" . $search . "%"));
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $rows = array();
}
?>
<script>
    const adminSession = localStorage.getItem("admin_session");
    const sessionTime = localStorage.getItem("session_time");
    const currentTime = Math.floor(Date.now() / 1000);
    if (!adminSession || (currentTime - sessionTime > 1200)) {
        localStorage.removeItem("admin_session");
        window.location.href = "index.php?error=session_expired";
    }
</script>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo $adminHeader->printAdminHeader(); ?>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>

  <main class="main-content position-relative border-radius-lg">
    <?php echo $adminHeader->printAdminNav2($adminHeader->getActivePageName()); ?>

    <div class="container-fluid py-4">
      <form method="get" action="blogs.php" class="mb-3">
        <div class="edi-products-toolbar">
          <h1 class="edi-products-title text-uppercase mb-0">Blogs</h1>
          <div class="edi-products-search edi-admin-search-inline">
            <input type="search" name="search" class="form-control" placeholder="Document title" value="<?php echo htmlspecialchars($search, ENT_QUOTES, "UTF-8"); ?>" autocomplete="off">
            <button type="submit" class="btn btn-success mb-0">Search</button>
          </div>
        </div>
      </form>

      <div class="edi-products-table-wrap">
        <div class="table-responsive">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="ps-4">Language</th>
                <th>Grade</th>
                <th>Category</th>
                <th>Document title</th>
                <th class="text-end pe-4">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">No blogs found.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($rows as $rowFetchBlogs): ?>
                  <?php
                  $blogID = (int) ($rowFetchBlogs["id"] ?? 0);
                  $tri = EdiContentTags::blogTagTripleParts((string) ($rowFetchBlogs["tag"] ?? ""));
                  $lang = $tri[0];
                  $grade = $tri[1];
                  $category = $tri[2];
                  $blogTitle = (string) ($rowFetchBlogs["title"] ?? "");
                  $blogStatus = ((string) ($rowFetchBlogs["status"] ?? "0") === "1") ? "checked" : "";
                  ?>
                  <tr>
                    <td class="ps-4"><?php echo htmlspecialchars($lang, ENT_QUOTES, "UTF-8"); ?></td>
                    <td><?php echo htmlspecialchars($grade, ENT_QUOTES, "UTF-8"); ?></td>
                    <td><?php echo htmlspecialchars($category, ENT_QUOTES, "UTF-8"); ?></td>
                    <td><span class="edi-product-name-cell"><?php echo htmlspecialchars($blogTitle, ENT_QUOTES, "UTF-8"); ?></span></td>
                    <td class="text-end pe-4">
                      <div class="d-inline-flex align-items-center justify-content-end flex-wrap" style="gap:0.65rem;">
                        <label class="edi-product-status-switch mb-0">
                          <input type="checkbox" name="blogStatus<?php echo $blogID; ?>" value="1" <?php echo $blogStatus; ?> onchange="chngBlogSts(<?php echo $blogID; ?>)">
                          <span class="edi-slider"></span>
                        </label>
                        <a href="./add-blog?id=<?php echo $blogID; ?>" class="text-secondary font-weight-bold" style="font-size:0.8125rem;">Edit</a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>

  <div id="chngBlogSts"></div>
  <?php echo $adminHeader->printAdminFooterJS(); ?>
  <script>
    function chngBlogSts(blogID) {
        var arr = {
            blogID: blogID,
            blogStatus: ($("input[name='blogStatus" + blogID + "']").is(":checked")) ? 1 : 0
        };
        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: { chngBlogSts: arr },
            success: function (html) {
                $("#chngBlogSts").html(html).show();
            }
        });
    }
  </script>
</body>
</html>
