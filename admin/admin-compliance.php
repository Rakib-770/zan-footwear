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

// Load compliance data
$complianceJsonPath = __DIR__ . '/../data/compliance.json';
$complianceData = json_decode(file_get_contents($complianceJsonPath), true);

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
                if (!empty($complianceData['hero_section']['background_image'])) {
                    safe_unlink($complianceData['hero_section']['background_image']);
                }
                $complianceData['hero_section']['background_image'] = $hero_uploaded;
            } elseif ($hero_delete) {
                if (!empty($complianceData['hero_section']['background_image'])) {
                    safe_unlink($complianceData['hero_section']['background_image']);
                }
                $complianceData['hero_section']['background_image'] = "";
            }

            // Other hero fields
            $complianceData['hero_section']['title'] = $_POST['hero_title'] ?? $complianceData['hero_section']['title'];
            $complianceData['hero_section']['description'] = $_POST['hero_description'] ?? $complianceData['hero_section']['description'];
            $complianceData['hero_section']['primary_button']['text'] = $_POST['primary_button_text'] ?? $complianceData['hero_section']['primary_button']['text'];
            $complianceData['hero_section']['primary_button']['link'] = $_POST['primary_button_link'] ?? $complianceData['hero_section']['primary_button']['link'];
            $complianceData['hero_section']['secondary_button']['text'] = $_POST['secondary_button_text'] ?? $complianceData['hero_section']['secondary_button']['text'];
            $complianceData['hero_section']['secondary_button']['link'] = $_POST['secondary_button_link'] ?? $complianceData['hero_section']['secondary_button']['link'];
            break;

        case 'update_commitment':
            // Commitment section fields
            $complianceData['commitment_section']['title'] = $_POST['commitment_title'] ?? $complianceData['commitment_section']['title'];
            $complianceData['commitment_section']['description'] = $_POST['commitment_description'] ?? $complianceData['commitment_section']['description'];
            
            // Features - preserve icons, only update titles and descriptions
            $features = $complianceData['commitment_section']['features'];
            for ($i = 0; $i < count($features); $i++) {
                $idx = $i + 1;
                $titleKey = "feature{$idx}_title";
                $descKey = "feature{$idx}_description";
                
                if (isset($_POST[$titleKey])) {
                    $features[$i]['title'] = $_POST[$titleKey];
                }
                if (isset($_POST[$descKey])) {
                    $features[$i]['description'] = $_POST[$descKey];
                }
                // Icon remains unchanged as requested
            }
            $complianceData['commitment_section']['features'] = $features;
            break;

        case 'update_certifications':
            // Certifications title
            $complianceData['certifications_section']['title'] = $_POST['certifications_title'] ?? $complianceData['certifications_section']['title'];
            
            // Handle existing certifications updates
            $certifications = $complianceData['certifications_section']['certifications'];
            $certCount = count($certifications);
            
            for ($i = 0; $i < $certCount; $i++) {
                $idx = $i + 1;
                $logoUploaded = handle_upload("cert{$idx}_logo_upload");
                $logoDelete = isset($_POST["delete_cert{$idx}_logo"]) && $_POST["delete_cert{$idx}_logo"] === '1';
                
                // Logo handling
                if ($logoUploaded) {
                    if (!empty($certifications[$i]['logo'])) {
                        safe_unlink($certifications[$i]['logo']);
                    }
                    $certifications[$i]['logo'] = $logoUploaded;
                } elseif ($logoDelete) {
                    if (!empty($certifications[$i]['logo'])) {
                        safe_unlink($certifications[$i]['logo']);
                    }
                    $certifications[$i]['logo'] = "";
                }
                
                // Update certification details
                $certifications[$i]['title'] = $_POST["cert{$idx}_title"] ?? $certifications[$i]['title'];
                $certifications[$i]['description'] = $_POST["cert{$idx}_description"] ?? $certifications[$i]['description'];
            }
            
            // Handle new certifications addition
            if (isset($_POST['new_cert_title']) && is_array($_POST['new_cert_title'])) {
                foreach ($_POST['new_cert_title'] as $index => $newTitle) {
                    if (!empty(trim($newTitle))) {
                        $newLogoUploaded = handle_upload("new_cert_logo_upload_{$index}");
                        
                        $newCert = [
                            'title' => trim($newTitle),
                            'description' => trim($_POST['new_cert_description'][$index] ?? ''),
                            'logo' => $newLogoUploaded ?: ''
                        ];
                        
                        $certifications[] = $newCert;
                    }
                }
            }
            
            $complianceData['certifications_section']['certifications'] = $certifications;
            break;

        case 'remove_certification':
            $removeIndex = $_POST['remove_index'] ?? null;
            if ($removeIndex !== null && isset($complianceData['certifications_section']['certifications'][$removeIndex])) {
                // Delete associated logo file
                $logoToRemove = $complianceData['certifications_section']['certifications'][$removeIndex]['logo'];
                if (!empty($logoToRemove)) {
                    safe_unlink($logoToRemove);
                }
                
                // Remove certification
                array_splice($complianceData['certifications_section']['certifications'], $removeIndex, 1);
            }
            break;
    }

    // Save to JSON file
    file_put_contents($complianceJsonPath, json_encode($complianceData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Compliance page information updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Compliance Page Management</title>
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

        .logo-preview {
            max-width: 120px;
            max-height: 80px;
            display: block;
            margin-bottom: 6px;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 4px;
            background: #fff;
        }

        .certification-item {
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
                    <h1 class="page-title">Compliance Page Management</h1>

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
                                        <?php if (!empty($complianceData['hero_section']['background_image'])): ?>
                                            <img id="hero_preview" src="../<?php echo htmlspecialchars($complianceData['hero_section']['background_image']); ?>" class="image-preview" alt="Hero background">
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
                                                value="<?php echo htmlspecialchars($complianceData['hero_section']['title']); ?>" required>
                                            <label for="hero_title">Hero Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="hero_description" name="hero_description"
                                                style="height: 100px" required><?php echo htmlspecialchars($complianceData['hero_section']['description']); ?></textarea>
                                            <label for="hero_description">Hero Description</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_text" name="primary_button_text"
                                                value="<?php echo htmlspecialchars($complianceData['hero_section']['primary_button']['text']); ?>" required>
                                            <label for="primary_button_text">Primary Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_link" name="primary_button_link"
                                                value="<?php echo htmlspecialchars($complianceData['hero_section']['primary_button']['link']); ?>" required>
                                            <label for="primary_button_link">Primary Button Link</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_text" name="secondary_button_text"
                                                value="<?php echo htmlspecialchars($complianceData['hero_section']['secondary_button']['text']); ?>" required>
                                            <label for="secondary_button_text">Secondary Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_link" name="secondary_button_link"
                                                value="<?php echo htmlspecialchars($complianceData['hero_section']['secondary_button']['link']); ?>" required>
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

                    <!-- Commitment Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Commitment Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_commitment">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="commitment_title" name="commitment_title"
                                                value="<?php echo htmlspecialchars($complianceData['commitment_section']['title']); ?>" required>
                                            <label for="commitment_title">Section Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="commitment_description" name="commitment_description"
                                                style="height: 100px" required><?php echo htmlspecialchars($complianceData['commitment_section']['description']); ?></textarea>
                                            <label for="commitment_description">Main Description</label>
                                        </div>
                                    </div>

                                    <!-- Features -->
                                    <?php foreach ($complianceData['commitment_section']['features'] as $i => $feature): ?>
                                        <div class="col-md-6">
                                            <h5>Feature <?php echo $i + 1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="feature<?php echo $i + 1; ?>_title" name="feature<?php echo $i + 1; ?>_title"
                                                    value="<?php echo htmlspecialchars($feature['title']); ?>" required>
                                                <label for="feature<?php echo $i + 1; ?>_title">Title</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="feature<?php echo $i + 1; ?>_description" name="feature<?php echo $i + 1; ?>_description"
                                                    style="height: 100px" required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                                                <label for="feature<?php echo $i + 1; ?>_description">Description</label>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Current Icon</label><br>
                                                <?php if (!empty($feature['icon'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($feature['icon']); ?>" class="logo-preview" alt="Feature icon">
                                                <?php else: ?>
                                                    <span class="text-muted">No icon set</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Commitment Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Certifications Section -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">Certifications Section</h4>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_certifications">
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="certifications_title" name="certifications_title"
                                                value="<?php echo htmlspecialchars($complianceData['certifications_section']['title']); ?>" required>
                                            <label for="certifications_title">Section Title</label>
                                        </div>
                                    </div>

                                    <!-- Existing Certifications -->
                                    <?php foreach ($complianceData['certifications_section']['certifications'] as $i => $cert): ?>
                                        <div class="col-12 certification-item">
                                            <h5>Certification <?php echo $i + 1; ?></h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Current Logo</label><br>
                                                    <?php if (!empty($cert['logo'])): ?>
                                                        <img id="cert<?php echo $i + 1; ?>_preview" src="../<?php echo htmlspecialchars($cert['logo']); ?>" class="logo-preview" alt="Certification logo">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" value="1" id="delete_cert<?php echo $i + 1; ?>_logo" name="delete_cert<?php echo $i + 1; ?>_logo">
                                                            <label class="form-check-label" for="delete_cert<?php echo $i + 1; ?>_logo">Delete current logo</label>
                                                        </div>
                                                    <?php else: ?>
                                                        <img id="cert<?php echo $i + 1; ?>_preview" src="" class="logo-preview" style="display:none;">
                                                    <?php endif; ?>

                                                    <div class="mb-2">
                                                        <label for="cert<?php echo $i + 1; ?>_logo_upload" class="form-label">Upload new logo</label>
                                                        <input class="form-control" type="file" id="cert<?php echo $i + 1; ?>_logo_upload" name="cert<?php echo $i + 1; ?>_logo_upload" accept="image/*">
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="cert<?php echo $i + 1; ?>_title" name="cert<?php echo $i + 1; ?>_title"
                                                            value="<?php echo htmlspecialchars($cert['title']); ?>" required>
                                                        <label for="cert<?php echo $i + 1; ?>_title">Title</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <textarea class="form-control" id="cert<?php echo $i + 1; ?>_description" name="cert<?php echo $i + 1; ?>_description"
                                                            style="height: 80px" required><?php echo htmlspecialchars($cert['description']); ?></textarea>
                                                        <label for="cert<?php echo $i + 1; ?>_description">Description</label>
                                                    </div>
                                                    <div class="text-end">
                                                        <button type="submit" form="remove_form_<?php echo $i; ?>" class="btn btn-danger btn-sm">Remove Certification</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- New Certifications -->
                                    <div class="col-12">
                                        <h5>Add New Certifications</h5>
                                        <div id="new_certifications_container">
                                            <div class="certification-item new-certification">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-2">
                                                            <label class="form-label">Upload Logo</label>
                                                            <input class="form-control" type="file" name="new_cert_logo_upload_0" accept="image/*">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" name="new_cert_title[]" placeholder="Certification Title">
                                                            <label>Title</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <textarea class="form-control" name="new_cert_description[]" style="height: 80px" placeholder="Description"></textarea>
                                                            <label>Description</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" id="add_new_cert" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="fas fa-plus"></i> Add Another Certification
                                        </button>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Certifications</button>
                                    </div>
                                </div>
                            </form>

                            <!-- Remove certification forms -->
                            <?php foreach ($complianceData['certifications_section']['certifications'] as $i => $cert): ?>
                                <form id="remove_form_<?php echo $i; ?>" method="POST" style="display: none;">
                                    <input type="hidden" name="action" value="remove_certification">
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

        // Certification logo previews
        <?php foreach ($complianceData['certifications_section']['certifications'] as $i => $cert): ?>
            (function() {
                const idx = <?php echo $i + 1; ?>;
                const input = document.getElementById('cert' + idx + '_logo_upload');
                const previewId = 'cert' + idx + '_preview';
                if (input) {
                    input.addEventListener('change', function() {
                        previewFile(this, previewId);
                    });
                }
            })();
        <?php endforeach; ?>

        // Add new certification fields
        let certCounter = 1;
        document.getElementById('add_new_cert').addEventListener('click', function() {
            const container = document.getElementById('new_certifications_container');
            const newCert = document.createElement('div');
            newCert.className = 'certification-item new-certification mt-3';
            newCert.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-2">
                            <label class="form-label">Upload Logo</label>
                            <input class="form-control" type="file" name="new_cert_logo_upload_${certCounter}" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="new_cert_title[]" placeholder="Certification Title" required>
                            <label>Title</label>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="new_cert_description[]" style="height: 80px" placeholder="Description" required></textarea>
                            <label>Description</label>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newCert);
            certCounter++;
        });
    </script>
</body>

</html>