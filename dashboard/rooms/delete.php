<?php
include '../../db/connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM rooms WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error deleting room: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>
