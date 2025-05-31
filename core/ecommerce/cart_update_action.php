<?php
namespace CartBasic\Core\Ecommerce;

use function CartBasic\Config\redirectWithMessage;
use function CartBasic\Config\verifyCSRFToken;

use PDO; // Ensure PDO is in scope

require_once __DIR__ . '/cart_functions.php';

global $pdo; // Assuming $pdo is globally available

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('cart', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    redirectWithMessage('login', 'warning', 'Please log in to update your cart.');
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('cart', 'danger', 'CSRF token validation failed.');
}

$cartItemId = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);
$newQuantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$userId = $_SESSION['user_id'];

if (!$cartItemId || $newQuantity === false || $newQuantity < 1) {
    redirectWithMessage('cart', 'danger', 'Invalid data for cart update.');
}

try {
    // Fetch product_id and current cart quantity for stock check
    $stmtCheck = $pdo->prepare(
        "SELECT ci.product_id, p.stock_quantity 
         FROM cart_items ci 
         JOIN products p ON ci.product_id = p.id 
         JOIN cart c ON ci.cart_id = c.id
         WHERE ci.id = ? AND c.user_id = ?"
    );

    $stmtCheck->execute([$cartItemId, $userId]);
    $itemDetails = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$itemDetails) {
        redirectWithMessage('cart', 'danger', 'Cart item not found or does not belong to you.');
    }

    if ($newQuantity > $itemDetails['stock_quantity']) {
        redirectWithMessage('cart', 'warning', 'Requested quantity exceeds available stock. Cart not updated.');
    }

    if (updateCartItemQuantity($pdo, $cartItemId, $newQuantity, $userId)) {
        redirectWithMessage('cart', 'success', 'Cart updated successfully.');
    } else {
        redirectWithMessage('cart', 'danger', 'Could not update cart item.');
    }
} catch (\PDOException $e) {
    error_log("Cart update error: " . $e->getMessage(), 3, LOGS_PATH . 'cart_errors.log');
    redirectWithMessage('cart', 'danger', 'A database error occurred while updating your cart.');
}