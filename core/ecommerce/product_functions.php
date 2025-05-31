<?php
namespace CartBasic\Core\Ecommerce;

use PDO;

/**
 * Fetches all products from the database, typically those in stock.
 * @param PDO $pdo The PDO database connection object.
 * @return array An array of products.
 */
function getAllProducts(PDO $pdo): array
{
    // You might want to add pagination for large numbers of products
    $stmt = $pdo->query("SELECT id, name, description, price, image_url, stock_quantity FROM products WHERE stock_quantity > 0 ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetches a single product by its ID.
 * @param PDO $pdo The PDO database connection object.
 * @param int $productId The ID of the product to fetch.
 * @return array|false The product data as an associative array, or false if not found.
 */
function getProductById(PDO $pdo, int $productId)
{
    $stmt = $pdo->prepare("SELECT id, name, description, price, image_url, stock_quantity FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// === Functions for Admin Product Management (Phase 3, but good to group here) ===

/**
 * Adds a new product to the database.
 * @param PDO $pdo
 * @param string $name
 * @param string $description
 * @param float $price
 * @param int $stock_quantity
 * @param string|null $image_url
 * @return bool True on success, false on failure.
 */
function addProduct(PDO $pdo, string $name, string $description, float $price, int $stock_quantity, ?string $image_url = null): bool
{
    $sql = "INSERT INTO products (name, description, price, stock_quantity, image_url, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$name, $description, $price, $stock_quantity, $image_url]);
    } catch (\PDOException $e) {
        // Log error $e->getMessage();
        error_log("Error adding product: " . $e->getMessage(), 3, LOGS_PATH . 'admin_errors.log');
        return false;
    }
}

/**
 * Updates an existing product in the database.
 * @param PDO $pdo
 * @param int $productId
 * @param string $name
 * @param string $description
 * @param float $price
 * @param int $stock_quantity
 * @param string|null $image_url
 * @return bool True on success, false on failure.
 */
function updateProduct(PDO $pdo, int $productId, string $name, string $description, float $price, int $stock_quantity, ?string $image_url = null): bool
{
    // If image_url is provided as an empty string but we don't want to clear it, handle that logic here
    // For now, if $image_url is null, we don't update it. If it's an empty string, we could clear it.
    // This example updates image_url if it's not null. If you want to allow clearing it, adjust accordingly.

    $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, updated_at = NOW()";
    $params = [$name, $description, $price, $stock_quantity];

    if ($image_url !== null) { // Only add image_url to update if it's explicitly provided (not null)
        $sql .= ", image_url = ?";
        $params[] = $image_url;
    }

    $sql .= " WHERE id = ?";
    $params[] = $productId;

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (\PDOException $e) {
        error_log("Error updating product ID $productId: " . $e->getMessage(), 3, LOGS_PATH . 'admin_errors.log');
        return false;
    }
}

/**
 * Deletes a product from the database.
 * @param PDO $pdo
 * @param int $productId
 * @return bool True on success, false on failure.
 */
function deleteProduct(PDO $pdo, int $productId): bool
{
    $sql = "DELETE FROM products WHERE id = ?";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$productId]);
    } catch (\PDOException $e) {
        // Log error, consider foreign key constraints if any cascade issues arise
        error_log("Error deleting product ID $productId: " . $e->getMessage(), 3, LOGS_PATH . 'admin_errors.log');
        return false;
    }
}

/**
 * Fetches all products for admin listing (including out of stock).
 * @param PDO $pdo The PDO database connection object.
 * @return array An array of all products.
 */
function getAllProductsForAdmin(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT id, name, price, stock_quantity, created_at, updated_at FROM products ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}