
<?php
include('../includes/db.php');
include('../includes/auth.php');

if (!isset($_GET['id'])) die("Concert ID is missing.");
$concertId = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM concerts WHERE id = ?");
$stmt->execute([$concertId]);
$concert = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$concert) die("Concert not found.");

// Prices for seat types
$vipPrice = 2000;
$regularPrice = 1000;

// Fetch seats
$stmt = $conn->prepare("SELECT * FROM seats WHERE concert_id=? ORDER BY seat_number ASC");
$stmt->execute([$concertId]);
$seats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separate VIP and Regular
$vipSeats = array_filter($seats, fn($s) => $s['seat_type'] === 'VIP');
$regularSeats = array_filter($seats, fn($s) => $s['seat_type'] === 'Regular');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($concert['title']); ?> - Seat Map</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<style>
.seat {
    width: 40px; height: 40px; margin: 5px; border-radius: 5px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.8rem; cursor: pointer; color: #fff;
    transition: transform 0.2s;
}
.seat.available { background: #4CAF50; }
.seat.booked    { background: #DC3545; cursor: not-allowed; }
.seat.vip { border: 2px solid gold; }
.seat.selected { outline: 3px solid #FFD700; }
.seat:hover { transform: scale(1.1); }
.legend span { display: inline-block; width: 20px; height: 20px; margin-right: 5px; vertical-align: middle; }
</style>
</head>
<body class="bg-dark text-light p-4">
<div class="container">
<h2><?= htmlspecialchars($concert['title']); ?> - Seat Map</h2>
<p><strong>Date:</strong> <?= date("F j, Y", strtotime($concert['date'])); ?> | <strong>Time:</strong> <?= date("g:i A", strtotime($concert['time'])); ?></p>

<h4>VIP Seats (₱<?= number_format($vipPrice); ?>)</h4>
<div class="d-flex flex-wrap mb-4">
<?php foreach($vipSeats as $seat): ?>
    <div class="seat vip <?= strtolower($seat['status']); ?>" 
         data-seat-id="<?= $seat['id']; ?>" 
         data-status="<?= $seat['status']; ?>"
         data-price="<?= $vipPrice ?>"
         title="Seat <?= $seat['seat_number'] ?> - ₱<?= $vipPrice ?> - <?= $seat['status']; ?>">
         <?= $seat['seat_number']; ?>
    </div>
<?php endforeach; ?>
</div>

<h4>Regular Seats (₱<?= number_format($regularPrice); ?>)</h4>
<div class="d-flex flex-wrap mb-4">
<?php foreach($regularSeats as $seat): ?>
    <div class="seat <?= strtolower($seat['status']); ?>" 
         data-seat-id="<?= $seat['id']; ?>" 
         data-status="<?= $seat['status']; ?>"
         data-price="<?= $regularPrice ?>"
         title="Seat <?= $seat['seat_number'] ?> - ₱<?= $regularPrice ?> - <?= $seat['status']; ?>">
         <?= $seat['seat_number']; ?>
    </div>
<?php endforeach; ?>
</div>

<div class="mb-3">
    <p>Total Selected Seats: <span id="totalSeats">0</span></p>
    <p>Total Price: ₱<span id="totalPrice">0</span></p>
    <button id="checkoutBtn" class="btn btn-primary">Checkout</button>
</div>

<div class="legend mb-4">
    <span class="seat available"></span> Available
    <span class="seat booked ms-3"></span> Booked
    <span class="seat vip ms-3" style="border:2px solid gold;"></span> VIP
    <span class="seat selected ms-3"></span> Selected
</div>

<a href="concerts.php" class="btn btn-outline-light">Back to Concerts</a>
</div>

<script>
$(document).ready(function(){
    let totalSeats = 0, totalPrice = 0;

    function updateTotals(){
        $('#totalSeats').text(totalSeats);
        $('#totalPrice').text(totalPrice);
    }

    $('.seat.available').click(function(){
        const price = parseInt($(this).data('price'));
        if($(this).hasClass('selected')){
            $(this).removeClass('selected');
            totalSeats--; totalPrice -= price;
        } else {
            $(this).addClass('selected');
            totalSeats++; totalPrice += price;
        }
        updateTotals();
    });

    $('#checkoutBtn').click(function(){
        let selected = [];
        $('.seat.selected').each(function(){ selected.push($(this).data('seat-id')); });

        if(selected.length === 0){ alert("Select at least one seat."); return; }

        $.ajax({
            url: 'reserve_seats.php',
            type: 'POST',
            data: { seats: selected },
            success: function(response){
                alert(response.message);
                if(response.success){
                    response.reserved.forEach(function(id){
                        let seatDiv = $('.seat[data-seat-id="'+id+'"]');
                        seatDiv.removeClass('available selected').addClass('booked');
                    });
                    totalSeats = 0; totalPrice = 0;
                    updateTotals();
                }
            },
            error: function(){ alert("An error occurred. Try again."); }
        });
    });
});
</script>
</body>
</html>
