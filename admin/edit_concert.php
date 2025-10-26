<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
$role = strtolower(trim((string)($_SESSION['role'] ?? '')));
if (!in_array($role, ['admin', 'administrator', '1', 'superadmin'], true)) {
    header('Location: ../user/index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM concerts WHERE id = ?");
$stmt->execute([$id]);
$concert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$concert) {
    die("Concert not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $artist = $_POST['artist'] ?? '';
    $venue = $_POST['venue'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';

    $stmt = $conn->prepare("UPDATE concerts SET title=?, artist=?, venue=?, date=?, time=? WHERE id=?");
    $stmt->execute([$title, $artist, $venue, $date, $time, $id]);

    $_SESSION['success_message'] = "Concert updated successfully.";
    header('Location: view_concerts.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Concert</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container mt-5">
    <h2>Edit Concert</h2>
    <form method="post">
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($concert['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Artist</label>
            <input type="text" name="artist" class="form-control" value="<?= htmlspecialchars($concert['artist']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Venue</label>
            <input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($concert['venue']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($concert['date']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Time</label>
            <input type="time" name="time" class="form-control" value="<?= htmlspecialchars($concert['time']); ?>" required>
        </div>
        <button class="btn btn-primary">Update Concert</button>
        <a href="view_concerts.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
