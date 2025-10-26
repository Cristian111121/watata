<?php
include('../includes/auth.php');
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QR Scanner - Concert Ticketing</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-dark text-light">
<div class="container text-center mt-5">
  <h3 class="mb-3">🎥 Scan Ticket QR Code</h3>
  <div id="reader" style="width:400px; margin:auto;"></div>
  <div id="result" class="mt-4 fs-5"></div>
  <a href="dashboard.php" class="btn btn-outline-light mt-4">Back to Dashboard</a>
</div>

<script>
function onScanSuccess(decodedText) {
  document.getElementById('result').innerHTML = `
    ✅ Ticket Verified! <br>
    <small>${decodedText}</small>`;
}
function onScanError(errorMessage) {
  console.warn(errorMessage);
}
new Html5Qrcode("reader").start(
  { facingMode: "environment" },
  { fps: 10, qrbox: 250 },
  onScanSuccess,
  onScanError
);
</script>
</body>
</html>
