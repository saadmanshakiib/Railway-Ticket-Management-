<?php
require_once '../includes/auth.php';
require_role(['customer']);

$schedule_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT s.*, t.name FROM schedules s JOIN trains t ON s.train_id=t.id WHERE s.id=?");
$stmt->execute([$schedule_id]);
$schedule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$schedule) die('Invalid schedule');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $seats = (int)$_POST['seats'];
    if ($seats > 0) {
        $total = $seats * $schedule['price'];
        try {
            $pdo->beginTransaction();
            $update = $pdo->prepare("UPDATE schedules SET available_seats = available_seats - ? WHERE id = ? AND available_seats >= ?");
            $update->execute([$seats, $schedule_id, $seats]);
            if ($update->rowCount() !== 1) {
                $pdo->rollBack();
                $error = 'Not enough seats available. Please search again.';
            } else {
                $pdo->prepare("INSERT INTO tickets (user_id, schedule_id, seats, total_price) VALUES (?,?,?,?)")
                    ->execute([$_SESSION['user_id'], $schedule_id, $seats, $total]);
                $pdo->commit();
                header('Location: dashboard.php?booked=1');
                exit;
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Booking could not be completed.';
        }
    } else {
        $error = 'Not enough seats available';
    }
}
include '../includes/header.php';
?>
<h1>Book: <?= htmlspecialchars($schedule['name']) ?></h1>
<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <p>Price per seat: $<?= $schedule['price'] ?></p>
    <p>Available: <?= $schedule['available_seats'] ?> seats</p>
    <label>Number of seats: <input type="number" name="seats" min="1" max="<?= $schedule['available_seats'] ?>" required></label>
    <button type="submit">Confirm Booking</button>
</form>
<?php include '../includes/footer.php'; ?>
