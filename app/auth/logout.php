<?php
session_start();
session_destroy();
header('Location: /TicketRailway/app/auth/login.php');
exit;
?>
