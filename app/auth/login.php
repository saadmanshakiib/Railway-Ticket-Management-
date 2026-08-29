<?php
require_once '../includes/auth.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        if ($password !== $user['password']) {
            $update = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
            $update->execute([$password, $user['id']]);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') header('Location: ' . BASE_URL . '/admin/dashboard.php');
        elseif ($user['role'] === 'manager') header('Location: ' . BASE_URL . '/manager/dashboard.php');
        else header('Location: ' . BASE_URL . '/customer/dashboard.php');
        exit;
    } 
    else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="../login.css"></head>
<body class="auth-body login-body">
<div class="auth-box login-box">
    <h2>Login</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" id="password" placeholder="Password" required>
        <label class="show-password"><input type="checkbox" onclick="showPassword()"> Show password</label>
        <button type="submit">Login</button>
    </form>
    <p>No account? <a href="register.php">Register</a></p>
</div>
<script>
function showPassword() {
    var password = document.getElementById('password');
    password.type = password.type === 'password' ? 'text' : 'password';
}
</script>
</body></html>
