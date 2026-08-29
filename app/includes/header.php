<?php require_once __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Train Ticket System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<nav>
    <div class="brand">🚆 Train Ticket System</div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="menu">
            <span>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> (<?= $_SESSION['role'] ?>)</span>
            <?php if ($_SESSION['role'] === 'customer'): ?>
                <a href="<?= BASE_URL ?>/customer/dashboard.php">My Tickets</a>
                <a href="<?= BASE_URL ?>/customer/search.php">Search Trains</a>
            <?php elseif ($_SESSION['role'] === 'manager'): ?>
                <a href="<?= BASE_URL ?>/manager/dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>/manager/trains.php">Trains</a>
                <a href="<?= BASE_URL ?>/manager/schedules.php">Schedules</a>
                <a href="<?= BASE_URL ?>/manager/users.php">Customers</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>/admin/trains.php">Trains</a>
                <a href="<?= BASE_URL ?>/admin/users.php">Users</a>
                <a href="<?= BASE_URL ?>/admin/tickets.php">Tickets</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/profile.php">Profile</a>
            <a href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    <?php endif; ?>
</nav>
<main>
