<?php
// Admins use the same simple train form as managers.
require_once '../includes/auth.php';
require_role(['admin']);
include '../manager/trains.php';
?>
