<?php 

    $id = $_GET['product_id'];
    include '../../db/connect.php';

    // Check if the product exists
    $sql = "DELETE FROM products WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        // Redirect to the products page with a success message
        header("Location: index.php?message=Product deleted successfully!.");
        exit;
    } else {
        // Redirect to the products page with an error message
        header("Location: index.php?error=Failed to delete product.");
        exit;
    }

?>