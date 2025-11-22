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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles/styles.css" />
</head>
<body>
    <div class="d-flex flex-column min-vh-100">
        <!-- Header -->
        <div class="page-header">
            <div class="main-container">
                <h1>🙏 Har Mahadev</h1>
                <p class="welcome-text mb-0">Voice Entry Application</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="auth-container flex-grow-1 d-flex align-items-center">
            <div class="w-100">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Sign In</h2>
                    </div>
                    <div class="card-body p-4">
                        <!-- Error Messages -->
                        <?php if ($errorMessage): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Login Failed!</strong> <?=htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Success Messages -->
                        <?php if ($successMessage): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Success!</strong> <?=htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8')?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form id="loginForm" action="auth/login.php" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required autocomplete="email" placeholder="Enter your email" />
                                <div class="input-error" id="emailError" aria-live="polite"></div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password" />
                                <div class="input-error" id="passwordError" aria-live="polite"></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
                        </form>

                        <hr class="my-4" />

                        <!-- Sign Up Link -->
                        <p class="text-center mb-0">
                            Don't have an account? 
                            <a href="registration.php" class="fw-semibold">Sign up here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js" defer></script>
</body>
</html>
