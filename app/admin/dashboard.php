<?php
require_once '../includes/auth.php';
require_role(['admin']);
$users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$trains = $pdo->query("SELECT COUNT(*) FROM trains")->fetchColumn();
$tickets = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$schedules = $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn();
include '../includes/header.php';
?>
<h1>Admin Dashboard</h1>
<div class="cards">
 <div class="card"><h3><?= $users ?></h3><p>Users</p><a href="users.php" class="btn">Manage Users</a></div>
 <div class="card"><h3><?= $trains ?></h3><p>Trains</p><a href="trains.php" class="btn">Manage Trains</a></div>
 <div class="card"><h3><?= $schedules ?></h3><p>Schedules</p><a href="schedules.php" class="btn">Manage Schedules</a></div>
 <div class="card"><h3><?= $tickets ?></h3><p>Bookings</p><a href="tickets.php" class="btn">View Tickets</a></div>
</div>
<?php include '../includes/footer.php'; ?>
