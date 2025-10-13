<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load facilities data
$facilitiesData = json_decode(file_get_contents('../data/facilities.json'), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_hero':
                $facilitiesData['page']['hero'] = [
                    'background_image' => $_POST['background_image'],
                    'title' => $_POST['hero_title'],
                    'description' => $_POST['hero_description']
                ];
                break;

            case 'update_facilities':
                $facilities = [];
                for ($i = 1; $i <= 4; $i++) {
                    $facilities[] = [
                        'title' => $_POST["facility{$i}_title"],
                        'image' => $_POST["facility{$i}_image"],
                        'alt' => $_POST["facility{$i}_alt"],
                        'items' => array_filter(array_map('trim', explode("\n", $_POST["facility{$i}_items"]))),
                        'layout' => $_POST["facility{$i}_layout"]
                    ];
                }
                $facilitiesData['page']['facilities'] = $facilities;
                break;
        }
    }

    // Save to JSON file
    file_put_contents('../data/facilities.json', json_encode($facilitiesData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Facilities page information updated successfully!";
}

// Handle image upload
if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === 0) {
    $uploadDir = '../images/facilities/';
    $fileName = uniqid() . '_' . basename($_FILES['image_upload']['name']);
    $targetFile = $uploadDir . $fileName;
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES['image_upload']['tmp_name']);
    if ($check !== false) {
        if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $targetFile)) {
            $uploadSuccess = "Image uploaded successfully: " . $fileName;
            $uploadedImagePath = 'images/facilities/' . $fileName;
        } else {
            $uploadError = "Sorry, there was an error uploading your file.";
        }
    } else {
        $uploadError = "File is not an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Facilities Page Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-style.css">
    <style>
        .upload-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .facility-section {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fafafa;
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
                    <h1 class="page-title">Facilities Page Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if (isset($uploadSuccess)): ?>
                        <div class="alert alert-success"><?php echo $uploadSuccess; ?></div>
                    <?php endif; ?>

                    <?php if (isset($uploadError)): ?>
                        <div class="alert alert-danger"><?php echo $uploadError; ?></div>
                    <?php endif; ?>

                    <!-- Hero Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Hero Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_hero">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="background_image" name="background_image" 
                                                value="<?php echo htmlspecialchars($facilitiesData['page']['hero']['background_image']); ?>" required disabled>
                                            <label for="background_image">Background Image Path</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="hero_title" name="hero_title" 
                                                value="<?php echo htmlspecialchars($facilitiesData['page']['hero']['title']); ?>" required>
                                            <label for="hero_title">Hero Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="hero_description" name="hero_description" 
                                                style="height: 100px" required><?php echo htmlspecialchars($facilitiesData['page']['hero']['description']); ?></textarea>
                                            <label for="hero_description">Hero Description</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Hero Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Facilities Section -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">Facilities Sections</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_facilities">
                                
                                <?php foreach ($facilitiesData['page']['facilities'] as $i => $facility): ?>
                                <div class="facility-section">
                                    <h5>Facility Section <?php echo $i + 1; ?></h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="facility<?php echo $i + 1; ?>_title" name="facility<?php echo $i + 1; ?>_title" 
                                                    value="<?php echo htmlspecialchars($facility['title']); ?>" required>
                                                <label for="facility<?php echo $i + 1; ?>_title">Section Title</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="facility<?php echo $i + 1; ?>_alt" name="facility<?php echo $i + 1; ?>_alt" 
                                                    value="<?php echo htmlspecialchars($facility['alt']); ?>" required>
                                                <label for="facility<?php echo $i + 1; ?>_alt">Image Alt Text</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="facility<?php echo $i + 1; ?>_image" name="facility<?php echo $i + 1; ?>_image" 
                                                    value="<?php echo htmlspecialchars($facility['image']); ?>" required disabled>
                                                <label for="facility<?php echo $i + 1; ?>_image">Image Path</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="facility<?php echo $i + 1; ?>_layout" name="facility<?php echo $i + 1; ?>_layout" required>
                                                    <option value="normal" <?php echo $facility['layout'] === 'normal' ? 'selected' : ''; ?>>Image Left / Text Right</option>
                                                    <option value="reverse" <?php echo $facility['layout'] === 'reverse' ? 'selected' : ''; ?>>Image Right / Text Left</option>
                                                </select>
                                                <label for="facility<?php echo $i + 1; ?>_layout">Layout</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="facility<?php echo $i + 1; ?>_items" name="facility<?php echo $i + 1; ?>_items" 
                                                    style="height: 200px" required><?php echo htmlspecialchars(implode("\n", $facility['items'])); ?></textarea>
                                                <label for="facility<?php echo $i + 1; ?>_items">Facility Items (one per line)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="text-end">
                                    <button type="submit" class="btn admin-contact-btn">Update All Facilities Sections</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>