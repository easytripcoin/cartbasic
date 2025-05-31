<?php
namespace CartBasic\Core\Ecommerce;

use function CartBasic\Config\redirectWithMessage;
use function CartBasic\Config\verifyCSRFToken;

require_once __DIR__ . '/cart_functions.php';
require_once __DIR__ . '/order_functions.php'; // For createOrder

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('checkout', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    redirectWithMessage('login', 'warning', 'Please log in to place an order.');
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('checkout', 'danger', 'CSRF token validation failed.');
}

$userId = (int) $_SESSION['user_id'];
$shippingAddress = trim($_POST['shipping_address'] ?? '');
$paymentMethod = trim($_POST['payment_method'] ?? '');

if (empty($shippingAddress) || empty($paymentMethod)) {
    redirectWithMessage('checkout', 'danger', 'Shipping address and payment method are required.');
}

$cartItems = getCartItems($pdo, $userId);

if (empty($cartItems)) {
    redirectWithMessage('products', 'info', 'Your cart is empty. Cannot place an order.');
}

$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['price_at_addition'] * $item['quantity'];
}

// Simulate payment status (in a real app, this would come after payment gateway interaction)
$paymentStatus = ($paymentMethod === 'cod') ? 'pending_cod_confirmation' : 'pending_payment';
if ($paymentMethod === 'placeholder_card') {
    // Simulate a successful placeholder payment
    $paymentStatus = 'paid_placeholder';
}

try {
    $orderId = createOrder($pdo, $userId, $totalAmount, $shippingAddress, $paymentMethod, $paymentStatus, $cartItems);

    if ($orderId) {
        clearUserCart($pdo, $userId); // Clear cart after successful order creation
        $_SESSION['last_order_id'] = $orderId;
        redirectWithMessage('order-confirmation', 'success', 'Your order has been placed successfully! Order ID: ' . $orderId);
    } else {
        // createOrder function itself logs specific PDO errors
        redirectWithMessage('checkout', 'danger', 'There was an issue placing your order. Please try again.');
    }
} catch (\Exception $e) { // Catches exceptions from createOrder (e.g., stock issues)
    redirectWithMessage('checkout', 'danger', 'Could not place order: ' . $e->getMessage());
}