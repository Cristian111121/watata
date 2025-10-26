<?php
session_start();
include('../includes/db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['seats'])) {
    echo json_encode(['success' => false, 'message' => 'No seats selected.']);
    exit;
}

$seats = $_POST['seats'];
$reserved = [];

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("UPDATE seats SET status='booked' WHERE id=? AND status='available'");

    foreach($seats as $seatId){
        $stmt->execute([$seatId]);
        if($stmt->rowCount() > 0){
            $reserved[] = $seatId;
        }
    }

    $conn->commit();

    // Store reserved seats in session for confirmation
    $_SESSION['reserved_seats'] = $reserved;

    echo json_encode([
        'success' => true,
        'message' => count($reserved) . " seat(s) reserved successfully!",
        'reserved' => $reserved
    ]);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
