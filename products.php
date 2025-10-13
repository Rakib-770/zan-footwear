<?php include 'header.php'; 

// Load products data from JSON file
$products_data = json_decode(file_get_contents('data/products.json'), true);
?>

<!-- Start Inner Page Hero -->
<div class="custom-hero2-about" style="background-image: url('<?php echo $products_data['hero']['background_image']; ?>');">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <div class="custom-hero2-content">
          <h1><?php echo $products_data['hero']['title']; ?></h1>
          <p class="mb-4">
            <?php echo $products_data['hero']['description']; ?>
          </p>
          <p>
            <?php foreach ($products_data['hero']['buttons'] as $button): ?>
              <a href="<?php echo $button['url']; ?>" class="<?php echo $button['class']; ?>"><?php echo $button['text']; ?></a>
            <?php endforeach; ?>
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
        <h2 class="mb-4 section-title"><?php echo $products_data['intro_section']['title']; ?></h2>
        <p class="mb-4"><?php echo $products_data['intro_section']['description']; ?></p>
        <p><a href="<?php echo $products_data['intro_section']['button_url']; ?>" class="btn"><?php echo $products_data['intro_section']['button_text']; ?></a></p>
      </div>
      <!-- End Column 1 -->

      <!-- Start Column 2 -->
      <?php foreach ($products_data['featured_collections'] as $collection): ?>
      <div class="col-12 col-md-4 col-lg-3 mb-5 mb-md-0">
        <a class="product-item" href="<?php echo $collection['url']; ?>">
          <img src="<?php echo $collection['image']; ?>" class="img-fluid product-thumbnail">
          <h3 class="product-title"><?php echo $collection['name']; ?></h3>

          <span class="icon-cross">
            <img src="images/cross.svg" class="img-fluid">
          </span>
        </a>
      </div>
      <?php endforeach; ?>
      <!-- End Column 2 -->

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
      <?php foreach ($products_data['products'] as $product): ?>
      <div class="custom-items-card <?php echo $product['category']; ?>">
        <img src="<?php echo $product['image']; ?>" class="custom-items-thumbnail" alt="<?php echo $product['name']; ?>">
        <h4 class="custom-items-title"><?php echo $product['name']; ?></h4>
      </div>
      <?php endforeach; ?>
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