<?php
namespace CartBasic\Core\Ecommerce;

use function CartBasic\Config\redirectWithMessage;
use function CartBasic\Config\verifyCSRFToken;

require_once __DIR__ . '/cart_functions.php';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('cart', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirectWithMessage('login', 'warning', 'Please log in.');
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('cart', 'danger', 'CSRF token validation failed.');
}

$cartItemId = filter_input(INPUT_POST, 'cart_item_id', FILTER_VALIDATE_INT);

if (!$cartItemId) {
    redirectWithMessage('cart', 'danger', 'Invalid item ID.');
}

if (removeCartItem($pdo, $cartItemId, $_SESSION['user_id'])) {
    redirectWithMessage('cart', 'success', 'Item removed from cart.');
} else {
    redirectWithMessage('cart', 'danger', 'Could not remove item from cart.');
}