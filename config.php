<?php
declare(strict_types=1);

// Load environment variables from .env if exists
// echo "<pre>";print_r(__DIR__ . '/vendor/autoload.php');echo "</pre>";exit;
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
}

// Database configuration constants
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'harmahadev_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Session configuration
ini_set('session.use_strict_mode', '1');

$sessionName = $_ENV['SESSION_NAME'] ?? 'harmahadev_session';
$sessionLifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 3600);
$sessionPath = $_ENV['SESSION_PATH'] ?? '/';
$sessionSecure = filter_var($_ENV['SESSION_SECURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
$sessionHttpOnly = filter_var($_ENV['SESSION_HTTPONLY'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
$sessionSameSite = $_ENV['SESSION_SAMESITE'] ?? 'Lax';

session_name($sessionName);
session_set_cookie_params([
    'lifetime' => $sessionLifetime,
    'path' => $sessionPath,
    'domain' => '',
    'secure' => $sessionSecure,
    'httponly' => $sessionHttpOnly,
    'samesite' => $sessionSameSite
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
