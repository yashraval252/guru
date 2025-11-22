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
                        <h2 class="mb-0">Create Account</h2>
                    </div>
                    <div class="card-body p-4">
                        <!-- Error Messages -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Registration Errors:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <?php $recaptchaSiteKey = $_ENV['RECAPTCHA_SITE_KEY'] ?? ''; ?>
                        <?php if ($recaptchaSiteKey): ?>
                            <script src="https://www.google.com/recaptcha/api.js?render=<?=htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8')?>"></script>
                        <?php endif; ?>

                        <form id="registerForm" action="auth/register.php" method="POST" novalidate>
                            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-register" />
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required minlength="2" maxlength="100" placeholder="Enter your full name" value="<?=htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
                                <div class="input-error" id="nameError" aria-live="polite"></div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required autocomplete="email" placeholder="Enter your email" value="<?=htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
                                <div class="input-error" id="emailError" aria-live="polite"></div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6" autocomplete="new-password" placeholder="Enter a strong password (min. 6 characters)" />
                                <div class="input-error" id="passwordError" aria-live="polite"></div>
                                <small class="text-muted d-block mt-1">Use at least 6 characters with uppercase, numbers, and symbols for better security.</small>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password" placeholder="Confirm your password" />
                                <div class="input-error" id="confirmPasswordError" aria-live="polite"></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">Create Account</button>
                        </form>

                        <?php if ($recaptchaSiteKey): ?>
                        <script>
                        (function(){
                            const form = document.getElementById('registerForm');
                            const tokenInput = document.getElementById('g-recaptcha-response-register');
                            if (!form) return;

                            form.addEventListener('submit', function(e){
                                // If token already present, allow submit to continue (validation may run)
                                if (tokenInput && tokenInput.value) return;
                                e.preventDefault();
                                grecaptcha.ready(function(){
                                    grecaptcha.execute('<?=htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8')?>', {action: 'register'})
                                    .then(function(token){
                                        if (tokenInput) tokenInput.value = token;
                                        if (typeof form.requestSubmit === 'function') {
                                            form.requestSubmit();
                                        } else {
                                            form.submit();
                                        }
                                    });
                                });
                            });
                        })();
                        </script>
                        <?php endif; ?>

                        <hr class="my-4" />

                        <!-- Sign In Link -->
                        <p class="text-center mb-0">
                            Already have an account? 
                            <a href="index.php" class="fw-semibold">Sign in here</a>
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
