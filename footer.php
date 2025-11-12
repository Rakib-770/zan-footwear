<?php
// Read and decode the JSON data
$contactData = json_decode(file_get_contents('data/contact.json'), true);
$socialMedia = $contactData['socialMedia'];
$mapIframe = $contactData['map']['iframe'];
$contactInfo = $contactData['contactInfo'];
?>

<!-- Start Footer Section -->
<footer class="custom-footer-section">
  <div class="custom-footer-container">
    <!-- Main Footer Content -->
    <div class="custom-footer-content">
      <!-- Logo and Description -->
      <div class="custom-footer-brand">
        <div class="custom-footer-logo-wrapper">
          <a href="index.php" class="custom-footer-logo">
            <img src="images/logo/white-logo.png" alt="Zan Logo" class="custom-logo-default">
            <img src="images/logo/logo.png" alt="Zan Logo Hover" class="custom-logo-hover">
          </a>
        </div>
        <p class="custom-footer-description">Creating innovative solutions for tomorrow's challenges. Join us in building a better future.</p>

        <!-- Social Media -->
        <ul class="custom-footer-social">
          <li>
            <a href="<?php echo $socialMedia['facebook']['url']; ?>" target="_blank" class="custom-social-link">
              <span class="custom-social-icon">
                <i class="fab fa-facebook-f"></i>
              </span>
              <span class="custom-social-tooltip">Facebook</span>
            </a>
          </li>
          <li>
            <a href="<?php echo $socialMedia['twitter']['url']; ?>" target="_blank" class="custom-social-link">
              <span class="custom-social-icon">
                <i class="fab fa-twitter"></i>
              </span>
              <span class="custom-social-tooltip">Twitter</span>
            </a>
          </li>
          <li>
            <a href="<?php echo $socialMedia['instagram']['url']; ?>" target="_blank" class="custom-social-link">
              <span class="custom-social-icon">
                <i class="fab fa-instagram"></i>
              </span>
              <span class="custom-social-tooltip">Instagram</span>
            </a>
          </li>
          <li>
            <a href="<?php echo $socialMedia['linkedin']['url']; ?>" target="_blank" class="custom-social-link">
              <span class="custom-social-icon">
                <i class="fab fa-linkedin-in"></i>
              </span>
              <span class="custom-social-tooltip">LinkedIn</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Quick Links -->
      <div class="custom-footer-links">
        <h3 class="custom-footer-heading">Quick Links</h3>
        <ul class="custom-footer-list">
          <li><a href="#" class="custom-footer-link">Home</a></li>
          <li><a href="#" class="custom-footer-link">About us</a></li>
          <li><a href="#" class="custom-footer-link">Products</a></li>
          <li><a href="#" class="custom-footer-link">Services</a></li>
          <li><a href="#" class="custom-footer-link">Operation</a></li>
        </ul>
      </div>

      <!-- Contact Information -->
      <div class="custom-footer-contact-info">
        <h3 class="custom-footer-heading">Contact Info</h3>
        <div class="custom-contact-details">
          <div class="custom-contact-item">
            <i class="<?php echo htmlspecialchars($contactInfo['address']['icon']); ?>"></i>
            <div>
              <span class="custom-contact-label">Address</span>
              <span class="custom-contact-value"><?php echo htmlspecialchars($contactInfo['address']['text']); ?></span>
            </div>
          </div>
          <div class="custom-contact-item">
            <i class="<?php echo htmlspecialchars($contactInfo['phone']['icon']); ?>"></i>
            <div>
              <span class="custom-contact-label">Phone</span>
              <span class="custom-contact-value"><?php echo htmlspecialchars($contactInfo['phone']['text']); ?></span>
            </div>
          </div>
          <div class="custom-contact-item">
            <i class="<?php echo htmlspecialchars($contactInfo['email']['icon']); ?>"></i>
            <div>
              <span class="custom-contact-label">Email</span>
              <span class="custom-contact-value"><?php echo htmlspecialchars($contactInfo['email']['text']); ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Map Section -->
      <div class="custom-footer-contact">
        <h3 class="custom-footer-heading">Find Us</h3>
        <div class="custom-footer-map">
          <?php echo $mapIframe; ?>
        </div>
      </div>
    </div>

    <!-- Copyright -->
    <div class="custom-footer-bottom">
      <div class="custom-footer-copyright">
        <p>&copy; <script>
            document.write(new Date().getFullYear());
          </script> Zan. All Rights Reserved.</p>
      </div>
      <div class="custom-footer-legal">
        <ul class="custom-legal-links">
          <li><a href="#" class="custom-legal-link">Terms & Conditions</a></li>
          <li><a href="#" class="custom-legal-link">Privacy Policy</a></li>
          <li><a href="#" class="custom-legal-link">Cookie Policy</a></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Animated Background Elements -->
  <div class="custom-footer-bg-elements">
    <div class="custom-bg-circle custom-bg-circle-1"></div>
    <div class="custom-bg-circle custom-bg-circle-2"></div>
    <div class="custom-bg-circle custom-bg-circle-3"></div>
  </div>
</footer>
<!-- End Footer Section -->

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/tiny-slider.js"></script>
<script src="js/custom.js"></script>
</body>

</html>