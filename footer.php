<?php
// Read and decode the JSON data
$contactData = json_decode(file_get_contents('data/contact.json'), true);
$socialMedia = $contactData['socialMedia'];
$mapIframe = $contactData['map']['iframe'];
?>

<!-- Start Footer Section -->
<footer class="footer-section">
    <div class="container relative">

      <div class="row g-5 mb-5">
        <!-- Logo and Social Media - 3 columns -->
        <div class="col-lg-3">
          <div class="mb-4 footer-logo-wrapper">
            <a href="index.php" class="footer-logo">
              <img src="images/logo/white-logo.png" alt="Zan Logo" class="logo-default">
              <img src="images/logo/logo.png" alt="Zan Logo Hover" class="logo-hover">
            </a>
          </div>

          <ul class="list-unstyled custom-social">
            <li><a href="<?php echo $socialMedia['facebook']['url']; ?>" target="_blank"><span class="fa fa-brands fa-facebook-f"></span></a></li>
            <li><a href="<?php echo $socialMedia['twitter']['url']; ?>" target="_blank"><span class="fa fa-brands fa-twitter"></span></a></li>
            <li><a href="<?php echo $socialMedia['instagram']['url']; ?>" target="_blank"><span class="fa fa-brands fa-instagram"></span></a></li>
            <li><a href="<?php echo $socialMedia['linkedin']['url']; ?>" target="_blank"><span class="fa fa-brands fa-linkedin"></span></a></li>
          </ul>
        </div>

        <!-- Map - 6 columns (double width) -->
        <div class="col-lg-6">
          <div class="custom-footer-map">
            <?php echo $mapIframe; ?>
          </div>
        </div>

        <!-- Useful Links - 3 columns -->
        <div class="col-lg-3">
          <div class="custom-footer-links">
            <h3>Useful Links</h3>
            <ul class="list-unstyled">
              <li><a href="#">Home</a></li>
              <li><a href="#">About us</a></li>
              <li><a href="#">Products</a></li>
              <li><a href="#">Operation</a></li>
              <li><a href="#">Contact us</a></li>
            </ul>
          </div>
        </div>
      </div>

      <div class="border-top copyright">
        <div class="row pt-4">
          <div class="col-lg-6">
            <p class="mb-2 text-center text-lg-start">Copyright &copy;<script>
                document.write(new Date().getFullYear());
              </script>. All Rights Reserved.
            </p>
          </div>

          <div class="col-lg-6 text-center text-lg-end">
            <ul class="list-unstyled d-inline-flex ms-auto">
              <li class="me-4"><a href="#">Terms &amp; Conditions</a></li>
              <li><a href="#">Privacy Policy</a></li>
            </ul>
          </div>

        </div>
      </div>

    </div>
  </footer>
  <!-- End Footer Section -->

  <script src="js/bootstrap.bundle.min.js"></script>
  <script src="js/tiny-slider.js"></script>
  <script src="js/custom.js"></script>
  </body>

  </html>