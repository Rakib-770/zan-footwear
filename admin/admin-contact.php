<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load contact data
$contactData = json_decode(file_get_contents('../data/contact.json'), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update contact data
    $contactData = [
        'page_title' => $_POST['page_title'],
        'header_content' => [
            'title' => $_POST['header_title'],
            'description' => $_POST['header_description']
        ],
        'contact_info' => [
            'address' => $_POST['address'],
            'office_hours' => $_POST['office_hours'],
            'email' => $_POST['email'],
            'secondary_email' => $_POST['secondary_email'] ?? '', // Add this line
            'phone' => $_POST['phone']
        ],
        'map_embed' => $_POST['map_embed'],
        'social_links' => [
            'facebook' => $_POST['facebook'],
            'linkedin' => $_POST['linkedin'],
            'instagram' => $_POST['instagram']
        ],
    ];

    // Save to JSON file
    file_put_contents('../data/contact.json', json_encode($contactData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Contact information updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Management</title>
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
                    <h1 class="page-title">Contact Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <div class="admin-card">
                        <div class="card-body">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <h4 class="section-title">Page Settings</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="page_title" name="page_title" value="<?php echo htmlspecialchars($contactData['page_title']); ?>" required>
                                            <label for="page_title">Page Title</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h4 class="section-title">Header Content</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="header_title" name="header_title" value="<?php echo htmlspecialchars($contactData['header_content']['title']); ?>" required>
                                            <label for="header_title">Header Title</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="header_description" name="header_description" style="height: 100px" required><?php echo htmlspecialchars($contactData['header_content']['description']); ?></textarea>
                                            <label for="header_description">Header Description</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h4 class="section-title">Contact Information</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="address" name="address" style="height: 100px" required><?php echo htmlspecialchars($contactData['contact_info']['address']); ?></textarea>
                                            <label for="address">Office Address</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="office_hours" name="office_hours" value="<?php echo htmlspecialchars($contactData['contact_info']['office_hours']); ?>" required>
                                            <label for="office_hours">Office Hours</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($contactData['contact_info']['email']); ?>" required>
                                            <label for="email">Primary Email Address</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="secondary_email" name="secondary_email" value="<?php echo htmlspecialchars($contactData['contact_info']['secondary_email'] ?? ''); ?>">
                                            <label for="secondary_email">Secondary Email Address (Optional)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($contactData['contact_info']['phone']); ?>" required>
                                            <label for="phone">Phone Number</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h4 class="section-title">Map Settings</h4>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="map_embed" name="map_embed" style="height: 100px" required><?php echo htmlspecialchars($contactData['map_embed']); ?></textarea>
                                            <label for="map_embed">Google Maps Embed Code</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h4 class="section-title">Social Media Links</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="facebook" name="facebook" value="<?php echo htmlspecialchars($contactData['social_links']['facebook']); ?>" required>
                                            <label for="facebook">Facebook</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="linkedin" name="linkedin" value="<?php echo htmlspecialchars($contactData['social_links']['linkedin']); ?>" required>
                                            <label for="linkedin">Linkedin</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="instagram" name="instagram" value="<?php echo htmlspecialchars($contactData['social_links']['instagram']); ?>" required>
                                            <label for="instagram">Instagram</label>
                                        </div>
                                    </div>

                                    <div class="col-12 text-center mt-4">
                                        <button type="submit" class="btn admin-contact-btn">Update Contact Information</button>
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
</body>

</html>