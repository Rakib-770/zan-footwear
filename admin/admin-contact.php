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

// Load contact data
$contactJsonPath = __DIR__ . '/../data/contact.json';
$contactData = json_decode(file_get_contents($contactJsonPath), true);

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
                if (!empty($contactData['hero']['backgroundImage'])) {
                    safe_unlink($contactData['hero']['backgroundImage']);
                }
                $contactData['hero']['backgroundImage'] = $hero_uploaded;
            } elseif ($hero_delete) {
                if (!empty($contactData['hero']['backgroundImage'])) {
                    safe_unlink($contactData['hero']['backgroundImage']);
                }
                $contactData['hero']['backgroundImage'] = "";
            }

            // Other hero fields
            $contactData['hero']['title'] = $_POST['hero_title'] ?? $contactData['hero']['title'];
            $contactData['hero']['description'] = $_POST['hero_description'] ?? $contactData['hero']['description'];
            
            // Update buttons
            $contactData['hero']['buttons'][0]['text'] = $_POST['primary_button_text'] ?? $contactData['hero']['buttons'][0]['text'];
            $contactData['hero']['buttons'][0]['url'] = $_POST['primary_button_link'] ?? $contactData['hero']['buttons'][0]['url'];
            $contactData['hero']['buttons'][1]['text'] = $_POST['secondary_button_text'] ?? $contactData['hero']['buttons'][1]['text'];
            $contactData['hero']['buttons'][1]['url'] = $_POST['secondary_button_link'] ?? $contactData['hero']['buttons'][1]['url'];
            break;

        case 'update_contact_info':
            // Contact information
            $contactData['contactInfo']['address']['text'] = $_POST['address_text'] ?? $contactData['contactInfo']['address']['text'];
            $contactData['contactInfo']['email']['text'] = $_POST['email_text'] ?? $contactData['contactInfo']['email']['text'];
            $contactData['contactInfo']['phone']['text'] = $_POST['phone_text'] ?? $contactData['contactInfo']['phone']['text'];
            break;

        case 'update_social_media':
            // Social media links
            $contactData['socialMedia']['facebook']['url'] = $_POST['facebook_url'] ?? $contactData['socialMedia']['facebook']['url'];
            $contactData['socialMedia']['instagram']['url'] = $_POST['instagram_url'] ?? $contactData['socialMedia']['instagram']['url'];
            $contactData['socialMedia']['twitter']['url'] = $_POST['twitter_url'] ?? $contactData['socialMedia']['twitter']['url'];
            $contactData['socialMedia']['linkedin']['url'] = $_POST['linkedin_url'] ?? $contactData['socialMedia']['linkedin']['url'];
            break;

        case 'update_contact_form':
            // Contact form fields
            $contactData['form']['firstName']['label'] = $_POST['first_name_label'] ?? $contactData['form']['firstName']['label'];
            $contactData['form']['lastName']['label'] = $_POST['last_name_label'] ?? $contactData['form']['lastName']['label'];
            $contactData['form']['email']['label'] = $_POST['email_label'] ?? $contactData['form']['email']['label'];
            $contactData['form']['message']['label'] = $_POST['message_label'] ?? $contactData['form']['message']['label'];
            $contactData['form']['submitButton']['text'] = $_POST['submit_button_text'] ?? $contactData['form']['submitButton']['text'];
            break;

        case 'update_map':
            // Map iframe
            $contactData['map']['iframe'] = $_POST['map_iframe'] ?? $contactData['map']['iframe'];
            break;
    }

    // Save to JSON file
    file_put_contents($contactJsonPath, json_encode($contactData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Contact page information updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Page Management</title>
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
        
        .map-preview {
            width: 100%;
            height: 200px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
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
                    <h1 class="page-title">Contact Page Management</h1>

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
                                        <?php if (!empty($contactData['hero']['backgroundImage'])): ?>
                                            <img id="hero_preview" src="../<?php echo htmlspecialchars($contactData['hero']['backgroundImage']); ?>" class="image-preview" alt="Hero background">
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
                                                value="<?php echo htmlspecialchars($contactData['hero']['title']); ?>" required>
                                            <label for="hero_title">Hero Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="hero_description" name="hero_description"
                                                style="height: 100px" required><?php echo htmlspecialchars($contactData['hero']['description']); ?></textarea>
                                            <label for="hero_description">Hero Description</label>
                                        </div>
                                        
                                        <h6>Primary Button</h6>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_text" name="primary_button_text"
                                                value="<?php echo htmlspecialchars($contactData['hero']['buttons'][0]['text']); ?>" required>
                                            <label for="primary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_link" name="primary_button_link"
                                                value="<?php echo htmlspecialchars($contactData['hero']['buttons'][0]['url']); ?>" required>
                                            <label for="primary_button_link">Button Link</label>
                                        </div>
                                        
                                        <h6>Secondary Button</h6>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_text" name="secondary_button_text"
                                                value="<?php echo htmlspecialchars($contactData['hero']['buttons'][1]['text']); ?>" required>
                                            <label for="secondary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_link" name="secondary_button_link"
                                                value="<?php echo htmlspecialchars($contactData['hero']['buttons'][1]['url']); ?>" required>
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

                    <!-- Contact Information Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Contact Information</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_contact_info">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <h6>Address</h6>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="address_text" name="address_text"
                                                value="<?php echo htmlspecialchars($contactData['contactInfo']['address']['text']); ?>" required>
                                            <label for="address_text">Address Text</label>
                                        </div>
                                        <div class="form-text">
                                            <i class="<?php echo htmlspecialchars($contactData['contactInfo']['address']['icon']); ?> me-1"></i>
                                            Icon: <?php echo htmlspecialchars($contactData['contactInfo']['address']['icon']); ?>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <h6>Email</h6>
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email_text" name="email_text"
                                                value="<?php echo htmlspecialchars($contactData['contactInfo']['email']['text']); ?>" required>
                                            <label for="email_text">Email Address</label>
                                        </div>
                                        <div class="form-text">
                                            <i class="<?php echo htmlspecialchars($contactData['contactInfo']['email']['icon']); ?> me-1"></i>
                                            Icon: <?php echo htmlspecialchars($contactData['contactInfo']['email']['icon']); ?>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <h6>Phone</h6>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="phone_text" name="phone_text"
                                                value="<?php echo htmlspecialchars($contactData['contactInfo']['phone']['text']); ?>" required>
                                            <label for="phone_text">Phone Number</label>
                                        </div>
                                        <div class="form-text">
                                            <i class="<?php echo htmlspecialchars($contactData['contactInfo']['phone']['icon']); ?> me-1"></i>
                                            Icon: <?php echo htmlspecialchars($contactData['contactInfo']['phone']['icon']); ?>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Contact Information</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Social Media Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Social Media Links</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_social_media">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h6>Facebook</h6>
                                        <div class="form-floating mb-3">
                                            <input type="url" class="form-control" id="facebook_url" name="facebook_url"
                                                value="<?php echo htmlspecialchars($contactData['socialMedia']['facebook']['url']); ?>" required>
                                            <label for="facebook_url">Facebook URL</label>
                                        </div>
                                        <div class="form-text">
                                            <i class="<?php echo htmlspecialchars($contactData['socialMedia']['facebook']['icon']); ?> me-1"></i>
                                            Icon: <?php echo htmlspecialchars($contactData['socialMedia']['facebook']['icon']); ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6>Instagram</h6>
                                        <div class="form-floating mb-3">
                                            <input type="url" class="form-control" id="instagram_url" name="instagram_url"
                                                value="<?php echo htmlspecialchars($contactData['socialMedia']['instagram']['url']); ?>" required>
                                            <label for="instagram_url">Instagram URL</label>
                                        </div>
                                        <div class="form-text">
                                            <i class="<?php echo htmlspecialchars($contactData['socialMedia']['instagram']['icon']); ?> me-1"></i>
                                            Icon: <?php echo htmlspecialchars($contactData['socialMedia']['instagram']['icon']); ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6>Twitter</h6>
                                        <div class="form-floating mb-3">
                                            <input type="url" class="form-control" id="twitter_url" name="twitter_url"
                                                value="<?php echo htmlspecialchars($contactData['socialMedia']['twitter']['url']); ?>" required>
                                            <label for="twitter_url">Twitter URL</label>
                                        </div>
                                        <div class="form-text">
                                            <i class="<?php echo htmlspecialchars($contactData['socialMedia']['twitter']['icon']); ?> me-1"></i>
                                            Icon: <?php echo htmlspecialchars($contactData['socialMedia']['twitter']['icon']); ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <h6>LinkedIn</h6>
                                        <div class="form-floating mb-3">
                                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url"
                                                value="<?php echo htmlspecialchars($contactData['socialMedia']['linkedin']['url']); ?>" required>
                                            <label for="linkedin_url">LinkedIn URL</label>
                                        </div>
                                        <div class="form-text">
                                            <i class="<?php echo htmlspecialchars($contactData['socialMedia']['linkedin']['icon']); ?> me-1"></i>
                                            Icon: <?php echo htmlspecialchars($contactData['socialMedia']['linkedin']['icon']); ?>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Social Media Links</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Contact Form Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Contact Form</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_contact_form">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="first_name_label" name="first_name_label"
                                                value="<?php echo htmlspecialchars($contactData['form']['firstName']['label']); ?>" required>
                                            <label for="first_name_label">First Name Label</label>
                                        </div>
                                        <div class="form-text">
                                            ID: <?php echo htmlspecialchars($contactData['form']['firstName']['id']); ?> | 
                                            Type: <?php echo htmlspecialchars($contactData['form']['firstName']['type']); ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="last_name_label" name="last_name_label"
                                                value="<?php echo htmlspecialchars($contactData['form']['lastName']['label']); ?>" required>
                                            <label for="last_name_label">Last Name Label</label>
                                        </div>
                                        <div class="form-text">
                                            ID: <?php echo htmlspecialchars($contactData['form']['lastName']['id']); ?> | 
                                            Type: <?php echo htmlspecialchars($contactData['form']['lastName']['type']); ?>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="email_label" name="email_label"
                                                value="<?php echo htmlspecialchars($contactData['form']['email']['label']); ?>" required>
                                            <label for="email_label">Email Label</label>
                                        </div>
                                        <div class="form-text">
                                            ID: <?php echo htmlspecialchars($contactData['form']['email']['id']); ?> | 
                                            Type: <?php echo htmlspecialchars($contactData['form']['email']['type']); ?>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="message_label" name="message_label"
                                                value="<?php echo htmlspecialchars($contactData['form']['message']['label']); ?>" required>
                                            <label for="message_label">Message Label</label>
                                        </div>
                                        <div class="form-text">
                                            ID: <?php echo htmlspecialchars($contactData['form']['message']['id']); ?> | 
                                            Rows: <?php echo htmlspecialchars($contactData['form']['message']['rows']); ?> | 
                                            Cols: <?php echo htmlspecialchars($contactData['form']['message']['cols']); ?>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="submit_button_text" name="submit_button_text"
                                                value="<?php echo htmlspecialchars($contactData['form']['submitButton']['text']); ?>" required>
                                            <label for="submit_button_text">Submit Button Text</label>
                                        </div>
                                        <div class="form-text">
                                            Class: <?php echo htmlspecialchars($contactData['form']['submitButton']['class']); ?>
                                        </div>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Contact Form</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">Map Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_map">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Current Map Preview</label>
                                        <div class="map-preview">
                                            <span class="text-muted">Map preview will appear here</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="map_iframe" name="map_iframe"
                                                style="height: 150px" required><?php echo htmlspecialchars($contactData['map']['iframe']); ?></textarea>
                                            <label for="map_iframe">Google Maps Iframe Code</label>
                                        </div>
                                        <div class="form-text">
                                            Paste the complete iframe code from Google Maps embed
                                        </div>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Map</button>
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
        // JS: client-side preview for hero background upload
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
    </script>
</body>

</html>