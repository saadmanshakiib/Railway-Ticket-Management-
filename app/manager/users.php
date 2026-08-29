<?php
require_once '../includes/auth.php';
require_role(['manager']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (isset($_POST['delete'])) {
        // Return booked seats before the customer's tickets are removed.
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE schedules s JOIN tickets t ON t.schedule_id=s.id JOIN users u ON u.id=t.user_id SET s.available_seats=s.available_seats+t.seats WHERE u.id=? AND u.role='customer'")->execute([$_POST['delete']]);
        $pdo->prepare("DELETE FROM users WHERE id=? AND role='customer'")->execute([$_POST['delete']]);
        $pdo->commit();
        header('Location: users.php'); exit;
    }
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    try {
        if ($id) {
            $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=? AND role='customer'")->execute([$name, $email, $id]);
        } else {
            $password = $_POST['password'];
            if (strlen($password) < 6) {
                $message = 'Password must be at least 6 characters long.';
            } else {
                $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,'customer')")->execute([$name, $email, $password]);
            }
        }
        if (!$message) { header('Location: users.php'); exit; }
    } catch (Exception $e) { $message = 'Email already exists.'; }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=? AND role='customer'");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
$customers = $pdo->query("SELECT id,name,email,created_at FROM users WHERE role='customer' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
include '../includes/header.php';
?>
<h1>Manage Customers</h1>
<?php if ($message): ?><p class="error"><?= $message ?></p><?php endif; ?>
<form method="POST" class="form-inline">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
    <input name="name" placeholder="Customer name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($edit['email'] ?? '') ?>" required>
    <?php if (!$edit): ?><input type="password" name="password" placeholder="Password" minlength="6" required><?php endif; ?>
    <button><?= $edit ? 'Update Customer' : 'Add Customer' ?></button>
</form>
<table><tr><th>#</th><th>Name</th><th>Email</th><th>Joined</th><th>Action</th></tr>
<?php foreach ($customers as $customer): ?><tr>
    <td><?= $customer['id'] ?></td><td><?= htmlspecialchars($customer['name']) ?></td><td><?= htmlspecialchars($customer['email']) ?></td><td><?= $customer['created_at'] ?></td>
    <td><a href="?edit=<?= $customer['id'] ?>">Edit</a> <form method="POST" class="inline-form" onsubmit="return confirm('Delete this customer?')"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button name="delete" value="<?= $customer['id'] ?>">Delete</button></form></td>
</tr><?php endforeach; ?></table>
<?php include '../includes/footer.php'; ?>
