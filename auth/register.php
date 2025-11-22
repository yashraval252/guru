<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';

use App\Database;

/**
 * Verify Google reCAPTCHA v3 token. Returns true if verification passed.
 */
function verify_recaptcha(string $token, string $expectedAction = ''): bool
{
    $secret = $_ENV['RECAPTCHA_SECRET'] ?? '';
    if (!$secret) {
        // If secret not configured, skip verification in development.
        error_log('reCAPTCHA secret not configured; skipping verification');
        return true;
    }

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $data,
            'timeout' => 5,
        ]
    ];
    $context = stream_context_create($opts);
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        error_log('reCAPTCHA verification request failed');
        return false;
    }
    $res = json_decode($result, true);
    if (!is_array($res) || empty($res['success'])) {
        error_log('reCAPTCHA verification failed: ' . ($result ?? 'no-response'));
        return false;
    }

    // For v3 we expect a score and action. Accept if score >= 0.5 and action matches (if provided)
    $score = isset($res['score']) ? (float)$res['score'] : 0.0;
    $action = $res['action'] ?? '';
    if ($expectedAction && $action !== $expectedAction) {
        error_log('reCAPTCHA action mismatch: expected ' . $expectedAction . ' got ' . $action);
        return false;
    }
    if ($score < 0.5) {
        error_log('reCAPTCHA low score: ' . $score);
        return false;
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$recaptchaToken = $_POST['g-recaptcha-response'] ?? '';
if ($recaptchaToken !== '') {
    if (!verify_recaptcha((string)$recaptchaToken, 'register')) {
        $errors[] = 'reCAPTCHA verification failed. Please try again.';
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_old'] = ['name' => $_POST['name'] ?? '', 'email' => $_POST['email'] ?? ''];
        header('Location: ../registration.php');
        exit();
    }
}

$name = trim($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errors = [];
$old = ['name' => $name, 'email' => $email];

// Validate inputs
if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors[] = 'Name must be between 2 and 100 characters.';
}
if (!$email) {
    $errors[] = 'Please enter a valid email address.';
}
if (mb_strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters.';
}
if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if ($errors) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old'] = $old;
    header('Location: ../registration.php');
    exit();
}

try {
    $db = Database::getInstance()->getConnection();

    // Check unique email
    $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Email is already registered.';
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_old'] = $old;
        header('Location: ../registration.php');
        exit();
    }

    // Insert user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
    ]);

    $_SESSION['register_success'] = 'Registration successful. You can now log in.';
    header('Location: ../index.php');
    exit();

} catch (Exception $e) {
    error_log('Registration error: ' . $e->getMessage());
    $errors[] = 'An error occurred. Please try again later.';
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old'] = $old;
    header('Location: ../registration.php');
    exit();
}
