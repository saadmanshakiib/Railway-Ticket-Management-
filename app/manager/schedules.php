<?php
require_once '../includes/auth.php';
require_role(['manager']);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (isset($_POST['delete'])) {
        $pdo->prepare("DELETE FROM schedules WHERE id=?")->execute([$_POST['delete']]);
        header('Location: schedules.php'); exit;
    }
    $id = $_POST['id'] ?? '';
    $data = [(int)$_POST['train_id'], $_POST['travel_date'], $_POST['departure_time'], $_POST['arrival_time'], (float)$_POST['price'], (int)$_POST['available_seats']];
    if ($data[5] < 0 || $data[4] < 0) $error = 'Price and available seats cannot be negative.';
    else {
        if ($id) {
            $data[] = $id;
            $pdo->prepare("UPDATE schedules SET train_id=?,travel_date=?,departure_time=?,arrival_time=?,price=?,available_seats=? WHERE id=?")->execute($data);
        } else {
            $pdo->prepare("INSERT INTO schedules (train_id,travel_date,departure_time,arrival_time,price,available_seats) VALUES (?,?,?,?,?,?)")->execute($data);
        }
        header('Location: schedules.php'); exit;
    }
}
$edit = null;
if (isset($_GET['edit'])) { $s = $pdo->prepare("SELECT * FROM schedules WHERE id=?"); $s->execute([$_GET['edit']]); $edit = $s->fetch(PDO::FETCH_ASSOC); }
$trains = $pdo->query("SELECT * FROM trains ORDER BY train_number")->fetchAll(PDO::FETCH_ASSOC);
$schedules = $pdo->query("SELECT s.*,t.name,t.train_number,t.source,t.destination FROM schedules s JOIN trains t ON t.id=s.train_id ORDER BY s.travel_date, s.departure_time")->fetchAll(PDO::FETCH_ASSOC);
include '../includes/header.php';
?>
<h1>Manage Schedules</h1>
<?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
<?php if (!$trains): ?><p class="error">Add a train before creating a schedule.</p><?php else: ?>
<form method="POST" class="form-inline">
 <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
 <select name="train_id" required><option value="">Select train</option><?php foreach ($trains as $train): ?><option value="<?= $train['id'] ?>" <?= (($edit['train_id'] ?? '') == $train['id']) ? 'selected' : '' ?>><?= htmlspecialchars($train['train_number'] . ' - ' . $train['name']) ?></option><?php endforeach; ?></select>
 <input type="date" name="travel_date" value="<?= $edit['travel_date'] ?? '' ?>" required><input type="time" name="departure_time" value="<?= $edit['departure_time'] ?? '' ?>" required><input type="time" name="arrival_time" value="<?= $edit['arrival_time'] ?? '' ?>" required>
 <input type="number" step="0.01" min="0" name="price" placeholder="Price" value="<?= $edit['price'] ?? '' ?>" required><input type="number" min="0" name="available_seats" placeholder="Available seats" value="<?= $edit['available_seats'] ?? '' ?>" required><button><?= $edit ? 'Update Schedule' : 'Add Schedule' ?></button>
</form><?php endif; ?>
<table><tr><th>Train</th><th>Route</th><th>Date</th><th>Time</th><th>Price</th><th>Seats</th><th>Action</th></tr>
<?php foreach ($schedules as $schedule): ?><tr><td><?= htmlspecialchars($schedule['train_number'] . ' - ' . $schedule['name']) ?></td><td><?= htmlspecialchars($schedule['source']) ?> → <?= htmlspecialchars($schedule['destination']) ?></td><td><?= $schedule['travel_date'] ?></td><td><?= $schedule['departure_time'] ?> - <?= $schedule['arrival_time'] ?></td><td>৳<?= $schedule['price'] ?></td><td><?= $schedule['available_seats'] ?></td><td><a href="?edit=<?= $schedule['id'] ?>">Edit</a> <form method="POST" class="inline-form" onsubmit="return confirm('Delete this schedule?')"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button name="delete" value="<?= $schedule['id'] ?>">Delete</button></form></td></tr><?php endforeach; ?></table>
<?php include '../includes/footer.php'; ?>
