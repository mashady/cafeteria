<?php 

    $id = $_GET['product_id'];
    include '../../db/connect.php';

    $sql = "DELETE FROM products WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        header("Location: index.php?message=Product deleted successfully!.");
        exit;
    } else {
        header("Location: index.php?error=Failed to delete product.");
        exit;
    }

?>