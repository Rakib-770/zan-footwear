<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Sample data (replace with actual database queries)
$stats = [
    'total_visitors' => 1245,
    'new_orders' => 24,
    'pending_tasks' => 5,
    'revenue' => 12345,
    'messages' => 8
];

$recent_activities = [
    ['id' => 1, 'action' => 'Login', 'user' => 'admin', 'date' => date('Y-m-d H:i'), 'status' => 'success'],
    ['id' => 2, 'action' => 'Updated About Page', 'user' => 'admin', 'date' => date('Y-m-d H:i', strtotime('-1 hour')), 'status' => 'success'],
    ['id' => 3, 'action' => 'Added New Team Member', 'user' => 'admin', 'date' => date('Y-m-d H:i', strtotime('-3 hours')), 'status' => 'success'],
    ['id' => 4, 'action' => 'Deleted Old Image', 'user' => 'admin', 'date' => date('Y-m-d H:i', strtotime('-1 day')), 'status' => 'warning']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin-style.css">
    <style>
        .stat-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .stat-card.visitors { border-color: #4e73df; }
        .stat-card.orders { border-color: #1cc88a; }
        .stat-card.tasks { border-color: #f6c23e; }
        .stat-card.revenue { border-color: #e74a3b; }
        .stat-card.messages { border-color: #36b9cc; }
        .quick-links .btn { transition: all 0.3s ease; }
        .quick-links .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .progress-thin {
            height: 6px;
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="page-title">Dashboard</h1>
                        <div class="text-muted">Last updated: <?php echo date('F j, Y, g:i a'); ?></div>
                    </div>
                    
                    <!-- Welcome Banner -->
                    <div class="admin-card mb-4 text-white" style="background-color: #6a0304;">
                        <div class="card-body py-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="mb-1">Welcome back, <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>!</h3>
                                    <p class="mb-0">Here's what's happening with your business today.</p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <i class="fas fa-chart-line fa-4x opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="stat-card visitors h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Visitors</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_visitors']); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="stat-card orders h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                New Orders</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['new_orders']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="stat-card tasks h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pending Tasks</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending_tasks']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="stat-card revenue h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Revenue</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($stats['revenue']); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="stat-card messages h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                New Messages</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['messages']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-2 col-md-4 mb-4">
                            <div class="stat-card h-100 py-2 bg-primary text-white">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                                System Status</div>
                                            <div class="h5 mb-0 font-weight-bold">All Systems OK</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-white-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="row mb-4 quick-links">
                        <div class="col-md-12">
                            <div class="admin-card">
                                <div class="card-body">
                                    <h5 class="card-title">Quick Actions</h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="admin-service.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i> Add New Service
                                        </a>
                                        <a href="admin-gallery.php" class="btn btn-success">
                                            <i class="fas fa-image me-2"></i> Upload Images
                                        </a>
                                        <a href="admin-team.php" class="btn btn-info">
                                            <i class="fas fa-user-plus me-2"></i> Add Team Member
                                        </a>
                                        <a href="admin-career.php" class="btn btn-warning">
                                            <i class="fas fa-briefcase me-2"></i> Post Job Opening
                                        </a>
                                        <a href="admin-contact.php" class="btn btn-danger">
                                            <i class="fas fa-envelope me-2"></i> Check Messages
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Recent Activity -->
                        <div class="col-lg-8 mb-4">
                            <div class="admin-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0">Recent Activity</h5>
                                        <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table admin-table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Action</th>
                                                    <th>User</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($recent_activities as $activity): ?>
                                                <tr>
                                                    <td><?php echo $activity['id']; ?></td>
                                                    <td><?php echo $activity['action']; ?></td>
                                                    <td><?php echo $activity['user']; ?></td>
                                                    <td><?php echo $activity['date']; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $activity['status'] == 'success' ? 'success' : 
                                                                 ($activity['status'] == 'warning' ? 'warning' : 'danger'); 
                                                        ?>">
                                                            <?php echo ucfirst($activity['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- System Overview -->
                        <div class="col-lg-4 mb-4">
                            <div class="admin-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">System Overview</h5>
                                    
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Storage Usage</span>
                                            <span>65%</span>
                                        </div>
                                        <div class="progress progress-thin">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 65%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Memory Usage</span>
                                            <span>42%</span>
                                        </div>
                                        <div class="progress progress-thin">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 42%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Database Size</span>
                                            <span>1.2GB</span>
                                        </div>
                                        <div class="progress progress-thin">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 75%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Bandwidth</span>
                                            <span>82%</span>
                                        </div>
                                        <div class="progress progress-thin">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: 82%"></div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-server fa-3x text-primary"></i>
                                        </div>
                                        <h5>Server Health</h5>
                                        <p class="text-success"><i class="fas fa-check-circle"></i> All systems operational</p>
                                        <small class="text-muted">Last checked: <?php echo date('g:i a'); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin-script.js"></script>
    <script>
        // Simple animation for stats cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>