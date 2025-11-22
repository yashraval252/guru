<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';

use App\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
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
