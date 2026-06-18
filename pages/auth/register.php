<?php
// ============================================
// Register Page - Unique Email & Phone Validation
// Name, email, password (Fixed Role to Patient)
// ============================================
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Admin.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');
    
    $role     = 'patient';

    // Server-side validation
    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email)) $errors[] = 'Email is required.';
    if ($email && !validateEmail($email)) $errors[] = 'Invalid email format.';
    if (empty($password)) $errors[] = 'Password is required.';
    if (!validatePassword($password)) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    
    if (!empty($phone)) {
        if (!preg_match('/^\+?[1-9]\d{9,14}$/', $phone)) {
            $errors[] = 'Invalid international phone number format.';
        }
    }

    if (empty($errors)) {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email address is already registered. Please choose another one.';
        }

        if (!empty($phone)) {
            $stmt = $db->prepare("SELECT id FROM users WHERE phone = :phone LIMIT 1");
            $stmt->execute([':phone' => $phone]);
            if ($stmt->fetch()) {
                $errors[] = 'This phone number is already registered. Please use a different number.';
            }
        }
    }

    if (empty($errors)) {
        $admin = new Admin();
        $result = $admin->register($name, $email, $password, $role, $phone ?: null);

        if ($result) {
            setFlash('success', 'Registration successful! Please login.');
            redirect(APP_URL . '/pages/auth/login.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Register';
include __DIR__ . '/../../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <h1>🏥 Create Account</h1>
            <p>Register for the National Health Database</p>
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

        <form method="POST" action="" id="register-form">
            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= sanitize($name ?? '') ?>" placeholder="Enter your full name" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= sanitize($email ?? '') ?>" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone (International)</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?= sanitize($phone ?? '') ?>"
                           placeholder="+201234567890"
                           pattern="^\+?[1-9]\d{9,14}$"
                           title="Please enter a valid international phone number starting with country code (10 to 15 digits total)">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="Min 6 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                           placeholder="Re-enter password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="register-btn">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?= APP_URL ?>/pages/auth/login.php">Sign in</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>