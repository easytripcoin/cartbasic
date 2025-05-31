<?php
namespace CartBasic\Core\Ecommerce;

use function CartBasic\Config\redirectWithMessage;
use function CartBasic\Config\verifyCSRFToken;

require_once __DIR__ . '/order_functions.php'; // For updateOrderStatus

global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('admin-orders', 'danger', 'Invalid request method.');
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirectWithMessage('login', 'danger', 'Access denied.');
}
if (!verifyCSRFToken($_POST['csrf_token'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    redirectWithMessage('admin-order-detail?id=' . $orderId, 'danger', 'CSRF token validation failed.');
}

$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$newStatus = trim($_POST['order_status'] ?? '');

$possible_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'pending_cod_confirmation', 'paid_placeholder', 'paid', 'failed'];
if (!$orderId || empty($newStatus) || !in_array($newStatus, $possible_statuses)) {
    redirectWithMessage('admin-order-detail?id=' . $orderId, 'danger', 'Invalid data for status update.');
}

if (updateOrderStatus($pdo, $orderId, $newStatus)) {
    // Optionally, send an email notification to the customer about the status update here
    redirectWithMessage('admin-order-detail?id=' . $orderId, 'success', 'Order status updated successfully.');
} else {
    redirectWithMessage('admin-order-detail?id=' . $orderId, 'danger', 'Failed to update order status.');
}