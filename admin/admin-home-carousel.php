<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Helper: allowed image extensions
$ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

function handle_upload($fileField, $subfolder = '')
{
    global $ALLOWED_EXT;
    if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no upload
    }

    $f = $_FILES[$fileField];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // basic mime/extension check
    $tmp = $f['tmp_name'];
    $origName = basename($f['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $ALLOWED_EXT)) return null;

    // create upload dir if not exists
    $uploadDir = __DIR__ . '/../images/uploads' . ($subfolder ? "/$subfolder" : '');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // unique filename
    $newFileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destPathFS = $uploadDir . '/' . $newFileName;
    if (!move_uploaded_file($tmp, $destPathFS)) {
        return null;
    }

    // return web path relative to public root used in JSON (e.g., images/uploads/...)
    $webPath = 'images/uploads' . ($subfolder ? "/$subfolder" : '') . '/' . $newFileName;
    return $webPath;
}

// Load home data
$homeJsonPath = __DIR__ . '/../data/home.json';
$homeData = json_decode(file_get_contents($homeJsonPath), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Function to delete existing file if exists and is inside images/ (safety)
    function safe_unlink($pathRelative)
    {
        if (!$pathRelative) return;
        // ensure it references images/ and not outside
        $candidate = realpath(__DIR__ . '/../' . $pathRelative);
        $imagesDir = realpath(__DIR__ . '/../images');
        if ($candidate && strpos($candidate, $imagesDir) === 0 && file_exists($candidate)) {
            @unlink($candidate);
        }
    }

    switch ($action) {
        case 'update_hero':
            // Handle carousel image uploads
            $carousel_images = $homeData['hero_section']['carousel_images'];
            for ($i = 0; $i < count($carousel_images); $i++) {
                $idx = $i + 1;
                $imageUploaded = handle_upload("carousel_image_{$idx}");
                $imageDelete = isset($_POST["delete_carousel_image_{$idx}"]) && $_POST["delete_carousel_image_{$idx}"] === '1';
                
                if ($imageUploaded) {
                    if (!empty($carousel_images[$i])) {
                        safe_unlink($carousel_images[$i]);
                    }
                    $carousel_images[$i] = $imageUploaded;
                } elseif ($imageDelete) {
                    if (!empty($carousel_images[$i])) {
                        safe_unlink($carousel_images[$i]);
                    }
                    $carousel_images[$i] = "";
                }
            }
            $homeData['hero_section']['carousel_images'] = $carousel_images;

            // Update hero content
            $homeData['hero_section']['title'] = $_POST['hero_title'] ?? $homeData['hero_section']['title'];
            $homeData['hero_section']['description'] = $_POST['hero_description'] ?? $homeData['hero_section']['description'];
            $homeData['hero_section']['primary_button']['text'] = $_POST['primary_button_text'] ?? $homeData['hero_section']['primary_button']['text'];
            $homeData['hero_section']['primary_button']['link'] = $_POST['primary_button_link'] ?? $homeData['hero_section']['primary_button']['link'];
            $homeData['hero_section']['secondary_button']['text'] = $_POST['secondary_button_text'] ?? $homeData['hero_section']['secondary_button']['text'];
            $homeData['hero_section']['secondary_button']['link'] = $_POST['secondary_button_link'] ?? $homeData['hero_section']['secondary_button']['link'];
            break;

        case 'update_about':
            // About section image
            $about_image_uploaded = handle_upload('about_image_upload');
            $about_image_delete = isset($_POST['delete_about_image']) && $_POST['delete_about_image'] === '1';

            if ($about_image_uploaded) {
                if (!empty($homeData['about_section']['image'])) {
                    safe_unlink($homeData['about_section']['image']);
                }
                $homeData['about_section']['image'] = $about_image_uploaded;
            } elseif ($about_image_delete) {
                if (!empty($homeData['about_section']['image'])) {
                    safe_unlink($homeData['about_section']['image']);
                }
                $homeData['about_section']['image'] = "";
            }

            // Update about content
            $homeData['about_section']['title'] = $_POST['about_title'] ?? $homeData['about_section']['title'];
            $homeData['about_section']['description'] = $_POST['about_description'] ?? $homeData['about_section']['description'];
            $homeData['about_section']['additional_description'] = $_POST['about_additional_description'] ?? $homeData['about_section']['additional_description'];

            // Update features
            $features = $homeData['about_section']['features'];
            for ($i = 0; $i < count($features); $i++) {
                $idx = $i + 1;
                $features[$i]['title'] = $_POST["feature{$idx}_title"] ?? $features[$i]['title'];
                $features[$i]['description'] = $_POST["feature{$idx}_description"] ?? $features[$i]['description'];
            }
            $homeData['about_section']['features'] = $features;
            break;

        case 'update_achievements':
            // Update achievements title
            $homeData['achievements_section']['title'] = $_POST['achievements_title'] ?? $homeData['achievements_section']['title'];
            
            // Handle existing achievements updates
            $achievements = $homeData['achievements_section']['achievements'];
            $achievementCount = count($achievements);
            
            for ($i = 0; $i < $achievementCount; $i++) {
                $idx = $i + 1;
                $imageUploaded = handle_upload("achievement{$idx}_image_upload");
                $imageDelete = isset($_POST["delete_achievement{$idx}_image"]) && $_POST["delete_achievement{$idx}_image"] === '1';
                
                if ($imageUploaded) {
                    if (!empty($achievements[$i]['image'])) {
                        safe_unlink($achievements[$i]['image']);
                    }
                    $achievements[$i]['image'] = $imageUploaded;
                } elseif ($imageDelete) {
                    if (!empty($achievements[$i]['image'])) {
                        safe_unlink($achievements[$i]['image']);
                    }
                    $achievements[$i]['image'] = "";
                }
                
                $achievements[$i]['title'] = $_POST["achievement{$idx}_title"] ?? $achievements[$i]['title'];
                $achievements[$i]['description'] = $_POST["achievement{$idx}_description"] ?? $achievements[$i]['description'];
            }
            
            // Handle new achievements addition
            if (isset($_POST['new_achievement_title']) && is_array($_POST['new_achievement_title'])) {
                foreach ($_POST['new_achievement_title'] as $index => $newTitle) {
                    if (!empty(trim($newTitle))) {
                        $newImageUploaded = handle_upload("new_achievement_image_upload_{$index}");
                        
                        $newAchievement = [
                            'title' => trim($newTitle),
                            'description' => trim($_POST['new_achievement_description'][$index] ?? ''),
                            'image' => $newImageUploaded ?: ''
                        ];
                        
                        $achievements[] = $newAchievement;
                    }
                }
            }
            
            $homeData['achievements_section']['achievements'] = $achievements;
            break;

        case 'remove_achievement':
            $removeIndex = $_POST['remove_index'] ?? null;
            if ($removeIndex !== null && isset($homeData['achievements_section']['achievements'][$removeIndex])) {
                // Delete associated image file
                $imageToRemove = $homeData['achievements_section']['achievements'][$removeIndex]['image'];
                if (!empty($imageToRemove)) {
                    safe_unlink($imageToRemove);
                }
                
                // Remove achievement
                array_splice($homeData['achievements_section']['achievements'], $removeIndex, 1);
            }
            break;

        case 'update_strength':
            // Update strength title
            $homeData['strength_section']['title'] = $_POST['strength_title'] ?? $homeData['strength_section']['title'];
            
            // Update existing strengths
            $strengths = $homeData['strength_section']['strengths'];
            for ($i = 0; $i < count($strengths); $i++) {
                $idx = $i + 1;
                $strengths[$i]['number'] = $_POST["strength{$idx}_number"] ?? $strengths[$i]['number'];
                $strengths[$i]['description'] = $_POST["strength{$idx}_description"] ?? $strengths[$i]['description'];
            }
            
            // Handle new strengths addition
            if (isset($_POST['new_strength_number']) && is_array($_POST['new_strength_number'])) {
                foreach ($_POST['new_strength_number'] as $index => $newNumber) {
                    if (!empty(trim($newNumber))) {
                        $newStrength = [
                            'number' => trim($newNumber),
                            'description' => trim($_POST['new_strength_description'][$index] ?? '')
                        ];
                        
                        $strengths[] = $newStrength;
                    }
                }
            }
            
            $homeData['strength_section']['strengths'] = $strengths;
            break;

        case 'remove_strength':
            $removeIndex = $_POST['remove_index'] ?? null;
            if ($removeIndex !== null && isset($homeData['strength_section']['strengths'][$removeIndex])) {
                array_splice($homeData['strength_section']['strengths'], $removeIndex, 1);
            }
            break;

        case 'update_news':
            // Update news title
            $homeData['news_section']['title'] = $_POST['news_title'] ?? $homeData['news_section']['title'];
            
            // Handle existing news updates
            $news = $homeData['news_section']['news'];
            $newsCount = count($news);
            
            for ($i = 0; $i < $newsCount; $i++) {
                $idx = $i + 1;
                $imageUploaded = handle_upload("news{$idx}_image_upload");
                $imageDelete = isset($_POST["delete_news{$idx}_image"]) && $_POST["delete_news{$idx}_image"] === '1';
                
                if ($imageUploaded) {
                    if (!empty($news[$i]['image'])) {
                        safe_unlink($news[$i]['image']);
                    }
                    $news[$i]['image'] = $imageUploaded;
                } elseif ($imageDelete) {
                    if (!empty($news[$i]['image'])) {
                        safe_unlink($news[$i]['image']);
                    }
                    $news[$i]['image'] = "";
                }
                
                $news[$i]['title'] = $_POST["news{$idx}_title"] ?? $news[$i]['title'];
                $news[$i]['author'] = $_POST["news{$idx}_author"] ?? $news[$i]['author'];
                $news[$i]['date'] = $_POST["news{$idx}_date"] ?? $news[$i]['date'];
                $news[$i]['link'] = $_POST["news{$idx}_link"] ?? $news[$i]['link'];
            }
            
            // Handle new news addition
            if (isset($_POST['new_news_title']) && is_array($_POST['new_news_title'])) {
                foreach ($_POST['new_news_title'] as $index => $newTitle) {
                    if (!empty(trim($newTitle))) {
                        $newImageUploaded = handle_upload("new_news_image_upload_{$index}");
                        
                        $newNews = [
                            'title' => trim($newTitle),
                            'author' => trim($_POST['new_news_author'][$index] ?? ''),
                            'date' => trim($_POST['new_news_date'][$index] ?? ''),
                            'link' => trim($_POST['new_news_link'][$index] ?? '#'),
                            'image' => $newImageUploaded ?: ''
                        ];
                        
                        $news[] = $newNews;
                    }
                }
            }
            
            $homeData['news_section']['news'] = $news;
            break;

        case 'remove_news':
            $removeIndex = $_POST['remove_index'] ?? null;
            if ($removeIndex !== null && isset($homeData['news_section']['news'][$removeIndex])) {
                // Delete associated image file
                $imageToRemove = $homeData['news_section']['news'][$removeIndex]['image'];
                if (!empty($imageToRemove)) {
                    safe_unlink($imageToRemove);
                }
                
                // Remove news
                array_splice($homeData['news_section']['news'], $removeIndex, 1);
            }
            break;
    }

    // Save to JSON file
    file_put_contents($homeJsonPath, json_encode($homeData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Home page information updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Home Page Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-style.css">
    <style>
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            display: block;
            margin-bottom: 8px;
            object-fit: cover;
            border: 1px solid #ddd;
            padding: 4px;
            background: #fff;
        }

        .small-preview {
            max-width: 150px;
            max-height: 100px;
            display: block;
            margin-bottom: 6px;
            object-fit: cover;
            border: 1px solid #ddd;
            padding: 4px;
            background: #fff;
        }

        .section-item {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
        }

        .carousel-item-preview {
            max-width: 200px;
            max-height: 120px;
            display: block;
            margin-bottom: 6px;
            object-fit: cover;
            border: 1px solid #ddd;
            padding: 4px;
            background: #fff;
        }
    </style>
</head>

<body class="admin-dashboard">
    <div class="admin-wrapper">
        <?php include 'admin-topbar.php'; ?>
        <div class="admin-container">
            <?php include 'admin-sidenav.php'; ?>

            <main class="admin-content">
                <div class="container-fluid">
                    <h1 class="page-title">Home Page Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <!-- Hero Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Hero Section & Carousel</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_hero">
                                
                                <!-- Carousel Images -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5>Carousel Images</h5>
                                        <div class="row g-3">
                                            <?php foreach ($homeData['hero_section']['carousel_images'] as $i => $image): ?>
                                                <div class="col-md-4">
                                                    <div class="section-item">
                                                        <label class="form-label">Carousel Image <?php echo $i + 1; ?></label><br>
                                                        <?php if (!empty($image)): ?>
                                                            <img id="carousel_preview_<?php echo $i + 1; ?>" src="../<?php echo htmlspecialchars($image); ?>" class="carousel-item-preview" alt="Carousel image">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" value="1" id="delete_carousel_image_<?php echo $i + 1; ?>" name="delete_carousel_image_<?php echo $i + 1; ?>">
                                                                <label class="form-check-label" for="delete_carousel_image_<?php echo $i + 1; ?>">Delete current image</label>
                                                            </div>
                                                        <?php else: ?>
                                                            <img id="carousel_preview_<?php echo $i + 1; ?>" src="" class="carousel-item-preview" style="display:none;">
                                                        <?php endif; ?>

                                                        <div class="mb-2">
                                                            <label for="carousel_image_<?php echo $i + 1; ?>" class="form-label">Upload new image</label>
                                                            <input class="form-control" type="file" id="carousel_image_<?php echo $i + 1; ?>" name="carousel_image_<?php echo $i + 1; ?>" accept="image/*">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hero Content -->
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="hero_title" name="hero_title"
                                                value="<?php echo htmlspecialchars($homeData['hero_section']['title']); ?>" required>
                                            <label for="hero_title">Hero Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="hero_description" name="hero_description"
                                                style="height: 100px" required><?php echo htmlspecialchars($homeData['hero_section']['description']); ?></textarea>
                                            <label for="hero_description">Hero Description</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Primary Button</h6>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_text" name="primary_button_text"
                                                value="<?php echo htmlspecialchars($homeData['hero_section']['primary_button']['text']); ?>" required>
                                            <label for="primary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_link" name="primary_button_link"
                                                value="<?php echo htmlspecialchars($homeData['hero_section']['primary_button']['link']); ?>" required>
                                            <label for="primary_button_link">Button Link</label>
                                        </div>
                                        
                                        <h6>Secondary Button</h6>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_text" name="secondary_button_text"
                                                value="<?php echo htmlspecialchars($homeData['hero_section']['secondary_button']['text']); ?>" required>
                                            <label for="secondary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_link" name="secondary_button_link"
                                                value="<?php echo htmlspecialchars($homeData['hero_section']['secondary_button']['link']); ?>" required>
                                            <label for="secondary_button_link">Button Link</label>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Hero Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- About Us Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">About Us Section</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_about">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="about_title" name="about_title"
                                                value="<?php echo htmlspecialchars($homeData['about_section']['title']); ?>" required>
                                            <label for="about_title">Section Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="about_description" name="about_description"
                                                style="height: 120px" required><?php echo htmlspecialchars($homeData['about_section']['description']); ?></textarea>
                                            <label for="about_description">Main Description</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="about_additional_description" name="about_additional_description"
                                                style="height: 100px" required><?php echo htmlspecialchars($homeData['about_section']['additional_description']); ?></textarea>
                                            <label for="about_additional_description">Additional Description</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Current About Image</label><br>
                                        <?php if (!empty($homeData['about_section']['image'])): ?>
                                            <img id="about_preview" src="../<?php echo htmlspecialchars($homeData['about_section']['image']); ?>" class="image-preview" alt="About image">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="delete_about_image" name="delete_about_image">
                                                <label class="form-check-label" for="delete_about_image">Delete current image</label>
                                            </div>
                                        <?php else: ?>
                                            <img id="about_preview" src="" class="image-preview" style="display:none;">
                                        <?php endif; ?>

                                        <div class="mb-2">
                                            <label for="about_image_upload" class="form-label">Upload new image</label>
                                            <input class="form-control" type="file" id="about_image_upload" name="about_image_upload" accept="image/*">
                                        </div>

                                        <!-- Features -->
                                        <?php foreach ($homeData['about_section']['features'] as $i => $feature): ?>
                                            <h6>Feature <?php echo $i + 1; ?></h6>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="feature<?php echo $i + 1; ?>_title" name="feature<?php echo $i + 1; ?>_title"
                                                    value="<?php echo htmlspecialchars($feature['title']); ?>" required>
                                                <label for="feature<?php echo $i + 1; ?>_title">Title</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="feature<?php echo $i + 1; ?>_description" name="feature<?php echo $i + 1; ?>_description"
                                                    style="height: 80px" required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                                                <label for="feature<?php echo $i + 1; ?>_description">Description</label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update About Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Achievements Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Achievements Section</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_achievements">
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="achievements_title" name="achievements_title"
                                                value="<?php echo htmlspecialchars($homeData['achievements_section']['title']); ?>" required>
                                            <label for="achievements_title">Section Title</label>
                                        </div>
                                    </div>

                                    <!-- Existing Achievements -->
                                    <?php foreach ($homeData['achievements_section']['achievements'] as $i => $achievement): ?>
                                        <div class="col-12 section-item">
                                            <h5>Achievement <?php echo $i + 1; ?></h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Current Image</label><br>
                                                    <?php if (!empty($achievement['image'])): ?>
                                                        <img id="achievement<?php echo $i + 1; ?>_preview" src="../<?php echo htmlspecialchars($achievement['image']); ?>" class="small-preview" alt="Achievement image">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" value="1" id="delete_achievement<?php echo $i + 1; ?>_image" name="delete_achievement<?php echo $i + 1; ?>_image">
                                                            <label class="form-check-label" for="delete_achievement<?php echo $i + 1; ?>_image">Delete current image</label>
                                                        </div>
                                                    <?php else: ?>
                                                        <img id="achievement<?php echo $i + 1; ?>_preview" src="" class="small-preview" style="display:none;">
                                                    <?php endif; ?>

                                                    <div class="mb-2">
                                                        <label for="achievement<?php echo $i + 1; ?>_image_upload" class="form-label">Upload new image</label>
                                                        <input class="form-control" type="file" id="achievement<?php echo $i + 1; ?>_image_upload" name="achievement<?php echo $i + 1; ?>_image_upload" accept="image/*">
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="achievement<?php echo $i + 1; ?>_title" name="achievement<?php echo $i + 1; ?>_title"
                                                            value="<?php echo htmlspecialchars($achievement['title']); ?>" required>
                                                        <label for="achievement<?php echo $i + 1; ?>_title">Title</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <textarea class="form-control" id="achievement<?php echo $i + 1; ?>_description" name="achievement<?php echo $i + 1; ?>_description"
                                                            style="height: 80px" required><?php echo htmlspecialchars($achievement['description']); ?></textarea>
                                                        <label for="achievement<?php echo $i + 1; ?>_description">Description</label>
                                                    </div>
                                                    <div class="text-end">
                                                        <button type="submit" form="remove_achievement_form_<?php echo $i; ?>" class="btn btn-danger btn-sm">Remove Achievement</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- New Achievements -->
                                    <div class="col-12">
                                        <h5>Add New Achievements</h5>
                                        <div id="new_achievements_container">
                                            <div class="section-item new-achievement">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label class="form-label">Upload Image</label>
                                                            <input class="form-control" type="file" name="new_achievement_image_upload_0" accept="image/*">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" name="new_achievement_title[]" placeholder="Achievement Title" required>
                                                            <label>Achievement Title</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="new_achievement_description[]" style="height: 80px" placeholder="Description" required></textarea>
                                                            <label>Description</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="add_new_achievement" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="fas fa-plus"></i> Add Another Achievement
                                        </button>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Achievements</button>
                                    </div>
                                </div>
                            </form>

                            <!-- Remove achievement forms -->
                            <?php foreach ($homeData['achievements_section']['achievements'] as $i => $achievement): ?>
                                <form id="remove_achievement_form_<?php echo $i; ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="action" value="remove_achievement">
                                    <input type="hidden" name="remove_index" value="<?php echo $i; ?>">
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Our Strength Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Our Strength Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_strength">
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="strength_title" name="strength_title"
                                                value="<?php echo htmlspecialchars($homeData['strength_section']['title']); ?>" required>
                                            <label for="strength_title">Section Title</label>
                                        </div>
                                    </div>

                                    <!-- Existing Strengths -->
                                    <?php foreach ($homeData['strength_section']['strengths'] as $i => $strength): ?>
                                        <div class="col-md-4">
                                            <div class="section-item">
                                                <h5>Strength <?php echo $i + 1; ?></h5>
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" id="strength<?php echo $i + 1; ?>_number" name="strength<?php echo $i + 1; ?>_number"
                                                        value="<?php echo htmlspecialchars($strength['number']); ?>" required>
                                                    <label for="strength<?php echo $i + 1; ?>_number">Number/Value</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <textarea class="form-control" id="strength<?php echo $i + 1; ?>_description" name="strength<?php echo $i + 1; ?>_description"
                                                        style="height: 80px" required><?php echo htmlspecialchars($strength['description']); ?></textarea>
                                                    <label for="strength<?php echo $i + 1; ?>_description">Description</label>
                                                </div>
                                                <div class="text-end">
                                                    <button type="submit" form="remove_strength_form_<?php echo $i; ?>" class="btn btn-danger btn-sm">Remove Strength</button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- New Strengths -->
                                    <div class="col-12">
                                        <h5>Add New Strengths</h5>
                                        <div id="new_strengths_container">
                                            <div class="section-item new-strength">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" name="new_strength_number[]" placeholder="Number/Value" required>
                                                            <label>Number/Value</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="new_strength_description[]" style="height: 80px" placeholder="Description" required></textarea>
                                                            <label>Description</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="add_new_strength" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="fas fa-plus"></i> Add Another Strength
                                        </button>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Strengths</button>
                                    </div>
                                </div>
                            </form>

                            <!-- Remove strength forms -->
                            <?php foreach ($homeData['strength_section']['strengths'] as $i => $strength): ?>
                                <form id="remove_strength_form_<?php echo $i; ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="action" value="remove_strength">
                                    <input type="hidden" name="remove_index" value="<?php echo $i; ?>">
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- News and Events Section -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">News and Events Section</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_news">
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="news_title" name="news_title"
                                                value="<?php echo htmlspecialchars($homeData['news_section']['title']); ?>" required>
                                            <label for="news_title">Section Title</label>
                                        </div>
                                    </div>

                                    <!-- Existing News -->
                                    <?php foreach ($homeData['news_section']['news'] as $i => $news): ?>
                                        <div class="col-12 section-item">
                                            <h5>News <?php echo $i + 1; ?></h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Current Image</label><br>
                                                    <?php if (!empty($news['image'])): ?>
                                                        <img id="news<?php echo $i + 1; ?>_preview" src="../<?php echo htmlspecialchars($news['image']); ?>" class="small-preview" alt="News image">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" value="1" id="delete_news<?php echo $i + 1; ?>_image" name="delete_news<?php echo $i + 1; ?>_image">
                                                            <label class="form-check-label" for="delete_news<?php echo $i + 1; ?>_image">Delete current image</label>
                                                        </div>
                                                    <?php else: ?>
                                                        <img id="news<?php echo $i + 1; ?>_preview" src="" class="small-preview" style="display:none;">
                                                    <?php endif; ?>

                                                    <div class="mb-2">
                                                        <label for="news<?php echo $i + 1; ?>_image_upload" class="form-label">Upload new image</label>
                                                        <input class="form-control" type="file" id="news<?php echo $i + 1; ?>_image_upload" name="news<?php echo $i + 1; ?>_image_upload" accept="image/*">
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="news<?php echo $i + 1; ?>_title" name="news<?php echo $i + 1; ?>_title"
                                                            value="<?php echo htmlspecialchars($news['title']); ?>" required>
                                                        <label for="news<?php echo $i + 1; ?>_title">Title</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="news<?php echo $i + 1; ?>_author" name="news<?php echo $i + 1; ?>_author"
                                                            value="<?php echo htmlspecialchars($news['author']); ?>" required>
                                                        <label for="news<?php echo $i + 1; ?>_author">Author</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="news<?php echo $i + 1; ?>_date" name="news<?php echo $i + 1; ?>_date"
                                                            value="<?php echo htmlspecialchars($news['date']); ?>" required>
                                                        <label for="news<?php echo $i + 1; ?>_date">Date</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="news<?php echo $i + 1; ?>_link" name="news<?php echo $i + 1; ?>_link"
                                                            value="<?php echo htmlspecialchars($news['link']); ?>" required>
                                                        <label for="news<?php echo $i + 1; ?>_link">Link</label>
                                                    </div>
                                                    <div class="text-end">
                                                        <button type="submit" form="remove_news_form_<?php echo $i; ?>" class="btn btn-danger btn-sm">Remove News</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- New News -->
                                    <div class="col-12">
                                        <h5>Add New News</h5>
                                        <div id="new_news_container">
                                            <div class="section-item new-news">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label class="form-label">Upload Image</label>
                                                            <input class="form-control" type="file" name="new_news_image_upload_0" accept="image/*">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" name="new_news_title[]" placeholder="News Title" required>
                                                            <label>News Title</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" name="new_news_author[]" placeholder="Author" required>
                                                            <label>Author</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" name="new_news_date[]" placeholder="Date" required>
                                                            <label>Date</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" name="new_news_link[]" value="#" placeholder="Link" required>
                                                            <label>Link</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="add_new_news" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="fas fa-plus"></i> Add Another News
                                        </button>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update News</button>
                                    </div>
                                </div>
                            </form>

                            <!-- Remove news forms -->
                            <?php foreach ($homeData['news_section']['news'] as $i => $news): ?>
                                <form id="remove_news_form_<?php echo $i; ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="action" value="remove_news">
                                    <input type="hidden" name="remove_index" value="<?php echo $i; ?>">
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // JS: client-side preview for uploads
        function previewFile(inputEl, previewSelector) {
            const preview = document.getElementById(previewSelector);
            const file = inputEl.files && inputEl.files[0];
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }

        // Carousel image previews
        <?php foreach ($homeData['hero_section']['carousel_images'] as $i => $image): ?>
            (function() {
                const idx = <?php echo $i + 1; ?>;
                const input = document.getElementById('carousel_image_' + idx);
                const previewId = 'carousel_preview_' + idx;
                if (input) {
                    input.addEventListener('change', function() {
                        previewFile(this, previewId);
                    });
                }
            })();
        <?php endforeach; ?>

        // About image preview
        const aboutInput = document.getElementById('about_image_upload');
        if (aboutInput) {
            aboutInput.addEventListener('change', function() {
                previewFile(this, 'about_preview');
            });
        }

        // Achievement image previews
        <?php foreach ($homeData['achievements_section']['achievements'] as $i => $achievement): ?>
            (function() {
                const idx = <?php echo $i + 1; ?>;
                const input = document.getElementById('achievement' + idx + '_image_upload');
                const previewId = 'achievement' + idx + '_preview';
                if (input) {
                    input.addEventListener('change', function() {
                        previewFile(this, previewId);
                    });
                }
            })();
        <?php endforeach; ?>

        // News image previews
        <?php foreach ($homeData['news_section']['news'] as $i => $news): ?>
            (function() {
                const idx = <?php echo $i + 1; ?>;
                const input = document.getElementById('news' + idx + '_image_upload');
                const previewId = 'news' + idx + '_preview';
                if (input) {
                    input.addEventListener('change', function() {
                        previewFile(this, previewId);
                    });
                }
            })();
        <?php endforeach; ?>

        // Add new achievement fields
        let achievementCounter = 1;
        document.getElementById('add_new_achievement').addEventListener('click', function() {
            const container = document.getElementById('new_achievements_container');
            const newAchievement = document.createElement('div');
            newAchievement.className = 'section-item new-achievement mt-3';
            newAchievement.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-2">
                            <label class="form-label">Upload Image</label>
                            <input class="form-control" type="file" name="new_achievement_image_upload_${achievementCounter}" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="new_achievement_title[]" placeholder="Achievement Title" required>
                            <label>Achievement Title</label>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="new_achievement_description[]" style="height: 80px" placeholder="Description" required></textarea>
                            <label>Description</label>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newAchievement);
            achievementCounter++;
        });

        // Add new strength fields
        let strengthCounter = 1;
        document.getElementById('add_new_strength').addEventListener('click', function() {
            const container = document.getElementById('new_strengths_container');
            const newStrength = document.createElement('div');
            newStrength.className = 'section-item new-strength mt-3';
            newStrength.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="new_strength_number[]" placeholder="Number/Value" required>
                            <label>Number/Value</label>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="new_strength_description[]" style="height: 80px" placeholder="Description" required></textarea>
                            <label>Description</label>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newStrength);
            strengthCounter++;
        });

        // Add new news fields
        let newsCounter = 1;
        document.getElementById('add_new_news').addEventListener('click', function() {
            const container = document.getElementById('new_news_container');
            const newNews = document.createElement('div');
            newNews.className = 'section-item new-news mt-3';
            newNews.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-2">
                            <label class="form-label">Upload Image</label>
                            <input class="form-control" type="file" name="new_news_image_upload_${newsCounter}" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="new_news_title[]" placeholder="News Title" required>
                            <label>News Title</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="new_news_author[]" placeholder="Author" required>
                            <label>Author</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="new_news_date[]" placeholder="Date" required>
                            <label>Date</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="new_news_link[]" value="#" placeholder="Link" required>
                            <label>Link</label>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newNews);
            newsCounter++;
        });
    </script>
</body>

</html>