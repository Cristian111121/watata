<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please provide both email and password.";
    } else {
        try {
            if (!isset($conn) || !$conn instanceof PDO) {
                throw new Exception("Database not connected properly.");
            }

            $stmt = $conn->prepare("SELECT id, name, email, password, role 
                                    FROM users 
                                    WHERE email = :email 
                                    LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = "Invalid email or password!";
            } else {
                $stored = (string) $user['password'];
                $passwordVerified = false;

                if (password_verify($password, $stored)) {
                    $passwordVerified = true;

                    if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $upd = $conn->prepare("UPDATE users SET password = :newHash WHERE id = :id");
                        $upd->execute([':newHash' => $newHash, ':id' => $user['id']]);
                    }
                }

                if ($passwordVerified) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = strtolower(trim($user['role']));

                    if (in_array($_SESSION['role'], ['admin', 'administrator', 'superadmin', '1'], true)) {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: user/index.php");
                    }
                    exit;
                } else {
                    $error = "Invalid email or password!";
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Internal error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AMP'D Ticketing - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: white;
            min-height: 100vh;
        }
        .login-box {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 40px;
            margin-top: 10%;
            box-shadow: 0 0 20px #6a11cb;
        }
        .btn-custom {
            background: #6a11cb;
            border: none;
        }
        .btn-custom:hover {
            background: #2575fc;
        }
        video#bg-video {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
        }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center">
    <div class="col-md-4 login-box">
        <h2 class="text-center mb-4">Welcome Back</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-custom w-100 text-white">Login</button>
        </form>
        <p class="text-center mt-3">Don't have an account? <a href="register.php" class="text-info">Register</a></p>
    </div>
</div>
<video autoplay muted loop id="bg-video">
    <source src="../videos/3.mov" type="video/mp4">
</video>
</body>
</html>
