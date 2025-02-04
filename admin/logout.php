<?php
session_start();
session_destroy();
header("Location: pages-sign-in.php");
exit();
?>
