<?php
require_once 'includes/auth.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim($_POST['name']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($name === '') {
        $error = 'Name is required.';
    } elseif ($new_password !== '' && strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } else {
        if ($new_password !== '') {
            $stmt = $pdo->prepare("UPDATE users SET name=?, password=? WHERE id=?");
            $stmt->execute([$name, $new_password, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name=? WHERE id=?");
            $stmt->execute([$name, $_SESSION['user_id']]);
        }

        $_SESSION['name'] = $name;
        $success = 'Profile updated successfully.';
    }
}

$stmt = $pdo->prepare("SELECT id, name, email, password, role, created_at FROM users WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<h1>My Profile</h1>
<?php if ($error) echo "<p class='error'>$error</p>"; ?>
<?php if ($success) echo "<p class='success'>$success</p>"; ?>

<div class="profile-box">
    <p><strong>User ID:</strong> <?= $user['id'] ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Password:</strong> <?= htmlspecialchars($user['password']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
    <p><strong>Joined:</strong> <?= $user['created_at'] ?></p>
</div>

<form method="POST" class="form-inline profile-form">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <h3>Update Profile</h3>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" placeholder="Full Name" required>
    <input type="password" name="new_password" id="new_password" placeholder="New Password (optional)" minlength="6">
    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" minlength="6">
    <label class="show-password"><input type="checkbox" onclick="showProfilePasswords()"> Show password</label>
    <button type="submit">Update Profile</button>
</form>

<script>
function showProfilePasswords() {
    var newPassword = document.getElementById('new_password');
    var confirmPassword = document.getElementById('confirm_password');
    var type = newPassword.type === 'password' ? 'text' : 'password';
    newPassword.type = type;
    confirmPassword.type = type;
}
</script>
<?php include 'includes/footer.php'; ?>
