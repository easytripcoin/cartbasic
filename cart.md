Okay, this is an exciting project! Extending your authentication system to a full-fledged e-commerce platform involves several key steps. We'll break this down into manageable phases, focusing on the backend logic and database structure first, and then outlining the necessary page creations and modifications.

Here's a walkthrough of the steps:

---

## Phase 1: Database Schema Expansion

First, we need to update your database to support e-commerce functionalities. This involves adding tables for products, shopping carts, orders, and the items within them.

### 1. Modify `database.sql`

We'll add new tables: `products`, `cart`, `cart_items`, `orders`, and `order_items`.

```sql
-- Drop existing tables if they exist (optional, for a clean start during development)
-- Drop existing tables if they exist (optional, for a clean start during development)
DROP TABLE IF EXISTS order_items;

DROP TABLE IF EXISTS orders;

DROP TABLE IF EXISTS cart_items;

DROP TABLE IF EXISTS cart;

DROP TABLE IF EXISTS products;

DROP TABLE IF EXISTS contact_submissions;

DROP TABLE IF EXISTS users;

-- Create the users table (from your existing schema)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(64) DEFAULT NULL,
    verification_token_expires DATETIME DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    remember_token VARCHAR(64) DEFAULT NULL,
    remember_token_expires DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the contact_submissions table (from your existing schema)
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- NEW E-COMMERCE TABLES --

-- Create the products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the cart table (associates a cart with a user)
-- For guest carts, user_id could be NULL and a session_id could be used instead.
-- For simplicity, we'll start with user-specific carts.
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the cart_items table
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price_at_addition DECIMAL(10, 2) NOT NULL, -- Price when item was added to cart
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY (cart_id, product_id) -- Ensures a product appears only once per cart, update quantity instead
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_status VARCHAR(50) DEFAULT 'pending', -- e.g., pending, processing, shipped, delivered, cancelled
    shipping_address TEXT,
    billing_address TEXT,
    payment_method VARCHAR(50),
    payment_status VARCHAR(50) DEFAULT 'pending', -- e.g., pending, paid, failed
    transaction_id VARCHAR(255) NULL, -- For payment gateway reference
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL -- Keep order even if user is deleted
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Create the order_items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10, 2) NOT NULL, -- Price at the time of order
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL -- Keep item even if product is deleted for history
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
```
**Action:** Replace the content of your `cartbasic/database.sql` file with the SQL above. Then, re-import this into your MySQL database to set up the new structure.

---

## Phase 2: Core Logic and Page Creation (PHP)

Now we'll outline the new PHP files and modifications.

### 1. Directory Structure:
Create a new directory for e-commerce core logic:
* `cartbasic/core/ecommerce/`

### 2. Product Display

* **`cartbasic/core/ecommerce/product_functions.php`:**
    ```php
    <?php
    namespace AuthBasic\Core\Ecommerce;
    use PDO;

    // Function to get all products
    function getAllProducts(PDO $pdo) {
        $stmt = $pdo->query("SELECT id, name, description, price, image_url, stock_quantity FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Function to get a single product by ID
    function getProductById(PDO $pdo, int $productId) {
        $stmt = $pdo->prepare("SELECT id, name, description, price, image_url, stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    ```

* **`cartbasic/pages/products.php`:** (Product Listing Page)
    ```php
    <?php
    namespace AuthBasic\Pages;
    use function AuthBasic\Core\Ecommerce\getAllProducts;
    use function AuthBasic\Config\displayMessage;

    global $pdo; // Assuming $pdo is globally available from config.php
    $products = getAllProducts($pdo);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Products | Auth System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    </head>
    <body>
        <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
        <main class="container py-5">
            <h1 class="mb-4">Our Products</h1>
            <?php echo displayMessage(); ?>
            <div class="row">
                <?php if (empty($products)): ?>
                    <p>No products available at the moment.</p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars(SITE_URL . '/' . $product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.png" class="card-img-top" alt="No image available" style="max-height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($product['description'], 0, 100))) . (strlen($product['description']) > 100 ? '...' : ''); ?></p>
                                    <p class="card-text"><strong>Price: $<?php echo htmlspecialchars($product['price']); ?></strong></p>
                                    <p class="card-text"><small>Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?></small></p>
                                    <div class="mt-auto">
                                        <a href="<?php echo SITE_URL; ?>/product?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-secondary me-2">View Details</a>
                                        <form action="<?php echo SITE_URL; ?>/cart-add-action" method="post" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo \AuthBasic\Config\generateCSRFToken(); ?>">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-sm btn-primary">Add to Cart</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
        <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    ```
    *(Note: You might need to create an `assets/images/placeholder.png` image or adjust image paths.)*

* **`cartbasic/pages/product.php`:** (Single Product Detail Page - simplified, assumes ID via GET)
    ```php
    <?php
    namespace AuthBasic\Pages;
    use function AuthBasic\Core\Ecommerce\getProductById;
    use function AuthBasic\Config\displayMessage;

    global $pdo;
    $productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $product = null;
    if ($productId) {
        $product = getProductById($pdo, $productId);
    }

    if (!$product) {
        \AuthBasic\Config\redirectWithMessage('products', 'danger', 'Product not found.');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($product['name']); ?> | Auth System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    </head>
    <body>
        <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
        <main class="container py-5">
            <?php echo displayMessage(); ?>
            <div class="row">
                <div class="col-md-6">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars(SITE_URL . '/' . $product['image_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.png" class="img-fluid rounded" alt="No image available">
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <p class="h4">Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                    <p><small>Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?></small></p>
                    <hr>
                    <form action="<?php echo SITE_URL; ?>/cart-add-action" method="post" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?php echo \AuthBasic\Config\generateCSRFToken(); ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Quantity:</label>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" class="form-control" required>
                        </div>
                        <div class="col-12">
                             <?php if ($product['stock_quantity'] > 0): ?>
                                <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </form>
                     <div class="mt-3">
                        <a href="<?php echo SITE_URL; ?>/products" class="btn btn-outline-secondary">Back to Products</a>
                    </div>
                </div>
            </div>
        </main>
        <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    ```

### 3. Shopping Cart Logic

* **`cartbasic/core/ecommerce/cart_functions.php`:**
    ```php
    <?php
    namespace AuthBasic\Core\Ecommerce;
    use PDO;

    // Get or create a cart for the logged-in user
    function getOrCreateUserCart(PDO $pdo, int $userId): ?int {
        // Check if user has an active cart
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart) {
            return (int)$cart['id'];
        } else {
            // Create a new cart
            $stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
            if ($stmt->execute([$userId])) {
                return (int)$pdo->lastInsertId();
            }
        }
        return null;
    }

    // Add item to cart or update quantity if it already exists
    function addItemToCart(PDO $pdo, int $cartId, int $productId, int $quantity, float $priceAtAddition): bool {
        // Check if item already in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cartId, $productId]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, price_at_addition = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$newQuantity, $priceAtAddition, $cartItem['id']]);
        } else {
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price_at_addition) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$cartId, $productId, $quantity, $priceAtAddition]);
        }
    }

    // Get cart items for a user
    function getCartItems(PDO $pdo, int $userId): array {
        $stmt = $pdo->prepare("
            SELECT ci.id as cart_item_id, p.id as product_id, p.name, p.image_url, ci.quantity, ci.price_at_addition
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            JOIN cart c ON ci.cart_id = c.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update cart item quantity
    function updateCartItemQuantity(PDO $pdo, int $cartItemId, int $newQuantity, int $userId): bool {
         // Ensure the cart item belongs to the user for security
        $stmt = $pdo->prepare("
            UPDATE cart_items ci
            JOIN cart c ON ci.cart_id = c.id
            SET ci.quantity = ?, ci.updated_at = NOW()
            WHERE ci.id = ? AND c.user_id = ?
        ");
        return $stmt->execute([$newQuantity, $cartItemId, $userId]);
    }

    // Remove item from cart
    function removeCartItem(PDO $pdo, int $cartItemId, int $userId): bool {
        // Ensure the cart item belongs to the user for security
        $stmt = $pdo->prepare("
            DELETE ci FROM cart_items ci
            JOIN cart c ON ci.cart_id = c.id
            WHERE ci.id = ? AND c.user_id = ?
        ");
        return $stmt->execute([$cartItemId, $userId]);
    }

    // Clear user's cart (e.g., after order placement)
    function clearUserCart(PDO $pdo, int $userId): bool {
        $cartId = getOrCreateUserCart($pdo, $userId);
        if ($cartId) {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            return $stmt->execute([$cartId]);
            // Optionally, you might delete the cart itself if it's empty and old,
            // or keep it for cart recovery features.
        }
        return false;
    }
    ?>
    ```

* **`cartbasic/core/ecommerce/cart_add_action.php`:**
    ```php
    <?php
    namespace AuthBasic\Core\Ecommerce;

    use function AuthBasic\Config\redirectWithMessage;
    use function AuthBasic\Config\verifyCSRFToken;
    use PDO; // Make sure PDO is in scope, or pass $pdo if global

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
        redirectWithMessage('product?id='.$productId, 'danger', 'Product not available or insufficient stock.');
    }

    $userId = $_SESSION['user_id'];
    $cartId = getOrCreateUserCart($pdo, $userId);

    if (!$cartId) {
        redirectWithMessage('products', 'danger', 'Could not access your cart. Please try again.');
    }

    if (addItemToCart($pdo, $cartId, $productId, $quantity, (float)$product['price'])) {
        redirectWithMessage('cart', 'success', htmlspecialchars($product['name']) . ' added to your cart!');
    } else {
        redirectWithMessage('product?id='.$productId, 'danger', 'Could not add item to cart.');
    }
    ?>
    ```

* **`cartbasic/pages/cart.php`:** (Display Cart)
    ```php
    <?php
    namespace AuthBasic\Pages;
    use function AuthBasic\Core\Ecommerce\getCartItems;
    use function AuthBasic\Config\displayMessage;
    use function AuthBasic\Config\generateCSRFToken;

    global $pdo;

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        \AuthBasic\Config\redirectWithMessage('login', 'warning', 'Please log in to view your cart.');
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
                <div class="alert alert-info">Your cart is empty. <a href="<?php echo SITE_URL; ?>/products">Continue shopping</a>.</div>
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
                            <?php $itemTotal = $item['price_at_addition'] * $item['quantity']; $cartTotal += $itemTotal; ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars(SITE_URL . '/' . $item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="<?php echo SITE_URL; ?>/assets/images/placeholder.png" alt="No image" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                </td>
                                <td><a href="<?php echo SITE_URL; ?>/product?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></td>
                                <td>$<?php echo htmlspecialchars(number_format($item['price_at_addition'], 2)); ?></td>
                                <td>
                                    <form action="<?php echo SITE_URL; ?>/cart-update-action" method="post" class="d-inline-flex align-items-center">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control form-control-sm" style="width: 70px;" onchange="this.form.submit()">
                                        </form>
                                </td>
                                <td>$<?php echo htmlspecialchars(number_format($itemTotal, 2)); ?></td>
                                <td>
                                    <form action="<?php echo SITE_URL; ?>/cart-remove-action" method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this item?');">&times;</button>
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
    ```

* **`cartbasic/core/ecommerce/cart_update_action.php`:**
    ```php
    <?php
    namespace AuthBasic\Core\Ecommerce;

    use function AuthBasic\Config\redirectWithMessage;
    use function AuthBasic\Config\verifyCSRFToken;

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
    $newQuantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if (!$cartItemId || $newQuantity === false || $newQuantity < 1) { // Allow 0 for removal, or handle separately
        redirectWithMessage('cart', 'danger', 'Invalid data for cart update.');
    }

    // Fetch product stock to ensure we don't exceed it
    $stmtStock = $pdo->prepare("SELECT p.stock_quantity FROM products p JOIN cart_items ci ON p.id = ci.product_id WHERE ci.id = ?");
    $stmtStock->execute([$cartItemId]);
    $productStock = $stmtStock->fetchColumn();

    if ($productStock === false || $newQuantity > $productStock) {
         redirectWithMessage('cart', 'danger', 'Requested quantity exceeds available stock.');
    }


    if (updateCartItemQuantity($pdo, $cartItemId, $newQuantity, $_SESSION['user_id'])) {
        redirectWithMessage('cart', 'success', 'Cart updated.');
    } else {
        redirectWithMessage('cart', 'danger', 'Could not update cart.');
    }
    ?>
    ```

* **`cartbasic/core/ecommerce/cart_remove_action.php`:**
    ```php
    <?php
    namespace AuthBasic\Core\Ecommerce;
    use function AuthBasic\Config\redirectWithMessage;
    use function AuthBasic\Config\verifyCSRFToken;

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
    ?>
    ```

### 4. Checkout and Order Placement

* **`cartbasic/pages/checkout.php`:** (Simplified, no real payment)
    ```php
    <?php
    namespace AuthBasic\Pages;
    use function AuthBasic\Core\Ecommerce\getCartItems;
    use function AuthBasic\Config\displayMessage;
    use function AuthBasic\Config\generateCSRFToken;

    global $pdo;

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        \AuthBasic\Config\redirectWithMessage('login', 'warning', 'Please log in to proceed to checkout.');
    }

    $userId = $_SESSION['user_id'];
    $cartItems = getCartItems($pdo, $userId);

    if (empty($cartItems)) {
        \AuthBasic\Config\redirectWithMessage('products', 'info', 'Your cart is empty. Please add products before checkout.');
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
                            <td class="text-end">$<?php echo htmlspecialchars(number_format($item['price_at_addition'] * $item['quantity'], 2)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="fw-bold">
                            <td>Total:</td>
                            <td class="text-end">$<?php echo htmlspecialchars(number_format($cartTotal, 2)); ?></td>
                        </tr>
                    </table>
                    <hr>
                    <h4>Shipping & Payment</h4>
                    <form action="<?php echo SITE_URL; ?>/order-place-action" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Shipping Address</label>
                            <textarea name="shipping_address" id="shipping_address" class="form-control" rows="3" required></textarea>
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
                            <p class="text-muted">This is a placeholder for card payment fields. No actual payment will be processed.</p>
                            <div class="mb-2"><label class="form-label">Card Number:</label><input type="text" class="form-control" placeholder="1234-5678-9012-3456"></div>
                            <div class="row">
                                <div class="col-md-6 mb-2"><label class="form-label">Expiry (MM/YY):</label><input type="text" class="form-control" placeholder="MM/YY"></div>
                                <div class="col-md-6 mb-2"><label class="form-label">CVV:</label><input type="text" class="form-control" placeholder="123"></div>
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
                paymentMethodSelect.addEventListener('change', function() {
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
    ```

* **`cartbasic/core/ecommerce/order_place_action.php`:**
    ```php
    <?php
    namespace AuthBasic\Core\Ecommerce;

    use function AuthBasic\Config\redirectWithMessage;
    use function AuthBasic\Config\verifyCSRFToken;
    use PDO;

    require_once __DIR__ . '/cart_functions.php'; // For getCartItems, clearUserCart
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirectWithMessage('checkout', 'danger', 'Invalid request method.');
    }
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        redirectWithMessage('login', 'warning', 'Please log in.');
    }
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        redirectWithMessage('checkout', 'danger', 'CSRF token validation failed.');
    }

    $userId = $_SESSION['user_id'];
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');

    if (empty($shippingAddress) || empty($paymentMethod)) {
        redirectWithMessage('checkout', 'danger', 'Shipping address and payment method are required.');
    }

    $cartItems = getCartItems($pdo, $userId);
    if (empty($cartItems)) {
        redirectWithMessage('products', 'info', 'Your cart is empty.');
    }

    $pdo->beginTransaction();
    try {
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            // Verify stock again before placing order
            $stmtStock = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmtStock->execute([$item['product_id']]);
            $stock = $stmtStock->fetchColumn();
            if ($stock === false || $item['quantity'] > $stock) {
                throw new \Exception("Product " . htmlspecialchars($item['name']) . " is out of stock or insufficient quantity.");
            }
            $totalAmount += $item['price_at_addition'] * $item['quantity'];
        }

        // Create order
        $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, payment_status) VALUES (?, ?, ?, ?, ?)");
        $paymentStatus = ($paymentMethod === 'cod') ? 'pending' : 'pending_payment'; // Placeholder for actual payment
        $stmtOrder->execute([$userId, $totalAmount, $shippingAddress, $paymentMethod, $paymentStatus]);
        $orderId = $pdo->lastInsertId();

        // Add order items and update stock
        foreach ($cartItems as $item) {
            $stmtOrderItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_per_unit) VALUES (?, ?, ?, ?)");
            $stmtOrderItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['price_at_addition']]);

            // Update product stock
            $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stmtUpdateStock->execute([$item['quantity'], $item['product_id']]);
        }

        // Clear cart
        clearUserCart($pdo, $userId);

        $pdo->commit();
        
        $_SESSION['last_order_id'] = $orderId; // For confirmation page
        redirectWithMessage('order-confirmation', 'success', 'Your order has been placed successfully!');

    } catch (\Exception $e) {
        $pdo->rollBack();
        error_log("Order placement error: " . $e->getMessage());
        redirectWithMessage('checkout', 'danger', 'Could not place order: ' . $e->getMessage());
    }
    ?>
    ```

* **`cartbasic/pages/order_confirmation.php`:**
    ```php
    <?php
    namespace AuthBasic\Pages;
    use function AuthBasic\Config\displayMessage;

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        \AuthBasic\Config\redirectWithMessage('login', 'warning', 'Please log in.');
    }
    $lastOrderId = $_SESSION['last_order_id'] ?? null;
    // unset($_SESSION['last_order_id']); // Optional: clear after display
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Order Confirmation | Auth System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    </head>
    <body>
        <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>
        <main class="container py-5 text-center">
            <?php echo displayMessage(); ?>
            <?php if ($lastOrderId): ?>
                <h1>Thank You for Your Order!</h1>
                <p class="lead">Your order #<?php echo htmlspecialchars($lastOrderId); ?> has been placed successfully.</p>
                <p>We will process it shortly. You can view your order details in your dashboard.</p>
            <?php else: ?>
                 <h1>Order Confirmation</h1>
                <p class="lead">There seems to be an issue retrieving your order details, or no recent order was found.</p>
            <?php endif; ?>
            <a href="<?php echo SITE_URL; ?>/products" class="btn btn-primary mt-3">Continue Shopping</a>
            <a href="<?php echo SITE_URL; ?>/dashboard" class="btn btn-outline-secondary mt-3">View My Orders</a>
        </main>
        <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    ```

### 5. Routing and Navigation Updates

* **`cartbasic/index.php`:**
    Add to `$availablePages`:
    ```php
    // ... inside $availablePages array
    'products' => 'products.php',
    'product' => 'product.php', // Will handle ?id=X
    'cart' => 'cart.php',
    'checkout' => 'checkout.php',
    'order-confirmation' => 'order_confirmation.php',
    'admin-products' => 'admin_products.php', // For admin
    'admin-orders' => 'admin_orders.php',     // For admin
    ```
    Add to `$availableActions`:
    ```php
    // ... inside $availableActions array
    'cart-add-action' => 'ecommerce/cart_add_action.php',
    'cart-update-action' => 'ecommerce/cart_update_action.php',
    'cart-remove-action' => 'ecommerce/cart_remove_action.php',
    'order-place-action' => 'ecommerce/order_place_action.php',
    'admin-product-add-action' => 'ecommerce/admin_product_add_action.php',
    'admin-product-edit-action' => 'ecommerce/admin_product_edit_action.php',
    'admin-product-delete-action' => 'ecommerce/admin_product_delete_action.php',
    'admin-order-update-status-action' => 'ecommerce/admin_order_update_status_action.php',
    ```

* **`cartbasic/templates/navbar.php`:**
    Add links, for example:
    ```php
    // ... inside <ul class="navbar-nav me-auto">
    <li class="nav-item">
        <a class="nav-link <?php echo ($currentPage === 'products') ? 'active' : ''; ?>"
            href="<?php echo SITE_URL; ?>/products">Products</a>
    </li>
    // ...

    // ... inside <ul class="navbar-nav"> (before login/register or user dropdown)
    <li class="nav-item">
        <a class="nav-link <?php echo ($currentPage === 'cart') ? 'active' : ''; ?>"
            href="<?php echo SITE_URL; ?>/cart"><i class="bi bi-cart"></i> Cart</a>
    </li>

    // Optional: Admin links within user dropdown if logged in (needs admin check)
    // <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['is_admin'] === true): // Assuming an is_admin session variable ?>
    //    <li><a class="dropdown-item <?php echo ($currentPage === 'admin-products') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin-products">Manage Products</a></li>
    //    <li><a class="dropdown-item <?php echo ($currentPage === 'admin-orders') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin-orders">Manage Orders</a></li>
    // <?php endif; ?>
    ```

---

## Phase 3: Basic Admin System

This requires a concept of an "admin" user. For now, you could manually set a flag in the `users` table (e.g., `is_admin TINYINT(1) DEFAULT 0`) and update a user to be an admin. The login process would then need to set `$_SESSION['is_admin']`.

### 1. Admin Product Management

* **`cartbasic/pages/admin_products.php`:** (List, Add, Edit, Delete links)
    * Fetch all products using `getAllProducts()`.
    * Display in a table with "Edit" and "Delete" buttons.
    * Form to add a new product (POSTs to `admin-product-add-action`).
    * Edit form (could be on a separate `admin_edit_product.php` page or inline) that POSTs to `admin-product-edit-action`.

* **`cartbasic/core/ecommerce/admin_product_add_action.php`:**
    * Handle POST data (name, description, price, stock, image upload).
    * Insert into `products` table.

* **`cartbasic/core/ecommerce/admin_product_edit_action.php`:**
    * Handle POST data for an existing product ID.
    * Update `products` table.

* **`cartbasic/core/ecommerce/admin_product_delete_action.php`:**
    * Handle POST data for a product ID.
    * Delete from `products` table (or mark as inactive).

### 2. Admin Order Management

* **`cartbasic/core/ecommerce/order_functions.php` (New functions):**
    ```php
    <?php
    // ... (add to existing cartbasic/core/ecommerce/order_functions.php or create new)
    namespace AuthBasic\Core\Ecommerce;
    use PDO;

    function getAllOrders(PDO $pdo) {
        $stmt = $pdo->query("
            SELECT o.*, u.username as customer_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getOrderDetails(PDO $pdo, int $orderId) {
        $orderStmt = $pdo->prepare("
            SELECT o.*, u.username as customer_name, u.email as customer_email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) return null;

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

    function updateOrderStatus(PDO $pdo, int $orderId, string $newStatus): bool {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$newStatus, $orderId]);
    }
    ?>
    ```

* **`cartbasic/pages/admin_orders.php`:** (List orders, link to view details)
    * Fetch all orders using `getAllOrders()`.
    * Display in a table (Order ID, Customer, Date, Total, Status, Action (View)).

* **`cartbasic/pages/admin_order_detail.php`:** (Display full order details, allow status update)
    * Fetch order details using `getOrderDetails()`.
    * Form to update order status (POSTs to `admin-order-update-status-action`).

* **`cartbasic/core/ecommerce/admin_order_update_status_action.php`:**
    * Handle POST data (order ID, new status).
    * Call `updateOrderStatus()`.

---

## Phase 4: README Update and Further Considerations

* **`cartbasic/readme.md`:**
    * Add a new section detailing the E-commerce features.
    * Explain the new database tables.
    * List new dependencies (if any were added, though for this basic setup none were explicitly added beyond what PHPMailer/DotEnv might need).
    * Instructions for admin setup (e.g., how to designate an admin user).

### Key Considerations for a Real System (Recap & Expansion):
* **Payment Gateway:** This is a major part. You'd use libraries like Stripe PHP or PayPal SDK. The `order_place_action.php` would redirect to payment, and you'd need a webhook or callback URL to confirm payment and update `payment_status` in the `orders` table.
* **Admin User Interface:** The admin pages outlined are very basic. A real system would have a more robust interface.
* **Image Uploads:** For `admin_product_add_action.php`, you'd need to handle file uploads securely, store images, and save their paths/URLs.
* **Stock Management:** Ensure stock is correctly decremented and handle cases where an item goes out of stock during checkout.
* **Security:** All admin actions and sensitive operations need strict authorization checks (e.g., `if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { /* redirect or deny */ }`). All user inputs must be validated and sanitized.
* **Error Handling and Logging:** Enhance this significantly.
* **Transactions:** Ensure database operations for order creation (inserting order, order items, updating stock) are within a transaction so they can be rolled back if any part fails. (The example for `order_place_action.php` includes basic transaction handling).
* **Email Notifications:** Send emails for order confirmation, shipping updates, etc., using your existing PHPMailer setup.
* **Session Management for Guest Carts:** If you want guests to be able to add to a cart, you'll need to use PHP sessions to store cart data instead of or in addition to the database `cart` table linked to `user_id`.

This is a substantial expansion. Start with the database, then implement one feature set at a time (e.g., product display, then cart, then basic checkout). Testing thoroughly at each step is crucial. Good luck!