<?php
// Start session before any output
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// No output before headers!
require_once 'database.php';
require_once 'user.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        // Validate
        if (empty($email) || empty($password)) {
            $error = 'Email and password are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format';
        } else {
            $result = $user->login($email, $password);
            if ($result['success']) {
                // Store session token for tracking
                $_SESSION['session_token'] = $result['session_token'];
                $_SESSION['user_id'] = $result['user']['id'];
                // Optionally, store the user email/name for later use
                $_SESSION['user_email'] = $result['user']['email'] ?? '';
                // Redirect to payment
                header("Location: https://www.teyzeevisas.com/payments/payment.php");
                exit();
            } else {
                $error = $result['message'];
            }
        }
    }
    if ($_POST['action'] === 'register') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        // Optionally add more fields and collect full_name, phone, address here.
        $result = $user->register($email, $password);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
// Output HTML below, but remember: nothing before header() above!
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | TeyzeeVisas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
        }
        .container {
            max-width: 400px;
            background: white;
            margin: 60px auto 0 auto;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-weight: bold; }
        input { width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd; }
        button { width: 100%; padding: 12px; background: #007cba; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background: #005a87; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;}
    </style>
</head>
<body>
<div class="container">
    <h1 style="text-align:center; color:#007cba;">Teyzee Visas</h1>
    <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="action" value="login">
        <div class="form-group">
            <label for="email">Email</label>
            <input name="email" id="email" type="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input name="password" id="password" type="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <hr>
    <form method="post">
        <input type="hidden" name="action" value="register">
        <div class="form-group">
            <label for="reg-email">Email</label>
            <input name="email" id="reg-email" type="email" required>
        </div>
        <div class="form-group">
            <label for="reg-password">Password</label>
            <input name="password" id="reg-password" type="password" minlength="6" required>
        </div>
        <button type="submit">Register</button>
    </form>
    <p style="text-align:center; margin-top:20px;">
        <a href="index.php">&larr; Back to Home</a>
    </p>
</div>
</body>
</html>
