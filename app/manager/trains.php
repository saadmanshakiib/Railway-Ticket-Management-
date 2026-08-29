<?php
require_once '../includes/auth.php';
require_role(['manager']);

if (isset($_POST['delete'])) {
    check_csrf();
    $pdo->prepare("DELETE FROM trains WHERE id = ?")->execute([$_POST['delete']]);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (isset($_POST['delete'])) { header('Location: trains.php'); exit; }
    $id = $_POST['id'] ?? null;
    $num = $_POST['train_number'];
    $name = $_POST['name'];
    $src = $_POST['source'];
    $dst = $_POST['destination'];
    $seats = $_POST['total_seats'];
    if ($id) {
        $pdo->prepare("UPDATE trains SET train_number=?, name=?, source=?, destination=?, total_seats=? WHERE id=?")
            ->execute([$num,$name,$src,$dst,$seats,$id]);
    } else {
        $pdo->prepare("INSERT INTO trains (train_number,name,source,destination,total_seats) VALUES (?,?,?,?,?)")
            ->execute([$num,$name,$src,$dst,$seats]);
    }
    header('Location: trains.php'); exit;
}
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM trains WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
$trains = $pdo->query("SELECT * FROM trains ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
include '../includes/header.php';
?>
<h1>Manage Trains</h1>
<a href="schedules.php" class="btn">Manage Schedules</a>
<form method="POST" class="form-inline">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
    <input name="train_number" placeholder="Train Number" value="<?= htmlspecialchars($edit['train_number'] ?? '') ?>" required>
    <input name="name" placeholder="Train Name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
    <input name="source" placeholder="Source" value="<?= htmlspecialchars($edit['source'] ?? '') ?>" required>
    <input name="destination" placeholder="Destination" value="<?= htmlspecialchars($edit['destination'] ?? '') ?>" required>
    <input type="number" name="total_seats" min="1" placeholder="Seats" value="<?= $edit['total_seats'] ?? '' ?>" required>
    <button type="submit"><?= $edit ? 'Update Train' : 'Add Train' ?></button>
</form>
<table>
    <tr><th>#</th><th>Number</th><th>Name</th><th>Route</th><th>Seats</th><th>Action</th></tr>
    <?php foreach ($trains as $t): ?>
    <tr>
        <td><?= $t['id'] ?></td>
        <td><?= $t['train_number'] ?></td>
        <td><?= htmlspecialchars($t['name']) ?></td>
        <td><?= htmlspecialchars($t['source']) ?> → <?= htmlspecialchars($t['destination']) ?></td>
        <td><?= $t['total_seats'] ?></td>
        <td><a href="?edit=<?= $t['id'] ?>">Edit</a> <form method="POST" class="inline-form" onsubmit="return confirm('Delete this train and its schedules?')"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button name="delete" value="<?= $t['id'] ?>">Delete</button></form></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include '../includes/footer.php'; ?>
