<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load service data
$serviceData = json_decode(file_get_contents('../data/service.json'), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_header') {
            // Update header content
            $serviceData['header_content'] = [
                'title' => $_POST['header_title'],
                'subtitle' => $_POST['header_subtitle']
            ];
        } elseif ($_POST['action'] === 'update_main_title') {
            // Update main title
            $serviceData['main_title'] = $_POST['main_title'];
        } elseif ($_POST['action'] === 'add_service') {
            // Add new service
            $newService = [
                'id' => uniqid(),
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'image' => $_POST['image'],
                'position' => $_POST['position']
            ];
            
            $serviceData['services'][] = $newService;
        } elseif ($_POST['action'] === 'update_service') {
            // Update existing service
            foreach ($serviceData['services'] as &$service) {
                if ($service['id'] === $_POST['service_id']) {
                    $service = [
                        'id' => $_POST['service_id'],
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'image' => $_POST['image'],
                        'position' => $_POST['position']
                    ];
                    break;
                }
            }
        }
    } elseif (isset($_POST['delete_service'])) {
        // Delete service
        $serviceData['services'] = array_filter($serviceData['services'], function($service) {
            return $service['id'] !== $_POST['delete_service'];
        });
        $serviceData['services'] = array_values($serviceData['services']); // Reindex array
    }

    // Save to JSON file
    file_put_contents('../data/service.json', json_encode($serviceData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Service information updated successfully!";
}

// Get service details for editing
$editService = null;
if (isset($_GET['edit_service'])) {
    foreach ($serviceData['services'] as $service) {
        if ($service['id'] === $_GET['edit_service']) {
            $editService = $service;
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
    <title>Admin - Service Management</title>
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
                    <h1 class="page-title">Service Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Page Header Content</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_header">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="header_title" name="header_title" 
                                                value="<?php echo htmlspecialchars($serviceData['header_content']['title']); ?>" required>
                                            <label for="header_title">Header Title</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="header_subtitle" name="header_subtitle" 
                                                value="<?php echo htmlspecialchars($serviceData['header_content']['subtitle']); ?>">
                                            <label for="header_subtitle">Header Subtitle</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Header</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Main Title</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_main_title">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="main_title" name="main_title" 
                                                value="<?php echo htmlspecialchars($serviceData['main_title']); ?>" required>
                                            <label for="main_title">Main Title Text</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Title</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table admin-table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Image</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($serviceData['services'] as $service): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($service['title']); ?></td>
                                                <td><?php echo htmlspecialchars($service['image']); ?></td>
                                                <td>
                                                    <a href="?edit_service=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary me-2">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="delete_service" value="<?php echo $service['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this service?')">
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

    <!-- Add/Edit Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel">
                        <?php echo $editService ? 'Edit Service' : 'Add New Service'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $editService ? 'update_service' : 'add_service'; ?>">
                    <?php if ($editService): ?>
                        <input type="hidden" name="service_id" value="<?php echo $editService['id']; ?>">
                    <?php endif; ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="title" name="title" 
                                        value="<?php echo $editService ? htmlspecialchars($editService['title']) : ''; ?>" required>
                                    <label for="title">Service Title</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="image" name="image" disabled
                                        value="<?php echo $editService ? htmlspecialchars($editService['image']) : ''; ?>" required>
                                    <label for="image">Image Path</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="position" name="position" disabled required>
                                        <option value="left" <?php echo ($editService && $editService['position'] === 'left') ? 'selected' : ''; ?>>Left</option>
                                        <option value="right" <?php echo ($editService && $editService['position'] === 'right') ? 'selected' : ''; ?>>Right</option>
                                    </select>
                                    <label for="position">Image Position</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="description" name="description" 
                                        style="height: 200px" required><?php echo $editService ? htmlspecialchars($editService['description']) : ''; ?></textarea>
                                    <label for="description">Service Description</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn admin-contact-btn">Save Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show modal if editing service
        <?php if ($editService): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('addServiceModal'));
                modal.show();
            });
        <?php endif; ?>
    </script>
</body>

</html>