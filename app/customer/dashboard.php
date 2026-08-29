<?php
require_once '../includes/auth.php';
require_role(['customer']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket'])) {
    check_csrf();
    $ticket_id = (int)$_POST['delete_ticket'];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT id, schedule_id, seats FROM tickets WHERE id=? AND user_id=?");
        $stmt->execute([$ticket_id, $_SESSION['user_id']]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            $pdo->prepare("UPDATE schedules SET available_seats=available_seats+? WHERE id=?")
                ->execute([$ticket['seats'], $ticket['schedule_id']]);
            $pdo->prepare("DELETE FROM tickets WHERE id=? AND user_id=?")
                ->execute([$ticket_id, $_SESSION['user_id']]);
            $pdo->commit();
            header('Location: dashboard.php?removed=1');
            exit;
        } else {
            $pdo->rollBack();
            $error = 'Ticket was not found.';
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = 'Ticket could not be removed.';
    }
}

$stmt = $pdo->prepare("SELECT t.*, s.travel_date, tr.name, tr.source, tr.destination
    FROM tickets t JOIN schedules s ON t.schedule_id=s.id
    JOIN trains tr ON s.train_id=tr.id WHERE t.user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
include '../includes/header.php';
?>
<h1>My Tickets</h1>
<?php if (isset($_GET['booked'])): ?><p class="success">Booking confirmed.</p><?php endif; ?>
<?php if (isset($_GET['removed'])): ?><p class="success">Ticket removed and seats returned.</p><?php endif; ?>
<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
<a href="search.php" class="btn">Search & Book New</a>
<table>
    <tr><th>#</th><th>Train</th><th>Route</th><th>Date</th><th>Seats</th><th>Total</th><th>Status</th><th>Action</th></tr>
    <?php foreach ($tickets as $t): ?>
    <tr>
        <td><?= $t['id'] ?></td>
        <td><?= htmlspecialchars($t['name']) ?></td>
        <td><?= htmlspecialchars($t['source']) ?> → <?= htmlspecialchars($t['destination']) ?></td>
        <td><?= $t['travel_date'] ?></td>
        <td><?= $t['seats'] ?></td>
        <td>$<?= $t['total_price'] ?></td>
        <td><?= htmlspecialchars($t['status']) ?></td>
        <td>
            <form method="POST" class="inline-form" onsubmit="return confirm('Remove this ticket?')">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button type="submit" name="delete_ticket" value="<?= $t['id'] ?>">Remove</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include '../includes/footer.php'; ?>
