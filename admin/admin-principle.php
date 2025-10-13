<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load principle data
$principleData = json_decode(file_get_contents('../data/principle.json'), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_hero':
                $principleData['page']['hero'] = [
                    'background_image' => $_POST['background_image'],
                    'title' => $_POST['hero_title'],
                    'description' => $_POST['hero_description'],
                    'buttons' => [
                        [
                            'text' => $_POST['primary_button_text'],
                            'url' => $_POST['primary_button_link'],
                            'class' => $_POST['primary_button_class']
                        ],
                        [
                            'text' => $_POST['secondary_button_text'],
                            'url' => $_POST['secondary_button_link'],
                            'class' => $_POST['secondary_button_class']
                        ]
                    ]
                ];
                break;

            case 'update_principles':
                $principleData['page']['section_title'] = $_POST['section_title'];
                
                $principles = [];
                for ($i = 1; $i <= 4; $i++) {
                    $principles[] = [
                        'icon' => $_POST["principle{$i}_icon"],
                        'alt' => $_POST["principle{$i}_alt"],
                        'title' => $_POST["principle{$i}_title"],
                        'description' => $_POST["principle{$i}_description"]
                    ];
                }
                $principleData['page']['principles'] = $principles;
                break;
        }
    }

    // Save to JSON file
    file_put_contents('../data/principle.json', json_encode($principleData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Principle page information updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Principle Page Management</title>
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
                    <h1 class="page-title">Principle Page Management</h1>

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
                                                value="<?php echo htmlspecialchars($principleData['page']['hero']['background_image']); ?>" required>
                                            <label for="background_image">Background Image Path</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="hero_title" name="hero_title" 
                                                value="<?php echo htmlspecialchars($principleData['page']['hero']['title']); ?>" required>
                                            <label for="hero_title">Hero Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="hero_description" name="hero_description" 
                                                style="height: 100px" required><?php echo htmlspecialchars($principleData['page']['hero']['description']); ?></textarea>
                                            <label for="hero_description">Hero Description</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Primary Button -->
                                    <div class="col-md-6">
                                        <h5>Primary Button</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_text" name="primary_button_text" 
                                                value="<?php echo htmlspecialchars($principleData['page']['hero']['buttons'][0]['text']); ?>" required>
                                            <label for="primary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_link" name="primary_button_link" 
                                                value="<?php echo htmlspecialchars($principleData['page']['hero']['buttons'][0]['url']); ?>" required>
                                            <label for="primary_button_link">Button Link</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_class" name="primary_button_class" 
                                                value="<?php echo htmlspecialchars($principleData['page']['hero']['buttons'][0]['class']); ?>" required>
                                            <label for="primary_button_class">Button CSS Class</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Secondary Button -->
                                    <div class="col-md-6">
                                        <h5>Secondary Button</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_text" name="secondary_button_text" 
                                                value="<?php echo htmlspecialchars($principleData['page']['hero']['buttons'][1]['text']); ?>" required>
                                            <label for="secondary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_link" name="secondary_button_link" 
                                                value="<?php echo htmlspecialchars($principleData['page']['hero']['buttons'][1]['url']); ?>" required>
                                            <label for="secondary_button_link">Button Link</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_class" name="secondary_button_class" 
                                                value="<?php echo htmlspecialchars($principleData['page']['hero']['buttons'][1]['class']); ?>" required>
                                            <label for="secondary_button_class">Button CSS Class</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Hero Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Principles Section -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">Principles Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_principles">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="section_title" name="section_title" 
                                                value="<?php echo htmlspecialchars($principleData['page']['section_title']); ?>" required>
                                            <label for="section_title">Section Title</label>
                                        </div>
                                    </div>
                                    
                                    <?php foreach ($principleData['page']['principles'] as $i => $principle): ?>
                                        <div class="col-md-6 col-lg-3">
                                            <h5>Principle <?php echo $i+1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="principle<?php echo $i+1; ?>_icon" name="principle<?php echo $i+1; ?>_icon" 
                                                    value="<?php echo htmlspecialchars($principle['icon']); ?>" required>
                                                <label for="principle<?php echo $i+1; ?>_icon">Icon Path</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="principle<?php echo $i+1; ?>_alt" name="principle<?php echo $i+1; ?>_alt" 
                                                    value="<?php echo htmlspecialchars($principle['alt']); ?>" required>
                                                <label for="principle<?php echo $i+1; ?>_alt">Icon Alt Text</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="principle<?php echo $i+1; ?>_title" name="principle<?php echo $i+1; ?>_title" 
                                                    value="<?php echo htmlspecialchars($principle['title']); ?>" required>
                                                <label for="principle<?php echo $i+1; ?>_title">Title</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="principle<?php echo $i+1; ?>_description" name="principle<?php echo $i+1; ?>_description" 
                                                    style="height: 100px" required><?php echo htmlspecialchars($principle['description']); ?></textarea>
                                                <label for="principle<?php echo $i+1; ?>_description">Description</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Principles Section</button>
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