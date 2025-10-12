<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load team data
$teamData = json_decode(file_get_contents('../data/team.json'), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a delete action
    if (isset($_POST['delete_member'])) {
        $type = $_POST['member_type'] ?? '';
        $id = $_POST['member_id'] ?? '';
        
        if ($type === 'top_management') {
            $teamData['top_management']['members'] = array_filter($teamData['top_management']['members'], function($member) use ($id) {
                return $member['id'] != $id;
            });
        } else if ($type === 'core_team') {
            $teamData['core_team']['members'] = array_filter($teamData['core_team']['members'], function($member) use ($id) {
                return $member['id'] != $id;
            });
        }
    } 
    // Check if it's an edit action
    elseif (isset($_POST['edit_member'])) {
        $type = $_POST['member_type'] ?? '';
        $id = $_POST['member_id'] ?? '';
        
        if ($type === 'top_management') {
            foreach ($teamData['top_management']['members'] as &$member) {
                if ($member['id'] == $id) {
                    $member['name'] = $_POST['name'] ?? $member['name'];
                    $member['position'] = $_POST['position'] ?? $member['position'];
                    if (!empty($_FILES['image']['name'])) {
                        $target_dir = "../img/team/";
                        $target_file = $target_dir . basename($_FILES["image"]["name"]);
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                            $member['image'] = basename($_FILES["image"]["name"]);
                        }
                    }
                    $member['social']['facebook'] = $_POST['facebook'] ?? $member['social']['facebook'];
                    $member['social']['twitter'] = $_POST['twitter'] ?? $member['social']['twitter'];
                    $member['social']['linkedin'] = $_POST['linkedin'] ?? $member['social']['linkedin'];
                    break;
                }
            }
        } else if ($type === 'core_team') {
            foreach ($teamData['core_team']['members'] as &$member) {
                if ($member['id'] == $id) {
                    $member['name'] = $_POST['name'] ?? $member['name'];
                    $member['position'] = $_POST['position'] ?? $member['position'];
                    if (!empty($_FILES['image']['name'])) {
                        $target_dir = "../img/team/";
                        $target_file = $target_dir . basename($_FILES["image"]["name"]);
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                            $member['image'] = basename($_FILES["image"]["name"]);
                        }
                    }
                    $member['social']['facebook'] = $_POST['facebook'] ?? $member['social']['facebook'];
                    $member['social']['twitter'] = $_POST['twitter'] ?? $member['social']['twitter'];
                    $member['social']['linkedin'] = $_POST['linkedin'] ?? $member['social']['linkedin'];
                    break;
                }
            }
        }
    }
    // Add new member
    elseif (isset($_POST['type'])) {
        $type = $_POST['type'] ?? '';
        $name = $_POST['name'] ?? '';
        $position = $_POST['position'] ?? '';
        
        if (!empty($type) && !empty($name) && !empty($position)) {
            $newMember = [
                'id' => uniqid(),
                'name' => $name,
                'position' => $position,
                'image' => 'default.jpg', // Default image
                'social' => [
                    'facebook' => $_POST['facebook'] ?? '#',
                    'twitter' => $_POST['twitter'] ?? '#',
                    'linkedin' => $_POST['linkedin'] ?? '#'
                ]
            ];
            
            if (!empty($_FILES['image']['name'])) {
                $target_dir = "../img/team/";
                $target_file = $target_dir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $newMember['image'] = basename($_FILES["image"]["name"]);
                }
            }
            
            if ($type === 'top_management') {
                $teamData['top_management']['members'][] = $newMember;
            } else if ($type === 'core_team') {
                $teamData['core_team']['members'][] = $newMember;
            }
        }
    }
    
    // Update page settings
    $teamData['page_title'] = $_POST['page_title'] ?? $teamData['page_title'];
    $teamData['header']['title'] = $_POST['header_title'] ?? $teamData['header']['title'];
    $teamData['header']['breadcrumb'] = $_POST['header_breadcrumb'] ?? $teamData['header']['breadcrumb'];
    $teamData['top_management']['title'] = $_POST['top_management_title'] ?? $teamData['top_management']['title'];
    $teamData['core_team']['title'] = $_POST['core_team_title'] ?? $teamData['core_team']['title'];
    
    // Save to JSON file
    file_put_contents('../data/team.json', json_encode($teamData, JSON_PRETTY_PRINT));
    
    // Success message
    $success = "Team information updated successfully!";
    
    // Reload data
    $teamData = json_decode(file_get_contents('../data/team.json'), true);
}

// Get next ID for new members
function getNextId($members) {
    $maxId = 0;
    foreach ($members as $member) {
        if (isset($member['id']) && $member['id'] > $maxId) {
            $maxId = $member['id'];
        }
    }
    return $maxId + 1;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Team Management</title>
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
                    <h1 class="page-title">Team Management</h1>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <div class="admin-card">
                        <div class="card-body">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <h4 class="admin-team-section-title">Page Settings</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="page_title" name="page_title" value="<?php echo htmlspecialchars($teamData['page_title']); ?>" required>
                                            <label for="page_title">Page Title</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h4 class="admin-team-section-title">Header Content</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="header_title" name="header_title" value="<?php echo htmlspecialchars($teamData['header']['title']); ?>" required>
                                            <label for="header_title">Header Title</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="header_breadcrumb" name="header_breadcrumb" value="<?php echo htmlspecialchars($teamData['header']['breadcrumb']); ?>" required>
                                            <label for="header_breadcrumb">Breadcrumb Text</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h4 class="admin-team-section-title">Section Titles</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="top_management_title" name="top_management_title" value="<?php echo htmlspecialchars($teamData['top_management']['title']); ?>" required>
                                            <label for="top_management_title">Top Management Title</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="core_team_title" name="core_team_title" value="<?php echo htmlspecialchars($teamData['core_team']['title']); ?>" required>
                                            <label for="core_team_title">Core Team Title</label>
                                        </div>
                                    </div>

                                    <div class="col-12 text-center mt-4">
                                        <button type="submit" class="btn admin-team-btn">Update Page Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Top Management Section -->
                    <div class="admin-card mt-4">
                        <div class="card-body">
                            <h3 class="admin-team-section-title">Top Management</h3>

                            <!-- Add New Top Management Member -->
                            <div class="mb-4">
                                <button class="btn admin-team-btn" data-bs-toggle="modal" data-bs-target="#addTopManagementModal">
                                    <i class="fas fa-plus"></i> Add New Top Management Member
                                </button>
                            </div>

                            <!-- Top Management Members List -->
                            <div class="row">
                                <?php foreach ($teamData['top_management']['members'] as $member): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="admin-team-card">
                                            <div class="d-flex align-items-start">
                                                <img src="../img/team/<?php echo htmlspecialchars($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="me-3">
                                                <div>
                                                    <h5><?php echo htmlspecialchars($member['name']); ?></h5>
                                                    <p class="mb-1"><?php echo htmlspecialchars($member['position']); ?></p>
                                                    <div class="d-flex mt-2">
                                                        <button class="btn admin-team-edit-btn me-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editMemberModal"
                                                            data-id="<?php echo $member['id']; ?>"
                                                            data-type="top_management"
                                                            data-name="<?php echo htmlspecialchars($member['name']); ?>"
                                                            data-position="<?php echo htmlspecialchars($member['position']); ?>"
                                                            data-facebook="<?php echo htmlspecialchars($member['social']['facebook']); ?>"
                                                            data-twitter="<?php echo htmlspecialchars($member['social']['twitter']); ?>"
                                                            data-linkedin="<?php echo htmlspecialchars($member['social']['linkedin']); ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                            <input type="hidden" name="member_type" value="top_management">
                                                            <button type="submit" name="delete_member" class="btn admin-team-delete-btn">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Core Team Section -->
                    <div class="admin-card mt-4">
                        <div class="card-body">
                            <h3 class="admin-team-section-title">Core Team</h3>

                            <!-- Add New Core Team Member -->
                            <div class="mb-4">
                                <button class="btn admin-team-btn" data-bs-toggle="modal" data-bs-target="#addCoreTeamModal">
                                    <i class="fas fa-plus"></i> Add New Core Team Member
                                </button>
                            </div>

                            <!-- Core Team Members List -->
                            <div class="row">
                                <?php foreach ($teamData['core_team']['members'] as $member): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="admin-team-card">
                                            <div class="d-flex align-items-start">
                                                <img src="../img/team/<?php echo htmlspecialchars($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="me-3">
                                                <div>
                                                    <h5><?php echo htmlspecialchars($member['name']); ?></h5>
                                                    <p class="mb-1"><?php echo htmlspecialchars($member['position']); ?></p>
                                                    <div class="d-flex mt-2">
                                                        <button class="btn admin-team-edit-btn me-2"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editMemberModal"
                                                            data-id="<?php echo $member['id']; ?>"
                                                            data-type="core_team"
                                                            data-name="<?php echo htmlspecialchars($member['name']); ?>"
                                                            data-position="<?php echo htmlspecialchars($member['position']); ?>"
                                                            data-facebook="<?php echo htmlspecialchars($member['social']['facebook']); ?>"
                                                            data-twitter="<?php echo htmlspecialchars($member['social']['twitter']); ?>"
                                                            data-linkedin="<?php echo htmlspecialchars($member['social']['linkedin']); ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                            <input type="hidden" name="member_type" value="core_team">
                                                            <button type="submit" name="delete_member" class="btn admin-team-delete-btn">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Top Management Modal -->
    <div class="modal fade" id="addTopManagementModal" tabindex="-1" aria-labelledby="addTopManagementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTopManagementModalLabel">Add New Top Management Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="type" value="top_management">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="position" name="position" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image">
                        </div>
                        <div class="mb-3">
                            <label for="facebook" class="form-label">Facebook URL</label>
                            <input type="text" class="form-control" id="facebook" name="facebook">
                        </div>
                        <div class="mb-3">
                            <label for="twitter" class="form-label">Twitter URL</label>
                            <input type="text" class="form-control" id="twitter" name="twitter">
                        </div>
                        <div class="mb-3">
                            <label for="linkedin" class="form-label">LinkedIn URL</label>
                            <input type="text" class="form-control" id="linkedin" name="linkedin">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn admin-team-btn">Add Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Core Team Modal -->
    <div class="modal fade" id="addCoreTeamModal" tabindex="-1" aria-labelledby="addCoreTeamModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCoreTeamModalLabel">Add New Core Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="type" value="core_team">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="position" name="position" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image">
                        </div>
                        <div class="mb-3">
                            <label for="facebook" class="form-label">Facebook URL</label>
                            <input type="text" class="form-control" id="facebook" name="facebook">
                        </div>
                        <div class="mb-3">
                            <label for="twitter" class="form-label">Twitter URL</label>
                            <input type="text" class="form-control" id="twitter" name="twitter">
                        </div>
                        <div class="mb-3">
                            <label for="linkedin" class="form-label">LinkedIn URL</label>
                            <input type="text" class="form-control" id="linkedin" name="linkedin">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn admin-team-btn">Add Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Member Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemberModalLabel">Edit Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="edit_member" value="1">
                        <input type="hidden" id="edit_member_id" name="member_id">
                        <input type="hidden" id="edit_member_type" name="member_type">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_position" class="form-label">Position</label>
                            <input type="text" class="form-control" id="edit_position" name="position" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image" class="form-label">Image (Leave blank to keep current)</label>
                            <input type="file" class="form-control" id="edit_image" name="image">
                        </div>
                        <div class="mb-3">
                            <label for="edit_facebook" class="form-label">Facebook URL</label>
                            <input type="text" class="form-control" id="edit_facebook" name="facebook">
                        </div>
                        <div class="mb-3">
                            <label for="edit_twitter" class="form-label">Twitter URL</label>
                            <input type="text" class="form-control" id="edit_twitter" name="twitter">
                        </div>
                        <div class="mb-3">
                            <label for="edit_linkedin" class="form-label">LinkedIn URL</label>
                            <input type="text" class="form-control" id="edit_linkedin" name="linkedin">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn admin-team-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit modal data
        document.getElementById('editMemberModal').addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var modal = this;

            modal.querySelector('#edit_member_id').value = button.getAttribute('data-id');
            modal.querySelector('#edit_member_type').value = button.getAttribute('data-type');
            modal.querySelector('#edit_name').value = button.getAttribute('data-name');
            modal.querySelector('#edit_position').value = button.getAttribute('data-position');
            modal.querySelector('#edit_facebook').value = button.getAttribute('data-facebook');
            modal.querySelector('#edit_twitter').value = button.getAttribute('data-twitter');
            modal.querySelector('#edit_linkedin').value = button.getAttribute('data-linkedin');
        });
    </script>
</body>

</html>