<?php
session_start();
session_destroy();
header('Location: ../sign_in1.php');
exit;
?>
