<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Admin authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));
if (!in_array($role, ['admin','administrator','1','superadmin'], true)) {
    header('Location: ../user/index.php');
    exit;
}

try {
    // Total concerts
    $totalConcerts = (int) $conn->query("SELECT COUNT(*) FROM concerts")->fetchColumn();

    // Total tickets sold
    $totalTickets = (int) $conn->query("SELECT COUNT(*) FROM tickets")->fetchColumn();

    // Total sales (check correct column, e.g., amount)
    $totalSales = $conn->query("SELECT SUM(amount) FROM tickets")->fetchColumn();
    $totalSales = $totalSales !== null ? (float)$totalSales : 0.0;

    // Latest concerts (last 5)
    $stmt = $conn->query("SELECT * FROM concerts ORDER BY id DESC LIMIT 5");
    $latestConcerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Dashboard DB error: '.$e->getMessage());
    $totalConcerts = $totalTickets = 0;
    $totalSales = 0.0;
    $latestConcerts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
    color: #fff;
    min-height: 100vh;
}
.panel {
    background: rgba(255,255,255,0.06);
    border-radius: 15px;
    padding: 30px;
    margin-top: 6%;
    box-shadow: 0 0 20px rgba(106,17,203,0.25);
}
.card-compact {
    border-radius: 12px;
    padding: 18px;
    color: #fff;
}
.card-primary { background: linear-gradient(45deg,#6a11cb,#2575fc); }
.card-success { background: linear-gradient(45deg,#11998e,#38ef7d); }
.card-info    { background: linear-gradient(45deg,#2193b0,#6dd5ed); }
a.btn-outline-light { border-color: rgba(255,255,255,0.2); color: #fff; }
a.btn-outline-light:hover { background: rgba(255,255,255,0.03); }
.concert-card {
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 15px;
}
.concert-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}
.concert-info { padding: 10px; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-transparent px-4">
  <a class="navbar-brand" href="index.php">Admin Panel</a>
  <div class="ms-auto">
    <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</nav> 

<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8 panel">
      <h3 class="mb-4 text-center">Dashboard Overview</h3>

      <div class="row text-center g-3">
        <div class="col-md-4">
          <div class="card-compact card-primary">
            <h4 class="mb-0"><?php echo htmlspecialchars($totalConcerts, ENT_QUOTES, 'UTF-8'); ?></h4>
            <p class="mb-0">Concerts</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-compact card-success">
            <h4 class="mb-0"><?php echo htmlspecialchars($totalTickets, ENT_QUOTES, 'UTF-8'); ?></h4>
            <p class="mb-0">Tickets Sold</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-compact card-info">
            <h4 class="mb-0">₱<?php echo number_format($totalSales, 2); ?></h4>
            <p class="mb-0">Total Sales</p>
          </div>
        </div>
      </div>

      <div class="text-center mt-4">
        <a href="add_concert.php" class="btn btn-outline-light me-2">Add New Concert</a>
        <a href="view_tickets.php" class="btn btn-outline-light me-2">View Tickets</a>
        <a href="qr_scanner.php" class="btn btn-outline-light me-2">QR Scanner</a>
        <a href="view_concerts.php" class="btn btn-outline-light me-2">View Concerts</a>
      </div>

      <hr class="my-4">
      <h5 class="mb-3">Latest Concerts</h5>
      <div class="row">
        <?php if(!empty($latestConcerts)): ?>
            <?php foreach($latestConcerts as $concert): ?>
            <div class="col-md-6">
                <div class="concert-card">
                    <img src="../uploads/<?php echo htmlspecialchars($concert['banner']); ?>" alt="Banner">
                    <div class="concert-info">
                        <h6><?php echo htmlspecialchars($concert['title']); ?></h6>
                        <p class="mb-1"><?php echo htmlspecialchars($concert['artist']); ?></p>
                        <small><?php echo date("F j, Y", strtotime($concert['date'])); ?> at <?php echo date("g:i A", strtotime($concert['time'])); ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted">No concerts found.</p>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
</body>
</html>
