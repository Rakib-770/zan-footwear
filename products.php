<?php include 'header.php'; ?>

<!-- Start Inner Page Hero -->
<div class="custom-hero2-about" style="background-image: url('images/carousel/man-foot.jpg');">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <div class="custom-hero2-content">
          <h1>Our Products</h1>
          <p class="mb-4">
            Discover the story behind our passion for crafting exceptional footwear that blends timeless style, superior comfort, and long-lasting durability for every step of your journey.
          </p>
          <p>
            <a href="shop.php" class="btn btn-secondary me-2">Shop Collection</a>
            <a href="#our-story" class="btn btn-white-outline">Our Story</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Inner Page Hero -->

<!-- Start Product Section -->
<div class="product-section">
  <div class="container">
    <div class="row">

      <!-- Start Column 1 -->
      <div class="col-md-12 col-lg-3 mb-5 mb-lg-0">
        <h2 class="mb-4 section-title">Crafted with excellent material.</h2>
        <p class="mb-4">Donec vitae odio quis nisl dapibus malesuada. Nullam ac aliquet velit. Aliquam vulputate velit imperdiet dolor tempor tristique. </p>
        <p><a href="shop.php" class="btn">Explore</a></p>
      </div>
      <!-- End Column 1 -->

      <!-- Start Column 2 -->
      <div class="col-12 col-md-4 col-lg-3 mb-5 mb-md-0">
        <a class="product-item" href="cart.php">
          <img src="images/products/product-2.jpg" class="img-fluid product-thumbnail">
          <h3 class="product-title">Mens Collection</h3>

          <span class="icon-cross">
            <img src="images/cross.svg" class="img-fluid">
          </span>
        </a>
      </div>
      <!-- End Column 2 -->

      <!-- Start Column 3 -->
      <div class="col-12 col-md-4 col-lg-3 mb-5 mb-md-0">
        <a class="product-item" href="cart.php">
          <img src="images/products/product-7.jpg" class="img-fluid product-thumbnail">
          <h3 class="product-title">Womens Collection</h3>

          <span class="icon-cross">
            <img src="images/cross.svg" class="img-fluid">
          </span>
        </a>
      </div>
      <!-- End Column 3 -->

      <!-- Start Column 4 -->
      <div class="col-12 col-md-4 col-lg-3 mb-5 mb-md-0">
        <a class="product-item" href="cart.php">
          <img src="images/products/product-6.jpg" class="img-fluid product-thumbnail">
          <h3 class="product-title">Kids Collection</h3>

          <span class="icon-cross">
            <img src="images/cross.svg" class="img-fluid">
          </span>
        </a>
      </div>
      <!-- End Column 4 -->

    </div>
  </div>
</div>
<!-- End Product Section -->

<div class="custom-items-section">
  <div class="container">
    <!-- Filter -->
    <ul class="custom-items-filter-list custom-items-filter">
      <li class="custom-items-filter-item active" data-filter="*"><a href="#">All</a></li>
      <li class="custom-items-filter-item" data-filter=".mens"><a href="#">Mens</a></li>
      <li class="custom-items-filter-item" data-filter=".womens"><a href="#">Womens</a></li>
      <li class="custom-items-filter-item" data-filter=".kids"><a href="#">Kids</a></li>
    </ul>

    <!-- Products Grid -->
    <div class="custom-items-grid">
      <div class="custom-items-card mens">
        <img src="images/products/product-2.jpg" class="custom-items-thumbnail" alt="Mens Collection">
        <h4 class="custom-items-title">Air Force 1</h4>
      </div>
      <div class="custom-items-card womens">
        <img src="images/products/product-7.jpg" class="custom-items-thumbnail" alt="Womens Collection">
        <h4 class="custom-items-title">Adidas Sambas</h4>
      </div>
      <div class="custom-items-card kids">
        <img src="images/products/product-6.jpg" class="custom-items-thumbnail" alt="Kids Collection">
        <h4 class="custom-items-title">ASICS</h4>
      </div>
      <div class="custom-items-card mens">
        <img src="images/products/product-2.jpg" class="custom-items-thumbnail" alt="Mens Collection">
        <h4 class="custom-items-title">Nike Air Force 1</h4>
      </div>
      <div class="custom-items-card womens">
        <img src="images/products/product-7.jpg" class="custom-items-thumbnail" alt="Womens Collection">
        <h4 class="custom-items-title">New Balance 574</h4>
      </div>
      <div class="custom-items-card kids">
        <img src="images/products/product-6.jpg" class="custom-items-thumbnail" alt="Kids Collection">
        <h4 class="custom-items-title">VEJA Campo</h4>
      </div>
      <div class="custom-items-card mens">
        <img src="images/products/product-2.jpg" class="custom-items-thumbnail" alt="Mens Collection">
        <h4 class="custom-items-title">Brooks Ghost 15</h4>
      </div>
      <div class="custom-items-card womens">
        <img src="images/products/product-7.jpg" class="custom-items-thumbnail" alt="Womens Collection">
        <h4 class="custom-items-title">Hoka Clifton 9</h4>
      </div>
      <div class="custom-items-card kids">
        <img src="images/products/product-6.jpg" class="custom-items-thumbnail" alt="Kids Collection">
        <h4 class="custom-items-title">ASICS GEL</h4>
      </div>
      <!-- Add more products here -->
    </div>
  </div>
</div>

<style>

</style>

<!-- Filter Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const filterItems = document.querySelectorAll('.custom-items-filter-item');
    const productGrid = document.querySelector('.custom-items-grid');

    filterItems.forEach(item => {
      item.addEventListener('click', function(e) {
        e.preventDefault();

        filterItems.forEach(i => i.classList.remove('active'));
        this.classList.add('active');

        const filterValue = this.getAttribute('data-filter');
        const products = document.querySelectorAll('.custom-items-card');

        products.forEach(product => {
          if (filterValue === '*' || product.classList.contains(filterValue.substring(1))) {
            product.style.display = 'block';
          } else {
            product.style.display = 'none';
          }
        });
      });
    });
  });
</script>

<?php include 'footer.php'; ?>