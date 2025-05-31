<?php
namespace CartBasic\Core\Ecommerce;

use PDO;

// Get or create a cart for the logged-in user
function getOrCreateUserCart(PDO $pdo, int $userId): ?int
{
    // Check if user has an active cart
    $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        return (int) $cart['id'];
    } else {
        // Create a new cart
        $stmt = $pdo->prepare("INSERT INTO cart (user_id) VALUES (?)");
        if ($stmt->execute([$userId])) {
            return (int) $pdo->lastInsertId();
        }
    }
    return null;
}

// Add item to cart or update quantity if it already exists
function addItemToCart(PDO $pdo, int $cartId, int $productId, int $quantity, float $priceAtAddition): bool
{
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
function getCartItems(PDO $pdo, int $userId): array
{
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
function updateCartItemQuantity(PDO $pdo, int $cartItemId, int $newQuantity, int $userId): bool
{
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
function removeCartItem(PDO $pdo, int $cartItemId, int $userId): bool
{
    // Ensure the cart item belongs to the user for security
    $stmt = $pdo->prepare("
        DELETE ci FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.id
        WHERE ci.id = ? AND c.user_id = ?
    ");
    return $stmt->execute([$cartItemId, $userId]);
}

// Clear user's cart (e.g., after order placement)
function clearUserCart(PDO $pdo, int $userId): bool
{
    $cartId = getOrCreateUserCart($pdo, $userId);
    if ($cartId) {
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        return $stmt->execute([$cartId]);
        // Optionally, you might delete the cart itself if it's empty and old,
        // or keep it for cart recovery features.
    }
    return false;
}