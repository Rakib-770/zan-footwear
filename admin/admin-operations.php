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

// Load operations data
$operationsJsonPath = __DIR__ . '/../data/operations.json';
$operationsData = json_decode(file_get_contents($operationsJsonPath), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Handle file uploads
    $hero_uploaded = handle_upload('hero_background_upload');
    $hero_delete = isset($_POST['delete_hero_image']) && $_POST['delete_hero_image'] === '1';

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
            // Hero background image handling
            if ($hero_uploaded) {
                if (!empty($operationsData['hero_section']['background_image'])) {
                    safe_unlink($operationsData['hero_section']['background_image']);
                }
                $operationsData['hero_section']['background_image'] = $hero_uploaded;
            } elseif ($hero_delete) {
                if (!empty($operationsData['hero_section']['background_image'])) {
                    safe_unlink($operationsData['hero_section']['background_image']);
                }
                $operationsData['hero_section']['background_image'] = "";
            }

            // Other hero fields
            $operationsData['hero_section']['title'] = $_POST['hero_title'] ?? $operationsData['hero_section']['title'];
            $operationsData['hero_section']['description'] = $_POST['hero_description'] ?? $operationsData['hero_section']['description'];
            $operationsData['hero_section']['primary_button']['text'] = $_POST['primary_button_text'] ?? $operationsData['hero_section']['primary_button']['text'];
            $operationsData['hero_section']['primary_button']['link'] = $_POST['primary_button_link'] ?? $operationsData['hero_section']['primary_button']['link'];
            $operationsData['hero_section']['secondary_button']['text'] = $_POST['secondary_button_text'] ?? $operationsData['hero_section']['secondary_button']['text'];
            $operationsData['hero_section']['secondary_button']['link'] = $_POST['secondary_button_link'] ?? $operationsData['hero_section']['secondary_button']['link'];
            break;

        case 'update_operations_grid':
            // Handle existing operations updates
            $operations = $operationsData['operations_grid']['operations'];
            $opCount = count($operations);
            
            for ($i = 0; $i < $opCount; $i++) {
                $idx = $i + 1;
                $imageUploaded = handle_upload("operation{$idx}_image_upload");
                $imageDelete = isset($_POST["delete_operation{$idx}_image"]) && $_POST["delete_operation{$idx}_image"] === '1';
                
                // Image handling
                if ($imageUploaded) {
                    if (!empty($operations[$i]['image'])) {
                        safe_unlink($operations[$i]['image']);
                    }
                    $operations[$i]['image'] = $imageUploaded;
                } elseif ($imageDelete) {
                    if (!empty($operations[$i]['image'])) {
                        safe_unlink($operations[$i]['image']);
                    }
                    $operations[$i]['image'] = "";
                }
                
                // Update operation details
                $operations[$i]['title'] = $_POST["operation{$idx}_title"] ?? $operations[$i]['title'];
                $operations[$i]['link'] = $_POST["operation{$idx}_link"] ?? $operations[$i]['link'];
            }
            
            // Handle new operations addition
            if (isset($_POST['new_operation_title']) && is_array($_POST['new_operation_title'])) {
                foreach ($_POST['new_operation_title'] as $index => $newTitle) {
                    if (!empty(trim($newTitle))) {
                        $newImageUploaded = handle_upload("new_operation_image_upload_{$index}");
                        
                        $newOperation = [
                            'title' => trim($newTitle),
                            'link' => trim($_POST['new_operation_link'][$index] ?? '#'),
                            'image' => $newImageUploaded ?: ''
                        ];
                        
                        $operations[] = $newOperation;
                    }
                }
            }
            
            $operationsData['operations_grid']['operations'] = $operations;
            break;

        case 'remove_operation':
            $removeIndex = $_POST['remove_index'] ?? null;
            if ($removeIndex !== null && isset($operationsData['operations_grid']['operations'][$removeIndex])) {
                // Delete associated image file
                $imageToRemove = $operationsData['operations_grid']['operations'][$removeIndex]['image'];
                if (!empty($imageToRemove)) {
                    safe_unlink($imageToRemove);
                }
                
                // Remove operation
                array_splice($operationsData['operations_grid']['operations'], $removeIndex, 1);
            }
            break;
    }

    // Save to JSON file
    file_put_contents($operationsJsonPath, json_encode($operationsData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Operations page information updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Operations Page Management</title>
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

        .operation-preview {
            max-width: 150px;
            max-height: 100px;
            display: block;
            margin-bottom: 6px;
            object-fit: cover;
            border: 1px solid #ddd;
            padding: 4px;
            background: #fff;
        }

        .operation-item {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
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
                    <h1 class="page-title">Operations Page Management</h1>

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
                                        <?php if (!empty($operationsData['hero_section']['background_image'])): ?>
                                            <img id="hero_preview" src="../<?php echo htmlspecialchars($operationsData['hero_section']['background_image']); ?>" class="image-preview" alt="Hero background">
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
                                                value="<?php echo htmlspecialchars($operationsData['hero_section']['title']); ?>" required>
                                            <label for="hero_title">Hero Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="hero_description" name="hero_description"
                                                style="height: 120px" required><?php echo htmlspecialchars($operationsData['hero_section']['description']); ?></textarea>
                                            <label for="hero_description">Hero Description</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_text" name="primary_button_text"
                                                value="<?php echo htmlspecialchars($operationsData['hero_section']['primary_button']['text']); ?>" required>
                                            <label for="primary_button_text">Primary Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_link" name="primary_button_link"
                                                value="<?php echo htmlspecialchars($operationsData['hero_section']['primary_button']['link']); ?>" required>
                                            <label for="primary_button_link">Primary Button Link</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_text" name="secondary_button_text"
                                                value="<?php echo htmlspecialchars($operationsData['hero_section']['secondary_button']['text']); ?>" required>
                                            <label for="secondary_button_text">Secondary Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_link" name="secondary_button_link"
                                                value="<?php echo htmlspecialchars($operationsData['hero_section']['secondary_button']['link']); ?>" required>
                                            <label for="secondary_button_link">Secondary Button Link</label>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Hero Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Operations Grid Section -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">Operations Grid Section</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_operations_grid">
                                
                                <!-- Existing Operations -->
                                <div class="row g-3">
                                    <?php foreach ($operationsData['operations_grid']['operations'] as $i => $operation): ?>
                                        <div class="col-md-6 col-lg-4 operation-item">
                                            <h5>Operation <?php echo $i + 1; ?></h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <label class="form-label">Current Image</label><br>
                                                    <?php if (!empty($operation['image'])): ?>
                                                        <img id="operation<?php echo $i + 1; ?>_preview" src="../<?php echo htmlspecialchars($operation['image']); ?>" class="operation-preview" alt="Operation image">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" value="1" id="delete_operation<?php echo $i + 1; ?>_image" name="delete_operation<?php echo $i + 1; ?>_image">
                                                            <label class="form-check-label" for="delete_operation<?php echo $i + 1; ?>_image">Delete current image</label>
                                                        </div>
                                                    <?php else: ?>
                                                        <img id="operation<?php echo $i + 1; ?>_preview" src="" class="operation-preview" style="display:none;">
                                                    <?php endif; ?>

                                                    <div class="mb-2">
                                                        <label for="operation<?php echo $i + 1; ?>_image_upload" class="form-label">Upload new image</label>
                                                        <input class="form-control" type="file" id="operation<?php echo $i + 1; ?>_image_upload" name="operation<?php echo $i + 1; ?>_image_upload" accept="image/*">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="operation<?php echo $i + 1; ?>_title" name="operation<?php echo $i + 1; ?>_title"
                                                            value="<?php echo htmlspecialchars($operation['title']); ?>" required>
                                                        <label for="operation<?php echo $i + 1; ?>_title">Title</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="operation<?php echo $i + 1; ?>_link" name="operation<?php echo $i + 1; ?>_link"
                                                            value="<?php echo htmlspecialchars($operation['link']); ?>" required>
                                                        <label for="operation<?php echo $i + 1; ?>_link">Link</label>
                                                    </div>
                                                    <div class="text-end">
                                                        <button type="submit" form="remove_operation_form_<?php echo $i; ?>" class="btn btn-danger btn-sm">Remove Operation</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- New Operations -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h5>Add New Operations</h5>
                                        <div id="new_operations_container">
                                            <div class="operation-item new-operation">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-2">
                                                            <label class="form-label">Upload Image</label>
                                                            <input class="form-control" type="file" name="new_operation_image_upload_0" accept="image/*">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" name="new_operation_title[]" placeholder="Operation Title" required>
                                                            <label>Title</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" name="new_operation_link[]" value="#" placeholder="Link" required>
                                                            <label>Link</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="add_new_operation" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="fas fa-plus"></i> Add Another Operation
                                        </button>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Operations Grid</button>
                                    </div>
                                </div>
                            </form>

                            <!-- Remove operation forms -->
                            <?php foreach ($operationsData['operations_grid']['operations'] as $i => $operation): ?>
                                <form id="remove_operation_form_<?php echo $i; ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="action" value="remove_operation">
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

        // Hero background preview
        const heroInput = document.getElementById('hero_background_upload');
        if (heroInput) {
            heroInput.addEventListener('change', function() {
                previewFile(this, 'hero_preview');
            });
        }

        // Operation image previews
        <?php foreach ($operationsData['operations_grid']['operations'] as $i => $operation): ?>
            (function() {
                const idx = <?php echo $i + 1; ?>;
                const input = document.getElementById('operation' + idx + '_image_upload');
                const previewId = 'operation' + idx + '_preview';
                if (input) {
                    input.addEventListener('change', function() {
                        previewFile(this, previewId);
                    });
                }
            })();
        <?php endforeach; ?>

        // Add new operation fields
        let operationCounter = 1;
        document.getElementById('add_new_operation').addEventListener('click', function() {
            const container = document.getElementById('new_operations_container');
            const newOperation = document.createElement('div');
            newOperation.className = 'operation-item new-operation mt-3';
            newOperation.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label">Upload Image</label>
                            <input class="form-control" type="file" name="new_operation_image_upload_${operationCounter}" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="new_operation_title[]" placeholder="Operation Title" required>
                            <label>Title</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="new_operation_link[]" value="#" placeholder="Link" required>
                            <label>Link</label>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newOperation);
            operationCounter++;
        });
    </script>
</body>

</html>