<?php

require_once '../includes/auth.php';

require_role(['customer']);

$results = [];

if (
    isset($_GET['source']) &&
    isset($_GET['destination']) &&
    isset($_GET['date'])
) {

    $source = trim($_GET['source']);
    $destination = trim($_GET['destination']);
    $date = $_GET['date'];

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
}

include '../includes/header.php';
?>

<h1>Search Trains</h1>

<form method="GET" class="form-inline" id="searchForm">

    <input 
        type="text" 
        name="source" 
        placeholder="From"
        value="<?= htmlspecialchars($_GET['source'] ?? '') ?>"
        required
    >

    <input 
        type="text" 
        name="destination" 
        placeholder="To"
        value="<?= htmlspecialchars($_GET['destination'] ?? '') ?>"
        required
    >

    <input 
        type="date" 
        name="date"
        value="<?= htmlspecialchars($_GET['date'] ?? '') ?>"
        required
    >

    <button type="submit">Search</button>

</form>

<div id="ajaxResults">
<?php if (isset($_GET['source'])): ?>

    <?php if (count($results) > 0): ?>

        <table>
            <tr>
                <th>Train</th>
                <th>Route</th>
                <th>Date</th>
                <th>Depart</th>
                <th>Arrive</th>
                <th>Price</th>
                <th>Seats</th>
                <th>Action</th>
            </tr>

            <?php foreach ($results as $r): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($r['train_name']) ?>
                        (<?= htmlspecialchars($r['train_number']) ?>)
                    </td>

                    <td>
                        <?= htmlspecialchars($r['source']) ?>
                        →
                        <?= htmlspecialchars($r['destination']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($r['travel_date']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($r['departure_time']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($r['arrival_time']) ?>
                    </td>

                    <td>
                        ৳<?= htmlspecialchars($r['price']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($r['available_seats']) ?>
                    </td>

                    <td>
                        <a href="book.php?id=<?= $r['id'] ?>">
                            Book
                        </a>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    <?php else: ?>

        <p>No trains found for this route and date.</p>

    <?php endif; ?>

<?php endif; ?>
</div>

<script>
document.getElementById('searchForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var source = document.querySelector('[name="source"]').value;
    var destination = document.querySelector('[name="destination"]').value;
    var date = document.querySelector('[name="date"]').value;
    var ajaxResults = document.getElementById('ajaxResults');

    ajaxResults.innerHTML = '<p>Searching trains...</p>';

    var xhttp = new XMLHttpRequest();
    var url = 'ajax_search.php?source=' + encodeURIComponent(source) +
        '&destination=' + encodeURIComponent(destination) +
        '&date=' + encodeURIComponent(date);

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var data = JSON.parse(this.responseText);

            if (!data.success) {
                ajaxResults.innerHTML = '<p>' + escapeHtml(data.message) + '</p>';
                return;
            }

            if (data.trains.length === 0) {
                ajaxResults.innerHTML = '<p>No trains found for this route and date.</p>';
                return;
            }

            var html = '<table>';
            html += '<tr>';
            html += '<th>Train</th>';
            html += '<th>Route</th>';
            html += '<th>Date</th>';
            html += '<th>Depart</th>';
            html += '<th>Arrive</th>';
            html += '<th>Price</th>';
            html += '<th>Seats</th>';
            html += '<th>Action</th>';
            html += '</tr>';

            for (var i = 0; i < data.trains.length; i++) {
                var train = data.trains[i];
                html += '<tr>';
                html += '<td>' + escapeHtml(train.train_name) + ' (' + escapeHtml(train.train_number) + ')</td>';
                html += '<td>' + escapeHtml(train.source) + ' → ' + escapeHtml(train.destination) + '</td>';
                html += '<td>' + escapeHtml(train.travel_date) + '</td>';
                html += '<td>' + escapeHtml(train.departure_time) + '</td>';
                html += '<td>' + escapeHtml(train.arrival_time) + '</td>';
                html += '<td>৳' + escapeHtml(train.price) + '</td>';
                html += '<td>' + escapeHtml(train.available_seats) + '</td>';
                html += '<td><a href="book.php?id=' + encodeURIComponent(train.id) + '">Book</a></td>';
                html += '</tr>';
            }

            html += '</table>';
            ajaxResults.innerHTML = html;
        }

        if (this.readyState == 4 && this.status != 200) {
            ajaxResults.innerHTML = '<p class="error">Search failed. Please try again.</p>';
        }
    };

    xhttp.open('GET', url, true);
    xhttp.send();
});

function escapeHtml(text) {
    var div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}
</script>

<?php include '../includes/footer.php'; ?>
