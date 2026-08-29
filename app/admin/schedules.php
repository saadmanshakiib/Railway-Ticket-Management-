<?php
// Admins use the same simple schedule form as managers.
require_once '../includes/auth.php';
require_role(['admin']);
include '../manager/schedules.php';
?>
