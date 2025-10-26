<?php
include('../includes/db.php');
include('../includes/auth.php');
requireAdmin();

// Join tables to show detailed ticket info
$stmt = $pdo->query("
  SELECT t.id, u.name AS buyer, c.title AS concert, t.seat_type, t.quantity, t.total, t.payment_method, t.status, t.created_at
  FROM tickets t
  JOIN users u ON t.user_id = u.id
  JOIN concerts c ON t.concert_id = c.id
  ORDER BY t.created_at DESC
");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Tickets - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container mt-5">
  <h3 class="mb-4">🎫 Sold Tickets</h3>
  <table class="table table-dark table-striped table-hover">
    <thead>
      <tr>
        <th>ID</th>
        <th>Buyer</th>
        <th>Concert</th>
        <th>Seat Type</th>
        <th>Qty</th>
        <th>Total (₱)</th>
        <th>Payment</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tickets as $t): ?>
      <tr>
        <td><?php echo $t['id']; ?></td>
        <td><?php echo htmlspecialchars($t['buyer']); ?></td>
        <td><?php echo htmlspecialchars($t['concert']); ?></td
