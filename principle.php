<?php 
// Read and decode the JSON data
$json_data = file_get_contents('data/principle.json');
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
          <p>
            <?php foreach ($page_data['hero']['buttons'] as $button): ?>
              <a href="<?php echo $button['url']; ?>" class="<?php echo $button['class']; ?>"><?php echo $button['text']; ?></a>
            <?php endforeach; ?>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Inner Page Hero -->

<!-- Start Values Section -->
<div class="why-choose-section">
  <div class="container">
    <div class="row mb-5">
      <div class="col-lg-5 mx-auto text-center">
        <h2 class="custom-section-title"><?php echo $page_data['section_title']; ?></h2>
      </div>
    </div>

    <div class="row">
      <?php foreach ($page_data['principles'] as $principle): ?>
      <div class="col-6 col-md-6 col-lg-3 mb-4">
        <div class="feature text-center custom-values-feature">
          <div class="icon mb-3 custom-values-icon">
            <img src="<?php echo $principle['icon']; ?>" alt="<?php echo $principle['alt']; ?>" class="img-fluid">
          </div>
          <h3><?php echo $principle['title']; ?></h3>
          <p><?php echo $principle['description']; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<!-- End Values Section -->

<?php include 'footer.php'; ?>