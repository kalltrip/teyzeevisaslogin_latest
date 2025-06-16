<?php include 'header.php'; ?>

<?php
// Start the session
session_start();

// Check if user is already logged in and has a valid session
if (isset($_SESSION['user']) && isset($_SESSION['session_token'])) {
    $email = $_SESSION['user'];
    $sessionToken = $_SESSION['session_token'];
    
    // If using database-based authentication, verify the session
    try {
        require_once 'database.php';
        require_once 'user.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        
        // Verify session is still valid
        $sessionData = $user->validateSession($sessionToken);
        if ($sessionData && $sessionData['email'] === $email) {
            // User is logged in with valid session, redirect to payment
            header('Location: payment.php');
            exit;
        } else {
            // Invalid session, clear it
            session_destroy();
            session_start();
        }
    } catch (Exception $e) {
        // Error checking session, clear it to be safe
        session_destroy();
        session_start();
    }
}

// Handle success messages from login redirects
$loginSuccess = isset($_GET['login']) && $_GET['login'] === 'success';
$registrationSuccess = isset($_GET['registration']) && $_GET['registration'] === 'success';
?>

<main>
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="left">
                    <h3>TeyZee Visas, The Fast visa platform ❤️</h3>
                    <h2>From application to approval,<br><span class="highlight">TeyZee Visas</span> makes visa applications simple and successful</h2>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <div class="rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
            </div>
            <h2>Best Visa Assistance in India</h2>

            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="user-info">
                            <h4>Rahul Shetty</h4>
                        </div>
                    </div>
                    <p>I was recommended to them by their long time corporate client after my Schengen visa was rejected twice. They took their time, requested additional items, and then I GOT MY VISA.</p>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="user-info">
                            <h4>Manish Kapoor</h4>
                        </div>
                    </div>
                    <p>They are well known in corporate circles and I got to them through a reference. Quick, efficient and they suggested an alternate country for Schengen application as I had to travel quickly.</p>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="user-info">
                            <h4>Kshitij Parikh</h4>
                        </div>
                    </div>
                    <p>Didn't want the hassle of visa issues so went with them on a personal recommendation. They are well known for almost 100% visa success.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-destinations">
        <div class="container">
            <div class="destinations-grid">
                <div class="destination-card">
                    <div class="destination-image">
                        <img src="https://www.teyzeevisas.com/VisaImages/destinations/france.jpg" alt="France" onerror="this.onerror=null; this.src='path/to/default-image.jpg';">
                        <div class="stats-badge">
                            <i class="fas fa-bolt"></i> Get Visa in 30 days
                        </div>
                    </div>
                    <div class="destination-info">
                        <div class="order">
                            <h3>France</h3>
                            <button type="button" class="link-btn" onclick="window.location.href='../html/france.html';">Get Visa</button>
                        </div>
                        <div class="visa-type">Sticker</div>
                        <div class="price-info">
                            <div class="price">₹8500</div>
                            <div class="tax">+₹3500 (Fees+Tax 18%)</div>
                            <div class="details">
                                <div class="visa-time">
                                    <span>52 issued in past few weeks</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Repeat similar structure for other destination cards -->
                <!-- Example for Italy -->
                <div class="destination-card">
                    <div class="destination-image">
                        <img src="https://www.teyzeevisas.com/VisaImages/destinations/italy.jpg" alt="Italy" onerror="this.onerror=null; this.src='path/to/default-image.jpg';">
                        <div class="stats-badge">
                            <i class="fas fa-bolt"></i> Get Visa in 30 days
                        </div>
                    </div>
                    <div class="destination-info">
                        <div class="order">
                            <h3>Italy</h3>
                            <button type="button" class="link-btn" onclick="window.location.href='../html/italy.html';">Get Visa</button>
                        </div>
                        <div class="visa-type">Sticker</div>
                        <div class="price-info">
                            <div class="price">₹8500</div>
                            <div class="tax">+₹3500 (Fees+Tax 18%)</div>
                            <div class="details">
                                <div class="visa-time">
                                    <span>97 issued in past few weeks</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add more destination cards as needed -->
            </div>
        </div>
    </section>

    <section class="visa-process">
        <div class="container">
            <h2>Expert Application With TeyZee Visas</h2>
            <h3>4 Simple Steps to Apply for Your Visa</h3>

            <div class="process-container">
                <div class="process-image">
                    <img src="https://www.teyzeevisas.com/VisaImages/destinations/contact-form.png" alt="Application Form">
                </div>
                <div class="process-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Upload Passport, Front & Back</h4>
                            <p>Clear Colour Scan of Passport first page and last page</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Upload Bank Statement (Min 1 lac closing balance)</h4>
                            <p>Last 3 months bank statement on pdf or excel format (bank original)</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Pay Rs 500 ON UPI for evaluation. <br>On Go ahead, upload all documents to Dropbox</h4>
                            <p>Documentation check in 12 hours</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Expert Double Check for a Perfect Application</h4>
                            <p>Sit Back as We Deliver Your Visa on Time. Your Worry-Free Journey Begins!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="visa-eligibility">
        <div class="container eligibility-container">
            <div class="eligibility-content">
                <h2>Check your Visa Eligibility for Rs 500 only</h2>
                <p>Upload your basic documents now.</p>
                <button class="check-btn">CHECK NOW</button>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3>Address</h3>
                <div class="address">
                    <p><i class="fas fa-map-marker-alt"></i> A-302, RG City Centre, DB Gupta Road, Delhi 110011</p>
                </div>
                <div class="address">
                    <p><i class="fas fa-map-marker-alt"></i> Mumbai - WeWSork Platinum, Marol, Mumbai, Maharashtra, 400059</p>
                </div>
                <a href="https://wa.me/919029027420" class="whatsapp-button"><i class="fab fa-whatsapp"></i> Chat with us</a>
            </div>

            <div class="footer-col">
                <h3>About us</h3>
                <ul>
                    <li><a href="mailto:business.tours@kalltrip.com">Email us</a></li>
                    <li><a href="#">Blogs</a></li>
                    <li><a href="/html/privacy.html">Privacy Policy</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>Support</h3>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Refund Policy</a></li>
                    <li><a href="/html/privacy.html">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Social</h3>
                <ul>
                    <li><a href="https://www.facebook.com/profile.php?id=61575094024472"><i class="fab fa-facebook"></i> Facebook</a></li>
                    <li>
                        <a href="https://x.com/TeyzeeVisas" target="_blank" style="text-decoration: none; display: inline-flex; align-items: center;">
                            <span style="font-weight: bold; font-size: 10px; background-color: rgb(255, 255, 255); color: rgb(160, 151, 151); padding: 0px 4px; border-radius: 8px;">𝕏</span>
                            <span style="margin-left: 6px; color: rgb(255, 255, 255);">X</span>
                        </a>
                    </li>
                    <li><a href="https://www.instagram.com/teyzee_visas/"><i class="fab fa-instagram"></i> Instagram</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-countries">
            <h3>Read more about visas</h3>
            <div class="country-links">
                <a href="https://www.teyzeevisas.com/html/france.html">France</a> •
                <a href="/html/italy_customer.html">Italy</a> •
                <a href="/html/germany_customer.html">Germany</a> •
                <a href="/html/switzerland.html">Switzerland</a> •
                <a href="/html/greece_customer.html">Greece</a> •
                <a href="/html/singapore_customer.html">Singapore</a> •
                <a href="/html/turkey_customer.html">Turkey</a> •
                <a href="/html/china_customer.html">China</a> •
                <a href="/html/russia_customer.html">Russia</a> •
                <a href="/html/united_arab_emirates.html">United Arab Emirates</a> •
                <a href="/html/indonesia_customer.html">Indonesia</a> •
                <a href="/html/vietnam_customer.html">Vietnam</a> •
                <a href="/html/azerbaijan_customer.html">Azerbaijan</a> •
                <a href="/html/united_kingdom.html">United Kingdom</a> •
                <a href="/html/spain_customer.html">Spain</a> •
                <a href="/html/south_korea_customer.html">South Korea</a> •
                <a href="/html/georgia_customer.html">Georgia</a> •
                <a href="/html/hungary_customer.html">Hungary</a> •
                <a href="/html/finland_customer.html">Finland</a> •
                <a href="/html/norway_customer.html">Norway</a> •
                <a href="/html/egypt_customer.html">Egypt</a> •
                <a href="/html/oman_customer.html">Oman</a> •
                <a href="/html/sweden_customer.html">Sweden</a> •
                <a href="/html/austria_customer.html">Austria</a> •
                <a href="/html/denmark_customer.html">Denmark</a> •
                <a href="/html/uzbekistan_customer.html">Uzbekistan</a> •
                <a href="/html/cambodia_customer.html">Cambodia</a> •
                <a href="/html/morocco_customer.html">Morocco</a> •
                <a href="/html/netherlands_customer.html">Netherlands</a> •
                <a href="/html/philippines_customer.html">Philippines</a> •
                <a href="/html/brazil_customer.html">Brazil</a> •
                <a href="/html/saudi_arabia_customer.html">Saudi Arabia</a> •
                <a href="/html/kenya_customer.html">Kenya</a> •
                <a href="/html/portugal_customer.html">Portugal</a> •
                <a href="/html/belgium_customer.html">Belgium</a> •
                <a href="/html/croatia_customer.html">Croatia</a> •
                <a href="/html/lithuania_customer.html">Lithuania</a> •
                <a href="/html/ireland_customer.html">Ireland</a> •
                <a href="/html/luxembourg_customer.html">Luxembourg</a> •
                <a href="/html/Hong Kong_customer.html">Hong Kong</a> •
                <a href="/html/malaysia_customer.html">Malaysia</a> •
                <a href="/html/japan_customer.html">Japan</a> •
                <a href="/html/bahrain_customer.html">Bahrain</a> •
                <a href="/html/czech_republic_customer.html">Czech Republic</a> •
                <a href="/html/romania_customer.html">Romania</a> •
                <a href="/html/bulgaria_customer.html">Bulgaria</a> •
                <a href="/html/slovakia_customer.html">Slovakia</a> •
                <a href="/html/latvia_customer.html">Latvia</a> •
                <a href="/html/estonia_customer.html">Estonia</a>
            </div>
        </div>
        <div class="copyright">
            <p>© 2025 TeyZee Visas. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<script src="/link.js"></script>
<script src="/scripts.js"></script>
</body>
</html>
