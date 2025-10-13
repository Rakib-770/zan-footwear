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
        switch ($_POST['action']) {
            case 'update_hero':
                $aboutData['hero_section'] = [
                    'background_image' => $_POST['background_image'],
                    'title' => $_POST['hero_title'],
                    'description' => $_POST['hero_description'],
                    'primary_button' => [
                        'text' => $_POST['primary_button_text'],
                        'link' => $_POST['primary_button_link']
                    ],
                    'secondary_button' => [
                        'text' => $_POST['secondary_button_text'],
                        'link' => $_POST['secondary_button_link']
                    ]
                ];
                break;

            case 'update_our_story':
                $paragraphs = array_filter(array_map('trim', explode("\n", $_POST['paragraphs'])));
                $aboutData['our_story_section'] = [
                    'title' => $_POST['our_story_title'],
                    'description' => $_POST['our_story_description'],
                    'paragraphs' => $paragraphs,
                    'image' => $_POST['our_story_image'],
                    'features' => [
                        [
                            'icon' => $_POST['feature1_icon'],
                            'title' => $_POST['feature1_title'],
                            'description' => $_POST['feature1_description']
                        ],
                        [
                            'icon' => $_POST['feature2_icon'],
                            'title' => $_POST['feature2_title'],
                            'description' => $_POST['feature2_description']
                        ]
                    ]
                ];
                break;

            case 'update_mission_vision':
                $aboutData['mission_vision_section'] = [
                    'image' => $_POST['mission_vision_image'],
                    'title' => $_POST['mission_vision_title'],
                    'mission' => [
                        'title' => $_POST['mission_title'],
                        'description' => $_POST['mission_description']
                    ],
                    'vision' => [
                        'title' => $_POST['vision_title'],
                        'description' => $_POST['vision_description']
                    ]
                ];
                break;

            case 'update_values':
                $values = [];
                for ($i = 1; $i <= 4; $i++) {
                    $values[] = [
                        'icon' => $_POST["value{$i}_icon"],
                        'title' => $_POST["value{$i}_title"],
                        'description' => $_POST["value{$i}_description"]
                    ];
                }
                $aboutData['values_section'] = [
                    'title' => $_POST['values_title'],
                    'values' => $values
                ];
                break;

            case 'update_manufacturing':
                $features = array_filter(array_map('trim', explode("\n", $_POST['manufacturing_features'])));
                $images = [
                    $_POST['manufacturing_image1'],
                    $_POST['manufacturing_image2'],
                    $_POST['manufacturing_image3']
                ];
                $aboutData['manufacturing_section'] = [
                    'title' => $_POST['manufacturing_title'],
                    'description' => $_POST['manufacturing_description'],
                    'features' => $features,
                    'conclusion' => $_POST['manufacturing_conclusion'],
                    'images' => $images
                ];
                break;

            case 'update_team':
                $members = [];
                for ($i = 1; $i <= 3; $i++) {
                    $social_links = [];
                    for ($j = 1; $j <= 3; $j++) {
                        $social_links[] = [
                            'icon' => $_POST["member{$i}_social_icon{$j}"],
                            'link' => $_POST["member{$i}_social_link{$j}"]
                        ];
                    }
                    $members[] = [
                        'image' => $_POST["member{$i}_image"],
                        'name' => $_POST["member{$i}_name"],
                        'position' => $_POST["member{$i}_position"],
                        'bio' => $_POST["member{$i}_bio"],
                        'social_links' => $social_links
                    ];
                }
                $aboutData['team_section'] = [
                    'title' => $_POST['team_title'],
                    'members' => $members
                ];
                break;

            case 'update_global_presence':
                $features = [];
                for ($i = 1; $i <= 3; $i++) {
                    $features[] = [
                        'icon' => $_POST["global_feature{$i}_icon"],
                        'title' => $_POST["global_feature{$i}_title"],
                        'description' => $_POST["global_feature{$i}_description"]
                    ];
                }
                $aboutData['global_presence_section'] = [
                    'title' => $_POST['global_presence_title'],
                    'features' => $features
                ];
                break;
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
                                                value="<?php echo htmlspecialchars($aboutData['hero_section']['background_image']); ?>" required>
                                            <label for="background_image">Background Image Path</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="hero_title" name="hero_title" 
                                                value="<?php echo htmlspecialchars($aboutData['hero_section']['title']); ?>" required>
                                            <label for="hero_title">Hero Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="hero_description" name="hero_description" 
                                                style="height: 100px" required><?php echo htmlspecialchars($aboutData['hero_section']['description']); ?></textarea>
                                            <label for="hero_description">Hero Description</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Primary Button</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_text" name="primary_button_text" 
                                                value="<?php echo htmlspecialchars($aboutData['hero_section']['primary_button']['text']); ?>" required>
                                            <label for="primary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="primary_button_link" name="primary_button_link" 
                                                value="<?php echo htmlspecialchars($aboutData['hero_section']['primary_button']['link']); ?>" required>
                                            <label for="primary_button_link">Button Link</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Secondary Button</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_text" name="secondary_button_text" 
                                                value="<?php echo htmlspecialchars($aboutData['hero_section']['secondary_button']['text']); ?>" required>
                                            <label for="secondary_button_text">Button Text</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="secondary_button_link" name="secondary_button_link" 
                                                value="<?php echo htmlspecialchars($aboutData['hero_section']['secondary_button']['link']); ?>" required>
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

                    <!-- Our Story Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Our Story Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_our_story">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="our_story_title" name="our_story_title" 
                                                value="<?php echo htmlspecialchars($aboutData['our_story_section']['title']); ?>" required>
                                            <label for="our_story_title">Section Title</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="our_story_image" name="our_story_image" 
                                                value="<?php echo htmlspecialchars($aboutData['our_story_section']['image']); ?>" required>
                                            <label for="our_story_image">Image Path</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="our_story_description" name="our_story_description" 
                                                style="height: 100px" required><?php echo htmlspecialchars($aboutData['our_story_section']['description']); ?></textarea>
                                            <label for="our_story_description">Main Description</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="paragraphs" name="paragraphs" 
                                                style="height: 150px"><?php echo htmlspecialchars(implode("\n", $aboutData['our_story_section']['paragraphs'])); ?></textarea>
                                            <label for="paragraphs">Additional Paragraphs (one per line)</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Features -->
                                    <?php foreach ($aboutData['our_story_section']['features'] as $i => $feature): ?>
                                        <div class="col-md-6">
                                            <h5>Feature <?php echo $i+1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="feature<?php echo $i+1; ?>_icon" name="feature<?php echo $i+1; ?>_icon" 
                                                    value="<?php echo htmlspecialchars($feature['icon']); ?>" required>
                                                <label for="feature<?php echo $i+1; ?>_icon">Icon Path</label>
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
                                        <button type="submit" class="btn admin-contact-btn">Update Our Story Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Mission & Vision Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Mission & Vision Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_mission_vision">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="mission_vision_image" name="mission_vision_image" 
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_section']['image']); ?>" required>
                                            <label for="mission_vision_image">Image Path</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="mission_vision_title" name="mission_vision_title" 
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_section']['title']); ?>" required>
                                            <label for="mission_vision_title">Section Title</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Mission -->
                                    <div class="col-md-6">
                                        <h5>Mission</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="mission_title" name="mission_title" 
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_section']['mission']['title']); ?>" required>
                                            <label for="mission_title">Mission Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="mission_description" name="mission_description" 
                                                style="height: 150px" required><?php echo htmlspecialchars($aboutData['mission_vision_section']['mission']['description']); ?></textarea>
                                            <label for="mission_description">Mission Description</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Vision -->
                                    <div class="col-md-6">
                                        <h5>Vision</h5>
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="vision_title" name="vision_title" 
                                                value="<?php echo htmlspecialchars($aboutData['mission_vision_section']['vision']['title']); ?>" required>
                                            <label for="vision_title">Vision Title</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="vision_description" name="vision_description" 
                                                style="height: 150px" required><?php echo htmlspecialchars($aboutData['mission_vision_section']['vision']['description']); ?></textarea>
                                            <label for="vision_description">Vision Description</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Mission & Vision</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Values Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Values Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_values">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="values_title" name="values_title" 
                                                value="<?php echo htmlspecialchars($aboutData['values_section']['title']); ?>" required>
                                            <label for="values_title">Section Title</label>
                                        </div>
                                    </div>
                                    
                                    <?php foreach ($aboutData['values_section']['values'] as $i => $value): ?>
                                        <div class="col-md-6 col-lg-3">
                                            <h5>Value <?php echo $i+1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="value<?php echo $i+1; ?>_icon" name="value<?php echo $i+1; ?>_icon" 
                                                    value="<?php echo htmlspecialchars($value['icon']); ?>" required>
                                                <label for="value<?php echo $i+1; ?>_icon">Icon Path</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="value<?php echo $i+1; ?>_title" name="value<?php echo $i+1; ?>_title" 
                                                    value="<?php echo htmlspecialchars($value['title']); ?>" required>
                                                <label for="value<?php echo $i+1; ?>_title">Title</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="value<?php echo $i+1; ?>_description" name="value<?php echo $i+1; ?>_description" 
                                                    style="height: 100px" required><?php echo htmlspecialchars($value['description']); ?></textarea>
                                                <label for="value<?php echo $i+1; ?>_description">Description</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Values Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Manufacturing Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Manufacturing Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_manufacturing">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="manufacturing_title" name="manufacturing_title" 
                                                value="<?php echo htmlspecialchars($aboutData['manufacturing_section']['title']); ?>" required>
                                            <label for="manufacturing_title">Section Title</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="manufacturing_description" name="manufacturing_description" 
                                                style="height: 100px" required><?php echo $aboutData['manufacturing_section']['description']; ?></textarea>
                                            <label for="manufacturing_description">Description</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="manufacturing_features" name="manufacturing_features" 
                                                style="height: 150px"><?php echo htmlspecialchars(implode("\n", $aboutData['manufacturing_section']['features'])); ?></textarea>
                                            <label for="manufacturing_features">Features (one per line)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="manufacturing_conclusion" name="manufacturing_conclusion" 
                                                style="height: 100px" required><?php echo htmlspecialchars($aboutData['manufacturing_section']['conclusion']); ?></textarea>
                                            <label for="manufacturing_conclusion">Conclusion</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Images -->
                                    <?php foreach ($aboutData['manufacturing_section']['images'] as $i => $image): ?>
                                        <div class="col-md-4">
                                            <h5>Image <?php echo $i+1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="manufacturing_image<?php echo $i+1; ?>" name="manufacturing_image<?php echo $i+1; ?>" 
                                                    value="<?php echo htmlspecialchars($image); ?>" required>
                                                <label for="manufacturing_image<?php echo $i+1; ?>">Image Path</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Manufacturing Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Team Section -->
                    <div class="admin-card mb-4">
                        <div class="card-body">
                            <h4 class="section-title">Team Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_team">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="team_title" name="team_title" 
                                                value="<?php echo htmlspecialchars($aboutData['team_section']['title']); ?>" required>
                                            <label for="team_title">Section Title</label>
                                        </div>
                                    </div>
                                    
                                    <?php foreach ($aboutData['team_section']['members'] as $i => $member): ?>
                                        <div class="col-md-4">
                                            <h5>Team Member <?php echo $i+1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="member<?php echo $i+1; ?>_image" name="member<?php echo $i+1; ?>_image" 
                                                    value="<?php echo htmlspecialchars($member['image']); ?>" required>
                                                <label for="member<?php echo $i+1; ?>_image">Image Path</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="member<?php echo $i+1; ?>_name" name="member<?php echo $i+1; ?>_name" 
                                                    value="<?php echo htmlspecialchars($member['name']); ?>" required>
                                                <label for="member<?php echo $i+1; ?>_name">Name</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="member<?php echo $i+1; ?>_position" name="member<?php echo $i+1; ?>_position" 
                                                    value="<?php echo htmlspecialchars($member['position']); ?>" required>
                                                <label for="member<?php echo $i+1; ?>_position">Position</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="member<?php echo $i+1; ?>_bio" name="member<?php echo $i+1; ?>_bio" 
                                                    style="height: 100px" required><?php echo htmlspecialchars($member['bio']); ?></textarea>
                                                <label for="member<?php echo $i+1; ?>_bio">Bio</label>
                                            </div>
                                            
                                            <!-- Social Links -->
                                            <?php foreach ($member['social_links'] as $j => $social): ?>
                                                <h6>Social Link <?php echo $j+1; ?></h6>
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" id="member<?php echo $i+1; ?>_social_icon<?php echo $j+1; ?>" name="member<?php echo $i+1; ?>_social_icon<?php echo $j+1; ?>" 
                                                        value="<?php echo htmlspecialchars($social['icon']); ?>" required>
                                                    <label for="member<?php echo $i+1; ?>_social_icon<?php echo $j+1; ?>">Icon Class</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" id="member<?php echo $i+1; ?>_social_link<?php echo $j+1; ?>" name="member<?php echo $i+1; ?>_social_link<?php echo $j+1; ?>" 
                                                        value="<?php echo htmlspecialchars($social['link']); ?>" required>
                                                    <label for="member<?php echo $i+1; ?>_social_link<?php echo $j+1; ?>">Link</label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Team Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Global Presence Section -->
                    <div class="admin-card">
                        <div class="card-body">
                            <h4 class="section-title">Global Presence Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_global_presence">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="global_presence_title" name="global_presence_title" 
                                                value="<?php echo htmlspecialchars($aboutData['global_presence_section']['title']); ?>" required>
                                            <label for="global_presence_title">Section Title</label>
                                        </div>
                                    </div>
                                    
                                    <?php foreach ($aboutData['global_presence_section']['features'] as $i => $feature): ?>
                                        <div class="col-md-4">
                                            <h5>Feature <?php echo $i+1; ?></h5>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="global_feature<?php echo $i+1; ?>_icon" name="global_feature<?php echo $i+1; ?>_icon" 
                                                    value="<?php echo htmlspecialchars($feature['icon']); ?>" required>
                                                <label for="global_feature<?php echo $i+1; ?>_icon">Icon Path</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="global_feature<?php echo $i+1; ?>_title" name="global_feature<?php echo $i+1; ?>_title" 
                                                    value="<?php echo htmlspecialchars($feature['title']); ?>" required>
                                                <label for="global_feature<?php echo $i+1; ?>_title">Title</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control" id="global_feature<?php echo $i+1; ?>_description" name="global_feature<?php echo $i+1; ?>_description" 
                                                    style="height: 100px" required><?php echo htmlspecialchars($feature['description']); ?></textarea>
                                                <label for="global_feature<?php echo $i+1; ?>_description">Description</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Global Presence</button>
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