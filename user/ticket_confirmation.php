<?php
session_start();
include('../includes/db.php');
require_once __DIR__ . '/../includes/phpqrcode/qrlib.php';

if (!isset($_SESSION['reserved_seats']) || empty($_SESSION['reserved_seats'])) {
    die("No reserved seats found. Please select seats first.");
}

$reservedSeats = $_SESSION['reserved_seats'];

// Fetch seat and concert info
$in  = str_repeat('?,', count($reservedSeats)-1) . '?';
$stmt = $conn->prepare("SELECT s.id AS seat_id, s.seat_number, s.seat_type, c.title, c.date, c.time, c.venue
                        FROM seats s
                        JOIN concerts c ON s.concert_id = c.id
                        WHERE s.id IN ($in)");
$stmt->execute($reservedSeats);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPrice = 0;
foreach ($tickets as $t) {
    $totalPrice += ($t['seat_type'] === 'VIP') ? 2000 : 1000;
}

// Clear session after loading tickets
unset($_SESSION['reserved_seats']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ticket Confirmation</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #111; color: #fff; font-family: 'Poppins', sans-serif; }
.ticket { background: rgba(0,0,0,0.7); border-radius: 15px; padding: 20px; margin-bottom: 20px; display:flex; align-items:center; justify-content:space-between; }
.ticket-info { max-width: 70%; }
.ticket h4 { color: #FFD700; }
.btn-print { margin-top: 20px; }
.qr-code { background: #fff; padding: 5px; border-radius: 8px; }
</style>
</head>
<body class="p-4">
<div class="container">
    <h2 class="mb-4 text-center">🎫 Ticket Confirmation</h2>

    <?php foreach ($tickets as $ticket): ?>
    <div class="ticket">
        <div class="ticket-info">
            <h4><?= htmlspecialchars($ticket['title']); ?></h4>
            <p><strong>Venue:</strong> <?= htmlspecialchars($ticket['venue']); ?></p>
            <p><strong>Date:</strong> <?= date("F j, Y", strtotime($ticket['date'])); ?></p>
            <p><strong>Time:</strong> <?= date("g:i A", strtotime($ticket['time'])); ?></p>
            <p><strong>Seat:</strong> <?= htmlspecialchars($ticket['seat_number']); ?> (<?= htmlspecialchars($ticket['seat_type']); ?>)</p>
            <p><strong>Price:</strong> ₱<?= ($ticket['seat_type']==='VIP')? 2000 : 1000 ?></p>
        </div>

        <div class="qr-code">
            <?php
            // Generate QR code
            $data = "concert_id={$ticket['seat_id']}&seat_number={$ticket['seat_number']}";
            $filename = '../temp_qr/qr_'.$ticket['seat_id'].'.png';
            QRcode::png($data, $filename, QR_ECLEVEL_L, 4);
            echo '<img src="'. $filename .'" alt="QR Code">';
            ?>
        </div>
    </div>
    <?php endforeach; ?>

    <h4>Total Price: ₱<?= $totalPrice; ?></h4>

    <button class="btn btn-warning btn-print" onclick="window.print()">🖨️ Print Ticket</button>
    <a href="concerts.php" class="btn btn-outline-light">Back to Concerts</a>
</div>
</body>
</html>
