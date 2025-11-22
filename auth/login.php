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

// Verify reCAPTCHA token first (v3)
$recaptchaToken = $_POST['g-recaptcha-response'] ?? '';
if ($recaptchaToken !== '') {
    if (!verify_recaptcha((string)$recaptchaToken, 'login')) {
        $_SESSION['login_error'] = 'reCAPTCHA verification failed. Please try again.';
        header('Location: ../index.php');
        exit();
    }
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || empty($password)) {
    $_SESSION['login_error'] = 'Please provide valid email and password.';
    header('Location: ../index.php');
    exit();
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT id, name, password FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = 'Invalid email or password.';
        header('Location: ../index.php');
        exit();
    }

    // Successful login
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = (string)$user['name'];

    header('Location: ../dashboard.php');
    exit();
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    $_SESSION['login_error'] = 'An error occurred. Please try again later.';
    header('Location: ../index.php');
    exit();
}
