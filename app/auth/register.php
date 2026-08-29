<?php
require_once '../includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $plain_password = $_POST['password'];

    if (strlen($plain_password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $plain_password]);
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        } catch (Exception $e) {
            $error = 'Email already exists';
        }
    }
}
?>
<!DOCTYPE html>
<html><head><link rel="stylesheet" href="../assets/style.css"></head>
<body class="auth-body">
<div class="auth-box">
    <h2>Register</h2>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" id="password" placeholder="Password" minlength="6" required>
        <label class="show-password"><input type="checkbox" onclick="showPassword()"> Show password</label>
        <button type="submit">Register</button>
    </form>
    <p>Have an account? <a href="login.php">Login</a></p>
</div>
<script>
function showPassword() {
    var password = document.getElementById('password');
    password.type = password.type === 'password' ? 'text' : 'password';
}
</script>
</body></html>
