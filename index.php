<?php include 'header.php'; 
$products_data = json_decode(file_get_contents('data/products.json'), true);
$aboutData = json_decode(file_get_contents('data/about.json'), true);
$homeData = json_decode(file_get_contents('data/home.json'), true);
?>

<!-- Start Hero Section -->
<div class="hero">
  <div class="container">
    <!-- Carousel -->
    <div id="customHeroCarousel" class="carousel slide custom-hero-carousel" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php foreach ($homeData['hero_section']['carousel_images'] as $index => $image): ?>
          <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
            <img src="<?php echo $image; ?>" class="d-block w-100" alt="Footwear Collection <?php echo $index + 1; ?>">
          </div>
        <?php endforeach; ?>
      </div>
      <!-- Carousel controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#customHeroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#customHeroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>

    <!-- Text Content -->
    <div class="row justify-content-between">
      <div class="col-lg-5">
        <div class="intro-excerpt custom-hero-content">
          <h2><?php echo htmlspecialchars($homeData['hero_section']['title']); ?></h2>
          <p class="mb-4"><?php echo htmlspecialchars($homeData['hero_section']['description']); ?></p>
          <p>
            <a href="<?php echo $homeData['hero_section']['primary_button']['link']; ?>" class="btn btn-secondary me-2">
              <?php echo htmlspecialchars($homeData['hero_section']['primary_button']['text']); ?>
            </a>
            <a href="<?php echo $homeData['hero_section']['secondary_button']['link']; ?>" class="btn btn-white-outline">
              <?php echo htmlspecialchars($homeData['hero_section']['secondary_button']['text']); ?>
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Hero Section -->

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

<!-- Start Why Choose Us Section -->
<div class="why-choose-section ">
  <div class="container">
    <div class="row justify-content-between">
      <div class="col-lg-6">
        <h2 class="section-title"><?php echo htmlspecialchars($homeData['about_section']['title']); ?></h2>
        <p><?php echo htmlspecialchars($homeData['about_section']['description']); ?></p>
        <p><?php echo htmlspecialchars($homeData['about_section']['additional_description']); ?></p>

        <div class="row my-5">
          <?php foreach ($homeData['about_section']['features'] as $feature): ?>
            <div class="col-6 col-md-6">
              <div class="feature">
                <div class="icon">
                  <img src="<?php echo $feature['icon']; ?>" alt="Image" class="imf-fluid">
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
          <img src="<?php echo $homeData['about_section']['image']; ?>" alt="Image" class="img-fluid">
        </div>
      </div>

    </div>
  </div>
</div>
<!-- End Why Choose Us Section -->

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

<!-- Achievement Section start -->
<div class="untree_co-section">
  <div class="container">
    <div class="row mb-5">
      <div class="col-lg-6 mx-auto text-center">
        <h2 class="custom-section-title"><?php echo htmlspecialchars($homeData['achievements_section']['title']); ?></h2>
      </div>
    </div>

    <div class="row g-4">
      <?php foreach ($homeData['achievements_section']['achievements'] as $achievement): ?>
        <div class="col-md-6">
          <div class="custom-achievement-card d-flex align-items-center">
            <img src="<?php echo $achievement['image']; ?>" alt="<?php echo htmlspecialchars($achievement['title']); ?>" class="custom-achievement-img">
            <div class="custom-achievement-content">
              <h3 class="custom-achievement-title"><?php echo htmlspecialchars($achievement['title']); ?></h3>
              <p class="custom-achievement-text">
                <?php echo htmlspecialchars($achievement['description']); ?>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<!-- Achievement Section end -->

<!-- Our Strength -->
<!-- OUR STRENGTH Section -->
<div class="untree_co-section custom-our-strength-section">
  <div class="container">
    <div class="row mb-5">
      <div class="col-lg-5 mx-auto text-center">
        <h2 class="custom-section-title"><?php echo htmlspecialchars($homeData['strength_section']['title']); ?></h2>
      </div>
    </div>

    <div class="row g-4 text-center">
      <?php foreach ($homeData['strength_section']['strengths'] as $strength): ?>
        <div class="col-md-4">
          <div class="custom-our-strength-card">
            <h3 class="custom-our-strength-number"><?php echo htmlspecialchars($strength['number']); ?></h3>
            <p class="custom-our-strength-text">
              <?php echo htmlspecialchars($strength['description']); ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<!-- Out Strength -->

<!-- Start Blog Section -->
<div class="custom-news-section mt-4 mb-4">
  <div class="container">
    <div class="row mb-5">
      <div class="col-md-6">
        <h2 class="section-title"><?php echo htmlspecialchars($homeData['news_section']['title']); ?></h2>
      </div>
    </div>

    <div class="custom-news-carousel">
      <div class="custom-news-track">
        <?php foreach ($homeData['news_section']['news'] as $news): ?>
          <div class="custom-news-slide">
            <div class="post-entry">
              <a href="<?php echo $news['link']; ?>" class="post-thumbnail">
                <img src="<?php echo $news['image']; ?>" alt="Image" class="img-fluid">
              </a>
              <div class="post-content-entry">
                <h3><a href="<?php echo $news['link']; ?>"><?php echo htmlspecialchars($news['title']); ?></a></h3>
                <div class="meta">
                  <span>by <a href="#"><?php echo htmlspecialchars($news['author']); ?></a></span> <span>on <a href="#"><?php echo htmlspecialchars($news['date']); ?></a></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Navigation Controls -->
      <button class="custom-news-prev">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
      <button class="custom-news-next">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>

      <!-- Pagination Dots -->
      <div class="custom-news-pagination"></div>
    </div>
  </div>
</div>
<!-- End Blog Section -->

<?php include 'footer.php'; ?>