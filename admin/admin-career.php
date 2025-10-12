<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load career data
$careerData = json_decode(file_get_contents('../data/career.json'), true);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle job updates or additions
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_header') {
            // Update header content
            $careerData['header_content'] = [
                'title' => $_POST['header_title'],
                'subtitle' => $_POST['header_subtitle']
            ];
        } elseif ($_POST['action'] === 'update_opening_positions') {
            // Update opening positions header
            $careerData['opening_positions'] = [
                'title' => $_POST['opening_title'],
                'subtitle' => $_POST['opening_subtitle']
            ];
        } elseif ($_POST['action'] === 'add_job') {
            // Add new job
            $newJob = [
                'id' => uniqid(),
                'position' => $_POST['position'],
                'department' => $_POST['department'],
                'location' => $_POST['location'],
                'type' => $_POST['type'],
                'experience' => $_POST['experience'],
                'excerpt' => $_POST['excerpt'],
                'description' => $_POST['description'],
                'requirements' => explode("\n", $_POST['requirements']),
                'responsibilities' => explode("\n", $_POST['responsibilities']),
                'how_to_apply' => explode("\n", $_POST['how_to_apply'])
            ];
            
            $careerData['jobs'][] = $newJob;
        } elseif ($_POST['action'] === 'update_job') {
            // Update existing job
            foreach ($careerData['jobs'] as &$job) {
                if ($job['id'] === $_POST['job_id']) {
                    $job = [
                        'id' => $_POST['job_id'],
                        'position' => $_POST['position'],
                        'department' => $_POST['department'],
                        'location' => $_POST['location'],
                        'type' => $_POST['type'],
                        'experience' => $_POST['experience'],
                        'excerpt' => $_POST['excerpt'],
                        'description' => $_POST['description'],
                        'requirements' => explode("\n", $_POST['requirements']),
                        'responsibilities' => explode("\n", $_POST['responsibilities']),
                        'how_to_apply' => explode("\n", $_POST['how_to_apply'])
                    ];
                    break;
                }
            }
        }
    } elseif (isset($_POST['delete_job'])) {
        // Delete job
        $careerData['jobs'] = array_filter($careerData['jobs'], function($job) {
            return $job['id'] !== $_POST['delete_job'];
        });
        $careerData['jobs'] = array_values($careerData['jobs']); // Reindex array
    }

    // Save to JSON file
    file_put_contents('../data/career.json', json_encode($careerData, JSON_PRETTY_PRINT));

    // Success message
    $success = "Career information updated successfully!";
}

// Get job details for editing
$editJob = null;
if (isset($_GET['edit_job'])) {
    foreach ($careerData['jobs'] as $job) {
        if ($job['id'] === $_GET['edit_job']) {
            $editJob = $job;
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
    <title>Admin - Career Management</title>
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
                    <h1 class="page-title">Career Management</h1>

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
                                                value="<?php echo htmlspecialchars($careerData['header_content']['title']); ?>" required>
                                            <label for="header_title">Header Title</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="header_subtitle" name="header_subtitle" 
                                                value="<?php echo htmlspecialchars($careerData['header_content']['subtitle']); ?>">
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
                            <h4 class="section-title">Opening Positions Section</h4>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_opening_positions">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="opening_title" name="opening_title" 
                                                value="<?php echo htmlspecialchars($careerData['opening_positions']['title']); ?>" required>
                                            <label for="opening_title">Section Title</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="opening_subtitle" name="opening_subtitle" 
                                                style="height: 100px"><?php echo htmlspecialchars($careerData['opening_positions']['subtitle']); ?></textarea>
                                            <label for="opening_subtitle">Section Subtitle</label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn admin-contact-btn">Update Section</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="section-title mb-0">Job Positions</h4>
                                <button class="btn admin-contact-btn" data-bs-toggle="modal" data-bs-target="#addJobModal">
                                    <i class="fas fa-plus me-2"></i>Add New Job
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table admin-table">
                                    <thead>
                                        <tr>
                                            <th>Position</th>
                                            <th>Department</th>
                                            <th>Location</th>
                                            <th>Type</th>
                                            <th>Experience</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($careerData['jobs'] as $job): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($job['position']); ?></td>
                                                <td><?php echo htmlspecialchars($job['department']); ?></td>
                                                <td><?php echo htmlspecialchars($job['location']); ?></td>
                                                <td><?php echo htmlspecialchars($job['type']); ?></td>
                                                <td><?php echo htmlspecialchars($job['experience']); ?></td>
                                                <td>
                                                    <a href="?edit_job=<?php echo $job['id']; ?>" class="btn btn-sm btn-primary me-2">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="delete_job" value="<?php echo $job['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this job?')">
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

    <!-- Add/Edit Job Modal -->
    <div class="modal fade" id="addJobModal" tabindex="-1" aria-labelledby="addJobModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addJobModalLabel">
                        <?php echo $editJob ? 'Edit Job Position' : 'Add New Job Position'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $editJob ? 'update_job' : 'add_job'; ?>">
                    <?php if ($editJob): ?>
                        <input type="hidden" name="job_id" value="<?php echo $editJob['id']; ?>">
                    <?php endif; ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="position" name="position" 
                                        value="<?php echo $editJob ? htmlspecialchars($editJob['position']) : ''; ?>" required>
                                    <label for="position">Position Title</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="department" name="department" 
                                        value="<?php echo $editJob ? htmlspecialchars($editJob['department']) : ''; ?>" required>
                                    <label for="department">Department</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="location" name="location" 
                                        value="<?php echo $editJob ? htmlspecialchars($editJob['location']) : ''; ?>" required>
                                    <label for="location">Location</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="type" name="type" 
                                        value="<?php echo $editJob ? htmlspecialchars($editJob['type']) : ''; ?>" required>
                                    <label for="type">Job Type (Full-time, Part-time, etc.)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="experience" name="experience" 
                                        value="<?php echo $editJob ? htmlspecialchars($editJob['experience']) : ''; ?>" required>
                                    <label for="experience">Experience Required</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="excerpt" name="excerpt" 
                                        style="height: 100px" required><?php echo $editJob ? htmlspecialchars($editJob['excerpt']) : ''; ?></textarea>
                                    <label for="excerpt">Short Excerpt</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="description" name="description" 
                                        style="height: 100px" required><?php echo $editJob ? htmlspecialchars($editJob['description']) : ''; ?></textarea>
                                    <label for="description">Job Description</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="responsibilities" name="responsibilities" 
                                        style="height: 150px" required><?php echo $editJob ? htmlspecialchars(implode("\n", $editJob['responsibilities'])) : ''; ?></textarea>
                                    <label for="responsibilities">Responsibilities (one per line)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="requirements" name="requirements" 
                                        style="height: 150px" required><?php echo $editJob ? htmlspecialchars(implode("\n", $editJob['requirements'])) : ''; ?></textarea>
                                    <label for="requirements">Requirements (one per line)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="how_to_apply" name="how_to_apply" 
                                        style="height: 100px" required><?php echo $editJob ? htmlspecialchars(implode("\n", $editJob['how_to_apply'])) : ''; ?></textarea>
                                    <label for="how_to_apply">How to Apply (one per line)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn admin-contact-btn">Save Job Position</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show modal if editing job
        <?php if ($editJob): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = new bootstrap.Modal(document.getElementById('addJobModal'));
                modal.show();
            });
        <?php endif; ?>
    </script>
</body>

</html>
