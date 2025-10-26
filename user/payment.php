<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate concert ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid concert ID.");
$concert_id = (int)$_GET['id'];

// Load concert info
try {
    $stmt = $conn->prepare("SELECT * FROM concerts WHERE id = :id LIMIT 1");
    $stmt->execute([':id'=>$concert_id]);
    $concert = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$concert) die("Concert not found.");
} catch(PDOException $e){
    die("Error loading concert info: ".$e->getMessage());
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_type = trim($_POST['ticket_type'] ?? '');
    if (empty($ticket_type) || !in_array($ticket_type, ['VIP','Regular'])) {
        $error = "Please select a valid ticket type.";
    } else {
        $price = ($ticket_type === 'VIP') ? $concert['vip_price'] : $concert['regular_price'];
        try {
            $stmt = $conn->prepare("INSERT INTO payments (user_id, concert_id, ticket_type, amount, payment_date)
                                    VALUES (:user_id, :concert_id, :ticket_type, :amount, NOW())");
            $stmt->execute([
                ':user_id' => $user_id,
                ':concert_id' => $concert_id,
                ':ticket_type' => $ticket_type,
                ':amount' => $price
            ]);

            // Redirect to success page (file in project root)
            header("Location: ../success.php?id=".$concert_id."&ticket=".$ticket_type);
            exit;

        } catch(PDOException $e){
            $error = "Error saving payment: ".$e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment - <?php echo htmlspecialchars($concert['title']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#121212; color:#f0f0f0; font-family:'Poppins',sans-serif; }
.card { background:#1c1c2e; border:1px solid #6a11cb; border-radius:12px; box-shadow:0 0 10px rgba(106,17,203,0.3);}
.btn-primary { background:#2575fc; color:#fff; border:none; }
.btn-primary:hover { background:#1a5ed6; }
.alert { background:#990000; color:#f0f0f0; border:1px solid #ff4444; }
.form-select { background:#1c1c2e; color:#f0f0f0; border:1px solid #6a11cb; }
</style>
</head>
<body>
<div class="container">
<div class="card p-4 mt-5">
<h3 class="text-center"><?php echo htmlspecialchars($concert['title']); ?></h3>
<p class="text-center text-muted mb-4"><?php echo htmlspecialchars($concert['artist']); ?></p>
<img src="../uploads/<?php echo htmlspecialchars($concert['banner']); ?>" class="img-fluid rounded mb-3" alt="Concert Banner">
<p><strong>Venue:</strong> <?php echo htmlspecialchars($concert['venue']); ?></p>
<p><strong>Date:</strong> <?php echo date("F j, Y", strtotime($concert['date'])); ?> at <?php echo date("g:i A", strtotime($concert['time'])); ?></p>

<hr>
<form method="POST">
    <label for="ticket_type" class="form-label">Select Ticket Type</label>
    <select name="ticket_type" id="ticket_type" class="form-select mb-3" required>
        <option value="">-- Choose Ticket Type --</option>
        <option value="VIP">VIP - ₱<?php echo number_format($concert['vip_price'],2); ?></option>
        <option value="Regular">Regular - ₱<?php echo number_format($concert['regular_price'],2); ?></option>
    </select>
    <button type="submit" class="btn btn-primary w-100">Proceed to Pay</button>
</form>

<?php if(!empty($error)): ?>
<div class="alert mt-3"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="text-center mt-3">
    <a href="../index.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
</div>
</div>
</div>
</body>
</html>
