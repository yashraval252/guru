<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';

use App\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
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
