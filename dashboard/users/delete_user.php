<?php
include '../../includes/header.php';
include '../../db/connect.php';
// echo "delete" ;
$id = $_GET["userid"];

$sql = "delete from users where id = $id";
$result = mysqli_query($conn, $sql);
header("Location: index.php");
?>