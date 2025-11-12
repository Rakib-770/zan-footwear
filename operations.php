<?php
include 'header.php';

// Load JSON data
$jsonData = file_get_contents('data/operations.json');
$data = json_decode($jsonData, true);

// Shortcuts for readability
$hero = $data['hero_section'];
$operations = $data['operations_grid']['operations'];
?>

<!-- Start Inner Page Hero -->
<div class="custom-hero2-about" style="background-image: url('<?php echo htmlspecialchars($hero['background_image']); ?>');">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <div class="custom-hero2-content">
          <h1><?php echo htmlspecialchars($hero['title']); ?></h1>
          <p class="mb-4">
            <?php echo htmlspecialchars($hero['description']); ?>
          </p>
          <p>
            <a href="<?php echo htmlspecialchars($hero['primary_button']['link']); ?>" class="btn btn-secondary me-2">
              <?php echo htmlspecialchars($hero['primary_button']['text']); ?>
            </a>
            <a href="<?php echo htmlspecialchars($hero['secondary_button']['link']); ?>" class="btn btn-white-outline">
              <?php echo htmlspecialchars($hero['secondary_button']['text']); ?>
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Inner Page Hero -->

<div class="blog-section">
  <div class="container">
    <div class="row">
      <?php foreach ($operations as $operation): ?>
        <div class="col-12 col-sm-6 col-md-4 mb-5">
          <div class="post-entry2">
            <a href="<?php echo htmlspecialchars($operation['link']); ?>" class="post-thumbnail">
              <img src="<?php echo htmlspecialchars($operation['image']); ?>" alt="<?php echo htmlspecialchars($operation['title']); ?>" class="img-fluid">
            </a>
            <div class="post-content-entry">
              <h3><a href="<?php echo htmlspecialchars($operation['link']); ?>"><?php echo htmlspecialchars($operation['title']); ?></a></h3>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
