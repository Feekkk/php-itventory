<?php
// Determine the correct path to public folder and CSS based on where footer is included
// Check if the calling script is in a subdirectory
$script_path = $_SERVER['PHP_SELF'] ?? '';
$is_subdirectory = (strpos($script_path, '/technician/') !== false);
$footer_base_path = $is_subdirectory ? '../public/' : 'public/';
$footer_css_path = $is_subdirectory ? '../css/footer.css' : 'css/footer.css';

// Include footer CSS (browsers handle duplicate stylesheet loads gracefully)
// Link tag works in body in modern browsers
echo '<link rel="stylesheet" href="' . htmlspecialchars($footer_css_path) . '">' . "\n";
?>
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="<?php echo $footer_base_path; ?>rcmp-white.png" alt="Royal College of Medicine Perak crest">
            </div>
            <div class="footer-tagline">
                <p class="footer-title">UNIKL Royal College of Medicine Perak</p>
                <p class="footer-subtitle">Information Technology (IT) Department</p>
            </div>
        </div>
        <div class="footer-links">
            <div>
                <h4>Explore</h4>
                <ul>
                    <li><a href="#">Browse Equipment</a></li>
                    <li><a href="#services">Services Provided</a></li>
                    <li><a href="#">Help Center</a></li>
                </ul>
            </div>
            <div>
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Submit Ticket</a></li>
                    <li><a href="#">Maintenance Schedule</a></li>
                    <li><a href="#">Policy &amp; Compliance</a></li>
                </ul>
            </div>
            <div>
                <h4>Contact</h4>
                <ul>
                    <li><a href="mailto:itventory@unikl.edu.my">itventory@unikl.edu.my</a></li>
                    <li><a href="tel:+6052497900">+60 5-249 7900</a></li>
                    <li><a href="#">RCMP Helpdesk Portal</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?php echo date('Y'); ?> Royal College of Medicine Perak. All rights reserved.</p>
        <div class="footer-social">
            <a href="#" aria-label="LinkedIn">LinkedIn</a>
            <span aria-hidden="true">•</span>
            <a href="#" aria-label="Twitter">Twitter</a>
            <span aria-hidden="true">•</span>
            <a href="#" aria-label="Facebook">Facebook</a>
        </div>
    </div>
</footer>
