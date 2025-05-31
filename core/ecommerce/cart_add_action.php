<?php
namespace CartBasic\Core\Ecommerce;

use function CartBasic\Config\redirectWithMessage;
use function CartBasic\Config\verifyCSRFToken;

require_once __DIR__ . '/product_functions.php'; // For getProductById
require_once __DIR__ . '/cart_functions.php';   // For getOrCreateUserCart, addItemToCart

global $pdo; // Assuming $pdo is globally available

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('products', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    redirectWithMessage('login', 'warning', 'Please log in to add items to your cart.');
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('products', 'danger', 'CSRF token validation failed.');
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$productId || !$quantity || $quantity < 1) {
    redirectWithMessage('products', 'danger', 'Invalid product data.');
}

$product = getProductById($pdo, $productId);

if (!$product || $product['stock_quantity'] < $quantity) {
    redirectWithMessage('product?id=' . $productId, 'danger', 'Product not available or insufficient stock.');
}

$userId = $_SESSION['user_id'];
$cartId = getOrCreateUserCart($pdo, $userId);

if (!$cartId) {
    redirectWithMessage('products', 'danger', 'Could not access your cart. Please try again.');
}

if (addItemToCart($pdo, $cartId, $productId, $quantity, (float) $product['price'])) {
    redirectWithMessage('cart', 'success', htmlspecialchars($product['name']) . ' added to your cart!');
} else {
    redirectWithMessage('product?id=' . $productId, 'danger', 'Could not add item to cart.');
}