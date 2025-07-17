<?php
session_start();
session_destroy();
header("Location: /BookNest/login.php");
exit;
?>
