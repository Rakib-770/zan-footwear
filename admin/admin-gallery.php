<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load gallery data
$galleryData = json_decode(file_get_contents('../data/gallery.json'), true) ?? [
    'images' => [],
    'videos' => []
];

// Handle image upload
if (isset($_POST['upload_image'])) {
    $targetDir = "../img/gallery/";
    $fileName = basename($_FILES["image_file"]["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES["image_file"]["tmp_name"]);
    if ($check === false) {
        $error = "File is not an image.";
    } elseif (file_exists($targetFile)) {
        $error = "Sorry, file already exists.";
    } elseif ($_FILES["image_file"]["size"] > 5000000) {
        $error = "Sorry, your file is too large.";
    } elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    } elseif (move_uploaded_file($_FILES["image_file"]["tmp_name"], $targetFile)) {
        $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
        
        $newImage = [
            'id' => uniqid(),
            'filename' => $fileName,
            'categories' => $categories,
            'uploaded_at' => date('Y-m-d H:i:s')
        ];
        
        $galleryData['images'][] = $newImage;
        file_put_contents('../data/gallery.json', json_encode($galleryData, JSON_PRETTY_PRINT));
        $success = "Image uploaded successfully!";
    } else {
        $error = "Sorry, there was an error uploading your file.";
    }
}

// Handle video link addition
if (isset($_POST['add_video'])) {
    $videoId = $_POST['video_id'];
    $videoTitle = $_POST['video_title'];
    
    // Validate YouTube video ID format
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
        $newVideo = [
            'id' => uniqid(),
            'video_id' => $videoId,
            'title' => $videoTitle,
            'added_at' => date('Y-m-d H:i:s')
        ];
        
        $galleryData['videos'][] = $newVideo;
        file_put_contents('../data/gallery.json', json_encode($galleryData, JSON_PRETTY_PRINT));
        $success = "Video added successfully!";
    } else {
        $error = "Invalid YouTube video ID format.";
    }
}

// Handle image deletion
if (isset($_GET['delete_image'])) {
    $imageId = $_GET['delete_image'];
    $imageIndex = array_search($imageId, array_column($galleryData['images'], 'id'));
    
    if ($imageIndex !== false) {
        $image = $galleryData['images'][$imageIndex];
        $filePath = "../img/gallery/" . $image['filename'];
        
        // Delete file from server
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Remove from array
        array_splice($galleryData['images'], $imageIndex, 1);
        file_put_contents('../data/gallery.json', json_encode($galleryData, JSON_PRETTY_PRINT));
        $success = "Image deleted successfully!";
    }
}

// Handle video deletion
if (isset($_GET['delete_video'])) {
    $videoId = $_GET['delete_video'];
    $videoIndex = array_search($videoId, array_column($galleryData['videos'], 'id'));
    
    if ($videoIndex !== false) {
        array_splice($galleryData['videos'], $videoIndex, 1);
        file_put_contents('../data/gallery.json', json_encode($galleryData, JSON_PRETTY_PRINT));
        $success = "Video deleted successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gallery Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-style.css">
    <style>
        
    </style>
</head>

<body class="admin-dashboard">
    <div class="admin-wrapper">
        <?php include 'admin-topbar.php'; ?>

        <div class="admin-container">
            <?php include 'admin-sidenav.php'; ?>

            <main class="admin-content">
                <div class="container-fluid">
                    <h1 class="page-title">Gallery Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="admin-card mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Upload New Image</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="image_file" class="form-label">Select Image</label>
                                            <input class="form-control" type="file" id="image_file" name="image_file" required accept="image/*">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Categories</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="category_events" name="categories[]" value="events">
                                                <label class="form-check-label" for="category_events">Events</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="category_activations" name="categories[]" value="activations">
                                                <label class="form-check-label" for="category_activations">Activations</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="category_visuals" name="categories[]" value="visuals">
                                                <label class="form-check-label" for="category_visuals">Audio Visuals</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="category_design" name="categories[]" value="design">
                                                <label class="form-check-label" for="category_design">Interactive Designs</label>
                                            </div>
                                        </div>
                                        <button type="submit" name="upload_image" class="btn btn-primary">Upload Image</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="admin-card mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">Add Video Link</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label for="video_id" class="form-label">YouTube Video ID</label>
                                            <input type="text" class="form-control" id="video_id" name="video_id" required placeholder="e.g., dQw4w9WgXcQ">
                                            <small class="text-muted">The ID is the 11-character code at the end of YouTube URLs</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="video_title" class="form-label">Video Title</label>
                                            <input type="text" class="form-control" id="video_title" name="video_title" required>
                                        </div>
                                        <button type="submit" name="add_video" class="btn btn-primary">Add Video</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Gallery Images</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($galleryData['images'] as $image): ?>
                                    <div class="col-md-4 col-lg-3 mb-4">
                                        <div class="card h-100">
                                            <img src="../img/gallery/<?php echo htmlspecialchars($image['filename']); ?>" class="card-img-top gallery-image" alt="Gallery Image">
                                            <div class="card-body">
                                                <div class="d-flex flex-wrap mb-2">
                                                    <?php foreach ($image['categories'] as $category): ?>
                                                        <span class="badge bg-primary category-badge">
                                                            <?php echo htmlspecialchars(ucfirst($category)); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                                <small class="text-muted d-block mb-2">
                                                    Uploaded: <?php echo date('M j, Y', strtotime($image['uploaded_at'])); ?>
                                                </small>
                                                <a href="?delete_image=<?php echo $image['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this image?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Video Links</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($galleryData['videos'] as $video): ?>
                                    <div class="col-md-4 col-lg-3 mb-4">
                                        <div class="card h-100 video-card">
                                            <img src="https://img.youtube.com/vi/<?php echo htmlspecialchars($video['video_id']); ?>/mqdefault.jpg" class="card-img-top video-thumbnail" alt="Video Thumbnail">
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($video['title']); ?></h6>
                                                <small class="text-muted d-block mb-2">
                                                    Added: <?php echo date('M j, Y', strtotime($video['added_at'])); ?>
                                                </small>
                                                <a href="https://youtube.com/watch?v=<?php echo htmlspecialchars($video['video_id']); ?>" target="_blank" class="btn btn-sm btn-info mb-2">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="?delete_video=<?php echo $video['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this video?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>