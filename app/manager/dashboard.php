<?php
require_once '../includes/auth.php';
require_role(['manager']);

$train_count = $pdo->query("SELECT COUNT(*) FROM trains")->fetchColumn();
$schedule_count = $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn();
$customer_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
include '../includes/header.php';
?>
<h1>Manager Dashboard</h1>
<div class="cards">
    <div class="card"><h3><?= $train_count ?></h3><p>Trains</p><a class="btn" href="trains.php">Manage Trains</a></div>
    <div class="card"><h3><?= $schedule_count ?></h3><p>Schedules</p><a class="btn" href="schedules.php">Manage Schedules</a></div>
    <div class="card"><h3><?= $customer_count ?></h3><p>Customers</p><a class="btn" href="users.php">Manage Customers</a></div>
</div>
<?php include '../includes/footer.php'; ?>
