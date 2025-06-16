<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'User' : '';

$page_title = isset($page_title) ? $page_title : "TeyZee Visas - India's Most Loved Visa Platform";
$page_description = isset($page_description) ? $page_description : "TeyZee Visas - Your trusted partner for visa services";
$additional_css = isset($additional_css) ? $additional_css : "";
$additional_js = isset($additional_js) ? $additional_js : "";
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <link rel="stylesheet" href="https://www.teyzeevisas.com/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Additional CSS files if needed -->
   <?php if (!empty($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?> 
    
    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i {
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
    </html>
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
                        <a href="/php/dashboard.php" class="dashboard-btn"><i class="fas fa-user"></i> Dashboard</a>
                        <a href="/php/logout.php" class="contact"><i class="fas fa-user-circle"></i> Logout</a>
                    </div>
                <?php else: ?>
                    <!-- User is not logged in - show login button -->
                    <a href="https://www.teyzeevisas.com/payments/login.php" class="contact"><i class="fas fa-user-circle"></i> Login</a>
                <?php endif; ?>
                <a href="https://wa.me/919029027420" class="contact"><i class="fab fa-whatsapp"></i> Chat with us</a>
                <a href="tel:919029027420" class="contact"><i class="fas fa-phone"></i> Call Us</a>
            </div>
        </div>
    </header>