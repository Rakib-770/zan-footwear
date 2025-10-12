<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load portfolio data
$portfolioFile = '../data/portfolio.json';
$portfolioData = json_decode(file_get_contents($portfolioFile), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle portfolio items
    $portfolioItems = [];
    if (isset($_POST['portfolio_id'])) {
        foreach ($_POST['portfolio_id'] as $index => $id) {
            $portfolioItems[] = [
                'id' => $id,
                'title' => $_POST['portfolio_title'][$index],
                'description' => $_POST['portfolio_description'][$index],
                'image' => $_POST['portfolio_image'][$index],
                'delay' => $_POST['portfolio_delay'][$index]
            ];
        }
    }

    // Handle client items
    $clientItems = [];
    if (isset($_POST['client_id'])) {
        foreach ($_POST['client_id'] as $index => $id) {
            $clientItems[] = [
                'id' => $id,
                'logo' => $_POST['client_logo'][$index],
                'company_name' => $_POST['client_company'][$index],
                'description' => $_POST['client_description'][$index]
            ];
        }
    }

    // Update portfolio data
    $portfolioData = [
        'portfolio_items' => $portfolioItems,
        'clients' => $clientItems
    ];

    // Save to JSON file
    file_put_contents($portfolioFile, json_encode($portfolioData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Portfolio information updated successfully!";
}

// Handle image uploads for portfolio items
if (!empty($_FILES['portfolio_images']['name'][0])) {
    foreach ($_FILES['portfolio_images']['name'] as $index => $name) {
        if (!empty($name)) {
            $targetDir = "../img/portfolio/";
            $targetFile = $targetDir . basename($name);
            move_uploaded_file($_FILES['portfolio_images']['tmp_name'][$index], $targetFile);
        }
    }
}

// Handle logo uploads for client items
if (!empty($_FILES['client_logos']['name'][0])) {
    foreach ($_FILES['client_logos']['name'] as $index => $name) {
        if (!empty($name)) {
            $targetDir = "../img/portfolio/clients/";
            $targetFile = $targetDir . basename($name);
            move_uploaded_file($_FILES['client_logos']['tmp_name'][$index], $targetFile);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Portfolio Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-style.css">
    <style>
        .image-preview {
            max-width: 150px;
            max-height: 150px;
            margin-bottom: 10px;
        }

        .upload-container {
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
                    <h1 class="page-title">Portfolio Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <div class="admin-card">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <!-- Page Settings and Header Content sections remain the same as before -->

                                <div class="col-12">
                                    <h4 class="section-title">Portfolio Items</h4>
                                    <div class="table-responsive">
                                        <table class="table" id="portfolio-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Image</th>
                                                    <th>Title</th>
                                                    <th>Description</th>
                                                    <th>Animation Delay</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($portfolioData['portfolio_items'] as $index => $item): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="portfolio_id[]" value="<?= $item['id'] ?>">
                                                            <?php if (!empty($item['image'])): ?>
                                                                <img src="../img/portfolio/<?= htmlspecialchars($item['image']) ?>" class="image-preview" id="portfolio-preview-<?= $index ?>">
                                                            <?php endif; ?>
                                                            <div class="upload-container">
                                                                <input type="file" class="form-control portfolio-image-upload"
                                                                    name="portfolio_images[]" data-preview="portfolio-preview-<?= $index ?>">
                                                                <input type="hidden" name="portfolio_image[]"
                                                                    value="<?= htmlspecialchars($item['image']) ?>">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" name="portfolio_title[]"
                                                                value="<?= htmlspecialchars($item['title']) ?>" required>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control" name="portfolio_description[]"
                                                                rows="3"><?= htmlspecialchars($item['description']) ?></textarea>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" name="portfolio_delay[]"
                                                                value="<?= htmlspecialchars($item['delay']) ?>" required>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-danger btn-sm remove-portfolio-item">Remove</button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-primary" id="add-portfolio-item">Add Portfolio Item</button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <h4 class="section-title">Client Items</h4>
                                    <div class="table-responsive">
                                        <table class="table" id="client-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Logo</th>
                                                    <th>Company Name</th>
                                                    <th>Description</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($portfolioData['clients'] as $index => $client): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="client_id[]" value="<?= $client['id'] ?>">
                                                            <?php if (!empty($client['logo'])): ?>
                                                                <img src="../img/portfolio/clients/<?= htmlspecialchars($client['logo']) ?>" class="image-preview" id="client-preview-<?= $index ?>">
                                                            <?php endif; ?>
                                                            <div class="upload-container">
                                                                <input type="file" class="form-control client-logo-upload"
                                                                    name="client_logos[]" data-preview="client-preview-<?= $index ?>">
                                                                <input type="hidden" name="client_logo[]"
                                                                    value="<?= htmlspecialchars($client['logo']) ?>">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" name="client_company[]"
                                                                value="<?= htmlspecialchars($client['company_name']) ?>">
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control" name="client_description[]"
                                                                rows="3"><?= htmlspecialchars($client['description']) ?></textarea>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-danger btn-sm remove-client-item">Remove</button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-primary" id="add-client-item">Add Client Item</button>
                                    </div>
                                </div>

                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn admin-contact-btn">Update Portfolio Information</button>
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
        // Add new portfolio item
        document.getElementById('add-portfolio-item').addEventListener('click', function() {
            const table = document.querySelector('#portfolio-items-table tbody');
            const newId = Date.now();
            const newIndex = table.querySelectorAll('tr').length;

            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <input type="hidden" name="portfolio_id[]" value="${newId}">
                    <img src="" class="image-preview" id="portfolio-preview-${newIndex}" style="display:none;">
                    <div class="upload-container">
                        <input type="file" class="form-control portfolio-image-upload" 
                            name="portfolio_images[]" data-preview="portfolio-preview-${newIndex}">
                        <input type="hidden" name="portfolio_image[]" value="">
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control" name="portfolio_title[]" value="" required>
                </td>
                <td>
                    <textarea class="form-control" name="portfolio_description[]" rows="3"></textarea>
                </td>
                <td>
                    <input type="text" class="form-control" name="portfolio_delay[]" value="0.1s" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-portfolio-item">Remove</button>
                </td>
            `;

            table.appendChild(newRow);
        });

        // Add new client item
        document.getElementById('add-client-item').addEventListener('click', function() {
            const table = document.querySelector('#client-items-table tbody');
            const newId = Date.now();
            const newIndex = table.querySelectorAll('tr').length;

            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <input type="hidden" name="client_id[]" value="${newId}">
                    <img src="" class="image-preview" id="client-preview-${newIndex}" style="display:none;">
                    <div class="upload-container">
                        <input type="file" class="form-control client-logo-upload" 
                            name="client_logos[]" data-preview="client-preview-${newIndex}">
                        <input type="hidden" name="client_logo[]" value="">
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control" name="client_company[]" value="">
                </td>
                <td>
                    <textarea class="form-control" name="client_description[]" rows="3"></textarea>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-client-item">Remove</button>
                </td>
            `;

            table.appendChild(newRow);
        });

        // Image preview for portfolio items
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('portfolio-image-upload')) {
                const file = e.target.files[0];
                const previewId = e.target.getAttribute('data-preview');
                const preview = document.getElementById(previewId);
                const hiddenInput = e.target.nextElementSibling;

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        preview.src = event.target.result;
                        preview.style.display = 'block';
                        hiddenInput.value = file.name;
                    };
                    reader.readAsDataURL(file);
                }
            }

            if (e.target.classList.contains('client-logo-upload')) {
                const file = e.target.files[0];
                const previewId = e.target.getAttribute('data-preview');
                const preview = document.getElementById(previewId);
                const hiddenInput = e.target.nextElementSibling;

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        preview.src = event.target.result;
                        preview.style.display = 'block';
                        hiddenInput.value = file.name;
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Remove items
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-portfolio-item')) {
                e.target.closest('tr').remove();
            }

            if (e.target.classList.contains('remove-client-item')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
</body>

</html>