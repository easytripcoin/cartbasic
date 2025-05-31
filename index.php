<?php
// This is the main entry point for the application.
// It handles routing and includes the necessary page content or action script.

// Ensure config is loaded first.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

// Include core initialization scripts (like remember_me handler)
// Note: Ensure $pdo is available globally or passed if these become functions/classes.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'auth' . DIRECTORY_SEPARATOR . 'remember_me.php';
// You can add other core initializations here, e.g.:
// require_once __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'session_setup.php';

// --- Routing Logic ---
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '';
// $subdirectory is defined in config.php
if (!empty($subdirectory) && strpos($requestUri, $subdirectory) === 0) {
    $basePath = $subdirectory;
}
if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestPath = strtok($requestUri, '?');
$requestPath = trim($requestPath, '/');

$currentPage = ''; // For navbar active state

// Define available pages (for GET requests to display content)
$availablePages = [
    '' => 'home.php',
    'home' => 'home.php',
    'about' => 'about.php',
    'contact' => 'contact.php',
    'login' => 'login.php',
    'register' => 'register.php',
    'dashboard' => 'dashboard.php',
    'profile' => 'profile.php',
    'privacy' => 'privacy.php',
    'terms' => 'terms.php',
    'change-password' => 'change_password.php',
    'forgot-password' => 'forgot_password.php',
    'reset-password' => 'reset_password.php',
    'verify-email' => 'verify_email.php',
    'products' => 'products.php',
    'product' => 'product.php', // Will handle ?id=X
    'cart' => 'cart.php',
    'checkout' => 'checkout.php',
    'order-confirmation' => 'order_confirmation.php',
    'admin-products' => 'admin_products.php', // For admin
    'admin-add-product' => 'admin_add_product.php',
    'admin-edit-product' => 'admin_edit_product.php',
    'admin-orders' => 'admin_orders.php',
    'admin-order-detail' => 'admin_order_detail.php',     // For admin
];

// Define available actions (typically for POST requests from forms)
// Paths are now relative to the `core/` directory.
$availableActions = [
    'login-action' => 'auth/login.php',
    'register-action' => 'auth/register.php',
    'contact-action' => 'contact/submit.php', // Updated path
    'forgot-password-action' => 'auth/forgot-password.php',
    'reset-password-action' => 'auth/reset-password.php',
    'change-password-action' => 'auth/change-password.php',
    'update-profile-action' => 'auth/update-profile.php',
    'logout-action' => 'auth/logout.php',
    'cart-add-action' => 'ecommerce/cart_add_action.php',
    'cart-update-action' => 'ecommerce/cart_update_action.php',
    'cart-remove-action' => 'ecommerce/cart_remove_action.php',
    'order-place-action' => 'ecommerce/order_place_action.php',
    'admin-product-add-action' => 'ecommerce/admin_product_add_action.php',
    'admin-product-edit-action' => 'ecommerce/admin_product_edit_action.php',
    'admin-product-delete-action' => 'ecommerce/admin_product_delete_action.php',
    'admin-order-update-status-action' => 'ecommerce/admin_order_update_status_action.php',
];

$scriptToInclude = null;

// Check if it's an action request
if (array_key_exists($requestPath, $availableActions)) {
    $actionFileRelativePath = $availableActions[$requestPath];
    // Construct path to core directory + action script path
    $filePath = PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . $actionFileRelativePath;
    if (file_exists($filePath)) {
        $scriptToInclude = $filePath;
    }
}
// If not an action, check if it's a page request
elseif (array_key_exists($requestPath, $availablePages)) {
    $pageFileName = $availablePages[$requestPath];
    $filePath = PAGES_PATH . DIRECTORY_SEPARATOR . $pageFileName;
    if (file_exists($filePath)) {
        $scriptToInclude = $filePath;
        $currentPage = $requestPath === '' ? 'home' : $requestPath;
    }
}

// --- Include Script (Page or Action) ---
if ($scriptToInclude) {
    require $scriptToInclude;
} else {
    // Page or Action not found - Handle 404
    http_response_code(404);
    $currentPage = '404';
    $notFoundPagePath = PAGES_PATH . DIRECTORY_SEPARATOR . '404.php';
    if (file_exists($notFoundPagePath)) {
        require $notFoundPagePath;
    } else {
        echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>404 Not Found</title></head><body>";
        echo "<h1>404 Not Found</h1><p>The page or action you requested could not be found.</p>";
        echo "<p><a href='" . SITE_URL . "/home'>Go to Homepage</a></p>";
        echo "</body></html>";
    }
}
// --- End Routing Logic ---