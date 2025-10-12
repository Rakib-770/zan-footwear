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
                <span>Manage Carousel</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-about.php' ? 'active' : ''; ?>">
            <a href="admin-about.php">
                <i class="fas fa-info-circle me-2"></i>
                <span>About Us</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-service.php' ? 'active' : ''; ?>">
            <a href="admin-service.php">
                <i class="fas fa-concierge-bell me-2"></i>
                <span>Manage Services</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-gallery.php' ? 'active' : ''; ?>">
            <a href="admin-gallery.php">
                <i class="fas fa-photo-video me-2"></i>
                <span>Gallery Items</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-portfolio.php' ? 'active' : ''; ?>">
            <a href="admin-portfolio.php">
                <i class="fas fa-briefcase me-2"></i>
                <span>Company Portfolio</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-team.php' ? 'active' : ''; ?>">
            <a href="admin-team.php">
                <i class="fas fa-users me-2"></i>
                <span>Management and Team</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-career.php' ? 'active' : ''; ?>">
            <a href="admin-career.php">
                <i class="fas fa-user-tie me-2"></i>
                <span>Career and Openings</span>
            </a>
        </li>
        <li class="menu-item <?php echo $current_page == 'admin-contact.php' ? 'active' : ''; ?>">
            <a href="admin-contact.php">
                <i class="fas fa-envelope me-2"></i>
                <span>Contact Us</span>
            </a>
        </li>
    </ul>
</div>