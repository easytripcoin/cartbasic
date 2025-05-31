# CartBasic: Secure PHP Login, Registration & E-commerce System

CartBasic is a comprehensive and secure user authentication and e-commerce system built with PHP, MySQL, and Bootstrap 5. It implements modern security best practices and provides a solid foundation for web applications requiring user management and online store capabilities. The system features a front controller pattern for clean URLs, email verification, password reset functionality, product management, shopping cart, a checkout process, and a basic admin panel for managing products and orders. Configuration, including sensitive credentials, is managed via a `.env` file for enhanced security.

## Key Features

* **Secure User Authentication:**
    * User registration with email verification.
    * Secure login with password hashing (bcrypt).
    * "Remember Me" functionality with secure tokens.
    * Password reset via email with time-limited tokens.
    * Secure POST-based logout with CSRF protection.
* **E-commerce Functionality:**
    * Product listing and detailed product view pages.
    * Shopping cart (add, update, remove items).
    * Basic checkout process (simulated payment).
    * Order creation and storage.
    * User order history (can be added to dashboard).
* **Admin Panel (Basic):**
    * Product Management (CRUD operations - Add, View, Edit, Delete products).
    * Order Management (View list of orders, view order details, update order status).
    * Admin access controlled via an `is_admin` flag in the `users` table.
* **Security Best Practices:**
    * **Environment-Based Configuration:** Sensitive credentials managed via `.env`.
    * **Password Hashing:** Uses `password_hash()` and `password_verify()`.
    * **Prepared Statements (PDO):** Protects against SQL injection.
    * **CSRF Protection:** On all state-changing forms.
    * **Session Security:** Session regeneration on login/sensitive actions.
    * **Input Sanitization & Validation:** Server-side and client-side.
    * **Rate Limiting:** Basic protection against brute-force and frequent submissions.
    * **Secure Image Handling (Admin):** Basic unique naming and directory storage for product images. *Further server-side MIME type validation is crucial for production.*
* **User Account Management:**
    * Profile viewing and updating (username, email).
    * Secure password change.
* **Email Handling:**
    * PHPMailer for reliable email sending (verification, password reset, order notifications - *order notifications to be implemented*).
    * HTML email templates.
    * Centralized SMTP configuration via `.env`.
* **Modern Architecture:**
    * Front Controller Pattern (`index.php`).
    * Organized core logic in `core/` (auth, ecommerce, contact).
    * Namespaced PHP code (primarily under `CartBasic` namespace).
    * Centralized configuration (`config/config.php`).
* **User Interface & Experience:**
    * Responsive design with Bootstrap 5.
    * User-friendly forms with client-side validation.
    * Session-based feedback messages.
* **Logging:**
    * Logging for important events and errors in `logs/`.

## Project Structure

* **`cartbasic/`** (Project Root)
    * **`assets/`**: Static frontend assets.
        * `css/style.css`
        * `js/script.js`
        * `images/products/` (For uploaded product images)
        * `images/placeholder.png`
    * **`config/`**: Application configuration.
        * `config.php`
        * `functions.php`
    * **`core/`**: Core application logic and action handlers.
        * **`auth/`**: Authentication logic.
        * **`contact/`**: Contact form logic.
        * **`ecommerce/`**: E-commerce logic.
            * `product_functions.php`
            * `cart_functions.php`, `cart_add_action.php`, etc.
            * `order_functions.php`, `order_place_action.php`, etc.
            * `admin_product_add_action.php`, `admin_order_update_status_action.php`, etc.
    * **`logs/`**: Application log files.
    * **`pages/`**: View files.
        * `home.php`, `login.php`, `register.php`, `dashboard.php`, etc.
        * `products.php`, `product.php`, `cart.php`, `checkout.php`, `order_confirmation.php`
        * `admin_products.php`, `admin_add_product.php`, `admin_edit_product.php`
        * `admin_orders.php`, `admin_order_detail.php`
        * `404.php`
    * **`templates/`**: Reusable HTML partials.
        * `navbar.php`, `footer.php`.
    * **`vendor/`**: Composer dependencies.
    * `.env`, `.env.example`, `.htaccess`, `composer.json`, `composer.lock`, `database.sql`, `index.php`, `readme.md`, `LICENSE`

## Requirements

* PHP 7.4 or higher (PHP 8.x recommended).
    * PDO Extension (with MySQL driver).
    * OpenSSL Extension.
    * Mbstring Extension.
    * Fileinfo Extension (recommended for server-side MIME type validation of uploads).
* MySQL 5.7+ or MariaDB 10.2+.
* Composer.
* Web Server (Apache with `mod_rewrite`, or Nginx).

## Installation & Setup

1.  **Clone Repository & Install Dependencies:**
    ```bash
    git clone [https://github.com/yourusername/cartbasic.git](https://github.com/yourusername/cartbasic.git) # Update URL if needed
    cd cartbasic
    composer install
    ```

2.  **Database Setup:**
    * Create a database (e.g., `cartbasic_db`).
    * Import `database.sql`: `mysql -u your_db_user -p cartbasic_db < database.sql`
    * **Admin User:** After importing, add the `is_admin` column if not already in `database.sql` and set a user as admin:
        ```sql
        ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0 COMMENT '1 for admin, 0 for regular user' AFTER is_verified;
        -- Then update a user:
        -- UPDATE users SET is_admin = 1 WHERE email = 'your_admin_email@example.com';
        ```

3.  **Environment Configuration (`.env` file):**
    * Copy `.env.example` to `.env`.
    * Edit `.env` with your `APP_URL`, `DB_*` credentials, `MAIL_*` credentials, etc.
    * Ensure `APP_SUBDIRECTORY` is set correctly if your project is in a subfolder (e.g., `/cartbasic`).

4.  **Web Server Configuration:**
    * **Apache:** Ensure `mod_rewrite` is enabled. The `.htaccess` file provided should work. If in a subdirectory, adjust `RewriteBase` in `.htaccess`.
    * **Nginx:** Configure to route requests to `index.php`. See example in the original `readme.md`.

5.  **Permissions:**
    * Ensure `logs/` is writable.
    * Ensure `assets/images/products/` is created and writable by the web server user for product image uploads.

6.  **Access Application:** Navigate to your `APP_URL`.

## Usage Guide

The application uses clean URLs managed by `index.php`.

* **User Routes:**
    * `/` or `/home`: Homepage
    * `/products`: Product listing
    * `/product?id={ID}`: Single product view
    * `/cart`: View shopping cart
    * `/checkout`: Checkout page
    * `/order-confirmation`: Order success page
    * `/register`, `/login`, `/dashboard`, `/profile`, `/change-password`, etc.
* **Action Routes (POST requests):**
    * `/cart-add-action`, `/cart-update-action`, `/cart-remove-action`
    * `/order-place-action`
    * Other auth actions as before.
* **Admin Routes (Require admin privileges):**
    * `/admin-products`: Manage products
    * `/admin-add-product`: Form to add a new product
    * `/admin-edit-product?id={ID}`: Form to edit an existing product
    * `/admin-orders`: Manage orders
    * `/admin-order-detail?id={ID}`: View order details and update status
* **Admin Action Routes (POST requests, require admin):**
    * `/admin-product-add-action`
    * `/admin-product-edit-action`
    * `/admin-product-delete-action`
    * `/admin-order-update-status-action`

## E-commerce Functionality Details

* **Product Management:** Admins can create, read, update, and delete products, including names, descriptions, prices, stock quantities, and images.
* **Shopping Cart:** Logged-in users can add products to a persistent cart, update quantities, or remove items.
* **Checkout:** A simplified process to collect shipping information and select a (simulated) payment method.
* **Order Processing:** On checkout, an order is created, cart items are moved to order items, and product stock is updated.
* **Order History:** Admins can view all orders and update their statuses. Users should be able to view their own order history (typically via their dashboard).

## Production System Considerations

For a production-ready e-commerce system, the following aspects built upon in this project need further hardening and development:

* **Robust Input Validation:** All user and admin inputs must be rigorously validated on the server-side.
* **Detailed Error Handling & Logging:** Implement more specific error catching and user-friendly error messages.
* **Secure Image Handling:**
    * **MIME Type Validation:** Server-side validation of uploaded file MIME types (e.g., using `finfo_file`).
    * **Image Resizing/Optimization & Sanitization:** Process images to prevent XSS or other attacks via malicious image files.
    * **Secure Storage:** Store uploads outside the webroot if possible, or with strict access controls.
* **Fine-Grained Permissions/Roles:** Implement a Role-Based Access Control (RBAC) system for more granular admin permissions.
* **Payment Gateway Integration:** Integrate a secure payment gateway (e.g., Stripe, PayPal).
* **Inventory Management:** Handle race conditions for stock, manage backorders.
* **Transaction Management (Database):** Ensure critical multi-step database operations are atomic.
* **Email Notifications:** Expand email notifications (order confirmation, shipping, etc.).
* **Security Headers:** Implement CSP, HSTS, etc.
* **HTTPS:** Enforce HTTPS sitewide.
* **Scalability & Testing:** Consider database indexing, caching, and implement unit/integration tests.
* **Regular Audits & Updates:** Keep all components updated.

## Contributing

Contributions are welcome! Please fork the repository, create a feature branch, and submit a pull request.

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.