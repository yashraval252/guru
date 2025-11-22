<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$errors = $_SESSION['register_errors'] ?? [];
$old = $_SESSION['register_old'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['register_old']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register - Har Mahadev Voice Entry</title>
    <link rel="stylesheet" href="styles/styles.css" />
</head>
<body>
    <main class="container">
        <h1>Register</h1>
        <?php if (!empty($errors)): ?>
            <div class="error-message" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form id="registerForm" action="auth/register.php" method="POST" novalidate>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required minlength="2" maxlength="100" value="<?=htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
            <div class="input-error" id="nameError" aria-live="polite"></div>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required autocomplete="email" value="<?=htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
            <div class="input-error" id="emailError" aria-live="polite"></div>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password" />
            <div class="input-error" id="passwordError" aria-live="polite"></div>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password" />
            <div class="input-error" id="confirmPasswordError" aria-live="polite"></div>

            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="index.php">Login here</a></p>
    </main>
    <script src="js/scripts.js" defer></script>
</body>
</html>
