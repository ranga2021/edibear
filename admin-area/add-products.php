<?php
  require_once("../classes/session_config.php");
  require_once("../classes/class.user.php");
  require_once("../classes/class.header.php");
  
  $adminHeader = new HEADER("add-product"); // Set active page for sidebar
  $user = new USER();

  try {
    $catStmt = $user->runQuery("SELECT * FROM product_categories ORDER BY name ASC");
    $catStmt->execute();
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    $subCatStmt = $user->runQuery("SELECT * FROM sub_category ORDER BY title ASC");
    $subCatStmt->execute();
    $sub_categories = $subCatStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    $sub_categories = [];
}

  

  // Handle Form Submission
  if (isset($_POST['btn-add-product'])) {
      $sub_category_id = $_POST['sub_category'];
      $category_id = $_POST['category'];
      $brand       = $_POST['brand'];
      $age_group   = $_POST['age_group'];
      $price       = $_POST['price'];
      $discount    = $_POST['discount'];
      $disc_price  = $_POST['discounted_price'];
      $p_name      = $_POST['product_name'];
      $stock       = $_POST['available'];
      $description = $_POST['description'];
      $language    = $_POST['language'];
      $author      = $_POST['author'];
      
      // Image Handling (Simplified - ensure your upload directory exists)
      $main_image = $_FILES['main_image']['name'];
      $target = "../img/products/" . basename($main_image);
      move_uploaded_file($_FILES['main_image']['tmp_name'], $target);

      try {
          $stmt = $user->runQuery("INSERT INTO products (category_id, sub_category_id, brand, product_name, price, discount_percentage, discounted_price, age_group, description, language, author, stock, image, status) 
                                 VALUES (:cid, :scid, :brand, :pname, :price, :disc, :dprice, :age, :desc, :lang, :auth, :stock, :img, 1)");
          $stmt->execute(array(
              ":cid"=>$category_id, ":scid"=>$sub_category_id, ":brand"=>$brand, ":pname"=>$p_name, ":price"=>$price, 
              ":disc"=>$discount, ":dprice"=>$disc_price, ":age"=>$age_group, ":desc"=>$description, 
              ":lang"=>$language, ":auth"=>$author, ":stock"=>$stock, ":img"=>$main_image
          ));
          $msg = "<div class='alert alert-success'>Product added successfully!</div>";
      } catch (PDOException $e) {
          $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
      }
  }
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
  <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php echo $adminHeader->printAdminNav(); ?>
  
  <main class="main-content position-relative border-radius-lg ">
    <?php echo $adminHeader->printAdminNav2("Add Product"); ?>
    
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0">
              <h6>PRODUCT INFORMATION</h6>
              <?php if(isset($msg)) echo $msg; ?>
            </div>
            <div class="card-body px-4 pt-0 pb-2">
              <form method="post" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-4">
             <label>Category</label>
            <select name="category" class="form-control" required>
           <option value="">Select Category</option>
           <?php foreach($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="col-md-3">
        <label>Subcategory</label>
        <select name="sub_category" class="form-control">
            <option value="">Select Subcategory</option>
            <?php foreach($sub_categories as $sub): ?>
                <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['title']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
                  <div class="col-md-4">
                    <label>Brand</label>
                    <input type="text" name="brand" class="form-control" placeholder="Sarasavi">
                  </div>
                  <div class="col-md-3">
        <label>Age group</label>
        <select name="age_group" class="form-control">
            <option value="">Select Age Group</option>
            <option value="Pre school">Pre school</option>
            <option value="Grade 1">Grade 1</option>
            <option value="Grade 2">Grade 2</option>
            <option value="Grade 3">Grade 3</option>
            <option value="Grade 4">Grade 4</option>
            <option value="Grade 5">Grade 5</option>
        </select>
    </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-4">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="LKR 700.00">
                  </div>
                  <div class="col-md-4">
                    <label>Discount Percentage (*)</label>
                    <input type="number" name="discount" id="discount" class="form-control" placeholder="5%">
                  </div>
                  <div class="col-md-4">
                    <label>Discounted Price (Auto cal)</label>
                    <input type="text" id="discounted_price_display" class="form-control" readonly>
                   <input type="hidden" name="discounted_price" id="discounted_price_value">
                 </div>
                </div>

                <div class="row mt-3">
                  <div class="col-md-8">
                    <label>Product Name</label>
                    <input type="text" name="product_name" class="form-control" placeholder="ENGLISH ALPHABET BOOK" required>
                  </div>
                  <div class="col-md-4">
                    <label>Available</label>
                    <input type="number" name="available" class="form-control" placeholder="10">
                  </div>
                </div>

                <div class="row mt-3">
                  <div class="col-12">
                    <label>Main Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                  </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6>Options</h6>
                        <div class="row mb-2">
                            <div class="col-5"><input type="text" class="form-control" value="Language" readonly></div>
                            <div class="col-7"><input type="text" name="language" class="form-control" placeholder="English"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5"><input type="text" class="form-control" value="Author" readonly></div>
                            <div class="col-7"><input type="text" name="author" class="form-control" placeholder="Jhon Jhones"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success mt-2">ADD ANOTHER OPTION +</button>
                    </div>
                    <div class="col-md-6">
                        <h6>More Details</h6>
                        <textarea name="more_details" id="more_details"></textarea>
                        <script>CKEDITOR.replace('more_details');</script>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <label>Main Image</label>
                        <input type="file" name="main_image" class="form-control" required>
                    </div>
                   
                </div>

                <div class="mt-4 mb-4">
                 
                  <button type="submit" name="btn-add-product" class="btn btn-success">Add</button>
                  <button type="reset" class="btn btn-secondary">CANCEL</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php echo $adminHeader->printAdminFooter(); ?>
    </div>
  </main>

  <div class="col-md-4">
    <label>Discounted Price (Auto cal)</label>
    <input type="text" id="discounted_price_display" class="form-control" readonly>
    <input type="hidden" name="discounted_price" id="discounted_price_value">
</div>

<script>
    const priceInput = document.getElementById('price');
    const discInput = document.getElementById('discount');
    const displayInput = document.getElementById('discounted_price_display');
    const valueInput = document.getElementById('discounted_price_value');

    function calculate() {
        const price = parseFloat(priceInput.value) || 0;
        const disc = parseFloat(discInput.value) || 0;
        const discounted = price - (price * (disc / 100));
        
        // Update the display for the admin
        displayInput.value = "LKR " + discounted.toFixed(2);
        // Set the clean numerical value for the PHP form submission
        valueInput.value = discounted.toFixed(2);
    }

    priceInput.addEventListener('input', calculate);
    discInput.addEventListener('input', calculate);
</script>

  <?php echo $adminHeader->printAdminFooterJS(); ?>
</body>
</html>