<?php
require_once __DIR__ . '/bootstrap.php';

// Get current path without base URL
$currentPath = str_replace('/mailzila', '', $_SERVER['REQUEST_URI']);
$currentPath = strtok($currentPath, '?'); // Remove query string

// Initialize login state and user data
$isLoggedIn = isLoggedIn();
$currentUser = $isLoggedIn ? getCurrentUser() : null;

// If not logged in and not on an auth page, redirect to login
$authPaths = ['/auth/login', '/auth/register', '/auth/google', '/auth/github'];
if (!$isLoggedIn && !in_array($currentPath, $authPaths)) {
    header('Location: ' . APP_URL . '/auth/login');
    exit;
}

// If logged in and on an auth page, redirect to home
if ($isLoggedIn && in_array($currentPath, $authPaths)) {
    header('Location: ' . APP_URL);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
    <script>
        // Initialize dark mode if user has enabled it
        <?php if ($currentUser && isset($currentUser['dark_mode']) && $currentUser['dark_mode']): ?>
        document.documentElement.setAttribute('data-theme', 'dark');
        <?php endif; ?>

        // Add sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const desktopToggle = document.getElementById('sidebarToggle');
            const mobileToggle = document.getElementById('mobileSidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            const mainContent = document.querySelector('.main-content');
            const mainColumn = document.querySelector('.col-md-10');
            
            function toggleSidebar() {
                if (window.innerWidth <= 767.98) {
                    // Mobile behavior
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                } else {
                    // Desktop behavior
                    sidebar.classList.toggle('collapsed');
                    document.body.classList.toggle('nav-collapsed');
                    mainContent.classList.toggle('expanded');
                    mainColumn.classList.toggle('expanded');
                    
                    // Save state to localStorage
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
                }
            }
            
            // Initialize sidebar state from localStorage
            if (window.innerWidth > 767.98) {
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState === 'true') {
                    sidebar.classList.add('collapsed');
                    document.body.classList.add('nav-collapsed');
                    mainContent.classList.add('expanded');
                    mainColumn.classList.add('expanded');
                }
            }
            
            // Add click handlers to both toggle buttons
            desktopToggle.addEventListener('click', toggleSidebar);
            mobileToggle.addEventListener('click', toggleSidebar);
            
            // Handle mobile backdrop click
            if (backdrop) {
                backdrop.addEventListener('click', () => {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                });
            }
            
            // Handle window resize
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    backdrop?.classList.remove('show');
                    sidebar.classList.remove('show');
                    
                    // Restore desktop state from localStorage
                    const savedState = localStorage.getItem('sidebarCollapsed');
                    if (savedState === 'true') {
                        sidebar.classList.add('collapsed');
                        document.body.classList.add('nav-collapsed');
                        mainContent.classList.add('expanded');
                        mainColumn.classList.add('expanded');
                    } else {
                        sidebar.classList.remove('collapsed');
                        document.body.classList.remove('nav-collapsed');
                        mainContent.classList.remove('expanded');
                        mainColumn.classList.remove('expanded');
                    }
                } else {
                    // Reset classes for mobile
                    sidebar.classList.remove('collapsed');
                    document.body.classList.remove('nav-collapsed');
                    mainContent.classList.remove('expanded');
                    mainColumn.classList.remove('expanded');
                }
            });
        });
    </script>
</head>
<body>
    <?php if ($isLoggedIn): ?>
        <div class="header-actions">
            <!-- Notification button -->
            <a href="<?php echo APP_URL; ?>/pages/notifications" class="notification-btn">
                <i class="fas fa-bell"></i>
                <?php
                try {
                    // Get unread notification count
                    $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
                    $result = $db->select($sql, [$_SESSION['user_id']]);
                    $unreadCount = $result[0]['count'] ?? 0;
                    if ($unreadCount > 0):
                ?>
                    <span class="notification-badge"><?php echo $unreadCount; ?></span>
                <?php 
                    endif;
                } catch (Exception $e) {
                    // Silently fail for notifications - they're not critical
                    error_log("Notification error: " . $e->getMessage());
                }
                ?>
            </a>
            <!-- Toggle buttons -->
            <button id="mobileSidebarToggle" class="mobile-sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <button id="sidebarToggle" class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="sidebar-backdrop"></div>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2 sidebar">
                    <div class="logo-container">
                        <a href="<?php echo APP_URL; ?>" class="app-logo">
                            <i class="fas fa-envelope"></i>
                            <h4><?php echo htmlspecialchars(APP_NAME); ?></h4>
                        </a>
                    </div>
                    <ul class="nav flex-column nav-main">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentPath === '/' ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($currentPath, '/campaigns') === 0 ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/campaigns">
                                <i class="fas fa-bullhorn"></i> Campaigns
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($currentPath, '/subscribers') === 0 ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/subscribers">
                                <i class="fas fa-users"></i> Subscribers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo APP_URL; ?>/templates" class="nav-link <?php echo $currentPath === '/templates' ? 'active' : ''; ?>">
                                <i class="fas fa-file-alt"></i> Templates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo APP_URL; ?>/domains" class="nav-link <?php echo $currentPath === '/domains' ? 'active' : ''; ?>">
                                <i class="fas fa-globe"></i> Domains
                            </a>
                        </li>
                    </ul>
                    <div class="user-menu">
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/notifications">
                                        <i class="fas fa-bell"></i> Notifications
                                        <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $unreadCount; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/profile">
                                        <i class="fas fa-user"></i> Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/settings">
                                        <i class="fas fa-cog"></i> Settings
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/auth/logout">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-10 main-content">
    <?php endif; ?> 