<?php
require_once '../includes/auth.php';
require_role(['admin']);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (isset($_POST['delete'])) {
        if ((int)$_POST['delete'] !== (int)$_SESSION['user_id']) {
            // Return booked seats before deleting a user and the user's tickets.
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE schedules s JOIN tickets t ON t.schedule_id=s.id SET s.available_seats=s.available_seats+t.seats WHERE t.user_id=?")->execute([$_POST['delete']]);
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$_POST['delete']]);
            $pdo->commit();
        }
        header('Location: users.php'); exit;
    }
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name']); $email = trim($_POST['email']); $role = $_POST['role'];
    if (!in_array($role, ['admin','manager','customer'])) $error = 'Invalid role.';
    elseif (!$id && strlen($_POST['password']) < 6) $error = 'Password must be at least 6 characters long.';
    else try {
        if ($id) $pdo->prepare("UPDATE users SET name=?,email=?,role=? WHERE id=?")->execute([$name,$email,$role,$id]);
        else $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)")->execute([$name,$email,$_POST['password'],$role]);
        header('Location: users.php'); exit;
    } catch (Exception $e) { $error = 'Email already exists.'; }
}
$edit = null;
if (isset($_GET['edit'])) { $q = $pdo->prepare("SELECT * FROM users WHERE id=?"); $q->execute([$_GET['edit']]); $edit = $q->fetch(PDO::FETCH_ASSOC); }
$users = $pdo->query("SELECT id,name,email,role,created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
include '../includes/header.php';
?>
<h1>Manage Users</h1><?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
<form method="POST" class="form-inline"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>"><input name="name" placeholder="Name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required><input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($edit['email'] ?? '') ?>" required><?php if (!$edit): ?><input type="password" name="password" placeholder="Password" minlength="6" required><?php endif; ?><select name="role"><option value="customer" <?= (($edit['role'] ?? '') === 'customer') ? 'selected' : '' ?>>Customer</option><option value="manager" <?= (($edit['role'] ?? '') === 'manager') ? 'selected' : '' ?>>Manager</option><option value="admin" <?= (($edit['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option></select><button><?= $edit ? 'Update User' : 'Add User' ?></button></form>
<table><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Action</th></tr><?php foreach ($users as $user): ?><tr><td><?= $user['id'] ?></td><td><?= htmlspecialchars($user['name']) ?></td><td><?= htmlspecialchars($user['email']) ?></td><td><?= $user['role'] ?></td><td><?= $user['created_at'] ?></td><td><a href="?edit=<?= $user['id'] ?>">Edit</a><?php if ($user['id'] != $_SESSION['user_id']): ?> <form method="POST" class="inline-form" onsubmit="return confirm('Delete this user?')"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><button name="delete" value="<?= $user['id'] ?>">Delete</button></form><?php endif; ?></td></tr><?php endforeach; ?></table>
<?php include '../includes/footer.php'; ?>
