<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load products data
$productsData = json_decode(file_get_contents('../data/products.json'), true);

// Handle image upload
function uploadImage($file, $target_dir = "../images/products/") {
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        return false;
    }
    
    // Generate unique filename
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return "images/products/" . $filename;
    }
    
    return false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_hero':
                $productsData['hero'] = [
                    'background_image' => $_POST['background_image'],
                    'title' => $_POST['hero_title'],
                    'description' => $_POST['hero_description'],
                    'buttons' => [
                        [
                            'text' => $_POST['primary_button_text'],
                            'url' => $_POST['primary_button_link'],
                            'class' => 'btn btn-secondary me-2'
                        ],
                        [
                            'text' => $_POST['secondary_button_text'],
                            'url' => $_POST['secondary_button_link'],
                            'class' => 'btn btn-white-outline'
                        ]
                    ]
                ];
                break;

            case 'update_intro':
                $productsData['intro_section'] = [
                    'title' => $_POST['intro_title'],
                    'description' => $_POST['intro_description'],
                    'button_text' => $_POST['intro_button_text'],
                    'button_url' => $_POST['intro_button_url']
                ];
                break;

            case 'update_featured_collections':
                $collections = [];
                for ($i = 1; $i <= 3; $i++) {
                    $image_path = $productsData['featured_collections'][$i-1]['image']; // Keep existing image by default
                    
                    // Handle new image upload
                    if (isset($_FILES["collection{$i}_image"]) && $_FILES["collection{$i}_image"]['error'] == 0) {
                        $uploaded_image = uploadImage($_FILES["collection{$i}_image"]);
                        if ($uploaded_image) {
                            $image_path = $uploaded_image;
                        }
                    }
                    
                    $collections[] = [
                        'name' => $_POST["collection{$i}_name"],
                        'image' => $image_path,
                        'url' => $_POST["collection{$i}_url"],
                        'category' => $i == 1 ? 'mens' : ($i == 2 ? 'womens' : 'kids')
                    ];
                }
                $productsData['featured_collections'] = $collections;
                break;

            case 'add_product':
                $image_path = '';
                if (isset($_FILES["new_product_image"]) && $_FILES["new_product_image"]['error'] == 0) {
                    $uploaded_image = uploadImage($_FILES["new_product_image"]);
                    if ($uploaded_image) {
                        $image_path = $uploaded_image;
                    }
                }
                
                if ($image_path) {
                    $newProduct = [
                        'name' => $_POST['new_product_name'],
                        'image' => $image_path,
                        'category' => $_POST['new_product_category']
                    ];
                    $productsData['products'][] = $newProduct;
                }
                break;

            case 'delete_product':
                $productIndex = $_POST['product_index'];
                if (isset($productsData['products'][$productIndex])) {
                    array_splice($productsData['products'], $productIndex, 1);
                }
                break;
        }
    }

    // Save to JSON file
    file_put_contents('../data/products.json', json_encode($productsData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Products information updated successfully!";
    
    // Reload data after update
    $productsData = json_decode(file_get_contents('../data/products.json'), true);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Products Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-style.css">
    <style>
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            margin-bottom: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #007bff;
            background: #e9ecef;
        }
        .upload-area i {
            font-size: 2rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .file-input {
            display: none;
        }
        .current-image {
            margin-bottom: 15px;
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
                    <h1 class="page-title">Products Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
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
                                                value="<?php echo htmlspecialchars($productsData['hero']['background_image']); ?>" required>
                                            <label for="background_image">Background Image Path</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="hero_title" name="hero_title" 
                                                value="<?php echo htmlspecialchars($productsData['hero']['title']); ?>" required>
                                            <label for="hero_title">Hero Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="hero_description" name="hero_description" 
                                                style="height: 100px" required><?php echo htmlspecialchars($productsData['hero']['description']); ?></textarea>
                                            <label for="hero_description">Hero Description</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Primary Button</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_text" name="primary_button_text" 
                                                value="<?php echo htmlspecialchars($productsData['hero']['buttons'][0]['text']); ?>" required>
                                            <label for="primary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_link" name="primary_button_link" 
                                                value="<?php echo htmlspecialchars($productsData['hero']['buttons'][0]['url']); ?>" required>
                                            <label for="primary_button_link">Button Link</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Secondary Button</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_text" name="secondary_button_text" 
                                                value="<?php echo htmlspecialchars($productsData['hero']['buttons'][1]['text']); ?>" required>
                                            <label for="secondary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_link" name="secondary_button_link" 
                                                value="<?php echo htmlspecialchars($productsData['hero']['buttons'][1]['url']); ?>" required>
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

                    <!-- Intro Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Intro Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_intro">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="intro_title" name="intro_title" 
                                                value="<?php echo htmlspecialchars($productsData['intro_section']['title']); ?>" required>
                                            <label for="intro_title">Section Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="intro_description" name="intro_description" 
                                                style="height: 100px" required><?php echo htmlspecialchars($productsData['intro_section']['description']); ?></textarea>
                                            <label for="intro_description">Description</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="intro_button_text" name="intro_button_text" 
                                                value="<?php echo htmlspecialchars($productsData['intro_section']['button_text']); ?>" required>
                                            <label for="intro_button_text">Button Text</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="intro_button_url" name="intro_button_url" 
                                                value="<?php echo htmlspecialchars($productsData['intro_section']['button_url']); ?>" required>
                                            <label for="intro_button_url">Button URL</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Intro Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Featured Collections -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Featured Collections</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_featured_collections">
                                <div class="row g-3">
                                    <?php foreach ($productsData['featured_collections'] as $i => $collection): ?>
                                    <div class="col-md-4">
                                        <h5><?php echo ucfirst($collection['category']); ?> Collection</h5>
                                        
                                        <!-- Current Image Preview -->
                                        <div class="current-image">
                                            <label class="form-label">Current Image:</label>
                                            <div>
                                                <img src="../<?php echo htmlspecialchars($collection['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($collection['name']); ?>" 
                                                     class="image-preview"
                                                     onerror="this.style.display='none'">
                                            </div>
                                        </div>

                                        <!-- Image Upload -->
                                        <div class="mb-3">
                                            <label class="form-label">Update Image:</label>
                                            <div class="upload-area" onclick="document.getElementById('collection<?php echo $i+1; ?>_image').click()">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <p>Click to upload image</p>
                                                <small class="text-muted">JPG, PNG, GIF (Max 5MB)</small>
                                            </div>
                                            <input type="file" class="file-input" id="collection<?php echo $i+1; ?>_image" 
                                                   name="collection<?php echo $i+1; ?>_image" 
                                                   accept="image/*" 
                                                   onchange="previewImage(this, 'preview<?php echo $i+1; ?>')">
                                            <div id="preview<?php echo $i+1; ?>"></div>
                                        </div>

                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="collection<?php echo $i+1; ?>_name" name="collection<?php echo $i+1; ?>_name" 
                                                value="<?php echo htmlspecialchars($collection['name']); ?>" required>
                                            <label for="collection<?php echo $i+1; ?>_name">Collection Name</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="collection<?php echo $i+1; ?>_url" name="collection<?php echo $i+1; ?>_url" 
                                                value="<?php echo htmlspecialchars($collection['url']); ?>" required>
                                            <label for="collection<?php echo $i+1; ?>_url">Link URL</label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Collections</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Add New Product -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Add New Product</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="add_product">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="new_product_name" name="new_product_name" required>
                                            <label for="new_product_name">Product Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Product Image *</label>
                                            <div class="upload-area" onclick="document.getElementById('new_product_image').click()">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <p>Click to upload image</p>
                                                <small class="text-muted">JPG, PNG, GIF (Max 5MB)</small>
                                            </div>
                                            <input type="file" class="file-input" id="new_product_image" 
                                                   name="new_product_image" 
                                                   accept="image/*" 
                                                   required
                                                   onchange="previewImage(this, 'newProductPreview')">
                                            <div id="newProductPreview"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="new_product_category" name="new_product_category" required>
                                                <option value="mens">Men's</option>
                                                <option value="womens">Women's</option>
                                                <option value="kids">Kids</option>
                                            </select>
                                            <label for="new_product_category">Category</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Add Product</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Products List -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">Manage Products</h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($productsData['products'])): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No products found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($productsData['products'] as $index => $product): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                         style="width: 60px; height: 60px; object-fit: cover;"
                                                         onerror="this.src='../images/placeholder.jpg'">
                                                </td>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        switch($product['category']) {
                                                            case 'mens': echo 'primary'; break;
                                                            case 'womens': echo 'success'; break;
                                                            case 'kids': echo 'warning'; break;
                                                            default: echo 'secondary';
                                                        }
                                                    ?>">
                                                        <?php echo ucfirst($product['category']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_product">
                                                        <input type="hidden" name="product_index" value="<?php echo $index; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                                onclick="return confirm('Are you sure you want to delete this product?')">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Add form validation for file uploads
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const fileInputs = form.querySelectorAll('input[type="file"][required]');
                    let valid = true;
                    
                    fileInputs.forEach(input => {
                        if (!input.files || input.files.length === 0) {
                            valid = false;
                            input.closest('.mb-3').classList.add('is-invalid');
                        } else {
                            input.closest('.mb-3').classList.remove('is-invalid');
                        }
                    });
                    
                    if (!valid) {
                        e.preventDefault();
                        alert('Please upload all required images.');
                    }
                });
            });
        });
    </script>
</body>

</html>