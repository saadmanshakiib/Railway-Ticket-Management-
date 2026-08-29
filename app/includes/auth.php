<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Config/db.php';

define('BASE_URL', '/TicketRailway/app');

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function require_role($allowed) {
    require_login();
    if ($_SESSION['role'] !== 'admin' && !in_array($_SESSION['role'], $allowed)) {
        die('Access denied.');
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf() {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid form request.');
    }
}
?>
