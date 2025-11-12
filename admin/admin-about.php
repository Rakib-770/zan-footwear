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

// Load about data
$aboutJsonPath = __DIR__ . '/../data/about.json';
$aboutData = json_decode(file_get_contents($aboutJsonPath), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Note: Each form posts an "action" value
    $action = $_POST['action'] ?? '';

    // -- Handle file uploads / deletes before assembling the posted data so we can use results --
    // Hero upload/delete
    $hero_uploaded = handle_upload('hero_background_upload');
    $hero_delete = isset($_POST['delete_hero_image']) && $_POST['delete_hero_image'] === '1';

    // Our story upload/delete
    $our_uploaded = handle_upload('our_story_image_upload');
    $our_delete = isset($_POST['delete_our_story_image']) && $_POST['delete_our_story_image'] === '1';

    // Team member uploads/deletes - for 3 members
    $team_uploaded = []; // index 0..2 => new path or null
    $team_delete_flags = [];
    for ($i = 1; $i <= 3; $i++) {
        $field = "member{$i}_image_upload";
        $team_uploaded[$i - 1] = handle_upload($field);
        $team_delete_flags[$i - 1] = isset($_POST["delete_member_{$i}_image"]) && $_POST["delete_member_{$i}_image"] === '1';
    }

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

    // Now switch by action
    switch ($action) {
        case 'update_hero':
            // If admin uploaded new image, delete old file and set new path
            if ($hero_uploaded) {
                // remove old hero image file if exists
                if (!empty($aboutData['hero_section']['background_image'])) {
                    safe_unlink($aboutData['hero_section']['background_image']);
                }
                $aboutData['hero_section']['background_image'] = $hero_uploaded;
            } elseif ($hero_delete) {
                // delete old
                if (!empty($aboutData['hero_section']['background_image'])) {
                    safe_unlink($aboutData['hero_section']['background_image']);
                }
                $aboutData['hero_section']['background_image'] = "";
            } else {
                // no upload, no delete -> preserve existing
            }

            // Other hero fields come from POST (these inputs are enabled)
            $aboutData['hero_section']['title'] = $_POST['hero_title'] ?? $aboutData['hero_section']['title'];
            $aboutData['hero_section']['description'] = $_POST['hero_description'] ?? $aboutData['hero_section']['description'];
            $aboutData['hero_section']['primary_button']['text'] = $_POST['primary_button_text'] ?? $aboutData['hero_section']['primary_button']['text'];
            $aboutData['hero_section']['primary_button']['link'] = $_POST['primary_button_link'] ?? $aboutData['hero_section']['primary_button']['link'];
            $aboutData['hero_section']['secondary_button']['text'] = $_POST['secondary_button_text'] ?? $aboutData['hero_section']['secondary_button']['text'];
            $aboutData['hero_section']['secondary_button']['link'] = $_POST['secondary_button_link'] ?? $aboutData['hero_section']['secondary_button']['link'];
            break;

        case 'update_our_story':
            // image handling
            if ($our_uploaded) {
                if (!empty($aboutData['our_story_section']['image'])) {
                    safe_unlink($aboutData['our_story_section']['image']);
                }
                $aboutData['our_story_section']['image'] = $our_uploaded;
            } elseif ($our_delete) {
                if (!empty($aboutData['our_story_section']['image'])) {
                    safe_unlink($aboutData['our_story_section']['image']);
                }
                $aboutData['our_story_section']['image'] = "";
            } else {
                // keep existing
            }

            // Regular fields
            $aboutData['our_story_section']['title'] = $_POST['our_story_title'] ?? $aboutData['our_story_section']['title'];
            $aboutData['our_story_section']['description'] = $_POST['our_story_description'] ?? $aboutData['our_story_section']['description'];
            // paragraphs: convert lines to array
            $paragraphs = array_filter(array_map('trim', explode("\n", $_POST['paragraphs'] ?? "")));
            if (!empty($paragraphs)) {
                $aboutData['our_story_section']['paragraphs'] = $paragraphs;
            }

            // Features: Only 2 features expected. You asked to disable Feature 1 and its icon path input.
            // So for feature 1 (index 0) preserve its icon and title if inputs missing.
            $features = $aboutData['our_story_section']['features']; // existing
            for ($i = 0; $i < count($features); $i++) {
                $idx = $i + 1;
                // icon - feature1 icon disabled in UI, so likely missing from POST -> preserve
                $iconKey = "feature{$idx}_icon";
                $titleKey = "feature{$idx}_title";
                $descKey = "feature{$idx}_description";

                if (isset($_POST[$descKey])) {
                    $features[$i]['description'] = $_POST[$descKey];
                }
                // For feature 1 specifically: you requested disabling "Feature 1 and Feature 1 icon path input field."
                if ($i === 0) {
                    // Preserve icon and title (don't overwrite)
                    // But if admin had provided description (allowed above), we'll update it
                } else {
                    // other features: update if provided
                    if (isset($_POST[$iconKey])) {
                        $features[$i]['icon'] = $_POST[$iconKey];
                    }
                    if (isset($_POST[$titleKey])) {
                        $features[$i]['title'] = $_POST[$titleKey];
                    }
                }
            }
            $aboutData['our_story_section']['features'] = $features;
            break;

        case 'update_mission_vision':
            // You asked to disable mission & vision image path input field -> preserve existing unless upload provided (we don't provide upload for mission in UI)
            // So we do not accept a new mission_vision_image from POST (field disabled), preserve existing
            $aboutData['mission_vision_section']['title'] = $_POST['mission_vision_title'] ?? $aboutData['mission_vision_section']['title'];
            $aboutData['mission_vision_section']['mission']['title'] = $_POST['mission_title'] ?? $aboutData['mission_vision_section']['mission']['title'];
            $aboutData['mission_vision_section']['mission']['description'] = $_POST['mission_description'] ?? $aboutData['mission_vision_section']['mission']['description'];
            $aboutData['mission_vision_section']['vision']['title'] = $_POST['vision_title'] ?? $aboutData['mission_vision_section']['vision']['title'];
            $aboutData['mission_vision_section']['vision']['description'] = $_POST['vision_description'] ?? $aboutData['mission_vision_section']['vision']['description'];
            // image not overwritten
            break;

        case 'update_values':
            // Values title
            $aboutData['values_section']['title'] = $_POST['values_title'] ?? $aboutData['values_section']['title'];
            // Values: icons disabled in UI. So preserve icons from existing JSON.
            $values = $aboutData['values_section']['values'];
            for ($i = 0; $i < count($values); $i++) {
                $idx = $i + 1;
                $titleKey = "value{$idx}_title";
                $descKey = "value{$idx}_description";
                if (isset($_POST[$titleKey])) {
                    $values[$i]['title'] = $_POST[$titleKey];
                }
                if (isset($_POST[$descKey])) {
                    $values[$i]['description'] = $_POST[$descKey];
                }
                // icon left as-is
            }
            $aboutData['values_section']['values'] = $values;
            break;

        case 'update_manufacturing':
            $aboutData['manufacturing_section']['title'] = $_POST['manufacturing_title'] ?? $aboutData['manufacturing_section']['title'];
            $aboutData['manufacturing_section']['description'] = $_POST['manufacturing_description'] ?? $aboutData['manufacturing_section']['description'];
            $featuresInput = array_filter(array_map('trim', explode("\n", $_POST['manufacturing_features'] ?? "")));
            if (!empty($featuresInput)) {
                $aboutData['manufacturing_section']['features'] = $featuresInput;
            }
            $aboutData['manufacturing_section']['conclusion'] = $_POST['manufacturing_conclusion'] ?? $aboutData['manufacturing_section']['conclusion'];
            // manufacturing images: UI disabled -> preserve existing images; do not accept new ones
            break;

        case 'update_team':
            $aboutData['team_section']['title'] = $_POST['team_title'] ?? $aboutData['team_section']['title'];
            $members = $aboutData['team_section']['members'];
            for ($i = 0; $i < count($members); $i++) {
                $idx = $i + 1;
                // Image: we support upload/delete for team images
                if (!empty($team_uploaded[$i])) {
                    // delete old
                    if (!empty($members[$i]['image'])) safe_unlink($members[$i]['image']);
                    $members[$i]['image'] = $team_uploaded[$i];
                } elseif ($team_delete_flags[$i]) {
                    if (!empty($members[$i]['image'])) safe_unlink($members[$i]['image']);
                    $members[$i]['image'] = "";
                } else {
                    // preserve existing image
                }

                // Name / position / bio can be updated from POST (these inputs are enabled)
                $members[$i]['name'] = $_POST["member{$idx}_name"] ?? $members[$i]['name'];
                $members[$i]['position'] = $_POST["member{$idx}_position"] ?? $members[$i]['position'];
                $members[$i]['bio'] = $_POST["member{$idx}_bio"] ?? $members[$i]['bio'];

                // Social links: you requested to disable all icon class inputs, so preserve 'icon' values and only accept link changes
                for ($j = 0; $j < count($members[$i]['social_links']); $j++) {
                    $linkKey = "member{$idx}_social_link" . ($j + 1);
                    if (isset($_POST[$linkKey])) {
                        $members[$i]['social_links'][$j]['link'] = $_POST[$linkKey];
                    }
                    // icon left as-is
                }
            }
            $aboutData['team_section']['members'] = $members;
            break;

        case 'update_global_presence':
            $aboutData['global_presence_section']['title'] = $_POST['global_presence_title'] ?? $aboutData['global_presence_section']['title'];
            $features = $aboutData['global_presence_section']['features'];
            for ($i = 0; $i < count($features); $i++) {
                $idx = $i + 1;
                $titleKey = "global_feature{$idx}_title";
                $descKey = "global_feature{$idx}_description";
                if (isset($_POST[$titleKey])) {
                    $features[$i]['title'] = $_POST[$titleKey];
                }
                if (isset($_POST[$descKey])) {
                    $features[$i]['description'] = $_POST[$descKey];
                }
                // icon preserved (disabled in UI)
            }
            $aboutData['global_presence_section']['features'] = $features;
            break;
    }

    // Save to JSON file
    file_put_contents($aboutJsonPath, json_encode($aboutData, JSON_PRETTY_PRINT));

    // Success message
    $success = "About page information updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - About Page Management</title>
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
            max-width: 100%;
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
                    <h1 class="page-title">About Page Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <!-- Hero Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Hero Section</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_hero">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Current Background Image</label><br>
                                        <?php if (!empty($aboutData['hero_section']['background_image'])): ?>
                                            <img id="hero_preview" src="../<?php echo htmlspecialchars($aboutData['hero_section']['background_image']); ?>" class="image-preview" alt="Hero background">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="delete_hero_image" name="delete_hero_image">
                                                <label class="form-check-label" for="delete_hero_image">Delete current image</label>
                                            </div>
                                        <?php else: ?>
                                            <img id="hero_preview" src="" class="image-preview" style="display:none;">
                                        <?php endif; ?>

                                        <div class="mb-2">
                                            <label for="hero_background_upload" class="form-label">Upload new background image</label>
                                            <input class="form-control" type="file" id="hero_background_upload" name="hero_background_upload" accept="image/*">
                                            <div class="form-text">Allowed: jpg, jpeg, png, webp, gif, svg. Upload replaces existing image.</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="hero_title" name="hero_title"
                                                value="<?php echo htmlspecialchars($aboutData['hero_section']['title']); ?>" required>
                                            <label for="hero_title">Hero Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="hero_description" name="hero_description"
                                                style="height: 100px" required><?php echo htmlspecialchars($aboutData['hero_section']['description']); ?></textarea>
                                            <label for="hero_description">Hero Description</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_text" name="primary_button_text"
                                                value="<?php echo htmlspecialchars($aboutData['hero_section']['primary_button']['text']); ?>" required>
                                            <label for="primary_button_text">Primary Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_link" name="primary_button_link"
                                                value="<?php echo htmlspecialchars($aboutData['hero_section']['primary_button']['link']); ?>" required>
                                            <label for="primary_button_link">Primary Button Link</label>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Hero Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Our Story Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Our Story Section</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_our_story">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="our_story_title" name="our_story_title"
                                                value="<?php echo htmlspecialchars($aboutData['our_story_section']['title']); ?>" required>
                                            <label for="our_story_title">Section Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="our_story_description" name="our_story_description"
                                                style="height: 100px" required><?php echo htmlspecialchars($aboutData['our_story_section']['description']); ?></textarea>
                                            <label for="our_story_description">Main Description</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="paragraphs" name="paragraphs"
                                                style="height: 150px"><?php echo htmlspecialchars(implode("\n", $aboutData['our_story_section']['paragraphs'])); ?></textarea>
                                            <label for="paragraphs">Additional Paragraphs (one per line)</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Current Section Image</label><br>
                                        <?php if (!empty($aboutData['our_story_section']['image'])): ?>
                                            <img id="our_preview" src="../<?php echo htmlspecialchars($aboutData['our_story_section']['image']); ?>" class="image-preview" alt="Our story image">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="delete_our_story_image" name="delete_our_story_image">
                                                <label class="form-check-label" for="delete_our_story_image">Delete current image</label>
                                            </div>
                                        <?php else: ?>
                                            <img id="our_preview" src="" class="image-preview" style="display:none;">
                                        <?php endif; ?>

                                        <div class="mb-2">
                                            <label for="our_story_image_upload" class="form-label">Upload new section image</label>
                                            <input class="form-control" type="file" id="our_story_image_upload" name="our_story_image_upload" accept="image/*">
                                            <div class="form-text">Upload replaces existing image.</div>
                                        </div>
                                    </div>

                                    <!-- Features -->
                                    <?php foreach ($aboutData['our_story_section']['features'] as $i => $feature): ?>
                                        <div class="col-md-6">
                                            <h5>Feature <?php echo $i + 1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <!-- Feature 1 icon input disabled as requested -->
                                                <input type="text" class="form-control" id="feature<?php echo $i + 1; ?>_icon" name="feature<?php echo $i + 1; ?>_icon"
                                                    value="<?php echo htmlspecialchars($feature['icon']); ?>" <?php echo $i === 0 ? 'disabled' : ''; ?>>
                                                <label for="feature<?php echo $i + 1; ?>_icon">Icon Path</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <!-- If you said "disable Feature 1", we treat title also as disabled -->
                                                <input type="text" class="form-control" id="feature<?php echo $i + 1; ?>_title" name="feature<?php echo $i + 1; ?>_title"
                                                    value="<?php echo htmlspecialchars($feature['title']); ?>" <?php echo $i === 0 ? 'disabled' : ''; ?>>
                                                <label for="feature<?php echo $i + 1; ?>_title">Title</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="feature<?php echo $i + 1; ?>_description" name="feature<?php echo $i + 1; ?>_description"
                                                    style="height: 100px" required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                                                <label for="feature<?php echo $i + 1; ?>_description">Description</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Our Story Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Mission & Vision Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Mission & Vision Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_mission_vision">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <!-- Disabled image path as requested -->
                                            <input type="text" class="form-control" id="mission_vision_image" name="mission_vision_image"
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_section']['image']); ?>" disabled>
                                            <label for="mission_vision_image">Image Path (disabled)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="mission_vision_title" name="mission_vision_title"
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_section']['title']); ?>" required>
                                            <label for="mission_vision_title">Section Title</label>
                                        </div>
                                    </div>

                                    <!-- Mission -->
                                    <div class="col-md-6">
                                        <h5>Mission</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="mission_title" name="mission_title"
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_section']['mission']['title']); ?>" required>
                                            <label for="mission_title">Mission Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="mission_description" name="mission_description"
                                                style="height: 150px" required><?php echo htmlspecialchars($aboutData['mission_vision_section']['mission']['description']); ?></textarea>
                                            <label for="mission_description">Mission Description</label>
                                        </div>
                                    </div>

                                    <!-- Vision -->
                                    <div class="col-md-6">
                                        <h5>Vision</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="vision_title" name="vision_title"
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_section']['vision']['title']); ?>" required>
                                            <label for="vision_title">Vision Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="vision_description" name="vision_description"
                                                style="height: 150px" required><?php echo htmlspecialchars($aboutData['mission_vision_section']['vision']['description']); ?></textarea>
                                            <label for="vision_description">Vision Description</label>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Mission & Vision</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Values Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Values Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_values">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="values_title" name="values_title"
                                                value="<?php echo htmlspecialchars($aboutData['values_section']['title']); ?>" required>
                                            <label for="values_title">Section Title</label>
                                        </div>
                                    </div>

                                    <?php foreach ($aboutData['values_section']['values'] as $i => $value): ?>
                                        <div class="col-md-6 col-lg-3">
                                            <h5>Value <?php echo $i + 1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <!-- Icon path inputs disabled as requested -->
                                                <input type="text" class="form-control" id="value<?php echo $i + 1; ?>_icon" name="value<?php echo $i + 1; ?>_icon"
                                                    value="<?php echo htmlspecialchars($value['icon']); ?>" disabled>
                                                <label for="value<?php echo $i + 1; ?>_icon">Icon Path (disabled)</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="value<?php echo $i + 1; ?>_title" name="value<?php echo $i + 1; ?>_title"
                                                    value="<?php echo htmlspecialchars($value['title']); ?>" required>
                                                <label for="value<?php echo $i + 1; ?>_title">Title</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="value<?php echo $i + 1; ?>_description" name="value<?php echo $i + 1; ?>_description"
                                                    style="height: 100px" required><?php echo htmlspecialchars($value['description']); ?></textarea>
                                                <label for="value<?php echo $i + 1; ?>_description">Description</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Values Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Manufacturing Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Manufacturing Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_manufacturing">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="manufacturing_title" name="manufacturing_title"
                                                value="<?php echo htmlspecialchars($aboutData['manufacturing_section']['title']); ?>" required>
                                            <label for="manufacturing_title">Section Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="manufacturing_description" name="manufacturing_description"
                                                style="height: 100px" required><?php echo $aboutData['manufacturing_section']['description']; ?></textarea>
                                            <label for="manufacturing_description">Description</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="manufacturing_features" name="manufacturing_features"
                                                style="height: 150px"><?php echo htmlspecialchars(implode("\n", $aboutData['manufacturing_section']['features'])); ?></textarea>
                                            <label for="manufacturing_features">Features (one per line)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="manufacturing_conclusion" name="manufacturing_conclusion"
                                                style="height: 100px" required><?php echo htmlspecialchars($aboutData['manufacturing_section']['conclusion']); ?></textarea>
                                            <label for="manufacturing_conclusion">Conclusion</label>
                                        </div>
                                    </div>

                                    <!-- Images listed but inputs disabled (as requested) -->
                                    <?php foreach ($aboutData['manufacturing_section']['images'] as $i => $image): ?>
                                        <div class="col-md-4">
                                            <h5>Image <?php echo $i + 1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="manufacturing_image<?php echo $i + 1; ?>" name="manufacturing_image<?php echo $i + 1; ?>"
                                                    value="<?php echo htmlspecialchars($image); ?>" disabled>
                                                <label for="manufacturing_image<?php echo $i + 1; ?>">Image Path (disabled)</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Manufacturing Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Team Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Team Section</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_team">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="team_title" name="team_title"
                                                value="<?php echo htmlspecialchars($aboutData['team_section']['title']); ?>" required>
                                            <label for="team_title">Section Title</label>
                                        </div>
                                    </div>

                                    <?php foreach ($aboutData['team_section']['members'] as $i => $member): ?>
                                        <?php $idx = $i + 1; ?>
                                        <div class="col-md-4">
                                            <h5>Team Member <?php echo $idx; ?></h5>

                                            <label class="form-label">Current Image</label><br>
                                            <?php if (!empty($member['image'])): ?>
                                                <img id="member<?php echo $idx; ?>_preview" src="../<?php echo htmlspecialchars($member['image']); ?>" class="small-preview" alt="Member image">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="1" id="delete_member_<?php echo $idx; ?>_image" name="delete_member_<?php echo $idx; ?>_image">
                                                    <label class="form-check-label" for="delete_member_<?php echo $idx; ?>_image">Delete current image</label>
                                                </div>
                                            <?php else: ?>
                                                <img id="member<?php echo $idx; ?>_preview" src="" class="small-preview" style="display:none;">
                                            <?php endif; ?>

                                            <div class="mb-2">
                                                <label for="member<?php echo $idx; ?>_image_upload" class="form-label">Upload new image</label>
                                                <input class="form-control" type="file" id="member<?php echo $idx; ?>_image_upload" name="member<?php echo $idx; ?>_image_upload" accept="image/*">
                                                <div class="form-text">Upload replaces existing image.</div>
                                            </div>

                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="member<?php echo $idx; ?>_name" name="member<?php echo $idx; ?>_name"
                                                    value="<?php echo htmlspecialchars($member['name']); ?>" required>
                                                <label for="member<?php echo $idx; ?>_name">Name</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="member<?php echo $idx; ?>_position" name="member<?php echo $idx; ?>_position"
                                                    value="<?php echo htmlspecialchars($member['position']); ?>" required>
                                                <label for="member<?php echo $idx; ?>_position">Position</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="member<?php echo $idx; ?>_bio" name="member<?php echo $idx; ?>_bio"
                                                    style="height: 100px" required><?php echo htmlspecialchars($member['bio']); ?></textarea>
                                                <label for="member<?php echo $idx; ?>_bio">Bio</label>
                                            </div>

                                            <!-- Social Links -->
                                            <?php foreach ($member['social_links'] as $j => $social): ?>
                                                <?php $jidx = $j + 1; ?>
                                                <h6>Social Link <?php echo $jidx; ?></h6>
                                                <div class="form-floating mb-3">
                                                    <!-- icon input disabled as requested -->
                                                    <input type="text" class="form-control" id="member<?php echo $idx; ?>_social_icon<?php echo $jidx; ?>" name="member<?php echo $idx; ?>_social_icon<?php echo $jidx; ?>"
                                                        value="<?php echo htmlspecialchars($social['icon']); ?>" disabled>
                                                    <label for="member<?php echo $idx; ?>_social_icon<?php echo $jidx; ?>">Icon Class (disabled)</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" id="member<?php echo $idx; ?>_social_link<?php echo $jidx; ?>" name="member<?php echo $idx; ?>_social_link<?php echo $jidx; ?>"
                                                        value="<?php echo htmlspecialchars($social['link']); ?>" required>
                                                    <label for="member<?php echo $idx; ?>_social_link<?php echo $jidx; ?>">Link</label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Team Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Global Presence Section -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">Global Presence Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_global_presence">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="global_presence_title" name="global_presence_title"
                                                value="<?php echo htmlspecialchars($aboutData['global_presence_section']['title']); ?>" required>
                                            <label for="global_presence_title">Section Title</label>
                                        </div>
                                    </div>

                                    <?php foreach ($aboutData['global_presence_section']['features'] as $i => $feature): ?>
                                        <div class="col-md-4">
                                            <h5>Feature <?php echo $i + 1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <!-- icon path disabled -->
                                                <input type="text" class="form-control" id="global_feature<?php echo $i + 1; ?>_icon" name="global_feature<?php echo $i + 1; ?>_icon"
                                                    value="<?php echo htmlspecialchars($feature['icon']); ?>" disabled>
                                                <label for="global_feature<?php echo $i + 1; ?>_icon">Icon Path (disabled)</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="global_feature<?php echo $i + 1; ?>_title" name="global_feature<?php echo $i + 1; ?>_title"
                                                    value="<?php echo htmlspecialchars($feature['title']); ?>" required>
                                                <label for="global_feature<?php echo $i + 1; ?>_title">Title</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="global_feature<?php echo $i + 1; ?>_description" name="global_feature<?php echo $i + 1; ?>_description"
                                                    style="height: 100px" required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                                                <label for="global_feature<?php echo $i + 1; ?>_description">Description</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Global Presence</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // JS: client-side preview for hero, our story and team uploads
        function previewFile(inputEl, previewSelector) {
            const preview = document.getElementById(previewSelector);
            const file = inputEl.files && inputEl.files[0];
            if (!file) {
                // if no file, keep existing preview
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }

        // Hero
        const heroInput = document.getElementById('hero_background_upload');
        if (heroInput) {
            heroInput.addEventListener('change', function() {
                previewFile(this, 'hero_preview');
            });
        }

        // Our story
        const ourInput = document.getElementById('our_story_image_upload');
        if (ourInput) {
            ourInput.addEventListener('change', function() {
                previewFile(this, 'our_preview');
            });
        }

        // Team members
        <?php for ($i = 1; $i <= 3; $i++): ?>
                (function() {
                    const idx = <?php echo $i; ?>;
                    const input = document.getElementById('member' + idx + '_image_upload');
                    const previewId = 'member' + idx + '_preview';
                    if (input) {
                        input.addEventListener('change', function() {
                            previewFile(this, previewId);
                        });
                    }
                })();
        <?php endfor; ?>
    </script>
</body>

</html>