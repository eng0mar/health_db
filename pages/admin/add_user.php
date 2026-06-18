<?php
// ============================================
// Admin Add User - Add doctor or patient
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Admin.php';

Middleware::requireRole('admin');

$admin = new Admin((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');

    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email)) $errors[] = 'Email is required.';
    if ($email && !validateEmail($email)) $errors[] = 'Invalid email format.';
    if (empty($password)) $errors[] = 'Password is required.';
    if (!validatePassword($password)) $errors[] = 'Password must be at least 6 characters.';
    if (!in_array($role, ['admin', 'doctor', 'patient'])) $errors[] = 'Invalid role.';

    if (empty($errors)) {
        $result = $admin->register($name, $email, $password, $role, $phone ?: null);
        if ($result) {
            setFlash('success', ucfirst($role) . ' added successfully.');
            redirect(APP_URL . '/pages/admin/users.php');
        } else {
            $errors[] = 'Email already exists or failed to add user.';
        }
    }
}

$pageTitle = 'Add User';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>➕ Add New User</h1>
    <p>Create a new doctor, patient, or admin account</p>
</div>

<div class="card" style="max-width:600px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <span class="alert-icon">✕</span>
            <span class="alert-message"><?php foreach ($errors as $e) echo sanitize($e) . '<br>'; ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="add-user-form">
        <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= sanitize($name ?? '') ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= sanitize($email ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= sanitize($phone ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="role">Role</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="">Select role</option>
                    <option value="admin" <?= (($role ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="doctor" <?= (($role ?? '') === 'doctor') ? 'selected' : '' ?>>Doctor</option>
                    <option value="patient" <?= (($role ?? '') === 'patient') ? 'selected' : '' ?>>Patient</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
        </div>
        <div class="flex-between mt-2">
            <a href="<?= APP_URL ?>/pages/admin/users.php" class="btn btn-outline">← Back</a>
            <button type="submit" class="btn btn-primary">Add User</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
