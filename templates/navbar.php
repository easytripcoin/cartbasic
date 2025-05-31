<?php
use function CartBasic\Config\generateCSRFToken;

// $currentPage variable is expected to be set by the main index.php (front controller)
global $currentPage; // Make it accessible if not passed directly

// Ensure SITE_URL and CSRF function are available
if (!defined('SITE_URL')) {
    define('SITE_URL', ''); // Fallback
}
// CSRF token function should be loaded via config/functions.php, included by index.php
// use function AuthBasic\Config\generateCSRFToken; // If you prefer explicit use
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>/home">Auth System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'home') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'about') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'products') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/products">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'contact') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/contact">Contact</a>
                </li>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/dashboard">Dashboard</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'cart') ? 'active' : ''; ?>"
                        href="<?php echo SITE_URL; ?>/cart"><i class="bi bi-cart"></i> Cart</a>
                </li>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['profile', 'change-password']) ? 'active' : ''; ?>"
                            href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item <?php echo ($currentPage === 'profile') ? 'active' : ''; ?>"
                                    href="<?php echo SITE_URL; ?>/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item <?php echo ($currentPage === 'change-password') ? 'active' : ''; ?>"
                                    href="<?php echo SITE_URL; ?>/change-password"><i class="bi bi-lock me-2"></i>Change
                                    Password</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-products') ? 'active' : ''; ?>"
                                        href="<?php echo SITE_URL; ?>/admin-products"><i class="bi bi-box-seam me-2"></i>Manage
                                        Products</a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage === 'admin-orders') ? 'active' : ''; ?>"
                                        href="<?php echo SITE_URL; ?>/admin-orders"><i class="bi bi-card-list me-2"></i>Manage
                                        Orders</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            <?php endif; ?>
                            <li>
                                <form action="<?php echo SITE_URL; ?>/logout-action" method="post" class="d-inline"
                                    id="logoutForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'login') ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/login"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'register') ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/register"><i class="bi bi-person-plus me-1"></i>Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>