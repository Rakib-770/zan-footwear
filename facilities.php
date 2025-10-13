<?php 
// Read and decode the JSON data
$json_data = file_get_contents('data/facilities.json');
$data = json_decode($json_data, true);
$page_data = $data['page'];
?>

<?php include 'header.php'; ?>

<!-- Start Inner Page Hero -->
<div class="custom-hero2-about" style="background-image: url('<?php echo $page_data['hero']['background_image']; ?>');">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <div class="custom-hero2-content">
          <h1><?php echo $page_data['hero']['title']; ?></h1>
          <p class="mb-4">
            <?php echo $page_data['hero']['description']; ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Inner Page Hero -->

<!-- Start Facilities Section -->
<div class="custom-facilities-section py-5">
  <div class="container">

    <?php foreach ($page_data['facilities'] as $facility): ?>
      <div class="row align-items-center custom-facilities-item mb-5 <?php echo $facility['layout'] === 'reverse' ? 'flex-lg-row-reverse' : ''; ?>">
        <div class="col-lg-6">
          <div class="custom-facilities-img">
            <img src="<?php echo $facility['image']; ?>" alt="<?php echo $facility['alt']; ?>" class="img-fluid">
          </div>
        </div>
        <div class="col-lg-6">
          <div class="custom-facilities-text-box p-4">
            <h2 class="custom-facilities-title"><?php echo $facility['title']; ?></h2>
            <ul class="custom-facilities-list">
              <?php foreach ($facility['items'] as $item): ?>
                <li><?php echo $item; ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</div>
<!-- End Facilities Section -->

<?php include 'footer.php'; ?>