<?php
namespace CartBasic\Core\Ecommerce;

use PDO;
use PDOException;

function getAllOrders(PDO $pdo)
{
    $stmt = $pdo->query("
        SELECT o.*, u.username as customer_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderDetails(PDO $pdo, int $orderId)
{
    $orderStmt = $pdo->prepare("
        SELECT o.*, u.username as customer_name, u.email as customer_email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order)
        return null;

    $itemsStmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    return $order;
}

function updateOrderStatus(PDO $pdo, int $orderId, string $newStatus): bool
{
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$newStatus, $orderId]);
}

/**
 * Creates a new order in the database.
 *
 * @param PDO $pdo
 * @param int $userId
 * @param float $totalAmount
 * @param string $shippingAddress
 * @param string $paymentMethod
 * @param string $paymentStatus
 * @param array $cartItems
 * @return int|false The new order ID on success, false on failure.
 * @throws \Exception If stock is insufficient.
 */
function createOrder(PDO $pdo, int $userId, float $totalAmount, string $shippingAddress, string $paymentMethod, string $paymentStatus, array $cartItems): ?int
{
    $pdo->beginTransaction();
    try {
        // Verify stock for all items before proceeding
        foreach ($cartItems as $item) {
            $stmtStock = $pdo->prepare("SELECT name, stock_quantity FROM products WHERE id = ? FOR UPDATE"); // Lock row for update
            $stmtStock->execute([$item['product_id']]);
            $product = $stmtStock->fetch(PDO::FETCH_ASSOC);

            if (!$product || $item['quantity'] > $product['stock_quantity']) {
                // Not enough stock for this item
                $pdo->rollBack(); // Rollback before throwing
                throw new \Exception("Insufficient stock for product: " . htmlspecialchars($product ? $product['name'] : 'Unknown Product ID ' . $item['product_id']) . ". Requested: {$item['quantity']}, Available: " . ($product ? $product['stock_quantity'] : 'N/A'));
            }
        }

        // Create order
        $stmtOrder = $pdo->prepare(
            "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, payment_status, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmtOrder->execute([$userId, $totalAmount, $shippingAddress, $paymentMethod, $paymentStatus]);
        $orderId = (int) $pdo->lastInsertId();

        // Add order items and update stock
        $stmtOrderItem = $pdo->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity, price_per_unit, created_at, updated_at) 
             VALUES (?, ?, ?, ?, NOW(), NOW())"
        );
        $stmtUpdateStock = $pdo->prepare(
            "UPDATE products SET stock_quantity = stock_quantity - ?, updated_at = NOW() WHERE id = ?"
        );

        foreach ($cartItems as $item) {
            $stmtOrderItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['price_at_addition']]);
            $stmtUpdateStock->execute([$item['quantity'], $item['product_id']]);
        }

        $pdo->commit();
        return $orderId;

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Order creation DB error: " . $e->getMessage(), 3, LOGS_PATH . 'order_errors.log');
        return null; // Indicate failure
    } catch (\Exception $e) { // Catch stock exception
        // Transaction already rolled back if stock issue
        error_log("Order creation failed: " . $e->getMessage(), 3, LOGS_PATH . 'order_errors.log');
        throw $e; // Re-throw to be caught by the caller
    }
}

/**
 * Formats the status text for display
 * @param string|null $status
 * @return string
 */
function formatStatusText($status)
{
    if ($status === null)
        return 'N/A'; // Handles NULL
    $status = str_replace('_', ' ', $status);
    $status = ucwords(strtolower($status));
    $status = str_replace('Cod', 'COD', $status);
    $status = str_replace('Paid Placeholder', 'Paid (Test)', $status);
    return htmlspecialchars($status); // htmlspecialchars is good here
}

/**
 * Helper function to get the CSS class for a status badge
 * @param string|null $status
 * @return string
 */
function getStatusBadgeClass($status)
{
    if ($status === null)
        return 'secondary'; // Default for null status
    switch (strtolower($status)) { // Use strtolower for case-insensitive matching
        case 'pending':
        case 'pending_payment':
        case 'pending_cod_confirmation':
            return 'warning text-dark'; // text-dark for better readability on yellow
        case 'processing':
            return 'info text-dark';    // text-dark for better readability on light blue
        case 'shipped':
            return 'primary';
        case 'paid_placeholder':
        case 'paid':
            return 'success'; // Consistent success indication for paid statuses
        case 'delivered':
            return 'success';
        case 'cancelled':
        case 'failed':
            return 'danger';
        default:
            return 'secondary'; // Fallback for unknown statuses
    }
}