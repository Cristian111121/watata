<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Admin check
$role = strtolower(trim((string)($_SESSION['role'] ?? '')));
if (!in_array($role, ['admin', 'administrator', '1', 'superadmin'], true)) {
    header('Location: ../user/index.php');
    exit;
}

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $concertId = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM concerts WHERE id = ?");
    $stmt->execute([$concertId]);
    $_SESSION['success_message'] = "Concert deleted successfully.";
    header('Location: view_concerts.php');
    exit;
}

// Fetch all concerts
$stmt = $conn->query("SELECT * FROM concerts ORDER BY date ASC");
$concerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Concerts</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
        color: white;
        min-height: 100vh;
    }
    .panel {
        background: rgba(255,255,255,0.06);
        border-radius: 15px;
        padding: 30px;
        margin-top: 6%;
        box-shadow: 0 0 20px rgba(106,17,203,0.25);
    }
    a.btn-outline-light {
        border-color: rgba(255,255,255,0.2);
        color: #fff;
    }
    a.btn-outline-light:hover {
        background: rgba(255,255,255,0.03);
    }
    a.btn-danger {
        border-color: rgba(255,0,0,0.5);
    }
    table {
        color: #fff;
    }
</style>
</head>
<body class="bg-dark text-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-transparent px-4">
  <a class="navbar-brand" href="dashboard.php">🎤 Admin Panel</a>
  <div class="ms-auto">
    <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-10 panel">
      <h3 class="mb-4 text-center">All Concerts</h3>

      <?php if(!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
      <?php endif; ?>

      <table class="table table-dark table-striped text-center">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Artist</th>
            <th>Venue</th>
            <th>Date</th>
            <th>Time</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($concerts)): ?>
            <?php foreach ($concerts as $concert): ?>
              <tr>
                <td><?= htmlspecialchars($concert['id']); ?></td>
                <td><?= htmlspecialchars($concert['title']); ?></td>
                <td><?= htmlspecialchars($concert['artist']); ?></td>
                <td><?= htmlspecialchars($concert['venue']); ?></td>
                <td><?= htmlspecialchars($concert['date']); ?></td>
                <td><?= htmlspecialchars($concert['time']); ?></td>
                <td>
                  <a href="edit_concert.php?id=<?= $concert['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                  <a href="view_concerts.php?delete=<?= $concert['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this concert?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7">No concerts found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-outline-light">Back to Dashboard</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
