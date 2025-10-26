<?php
session_start();
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/db.php';

// Only allow admin
$role = strtolower($_SESSION['role'] ?? '');
if (!in_array($role, ['admin','administrator','1','superadmin'], true)) {
    header('Location: ../user/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? '');
    $venue  = trim($_POST['venue'] ?? '');
    $date   = $_POST['date'] ?? '';
    $time   = $_POST['time'] ?? '';
    $vip_price     = floatval($_POST['vip_price'] ?? 0);
    $regular_price = floatval($_POST['regular_price'] ?? 0);

    // Validate
    if (!$title || !$artist || !$venue || !$date || !$time || $vip_price <=0 || $regular_price <=0) {
        $error = "All fields are required and prices must be greater than 0.";
    } elseif (!isset($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
        $error = "Banner image is required.";
    } else {
        // Handle banner upload
        $uploadDir = __DIR__.'/../uploads/';
        $filename = time().'_'.basename($_FILES['banner']['name']);
        $targetFile = $uploadDir.$filename;
        if (!move_uploaded_file($_FILES['banner']['tmp_name'], $targetFile)) {
            $error = "Failed to upload banner image.";
        } else {
            // Save to database
            try {
                $stmt = $conn->prepare("
                    INSERT INTO concerts (title, artist, venue, date, time, vip_price, regular_price, banner)
                    VALUES (:title, :artist, :venue, :date, :time, :vip_price, :regular_price, :banner)
                ");
                $stmt->execute([
                    ':title' => $title,
                    ':artist' => $artist,
                    ':venue' => $venue,
                    ':date' => $date,
                    ':time' => $time,
                    ':vip_price' => $vip_price,
                    ':regular_price' => $regular_price,
                    ':banner' => $filename
                ]);
                $success = "Concert added successfully!";
            } catch(PDOException $e){
                $error = "Error saving concert: ".$e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Concert</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#121212; color:#f0f0f0; font-family:'Poppins',sans-serif; }
.card { background:#1c1c2e; padding:30px; border-radius:12px; margin-top:50px; }
.btn-primary { background:#2575fc; border:none; }
.btn-primary:hover { background:#1a5ed6; }
.alert { color:#fff; background:#990000; border:1px solid #ff4444; }
</style>
</head>
<body>
<div class="container">
<div class="card mx-auto" style="max-width:600px;">
<h3 class="mb-4 text-center">Add New Concert</h3>

<?php if($error): ?>
<div class="alert mb-3"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if($success): ?>
<div class="alert mb-3" style="background:green;"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="mb-3">
<label class="form-label">Title</label>
<input type="text" name="title" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Artist</label>
<input type="text" name="artist" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Venue</label>
<input type="text" name="venue" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Date</label>
<input type="date" name="date" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Time</label>
<input type="time" name="time" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">VIP Price</label>
<input type="number" step="0.01" name="vip_price" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Regular Price</label>
<input type="number" step="0.01" name="regular_price" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Banner Image</label>
<input type="file" name="banner" class="form-control" accept="image/*" required>
</div>

<button type="submit" class="btn btn-primary w-100">Add Concert</button>
</form>
</div>
</div>
</body>
</html>
