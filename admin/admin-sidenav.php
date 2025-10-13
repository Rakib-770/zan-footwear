<?php
// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="admin-sidenav">
    <div class="sidenav-header">
        <h3>Admin Panel</h3>
    </div>
    <ul class="sidenav-menu">
        <li class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-home-carousel.php' ? 'active' : ''; ?>">
            <a href="admin-home-carousel.php">
                <i class="fas fa-images me-2"></i>
                <span>Homepage Carousel</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-about.php' ? 'active' : ''; ?>">
            <a href="admin-about.php">
                <i class="fas fa-info-circle me-2"></i>
                <span>About Page</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-products.php' ? 'active' : ''; ?>">
            <a href="admin-products.php">
                <i class="fas fa-box me-2"></i>
                <span>Products</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-compliance.php' ? 'active' : ''; ?>">
            <a href="admin-compliance.php">
                <i class="fas fa-certificate me-2"></i>
                <span>Compliance</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-principle.php' ? 'active' : ''; ?>">
            <a href="admin-principle.php">
                <i class="fas fa-bullseye me-2"></i>
                <span>Principles & Values</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-operations.php' ? 'active' : ''; ?>">
            <a href="admin-operations.php">
                <i class="fas fa-cogs me-2"></i>
                <span>Operations</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-strength.php' ? 'active' : ''; ?>">
            <a href="admin-strength.php">
                <i class="fas fa-chart-line me-2"></i>
                <span>Strengths</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-facilities.php' ? 'active' : ''; ?>">
            <a href="admin-facilities.php">
                <i class="fas fa-industry me-2"></i>
                <span>Facilities</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-contact.php' ? 'active' : ''; ?>">
            <a href="admin-contact.php">
                <i class="fas fa-envelope me-2"></i>
                <span>Contact Information</span>
            </a>
        </li>
    </ul>
</div>