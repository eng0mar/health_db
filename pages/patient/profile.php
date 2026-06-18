<?php
// ============================================
// Patient Profile - Update name, email, password
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Patient.php';

Middleware::requireRole('patient');

$patient = new Patient((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$userData = User::getUserById((int)$_SESSION['user_id']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $newPass  = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email)) $errors[] = 'Email is required.';
    if ($email && !validateEmail($email)) $errors[] = 'Invalid email format.';
    if ($newPass && !validatePassword($newPass)) $errors[] = 'Password must be at least 6 characters.';
    if ($newPass && $newPass !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $result = $patient->updateProfile(
            (int)$_SESSION['user_id'], $name, $email, $phone ?: null, $newPass ?: null
        );
        if ($result) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            setFlash('success', 'Profile updated successfully.');
            redirect(APP_URL . '/pages/patient/profile.php');
        } else {
            $errors[] = 'Email already taken or update failed.';
        }
    }
}

$pageTitle = 'My Profile';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header"><h1>👤 My Profile</h1><p>Update your personal information</p></div>

<div class="card" style="max-width:550px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><span class="alert-icon">✕</span><span class="alert-message"><?php foreach ($errors as $e) echo sanitize($e) . '<br>'; ?></span></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= sanitize($userData['name']) ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= sanitize($userData['email']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= sanitize($userData['phone'] ?? '') ?>">
            </div>
        </div>
        <hr style="border-color:var(--border);margin:1.5rem 0;">
        <p class="form-hint mb-2">Leave password fields empty to keep current password.</p>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Min 6 characters">
            </div>
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-2">Update Profile</button>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
