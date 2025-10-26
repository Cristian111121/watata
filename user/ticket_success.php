<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['name'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$concertId = (int)$_POST['concert_id'];
$seatType = $_POST['seat_type'];
$quantity = (int)$_POST['quantity'];
$paymentMethod = $_POST['payment_method'];

$db = isset($conn) ? $conn : (isset($pdo) ? $pdo : null);
if (!$db) die("Database not connected.");

// Get concert info
$stmt = $db->prepare("SELECT * FROM concerts WHERE id = ?");
$stmt->execute([$concertId]);
$concert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$concert) {
    die("<h3 style='color:red;text-align:center;'>Concert not found.</h3>");
}

// Compute total
$price = ($seatType === 'VIP') ? 3000 : 1500;
$total = $price * $quantity;

$username = htmlspecialchars($_SESSION['name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ticket Confirmation</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #0f0c29; color: white; font-family: 'Poppins', sans-serif; }
.container { margin-top: 50px; max-width: 700px; }
.card { background-color: #1a1a2e; border: 1px solid #6a11cb; border-radius: 15px; padding: 20px; }
.btn-home { background-color: #6a11cb; border: none; border-radius: 10px; }
.btn-home:hover { background-color: #2575fc; }
.qr-box { text-align: center; margin-top: 20px; }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <h3 class="text-center mb-3">✅ Payment Successful</h3>
        <hr class="border-light">
        <p><strong>Name:</strong> <?php echo $username; ?></p>
        <p><strong>Concert:</strong> <?php echo htmlspecialchars($concert['title']); ?></p>
        <p><strong>Seat Type:</strong> <?php echo htmlspecialchars($seatType); ?></p>
        <p><strong>Tickets:</strong> <?php echo $quantity; ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($paymentMethod); ?></p>
        <h4 class="mt-3 text-success">Total: ₱<?php echo number_format($total, 2); ?></h4>

        <div class="qr-box">
            <?php
            include_once('../phpqrcode/qrlib.php');
            $ticketCode = 'TICKET-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $filePath = "../uploads/qrcodes/$ticketCode.png";
            if (!is_dir("../uploads/qrcodes")) mkdir("../uploads/qrcodes", 0777, true);
            QRcode::png($ticketCode, $filePath, QR_ECLEVEL_L, 5);
            echo "<img src='$filePath' width='150' alt='QR Code'>";
            ?>
            <p class="mt-2"><strong>Ticket Code:</strong> <?php echo $ticketCode; ?></p>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-home btn-lg text-white">🏠 Back to Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
