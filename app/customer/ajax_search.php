<?php
require_once '../includes/auth.php';
require_role(['customer']);

header('Content-Type: application/json');

$source = trim($_GET['source'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$date = $_GET['date'] ?? '';

if ($source === '' || $destination === '' || $date === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter source, destination and date.'
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        s.id,
        s.travel_date,
        s.departure_time,
        s.arrival_time,
        s.price,
        s.available_seats,
        t.name AS train_name,
        t.train_number,
        t.source,
        t.destination
    FROM schedules s
    JOIN trains t ON s.train_id = t.id
    WHERE t.source LIKE ?
    AND t.destination LIKE ?
    AND s.travel_date = ?
    AND s.available_seats > 0
");

$stmt->execute([
    "%$source%",
    "%$destination%",
    $date
]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'trains' => $results
]);
?>
