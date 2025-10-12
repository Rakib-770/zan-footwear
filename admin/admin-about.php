<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load about data
$aboutData = json_decode(file_get_contents('../data/about.json'), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_header') {
            // Update header content
            $aboutData['header'] = [
                'title' => $_POST['header_title'],
                'breadcrumb' => explode(",", $_POST['breadcrumb_items'])
            ];
        } elseif ($_POST['action'] === 'update_about_section') {
            // Update about section
            $bullet_points = array_filter(array_map('trim', explode("\n", $_POST['bullet_points'])));
            
            $aboutData['about_section'] = [
                'image' => $_POST['about_image'],
                'title' => $_POST['about_title'],
                'description' => $_POST['about_description'],
                'features' => [
                    [
                        'icon' => $_POST['feature1_icon'],
                        'title' => $_POST['feature1_title']
                    ],
                    [
                        'icon' => $_POST['feature2_icon'],
                        'title' => $_POST['feature2_title']
                    ]
                ],
                'bullet_points' => $bullet_points
            ];
        } elseif ($_POST['action'] === 'update_features_section') {
            // Update features section
            $aboutData['features_section'] = [
                'title' => $_POST['features_title'],
                'subtitle' => $_POST['features_subtitle'],
                'features' => []
            ];
            
            for ($i = 1; $i <= 4; $i++) {
                $aboutData['features_section']['features'][] = [
                    'title' => $_POST["feature{$i}_title"],
                    'description' => $_POST["feature{$i}_description"],
                    'icon' => $_POST["feature{$i}_icon"]
                ];
            }
        } elseif ($_POST['action'] === 'update_mission_vision') {
            // Update mission, vision, goal
            $aboutData['mission_vision_goal'] = [
                'mission' => [
                    'title' => $_POST['mission_title'],
                    'description' => $_POST['mission_description'],
                    'icon' => $_POST['mission_icon']
                ],
                'vision' => [
                    'title' => $_POST['vision_title'],
                    'description' => $_POST['vision_description'],
                    'icon' => $_POST['vision_icon']
                ],
                'goal' => [
                    'title' => $_POST['goal_title'],
                    'description' => $_POST['goal_description'],
                    'icon' => $_POST['goal_icon']
                ]
            ];
        }
    }

    // Save to JSON file
    file_put_contents('../data/about.json', json_encode($aboutData, JSON_PRETTY_PRINT));

    // Success message
    $success = "About page information updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - About Page Management</title>
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
                    <h1 class="page-title">About Page Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <!-- Header Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Page Header</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_header">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="header_title" name="header_title" 
                                                value="<?php echo htmlspecialchars($aboutData['header']['title']); ?>" required>
                                            <label for="header_title">Page Title</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="breadcrumb_items" name="breadcrumb_items" 
                                                value="<?php echo htmlspecialchars(implode(",", $aboutData['header']['breadcrumb'])); ?>">
                                            <label for="breadcrumb_items">Breadcrumb Items (comma separated)</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Header</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- About Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">About Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_about_section">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="about_image" name="about_image" disabled
                                                value="<?php echo htmlspecialchars($aboutData['about_section']['image']); ?>" required>
                                            <label for="about_image">Image Path</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="about_title" name="about_title" 
                                                value="<?php echo htmlspecialchars($aboutData['about_section']['title']); ?>" required>
                                            <label for="about_title">Section Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="about_description" name="about_description" 
                                                style="height: 100px" required><?php echo htmlspecialchars($aboutData['about_section']['description']); ?></textarea>
                                            <label for="about_description">Description</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Features -->
                                    <div class="col-md-6">
                                        <h5>Feature 1</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="feature1_icon" name="feature1_icon" 
                                                value="<?php echo htmlspecialchars($aboutData['about_section']['features'][0]['icon']); ?>" required>
                                            <label for="feature1_icon">Icon Class</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="feature1_title" name="feature1_title" 
                                                value="<?php echo htmlspecialchars($aboutData['about_section']['features'][0]['title']); ?>" required>
                                            <label for="feature1_title">Title</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Feature 2</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="feature2_icon" name="feature2_icon" 
                                                value="<?php echo htmlspecialchars($aboutData['about_section']['features'][1]['icon']); ?>" required>
                                            <label for="feature2_icon">Icon Class</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="feature2_title" name="feature2_title" 
                                                value="<?php echo htmlspecialchars($aboutData['about_section']['features'][1]['title']); ?>" required>
                                            <label for="feature2_title">Title</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="bullet_points" name="bullet_points" 
                                                style="height: 150px"><?php echo htmlspecialchars(implode("\n", $aboutData['about_section']['bullet_points'])); ?></textarea>
                                            <label for="bullet_points">Bullet Points (one per line)</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update About Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Features Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Features Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_features_section">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="features_title" name="features_title" 
                                                value="<?php echo htmlspecialchars($aboutData['features_section']['title']); ?>" required>
                                            <label for="features_title">Section Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="features_subtitle" name="features_subtitle" 
                                                style="height: 100px" required><?php echo htmlspecialchars($aboutData['features_section']['subtitle']); ?></textarea>
                                            <label for="features_subtitle">Section Subtitle</label>
                                        </div>
                                    </div>
                                    
                                    <?php foreach ($aboutData['features_section']['features'] as $i => $feature): ?>
                                        <div class="col-md-6">
                                            <h5>Feature <?php echo $i+1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="feature<?php echo $i+1; ?>_icon" name="feature<?php echo $i+1; ?>_icon" 
                                                    value="<?php echo htmlspecialchars($feature['icon']); ?>" required>
                                                <label for="feature<?php echo $i+1; ?>_icon">Icon Class</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="feature<?php echo $i+1; ?>_title" name="feature<?php echo $i+1; ?>_title" 
                                                    value="<?php echo htmlspecialchars($feature['title']); ?>" required>
                                                <label for="feature<?php echo $i+1; ?>_title">Title</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="feature<?php echo $i+1; ?>_description" name="feature<?php echo $i+1; ?>_description" 
                                                    style="height: 100px" required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                                                <label for="feature<?php echo $i+1; ?>_description">Description</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Features Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Mission, Vision, Goal Section -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">Mission, Vision & Goal</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_mission_vision">
                                <div class="row g-3">
                                    <!-- Mission -->
                                    <div class="col-md-4">
                                        <h5>Mission</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="mission_icon" name="mission_icon" disabled
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_goal']['mission']['icon']); ?>" required>
                                            <label for="mission_icon">Icon Class</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="mission_title" name="mission_title" 
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_goal']['mission']['title']); ?>" required>
                                            <label for="mission_title">Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="mission_description" name="mission_description" 
                                                style="height: 150px" required><?php echo htmlspecialchars($aboutData['mission_vision_goal']['mission']['description']); ?></textarea>
                                            <label for="mission_description">Description</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Vision -->
                                    <div class="col-md-4">
                                        <h5>Vision</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="vision_icon" name="vision_icon" disabled
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_goal']['vision']['icon']); ?>" required>
                                            <label for="vision_icon">Icon Class</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="vision_title" name="vision_title" 
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_goal']['vision']['title']); ?>" required>
                                            <label for="vision_title">Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="vision_description" name="vision_description" 
                                                style="height: 150px" required><?php echo htmlspecialchars($aboutData['mission_vision_goal']['vision']['description']); ?></textarea>
                                            <label for="vision_description">Description</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Goal -->
                                    <div class="col-md-4">
                                        <h5>Goal</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="goal_icon" name="goal_icon" disabled
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_goal']['goal']['icon']); ?>" required>
                                            <label for="goal_icon">Icon Class</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="goal_title" name="goal_title" 
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_goal']['goal']['title']); ?>" required>
                                            <label for="goal_title">Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="goal_description" name="goal_description" 
                                                style="height: 150px" required><?php echo htmlspecialchars($aboutData['mission_vision_goal']['goal']['description']); ?></textarea>
                                            <label for="goal_description">Description</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Mission/Vision/Goal</button>
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