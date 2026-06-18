<?php
// ============================================
// Login Page
// Email + password, session-based auth
// ============================================
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Admin.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Law already logged in, redirect le dashboard
if (isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side validation
    if (empty($email)) $errors[] = 'Email is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if ($email && !validateEmail($email)) $errors[] = 'Invalid email format.';

    if (empty($errors)) {
        $user = User::login($email, $password);
        if ($user) {
            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect(APP_URL . '/index.php');
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
include __DIR__ . '/../../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h1>🏥 Welcome Back</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <span class="alert-icon">✕</span>
                <span class="alert-message">
                    <?php foreach ($errors as $err): ?>
                        <?= sanitize($err) ?><br>
                    <?php endforeach; ?>
                </span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="login-form">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= sanitize($email ?? '') ?>" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="login-btn">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="<?= APP_URL ?>/pages/auth/register.php">Register here</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
