<?php
use function CartBasic\Core\Ecommerce\getCartItems;
use function CartBasic\Config\displayMessage;
use function CartBasic\Config\generateCSRFToken;
use function CartBasic\Config\redirectWithMessage;

// IMPORTANT: Include the functions file before calling its functions
require_once PROJECT_ROOT_PATH . '/core/ecommerce/cart_functions.php';

global $pdo;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirectWithMessage('login', 'warning', 'Please log in to view your cart.');
}

$userId = $_SESSION['user_id'];
$cartItems = getCartItems($pdo, $userId);
$cartTotal = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart | Auth System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <h1 class="mb-4">Your Shopping Cart</h1>
        <?php echo displayMessage(); ?>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info">Your cart is empty. <a href="<?php echo SITE_URL; ?>/products">Continue
                    shopping</a>.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th></th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <?php $itemTotal = $item['price_at_addition'] * $item['quantity'];
                        $cartTotal += $itemTotal; ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars(SITE_URL . '/' . $item['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.png" alt="No image"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td><a
                                    href="<?php echo SITE_URL; ?>/product?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                            </td>
                            <td>$<?php echo htmlspecialchars(number_format($item['price_at_addition'], 2)); ?></td>
                            <td>
                                <form action="<?php echo SITE_URL; ?>/cart-update-action" method="post"
                                    class="d-inline-flex align-items-center">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1"
                                        class="form-control form-control-sm" style="width: 70px;" onchange="this.form.submit()">
                                </form>
                            </td>
                            <td>$<?php echo htmlspecialchars(number_format($itemTotal, 2)); ?></td>
                            <td>
                                <form action="<?php echo SITE_URL; ?>/cart-remove-action" method="post" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to remove this item?');">&times;</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong>$<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="text-end mt-3">
                <a href="<?php echo SITE_URL; ?>/products" class="btn btn-outline-secondary">Continue Shopping</a>
                <a href="<?php echo SITE_URL; ?>/checkout" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>