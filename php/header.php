<?php
// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: show errors in dev
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Login check
if (!isset($_SESSION['user']) || !isset($_SESSION['session_token'])) {
    header("Location: ../php/login.php");
    exit;
}


// Mark user as logged in based on session
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'User') : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>teyzeevisas.com</title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <link rel="stylesheet" href="https://www.teyzeevisas.com/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Additional CSS files if needed -->
    <?php if (!empty($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
    
    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
        var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-TZGFW4FB');</script>
    <!-- End Google Tag Manager -->
    
    <!-- Additional JavaScript if needed -->
    <?php if (!empty($additional_js)): ?>
        <?php echo $additional_js; ?>
    <?php endif; ?>
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TZGFW4FB" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    
    <header>
        <div class="container header-container">
            <div class="logo">
                <a href="/">
                    <img src="https://www.teyzeevisas.com/VisaImages/destinations/Teyzee_logo_240w_500h.jpeg" alt="TeyZee Visas Logo">
                </a>
            </div>
            <div class="header-actions">
                <?php if ($isLoggedIn): ?>
                    <!-- User is logged in - show user menu -->
                    <div class="user-menu">
                        <span class="user-greeting">Welcome, <?php echo htmlspecialchars($userName); ?>!</span>
                        <a href="https://www.teyzeevisas.com/php/dashboard.php" class="dashboard-btn"><i class="fas fa-user"></i> Dashboard</a>
                        <a href="https://www.teyzeevisas.com/php/logout.php" class="contact"><i class="fas fa-user-circle"></i> Logout</a>
                    </div>
                <?php else: ?>
                    <!-- User is not logged in - redirect to login page -->
                    <a href="login.php" class="contact"><i class="fas fa-user-circle"></i> Login</a>
                    <a href="login.php" class="contact"><i class="fas fa-user-circle"></i> Sign Up</a>
                <?php endif; ?>
                <a href="https://wa.me/919029027420" class="contact"><i class="fab fa-whatsapp"></i> Chat with us</a>
                <a href="tel:919029027420" class="contact"><i class="fas fa-phone"></i> Call Us</a>
            </div>
        </div>
    </header>

    <!-- Display error message if any -->
    <?php if (!empty($error_message)): ?>
        <div class="error-message" style="margin: 10px; padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>