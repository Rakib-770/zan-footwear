<?php 
// Load about data from JSON
$aboutData = json_decode(file_get_contents('data/about.json'), true);
?>

<?php include 'header.php'; ?>

<!-- Start Inner Page Hero -->
<div class="custom-hero2-about" style="background-image: url('<?php echo $aboutData['hero_section']['background_image']; ?>');">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-8">
        <div class="custom-hero2-content">
          <h1><?php echo htmlspecialchars($aboutData['hero_section']['title']); ?></h1>
          <p class="mb-4">
            <?php echo htmlspecialchars($aboutData['hero_section']['description']); ?>
          </p>
          <p>
            <a href="<?php echo $aboutData['hero_section']['primary_button']['link']; ?>" class="btn btn-secondary me-2">
              <?php echo htmlspecialchars($aboutData['hero_section']['primary_button']['text']); ?>
            </a>
            <a href="<?php echo $aboutData['hero_section']['secondary_button']['link']; ?>" class="btn btn-white-outline">
              <?php echo htmlspecialchars($aboutData['hero_section']['secondary_button']['text']); ?>
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Inner Page Hero -->

<!-- Start Our Story Section -->
<div id="our-story" class="why-choose-section">
  <div class="container">
    <div class="row justify-content-between">
      <div class="col-lg-6">
        <h2 class="section-title"><?php echo htmlspecialchars($aboutData['our_story_section']['title']); ?></h2>
        <p><?php echo htmlspecialchars($aboutData['our_story_section']['description']); ?></p>

        <?php foreach ($aboutData['our_story_section']['paragraphs'] as $paragraph): ?>
          <p><?php echo htmlspecialchars($paragraph); ?></p>
        <?php endforeach; ?>

        <div class="row my-5">
          <?php foreach ($aboutData['our_story_section']['features'] as $feature): ?>
            <div class="col-6 col-md-6">
              <div class="feature">
                <div class="icon">
                  <img src="<?php echo $feature['icon']; ?>" alt="<?php echo htmlspecialchars($feature['title']); ?>" class="imf-fluid">
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
          <img src="<?php echo $aboutData['our_story_section']['image']; ?>" alt="Zan Footwear Factory" class="img-fluid">
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Our Story Section -->

<!-- Start Mission Vision Section -->
<div class="we-help-section bg-light">
  <div class="container">
    <div class="row justify-content-between">
      <div class="col-lg-5 mb-5 mb-lg-0">
        <div class="img-wrap">
          <img src="<?php echo $aboutData['mission_vision_section']['image']; ?>" alt="Our Mission and Vision" class="img-fluid">
        </div>
      </div>
      <div class="col-lg-6 ps-lg-5">
        <h2 class="section-title mb-4"><?php echo htmlspecialchars($aboutData['mission_vision_section']['title']); ?></h2>

        <div class="mission-vision-item mb-5">
          <h3 class="mb-3"><?php echo htmlspecialchars($aboutData['mission_vision_section']['mission']['title']); ?></h3>
          <p><?php echo htmlspecialchars($aboutData['mission_vision_section']['mission']['description']); ?></p>
        </div>

        <div class="mission-vision-item">
          <h3 class="mb-3"><?php echo htmlspecialchars($aboutData['mission_vision_section']['vision']['title']); ?></h3>
          <p><?php echo htmlspecialchars($aboutData['mission_vision_section']['vision']['description']); ?></p>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Mission Vision Section -->

<!-- Start Values Section -->
<div class="why-choose-section">
  <div class="container">
    <div class="row mb-5">
      <div class="col-lg-5 mx-auto text-center">
        <h2 class="custom-section-title"><?php echo htmlspecialchars($aboutData['values_section']['title']); ?></h2>
      </div>
    </div>

    <div class="row">
      <?php foreach ($aboutData['values_section']['values'] as $value): ?>
        <div class="col-6 col-md-6 col-lg-3 mb-4">
          <div class="feature text-center custom-values-feature">
            <div class="icon mb-3 custom-values-icon">
              <img src="<?php echo $value['icon']; ?>" alt="<?php echo htmlspecialchars($value['title']); ?>" class="img-fluid">
            </div>
            <h3><?php echo htmlspecialchars($value['title']); ?></h3>
            <p><?php echo htmlspecialchars($value['description']); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<!-- End Values Section -->

<!-- Start Manufacturing Section -->
<div class="we-help-section">
  <div class="container">
    <div class="row justify-content-between">
      <div class="col-lg-7 mb-5 mb-lg-0">
        <div class="imgs-grid">
          <?php foreach ($aboutData['manufacturing_section']['images'] as $index => $image): ?>
            <div class="grid grid-<?php echo $index + 1; ?>">
              <img src="<?php echo $image; ?>" alt="Manufacturing Image <?php echo $index + 1; ?>" class="img-fluid">
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-5 ps-lg-5">
        <h2 class="section-title mb-4"><?php echo htmlspecialchars($aboutData['manufacturing_section']['title']); ?></h2>
        <p><?php echo $aboutData['manufacturing_section']['description']; ?></p>

        <ul class="list-unstyled custom-list my-4">
          <?php foreach ($aboutData['manufacturing_section']['features'] as $feature): ?>
            <li>✔ <?php echo htmlspecialchars($feature); ?></li>
          <?php endforeach; ?>
        </ul>

        <p><?php echo htmlspecialchars($aboutData['manufacturing_section']['conclusion']); ?></p>
      </div>
    </div>
  </div>
</div>
<!-- End Manufacturing Section -->

<!-- Start Team Section -->
<div class="untree_co-section">
  <div class="container">
    <div class="row mb-5">
      <div class="col-lg-5 mx-auto text-center">
        <h2 class="custom-section-title"><?php echo htmlspecialchars($aboutData['team_section']['title']); ?></h2>
      </div>
    </div>

    <div class="row">
      <?php foreach ($aboutData['team_section']['members'] as $member): ?>
        <div class="col-12 col-md-6 col-lg-4 mb-5 mb-md-0">
          <div class="custom-leadership-card">
            <div class="custom-img-container">
              <img src="<?php echo $member['image']; ?>" class="custom-team-img" alt="<?php echo htmlspecialchars($member['name']); ?>">
            </div>
            <div class="custom-team-content">
              <h3 class="custom-member-name"><a href="#"><?php echo htmlspecialchars($member['name']); ?></a></h3>
              <span class="custom-member-position"><?php echo htmlspecialchars($member['position']); ?></span>
              <p class="custom-member-bio"><?php echo htmlspecialchars($member['bio']); ?></p>
              <div class="custom-social-links">
                <?php foreach ($member['social_links'] as $social): ?>
                  <a href="<?php echo $social['link']; ?>"><i class="<?php echo $social['icon']; ?>"></i></a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<!-- End Team Section -->

<!-- Start Global Presence Section -->
<div class="why-choose-section">
  <div class="container">
    <div class="row mb-5">
      <div class="col-lg-5 mx-auto text-center">
        <h2 class="custom-section-title"><?php echo htmlspecialchars($aboutData['global_presence_section']['title']); ?></h2>
      </div>
    </div>

    <div class="row">
      <?php foreach ($aboutData['global_presence_section']['features'] as $feature): ?>
        <div class="col-md-4 mb-4">
          <div class="feature text-center">
            <div class="icon mb-3 custom-values-icon">
              <img src="<?php echo $feature['icon']; ?>" alt="<?php echo htmlspecialchars($feature['title']); ?>" class="imf-fluid">
            </div>
            <h3><?php echo htmlspecialchars($feature['title']); ?></h3>
            <p><?php echo htmlspecialchars($feature['description']); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<!-- End Global Presence Section -->

<?php include 'footer.php'; ?>