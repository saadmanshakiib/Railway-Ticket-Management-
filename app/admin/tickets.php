<?php
require_once '../includes/auth.php';
require_role(['admin']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    check_csrf();
    // Deleting a ticket acts like a cancellation and returns the booked seats.
    $pdo->beginTransaction();
    $ticket = $pdo->prepare("SELECT schedule_id,seats FROM tickets WHERE id=?");
    $ticket->execute([$_POST['delete']]);
    $ticket = $ticket->fetch(PDO::FETCH_ASSOC);
    if ($ticket) {
        $pdo->prepare("UPDATE schedules SET available_seats=available_seats+? WHERE id=?")->execute([$ticket['seats'], $ticket['schedule_id']]);
        $pdo->prepare("DELETE FROM tickets WHERE id=?")->execute([$_POST['delete']]);
    }
    $pdo->commit();
    header('Location: tickets.php'); exit;
}
$tickets = $pdo->query("SELECT ti.*,u.name AS customer,tr.name AS train_name,tr.train_number,s.travel_date FROM tickets ti JOIN users u ON u.id=ti.user_id JOIN schedules s ON s.id=ti.schedule_id JOIN trains tr ON tr.id=s.train_id ORDER BY ti.id DESC")->fetchAll(PDO::FETCH_ASSOC);
include '../includes/header.php';
?>
<h1>All Tickets</h1><table><tr><th>#</th><th>Customer</th><th>Train</th><th>Date</th><th>Seats</th><th>Total</th><th>Status</th><th>Action</th></tr><?php foreach ($tickets as $ticket): ?><tr><td><?= $ticket['id'] ?></td><td><?= htmlspecialchars($ticket['customer']) ?></td><td><?= htmlspecialchars($ticket['train_number'] . ' - ' . $ticket['train_name']) ?></td><td><?= $ticket['travel_date'] ?></td><td><?= $ticket['seats'] ?></td><td>৳<?= $ticket['total_price'] ?></td><td><?= htmlspecialchars($ticket['status']) ?></td><td><form method="POST" class="inline-form" onsubmit="return confirm('Delete this ticket?')"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button name="delete" value="<?= $ticket['id'] ?>">Delete</button></form></td></tr><?php endforeach; ?></table>
<?php include '../includes/footer.php'; ?>
