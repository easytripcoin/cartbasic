<?php
use function CartBasic\Core\Ecommerce\getCartItems;
use function CartBasic\Config\displayMessage;
use function CartBasic\Config\generateCSRFToken;
use function CartBasic\Config\redirectWithMessage;

// IMPORTANT: Include the functions file before calling its functions
require_once PROJECT_ROOT_PATH . '/core/ecommerce/cart_functions.php';

global $pdo;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    redirectWithMessage('login', 'warning', 'Please log in to proceed to checkout.');
}

$userId = $_SESSION['user_id'];
$cartItems = getCartItems($pdo, $userId);

if (empty($cartItems)) {
    redirectWithMessage('products', 'info', 'Your cart is empty. Please add products before checkout.');
}

$cartTotal = 0;
foreach ($cartItems as $item) {
    $cartTotal += $item['price_at_addition'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout | Auth System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
    <main class="container py-5">
        <h1 class="mb-4">Checkout</h1>
        <?php echo displayMessage(); ?>

        <div class="row">
            <div class="col-md-7">
                <h4>Order Summary</h4>
                <table class="table table-sm">
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></td>
                            <td class="text-end">
                                $<?php echo htmlspecialchars(number_format($item['price_at_addition'] * $item['quantity'], 2)); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="fw-bold">
                        <td>Total:</td>
                        <td class="text-end">$<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></td>
                    </tr>
                </table>
                <hr>
                <h4>Shipping & Payment</h4>
                <form action="<?php echo SITE_URL; ?>/order-place-action" method="post" class="needs-validation"
                    novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Shipping Address</label>
                        <textarea name="shipping_address" id="shipping_address" class="form-control" rows="3"
                            required></textarea>
                        <div class="invalid-feedback">Please enter your shipping address.</div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="">Select Payment Method</option>
                            <option value="cod">Cash on Delivery (COD)</option>
                            <option value="placeholder_card">Credit/Debit Card (Placeholder)</option>
                        </select>
                        <div class="invalid-feedback">Please select a payment method.</div>
                    </div>
                    <div id="card_details_placeholder" style="display:none;" class="mb-3 border p-3 rounded">
                        <p class="text-muted">This is a placeholder for card payment fields. No actual payment will be
                            processed.</p>
                        <div class="mb-2"><label class="form-label">Card Number:</label><input type="text"
                                class="form-control" placeholder="1234-5678-9012-3456"></div>
                        <div class="row">
                            <div class="col-md-6 mb-2"><label class="form-label">Expiry (MM/YY):</label><input
                                    type="text" class="form-control" placeholder="MM/YY"></div>
                            <div class="col-md-6 mb-2"><label class="form-label">CVV:</label><input type="text"
                                    class="form-control" placeholder="123"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
                </form>
            </div>
            <div class="col-md-5">
                <h4>Your Details</h4>
                <p>Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            </div>
        </div>
    </main>
    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Basic form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
        // Show/hide card details placeholder
        const paymentMethodSelect = document.getElementById('payment_method');
        const cardDetailsPlaceholder = document.getElementById('card_details_placeholder');
        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function () {
                if (this.value === 'placeholder_card') {
                    cardDetailsPlaceholder.style.display = 'block';
                } else {
                    cardDetailsPlaceholder.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>