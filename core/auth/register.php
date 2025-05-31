<?php
namespace CartBasic\Core\Auth;

use function CartBasic\Config\sanitizeInput;
use function CartBasic\Config\redirectWithMessage;
use function CartBasic\Config\verifyCSRFToken;

use PDOException;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('register', 'danger', 'Invalid request method.'); // Use page key
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    redirectWithMessage('register', 'danger', 'CSRF token validation failed.'); // Use page key
}

// Rate limiting: Prevent abuse
$ip_address = $_SERVER['REMOTE_ADDR'];
$last_registration_key = 'last_registration_' . md5($ip_address);
$registration_interval = 30; // 5 minutes in seconds

if (isset($_SESSION[$last_registration_key])) {
    $last_registration_time = $_SESSION[$last_registration_key];
    if (time() - $last_registration_time < $registration_interval) {
        redirectWithMessage('register', 'danger', 'Please wait a few minutes before registering another account.');
    }
}

// Sanitize and validate input
$username = sanitizeInput($_POST['username'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate inputs
$errors = [];
$form_data = []; // Store only valid, non-sensitive fields

if (empty($username)) {
    $errors[] = 'Username is required.';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters.';
} else {
    $form_data['username'] = $username; // Save valid username
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
} else {
    $form_data['email'] = $email; // Save valid email
}

if (empty($password) || strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
} elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password must contain at least one letter and one number.';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
}

// Check if username or email already exists
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Username or email already exists.';
    }
} catch (PDOException $e) {
    error_log("Database error during registration check: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    redirectWithMessage('register', 'danger', 'Database error: ' . $e->getMessage());
}

if (!empty($errors)) {
    // Save only valid fields to session
    $_SESSION['form_data'] = $form_data;
    redirectWithMessage('register', 'danger', implode('<br>', $errors));
}

// Clear form data on successful validation
unset($_SESSION['form_data']);

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Generate verification token and expiry (24 hours from now)
$verification_token = bin2hex(random_bytes(32));
$verification_token_expires = date('Y-m-d H:i:s', time() + 24 * 3600);

// Insert user into database
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verification_token, verification_token_expires, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $email, $password_hash, $verification_token, $verification_token_expires]);

    // Log the registration
    $log_message = date('Y-m-d H:i:s') . " - New user registered: {$username} (Email: {$email}, IP: {$ip_address})\n";
    file_put_contents(LOGS_PATH . 'registrations.log', $log_message, FILE_APPEND);

    // Send verification email using PHPMailer
    // Use the new URI structure for the verification link
    $verification_link = SITE_URL . "/verify-email?token=" . urlencode($verification_token);
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email, $username);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Verify Your Email Address";
        $mail->Body = "
            <html>
            <head>
                <title>Email Verification</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .button { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; }
                    .button:hover { background-color: #0056b3; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Email Verification</h2>
                    <p>Hello " . htmlspecialchars($username) . ",</p>
                    <p>Thank you for registering with us! Please verify your email address by clicking the button below:</p>
                    <p><a href='$verification_link' class='button' style='color: #ffffff; text-decoration: none; border-radius: 5px;'>Verify Email</a></p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p><a href='$verification_link'>$verification_link</a></p>
                    <p>This link will expire in 24 hours.</p>
                    <p>If you didn't register, please ignore this email or contact our support team.</p>
                    <p>Best regards,<br>Auth System Team</p>
                </div>
            </body>
            </html>
        ";

        $mail->send();

        // Update last registration time
        $_SESSION[$last_registration_key] = time();

        redirectWithMessage('login', 'success', 'Registration successful! Please check your email to verify your account.');
    } catch (Exception $e) {
        error_log("Failed to send verification email: " . $mail->ErrorInfo, 3, LOGS_PATH . 'email_errors.log');
        redirectWithMessage('register', 'warning', 'Registration successful, but we couldn\'t send the verification email. Please contact support.');
    }
} catch (PDOException $e) {
    error_log("Database error during registration: " . $e->getMessage(), 3, LOGS_PATH . 'database_errors.log');
    redirectWithMessage('register', 'danger', 'Database error: ' . $e->getMessage());
}