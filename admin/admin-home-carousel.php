<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load carousel data
$carouselData = json_decode(file_get_contents('../data/carousel.json'), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_text_content') {
            // Update text content
            $carouselData['text_content'] = [
                'heading' => $_POST['heading'],
                'subheading' => $_POST['subheading'],
                'button_text' => $_POST['button_text'],
                'button_link' => $_POST['button_link']
            ];
        } elseif ($_POST['action'] === 'add_slide') {
            // Handle image upload
            if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../img/carousel/';
                $fileName = uniqid() . '_' . basename($_FILES['slide_image']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['slide_image']['tmp_name'], $targetPath)) {
                    // Add new slide
                    $newSlide = [
                        'id' => uniqid(),
                        'image' => 'img/carousel/' . $fileName,
                        'alt' => $_POST['slide_alt']
                    ];
                    
                    if (count($carouselData['slides']) < 5) {
                        $carouselData['slides'][] = $newSlide;
                    } else {
                        $error = "Maximum of 5 slides allowed. Please delete a slide before adding a new one.";
                    }
                } else {
                    $error = "Error uploading image.";
                }
            } else {
                $error = "Please select an image to upload.";
            }
        } elseif ($_POST['action'] === 'update_slide') {
            // Update existing slide
            foreach ($carouselData['slides'] as &$slide) {
                if ($slide['id'] === $_POST['slide_id']) {
                    // Check if new image was uploaded
                    if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = '../img/carousel/';
                        $fileName = uniqid() . '_' . basename($_FILES['slide_image']['name']);
                        $targetPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['slide_image']['tmp_name'], $targetPath)) {
                            // Delete old image if it exists
                            if (file_exists('../' . $slide['image'])) {
                                unlink('../' . $slide['image']);
                            }
                            $slide['image'] = 'img/carousel/' . $fileName;
                        } else {
                            $error = "Error uploading image.";
                        }
                    }
                    
                    $slide['alt'] = $_POST['slide_alt'];
                    break;
                }
            }
        }
    } elseif (isset($_POST['delete_slide'])) {
        // Delete slide
        $slideToDelete = null;
        foreach ($carouselData['slides'] as $key => $slide) {
            if ($slide['id'] === $_POST['delete_slide']) {
                $slideToDelete = $slide;
                unset($carouselData['slides'][$key]);
                break;
            }
        }
        
        if ($slideToDelete && file_exists('../' . $slideToDelete['image'])) {
            unlink('../' . $slideToDelete['image']);
        }
        
        $carouselData['slides'] = array_values($carouselData['slides']); // Reindex array
    }

    // Save to JSON file
    file_put_contents('../data/carousel.json', json_encode($carouselData, JSON_PRETTY_PRINT));

    // Success message
    if (!isset($error)) {
        $success = "Carousel information updated successfully!";
    }
}

// Get slide details for editing
$editSlide = null;
if (isset($_GET['edit_slide'])) {
    foreach ($carouselData['slides'] as $slide) {
        if ($slide['id'] === $_GET['edit_slide']) {
            $editSlide = $slide;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Home Carousel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-style.css">
</head>

<body class="admin-dashboard">
    <div class="admin-wrapper">
        <?php include 'admin-topbar.php'; ?>
        <div class="admin-container">
            <?php include 'admin-sidenav.php'; ?>

            <main class="admin-content">
                <div class="container-fluid">
                    <h1 class="page-title">Home Carousel Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <!-- Text Content Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Text Content</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_text_content">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="heading" name="heading" 
                                                value="<?php echo htmlspecialchars($carouselData['text_content']['heading']); ?>" required>
                                            <label for="heading">Heading Text</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="subheading" name="subheading" 
                                                value="<?php echo htmlspecialchars($carouselData['text_content']['subheading']); ?>" required>
                                            <label for="subheading">Subheading Text</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="button_text" name="button_text" 
                                                value="<?php echo htmlspecialchars($carouselData['text_content']['button_text']); ?>" required>
                                            <label for="button_text">Button Text</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="button_link" name="button_link" disabled
                                                value="<?php echo htmlspecialchars($carouselData['text_content']['button_link']); ?>" required>
                                            <label for="button_link">Button Link</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Text Content</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Slides Management -->
                    <div class="admin-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="section-title mb-0">Carousel Slides (<?php echo count($carouselData['slides']); ?>/5)</h4>
                                <button class="btn admin-contact-btn" data-bs-toggle="modal" data-bs-target="#addSlideModal" <?php echo count($carouselData['slides']) >= 5 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-plus me-2"></i>Add New Slide
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table admin-table">
                                    <thead>
                                        <tr>
                                            <th>Preview</th>
                                            <th>Alt Text</th>
                                            <th>Image Path</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($carouselData['slides'] as $slide): ?>
                                            <tr>
                                                <td>
                                                    <img src="../<?php echo htmlspecialchars($slide['image']); ?>" alt="<?php echo htmlspecialchars($slide['alt']); ?>" style="max-width: 100px; max-height: 60px;">
                                                </td>
                                                <td><?php echo htmlspecialchars($slide['alt']); ?></td>
                                                <td><?php echo htmlspecialchars($slide['image']); ?></td>
                                                <td>
                                                    <a href="?edit_slide=<?php echo $slide['id']; ?>" class="btn btn-sm btn-primary me-2">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="delete_slide" value="<?php echo $slide['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this slide?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Slide Modal -->
    <div class="modal fade" id="addSlideModal" tabindex="-1" aria-labelledby="addSlideModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSlideModalLabel">
                        <?php echo $editSlide ? 'Edit Slide' : 'Add New Slide'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $editSlide ? 'update_slide' : 'add_slide'; ?>">
                    <?php if ($editSlide): ?>
                        <input type="hidden" name="slide_id" value="<?php echo $editSlide['id']; ?>">
                    <?php endif; ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="slide_image" class="form-label">Slide Image</label>
                            <input class="form-control" type="file" id="slide_image" name="slide_image" <?php echo !$editSlide ? 'required' : ''; ?>>
                            <?php if ($editSlide): ?>
                                <div class="form-text">Leave empty to keep current image</div>
                                <div class="mt-2">
                                    <small>Current Image:</small><br>
                                    <img src="../<?php echo htmlspecialchars($editSlide['image']); ?>" alt="<?php echo htmlspecialchars($editSlide['alt']); ?>" style="max-width: 100%; max-height: 150px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="slide_alt" name="slide_alt" 
                                value="<?php echo $editSlide ? htmlspecialchars($editSlide['alt']) : ''; ?>" required>
                            <label for="slide_alt">Alt Text</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn admin-contact-btn">Save Slide</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show modal if editing slide
        <?php if ($editSlide): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('addSlideModal'));
                modal.show();
            });
        <?php endif; ?>
    </script>
</body>

</html>