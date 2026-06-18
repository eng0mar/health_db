<?php
// ============================================
// Admin Users Management - List, search, delete users
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Admin.php';
require_once __DIR__ . '/../../includes/helpers.php';

Middleware::requireRole('admin');

$admin = new Admin((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);

$searchKeyword = trim($_GET['search'] ?? '');
$roleFilter    = trim($_GET['role'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $deleteId = (int)$_POST['delete_user_id'];
    if ($admin->deleteUser($deleteId)) {
        setFlash('success', 'User deleted successfully.');
    } else {
        setFlash('error', 'Cannot delete this user. You cannot delete yourself.');
    }
    redirect(APP_URL . '/pages/admin/users.php');
}

if (!empty($searchKeyword)) {
    $users = $admin->searchUsers($searchKeyword);
} else {
    $users = $admin->getUsers();
}


if (!empty($roleFilter) && is_array($users)) {
    $filteredUsers = [];
    foreach ($users as $user) {
        if (isset($user['role']) && $user['role'] === $roleFilter) {
            $filteredUsers[] = $user;
        }
    }
    $users = $filteredUsers;
}

$pageTitle = 'User Management';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header page-header-flex">
    <div>
        <h1>👥 User Management</h1>
        <p>View, add, and manage system users</p>
    </div>
    <a href="<?= APP_URL ?>/pages/admin/add_user.php" class="btn btn-primary" id="add-user-btn">+ Add User</a>
</div>

<div class="search-bar" id="user-search">
    <form method="GET" action="" style="display:flex; gap:0.5rem; flex:1;">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
               value="<?= sanitize($searchKeyword) ?>" id="search-input">
        
        <select name="role" class="form-control" style="max-width:160px;" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="doctor" <?= $roleFilter === 'doctor' ? 'selected' : '' ?>>Doctor</option>
            <option value="patient" <?= $roleFilter === 'patient' ? 'selected' : '' ?>>Patient</option>
        </select>
        
        <button type="submit" class="btn btn-outline">Search</button>
        
        <?php if (!empty($searchKeyword) || !empty($roleFilter)): ?>
            <a href="<?= APP_URL ?>/pages/admin/users.php" class="btn btn-danger" style="text-decoration:none; display:flex; align-items:center;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <?php if (!empty($users)): ?>
        <div class="table-wrapper">
            <table id="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?= (int)$user['id'] ?></td>
                            <td><?= sanitize($user['name']) ?></td>
                            <td><?= sanitize($user['email']) ?></td>
                            <td><span class="user-badge role-<?= sanitize($user['role']) ?>"><?= sanitize(ucfirst($user['role'])) ?></span></td>
                            <td><?= sanitize($user['phone'] ?? '—') ?></td>
                            <td><?= formatDate($user['created_at']) ?></td>
                            <td>
                                <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="delete_user_id" value="<?= (int)$user['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-confirm="Are you sure you want to delete this user? All their records will be deleted too.">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-info">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <h3>No users found</h3>
            <p>Try a different search or change filters.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>