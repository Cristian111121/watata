<?php
include('../includes/db.php');
include('../includes/auth.php');

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['name'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    die("<h3 style='color:red;text-align:center;'>No concert ID provided.</h3>");
}

$concertId = (int)$_GET['id'];

// Ensure PDO
if (isset($conn) && $conn instanceof PDO) {
    $db = $conn;
} elseif (isset($pdo) && $pdo instanceof PDO) {
    $db = $pdo;
} else {
    die("<h3 style='color:red;text-align:center;'>Database connection not found. Check includes/db.php</h3>");
}

// Fetch concert details
$stmt = $db->prepare("SELECT * FROM concerts WHERE id = ?");
$stmt->execute([$concertId]);
$concert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$concert) {
    die("<h3 style='color:red;text-align:center;'>Concert not found.</h3>");
}

$username = htmlspecialchars($_SESSION['name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($concert['title']); ?> - AMP'D Ticketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f0c29; color: white; font-family: 'Poppins', sans-serif; }
        .container { margin-top: 40px; }
        .btn-buy { background-color: #6a11cb; border: none; border-radius: 10px; }
        .btn-buy:hover { background-color: #2575fc; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn btn-outline-light mb-3">&larr; Back to Dashboard</a>

    <div class="card p-4 bg-dark border-light">
        <?php
        $bannerPath = '../uploads/' . ($concert['banner'] ?? '');
        if (!empty($concert['banner']) && file_exists($bannerPath)) {
            echo '<img src="' . htmlspecialchars($bannerPath) . '" class="img-fluid rounded mb-3" alt="Concert Image">';
        }
        ?>
        <h2><?php echo htmlspecialchars($concert['title']); ?></h2>
        <p><strong>Artist:</strong> <?php echo htmlspecialchars($concert['artist']); ?></p>
        <p><strong>Venue:</strong> <?php echo htmlspecialchars($concert['venue']); ?></p>
        <p><strong>Date:</strong> <?php echo date("F j, Y", strtotime($concert['date'])); ?></p>
        <p><strong>Time:</strong> <?php echo date("g:i A", strtotime($concert['time'])); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($concert['description']); ?></p>

        <div class="text-center mt-4">
            <a href="payment.php?id=<?php echo $concertId; ?>" class="btn btn-buy btn-lg text-white">
                🎟 Avail Ticket
            </a>
        </div>
    </div>
</div>

</body>
</html>
