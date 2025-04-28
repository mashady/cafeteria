<?php
session_start();

session_unset();
session_destroy();

header("Location: /cafeteria/login.php");
exit();
?>
