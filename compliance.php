<?php
include 'header.php';

// Load JSON data
$jsonData = file_get_contents('data/compliance.json');
$data = json_decode($jsonData, true);

// Shortcuts for cleaner access
$hero = $data['hero_section'];
$commitment = $data['commitment_section'];
$certifications = $data['certifications_section'];
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


<!-- Start Why Choose Us Section -->
<div class="why-choose-section">
  <div class="container">
    <div class="row justify-content-between align-items-center">
      <div class="col-lg-6">
        <h2 class="section-title"><?php echo htmlspecialchars($commitment['title']); ?></h2>
        <p><?php echo htmlspecialchars($commitment['description']); ?></p>

        <div class="row my-5">
          <?php foreach ($commitment['features'] as $feature): ?>
            <div class="col-6 col-md-6">
              <div class="feature">
                <div class="icon custom-compliance-icon-wrapper">
                  <img src="<?php echo htmlspecialchars($feature['icon']); ?>" alt="<?php echo htmlspecialchars($feature['title']); ?>" class="img-fluid">
                </div>
                <h3><?php echo htmlspecialchars($feature['title']); ?></h3>
                <p><?php echo htmlspecialchars($feature['description']); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="img-wrap">
          <img src="<?php echo htmlspecialchars($commitment['image']); ?>" alt="Compliance at Zan Footwear" class="img-fluid rounded shadow-sm">
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Why Choose Us Section -->


<!-- Start Certifications Section -->
<div id="certifications" class="why-choose-section">
  <div class="container">
    <div class="row justify-content-center">
      <?php foreach ($certifications['certifications'] as $cert): ?>
        <div class="col-6 col-md-4 col-lg-3 mb-4">
          <div class="feature text-center">
            <div class="custom-compliance-logo">
              <img src="<?php echo htmlspecialchars($cert['logo']); ?>" alt="<?php echo htmlspecialchars($cert['title']); ?>" class="img-fluid">
            </div>
            <h3><?php echo htmlspecialchars($cert['title']); ?></h3>
            <p><?php echo htmlspecialchars($cert['description']); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<!-- End Certifications Section -->

<?php include 'footer.php'; ?>
