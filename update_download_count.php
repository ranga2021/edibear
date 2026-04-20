<?php
// Inside update_download_count.php file

// Connect to your database
if (isset($_POST['downloadButton']) && $_POST['downloadButton'] == 1 && isset($_POST['pdfId']) && isset($_POST['type'])) {
    $servername = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USERNAME') ?: '';
    $password = getenv('DB_PASSWORD') ?: '';
    $dbname = getenv('DB_NAME') ?: '';
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $pdfId = $_POST['pdfId'];
   
    // Update the download count in the database
    if ($_POST['type'] == 'pdf'){
        $sql = "UPDATE pdf_details SET download_count = download_count + 1 WHERE id = $pdfId";
    } else if ($_POST['type'] == 'book'){
        $sql = "UPDATE books_details SET download_count = download_count + 1 WHERE id = $pdfId"; 
    } else {
        $sql = "UPDATE homework_details SET download_count = download_count + 1 WHERE id = $pdfId"; 
    }
    $conn->query($sql);

    // Retrieve the updated count from the database
    if ($_POST['type'] == 'pdf'){
        $sql = "SELECT download_count FROM pdf_details WHERE id = $pdfId";
    } else if ($_POST['type'] == 'book'){
        $sql = "SELECT download_count FROM books_details WHERE id = $pdfId";
    } else {
        $sql = "SELECT download_count FROM homework_details WHERE id = $pdfId";
    }
 
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $downloadcount = $row['download_count'];

    $conn->close();

    // Return the updated count as the response
    echo $downloadcount;
}

?>
