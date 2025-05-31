<?php
namespace CartBasic\Core\Ecommerce;

use function CartBasic\Config\redirectWithMessage;
use function CartBasic\Config\verifyCSRFToken;
use function CartBasic\Config\sanitizeInput;

require_once __DIR__ . '/product_functions.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('admin-products', 'danger', 'Invalid request method.');
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}
if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('admin-add-product', 'danger', 'CSRF token validation failed.');
}

$name = sanitizeInput($_POST['name'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
$stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);
$image_url = null; // Initialize

// Basic Validation
if (empty($name) || $price === false || $price < 0 || $stock_quantity === false || $stock_quantity < 0) {
    redirectWithMessage('admin-add-product', 'danger', 'Name, valid price, and valid stock are required.');
}

// Image Upload Handling (Basic)
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
    $target_dir = PROJECT_ROOT_PATH . "/assets/images/products/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0775, true);
    }
    $imageFileType = strtolower(pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION));
    $safe_filename = uniqid('prod_', true) . '.' . $imageFileType; // Create a unique, safe filename
    $target_file = $target_dir . $safe_filename;
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image_file"]["tmp_name"]);
    if ($check === false) {
        redirectWithMessage('admin-add-product', 'danger', 'File is not an image.');
    }
    // Check file size (e.g., 2MB max)
    if ($_FILES["image_file"]["size"] > 2000000) {
        redirectWithMessage('admin-add-product', 'danger', 'Sorry, your file is too large.');
    }
    // Allow certain file formats
    if (!in_array($imageFileType, $allowed_types)) {
        redirectWithMessage('admin-add-product', 'danger', 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
    }

    if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
        $image_url = "assets/images/products/" . $safe_filename; // Store relative path
    } else {
        redirectWithMessage('admin-add-product', 'danger', 'Sorry, there was an error uploading your file.');
    }
}


if (addProduct($pdo, $name, $description, (float) $price, (int) $stock_quantity, $image_url)) {
    redirectWithMessage('admin-products', 'success', 'Product added successfully.');
} else {
    // The addProduct function should log specific SQL errors.
    redirectWithMessage('admin-add-product', 'danger', 'Failed to add product. Please check logs.');
}