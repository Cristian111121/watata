<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['name'])) {
    header('Location: ../login.php');
    exit();
}

$username = htmlspecialchars($_SESSION['name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AMP'D Concerts</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

body, html {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    height: 100%;
    background: url('12.jpg') no-repeat center center fixed; 
    background-size: cover;
    color: #00e5ff;
    overflow-x: hidden;
    position: relative;
}

 body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: -1;
}

.navbar {
    background: rgba(28,28,46,0.8);
    backdrop-filter: blur(10px);
    padding: 15px 30px;
    border-bottom: 1px solid #00e5ff;
    z-index: 10;
    position: relative;
}

.navbar-brand { color: #ff00ff; font-weight: 700; font-size: 1.5rem; }
.navbar a { color: #00e5ff; margin-right: 20px; font-weight: 500; transition: 0.2s; text-shadow: 0 0 5px #00e5ff; }
.navbar a:hover { color: #ff00ff; text-shadow: 0 0 10px #ff00ff; }
.username { color: #00ff99; font-weight: 500; text-shadow: 0 0 5px #00ff99; }

/* Hero Section */
.hero {
    height: 90vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: #ff00ff;
    padding: 0 20px;
    text-shadow: 0 0 10px #ff00ff;
    z-index: 5;
    position: relative;
}
.hero h1 { font-size: 3rem; margin-bottom: 20px; }
.hero p { font-size: 1.2rem; margin-bottom: 30px; }
.hero .btn-primary {
    padding: 10px 25px;
    font-size: 1.1rem;
    background: #ff00ff;
    color: #121212;
    box-shadow: 0 0 10px #ff00ff;
}
.hero .btn-primary:hover { background: #ff33ff; box-shadow: 0 0 20px #ff33ff; }

/* Sections */
section { padding: 60px 0; position: relative; z-index: 5; }
section h2, section h3 { color: #00e5ff; text-shadow: 0 0 5px #00e5ff; }
section p { color: #99fffc; }

 .card {
    background: rgba(28,28,46,0.75);
    border: 1px solid #00e5ff;
    border-radius: 12px;
    color: #00e5ff;
    box-shadow: 0 0 10px #00e5ff;
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: scale(1.03);
    box-shadow: 0 0 20px #ff00ff;
}
.btn-primary { background: #ff00ff; border: none; color: #121212; font-weight: 600; box-shadow: 0 0 10px #ff00ff; }
.btn-primary:hover { background: #ff33ff; box-shadow: 0 0 20px #ff33ff; }

.concert-img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; }

@media(max-width:768px){
    .hero h1 { font-size: 2rem; }
    .hero p { font-size: 1rem; }
    .navbar a { font-size: 14px; margin-right: 10px; }
}
</style>
</head>
<body>
 <nav class="navbar fixed-top d-flex justify-content-between">
    <div class="d-flex align-items-center">
        <span class="navbar-brand">AMP'D</span>
        <a href="#">Home</a>
        <a href="#about">About Us</a>
        <a href="#concerts">Concerts</a>
        <a href="my_tickets.php">My Tickets</a>
        <a href="payment.php">Payment</a>
    </div>
    <div class="d-flex align-items-center">
        <span class="username">Welcome, <?php echo $username; ?></span>
        <a href="../logout.php" class="ms-3 text-danger">Logout</a>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <h1>Feel the Music Live</h1>
    <p>Discover and book your favorite concerts instantly with AMP'D</p>
    <a href="#concerts" class="btn btn-primary">Book Tickets Now</a>
</section>

 <section id="about" class="container text-center">
    <h2>About Us</h2>
    <p>AMP'D brings you closer to live music experiences with seamless ticket booking and exclusive concert info. Join thousands of music lovers!</p>
</section>

<!-- Upcoming Concerts Section -->
<section id="concerts" class="container">
    <h3 class="text-center mb-4">Upcoming Concerts</h3>
    <div class="row g-4">
        <?php
        try {
            $stmt = $conn->query("SELECT * FROM concerts ORDER BY date ASC, time ASC LIMIT 6");
            if ($stmt->rowCount() > 0) {
                while ($concert = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $imgPath = "../uploads/" . htmlspecialchars($concert['banner']);
                    $date = date("F j, Y", strtotime($concert['date']));
                    $time = date("g:i A", strtotime($concert['time']));
                    echo '
                    <div class="col-md-4 col-sm-6">
                        <div class="card p-2">
                            <img src="'.$imgPath.'" class="concert-img mb-2" alt="Concert Banner">
                            <div class="p-2">
                                <h5 class="mb-1">'.htmlspecialchars($concert['title']).'</h5>
                                <p class="text-muted mb-1">'.htmlspecialchars($concert['artist']).'</p>
                                <p class="mb-1"><i class="bi bi-geo-alt"></i> '.htmlspecialchars($concert['venue']).'</p>
                                <p class="mb-1"><i class="bi bi-calendar-event"></i> '.$date.' at '.$time.'</p>
                                <a href="payment.php?id='.$concert['id'].'" class="btn btn-sm btn-primary w-100">Book Now</a>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="text-center text-light">No concerts available right now.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="text-danger text-center">Error loading concerts.</p>';
        }
        ?>
    </div>
</section>

</body>
</html>
