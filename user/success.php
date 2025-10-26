<?php
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/db.php';
if(!isset($_SESSION)) session_start();
if(!isset($_SESSION['user_id'])) { header('Location: index.php'); exit(); }

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid concert ID.");
$concert_id = (int)$_GET['id'];
$ticket_type = $_GET['ticket'] ?? '';

// Load concert info
$stmt = $conn->prepare("SELECT * FROM concerts WHERE id=:id LIMIT 1");
$stmt->execute([':id'=>$concert_id]);
$concert = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$concert) die("Concert not found.");

$amount = ($ticket_type==='VIP') ? $concert['vip_price'] : $concert['regular_price'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Success</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#121212; color:#f0f0f0; font-family:'Poppins',sans-serif; }
.card { background:#1c1c2e; border:1px solid #6a11cb; border-radius:12px; padding:30px; text-align:center; box-shadow:0 0 10px rgba(106,17,203,0.3);}
.btn-primary { background:#2575fc; color:#fff; border:none; }
.btn-primary:hover { background:#1a5ed6; }
</style>
</head>
<body>
<div class="container">
<div class="card mt-5">
<h2>Payment Successful!</h2>
<p class="mb-3">You have purchased a <strong><?php echo htmlspecialchars($ticket_type); ?></strong> ticket</p>
<h4><?php echo htmlspecialchars($concert['title']); ?></h4>
<p><?php echo htmlspecialchars($concert['artist']); ?></p>
<img src="uploads/<?php echo htmlspecialchars($concert['banner']); ?>" class="img-fluid rounded mb-3" alt="Concert Banner">
<p><strong>Amount Paid:</strong> ₱<?php echo number_format($amount,2); ?></p>
<a href="index.php" class="btn btn-primary mt-3">Back to Dashboard</a>
</div>
</div>
</body>
</html>
