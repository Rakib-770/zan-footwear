<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="author" content="Untree.co">
  <link rel="shortcut icon" href="images/logo/logo.ico">

  <meta name="description" content="" />
  <meta name="keywords" content="bootstrap, bootstrap4" />

  <!-- Bootstrap CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="css/tiny-slider.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <link href="css/custom-style.css" rel="stylesheet">
  <title>ZAN FOOTWEAR LTD.</title>
</head>

<body>

  <!-- Start Header/Navigation -->
  <nav class="custom-navbar navbar navbar navbar-expand-md navbar-dark bg-dark" arial-label="Furni navigation bar">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <img src="images/logo/logo.png" alt="Zan Logo">
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsFurni" aria-controls="navbarsFurni" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarsFurni">
        <ul class="custom-navbar-nav navbar-nav ms-auto mb-2 mb-md-0">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li><a class="nav-link" href="about.php">About</a></li>
          <li><a class="nav-link" href="products.php">Products</a></li>
          <li><a class="nav-link" href="compliance.php">Compliance</a></li>
          <li><a class="nav-link" href="principle.php">Principle</a></li>
          <li><a class="nav-link" href="operations.php">Operations</a></li>
          <li><a class="nav-link" href="strength.php">Strength</a></li>
          <li><a class="nav-link" href="facilities.php">Facilities</a></li>
          <li><a class="nav-link" href="contact.php">Contact us</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <script>
    // Function to set active navigation link
    function setActiveNavLink() {
      // Get current page URL
      const currentLocation = window.location.href;

      // Get all navigation links
      const navLinks = document.querySelectorAll('.custom-navbar-nav .nav-link');

      // Loop through each link
      navLinks.forEach(link => {
        // Check if the link's href matches the current URL
        if (link.href === currentLocation) {
          // Add active class to the link
          link.classList.add('active');

          // Also add active class to the parent li if it exists
          if (link.parentElement) {
            link.parentElement.classList.add('active');
          }
        }
      });
    }

    // Call the function when the page loads
    document.addEventListener('DOMContentLoaded', setActiveNavLink);
  </script>
  <!-- End Header/Navigation -->