<?php
namespace CartBasic\Core\Auth;

use function CartBasic\Config\redirectWithMessage;
use function CartBasic\Config\verifyCSRFToken;

use PDO;
use PDOException;

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('login', 'danger', 'Invalid request method.');
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('login', 'danger', 'CSRF token validation failed.');
}

// Rate limiting: Prevent brute-force attacks
$ip_address = $_SERVER['REMOTE_ADDR'];
$failed_attempts_key = 'login_attempts_' . md5($ip_address);
$lockout_duration = 900; // 15 minutes in seconds
$max_attempts = 5; // Maximum failed attempts before lockout
$attempt_window = 900; // 15 minutes in seconds

// Check for lockout
if (isset($_SESSION[$failed_attempts_key])) {
    $attempt_data = $_SESSION[$failed_attempts_key];
    if ($attempt_data['count'] >= $max_attempts && (time() - $attempt_data['first_attempt_time']) < $lockout_duration) {
        redirectWithMessage('login', 'danger', 'Too many failed login attempts. Please try again in 15 minutes.');
    }
}

// Sanitize input
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) ? true : false;

// Validate inputs
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithMessage('login', 'danger', 'Please provide a valid email address.');
}

if (empty($password)) {
    redirectWithMessage('login', 'danger', 'Please provide your password.');
}

// Check user credentials
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Initialize or update failed login attempts
    if (!$user || !password_verify($password, $user['password'])) {
        if (!isset($_SESSION[$failed_attempts_key])) {
            $_SESSION[$failed_attempts_key] = [
                'count' => 1,
                'first_attempt_time' => time()
            ];
        } else {
            $attempt_data = $_SESSION[$failed_attempts_key];
            if ((time() - $attempt_data['first_attempt_time']) > $attempt_window) {
                // Reset attempts if the window has expired
                $_SESSION[$failed_attempts_key] = [
                    'count' => 1,
                    'first_attempt_time' => time()
                ];
            } else {
                // Increment attempts
                $_SESSION[$failed_attempts_key]['count'] = $attempt_data['count'] + 1;
            }
        }

        // Log the failed attempt
        $log_message = date('Y-m-d H:i:s') . " - Failed login attempt for email: {$email} (IP: {$ip_address})\n";
        file_put_contents(LOGS_PATH . 'login_attempts.log', $log_message, FILE_APPEND);

        redirectWithMessage('login', 'danger', 'Invalid email or password.');
    }

    // Reset failed attempts on successful login
    unset($_SESSION[$failed_attempts_key]);

    // Verify password
    if (!password_verify($password, $user['password'])) {
        redirectWithMessage('login', 'danger', 'Invalid email or password.');
    }

    // Check if email is verified
    if (!$user['is_verified']) {
        redirectWithMessage('login', 'warning', 'Please verify your email address before logging in.');
    }

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    $_SESSION['is_admin'] = (bool) $user['is_admin'];

    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 60 * 60 * 24 * 30; // 30 days

        // Store token in database
        $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, remember_token_expires = ? WHERE id = ?");
        $stmt->execute([$token, date('Y-m-d H:i:s', $expiry), $user['id']]);

        // Set cookie with secure flags
        setcookie('remember_token', $token, [
            'expires' => $expiry,
            'path' => '/',
            'secure' => true, // Only send over HTTPS
            'httponly' => true, // Prevent JavaScript access
            'samesite' => 'Strict' // Prevent CSRF
        ]);
    }

    // Log successful login
    $log_message = date('Y-m-d H:i:s') . " - Successful login for user: {$user['username']} (ID: {$user['id']}, IP: {$ip_address})\n";
    file_put_contents(LOGS_PATH . 'login_success.log', $log_message, FILE_APPEND);

    // Redirect to dashboard
    redirectWithMessage('dashboard', 'success', 'Login successful!');
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    redirectWithMessage('login', 'danger', 'Database error: ' . $e->getMessage());
}