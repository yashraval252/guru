<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$errorMessage = '';
if (isset($_SESSION['login_error'])) {
    $errorMessage = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

$successMessage = '';
if (isset($_SESSION['register_success'])) {
    $successMessage = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Har Mahadev Voice Entry</title>
    <link rel="stylesheet" href="styles/styles.css" />
</head>
<body>
    <main class="container">
        <h1>Login</h1>
        <?php if ($errorMessage): ?>
            <div class="error-message" role="alert"><?=htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')?></div>
        <?php endif; ?>
        <?php if ($successMessage): ?>
            <div class="success-message" role="alert"><?=htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8')?></div>
        <?php endif; ?>
        <form id="loginForm" action="auth/login.php" method="POST" novalidate>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required autocomplete="email" />
            <div class="input-error" id="emailError" aria-live="polite"></div>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required autocomplete="current-password" />
            <div class="input-error" id="passwordError" aria-live="polite"></div>

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="registration.php">Register here</a></p>
    </main>
    <script src="js/scripts.js" defer></script>
</body>
</html>
