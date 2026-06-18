<?php
// ============================================
// Admin Dashboard - Statistics overview (Bonus +3)
// Total counts le users, records, prescriptions
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Admin.php';

Middleware::requireRole('admin');

$admin = new Admin((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$data = $admin->getDashboardData();

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>📊 Admin Dashboard</h1>
    <p>System overview and statistics</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card" id="stat-total-users">
        <span class="stat-icon">👥</span>
        <div class="stat-info">
            <div class="stat-value"><?= (int)$data['total_users'] ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>
    <div class="stat-card" id="stat-doctors">
        <span class="stat-icon">🩺</span>
        <div class="stat-info">
            <div class="stat-value"><?= (int)($data['role_counts']['doctor'] ?? 0) ?></div>
            <div class="stat-label">Doctors</div>
        </div>
    </div>
    <div class="stat-card" id="stat-patients">
        <span class="stat-icon">🧑‍🤝‍🧑</span>
        <div class="stat-info">
            <div class="stat-value"><?= (int)($data['role_counts']['patient'] ?? 0) ?></div>
            <div class="stat-label">Patients</div>
        </div>
    </div>
    <div class="stat-card" id="stat-records">
        <span class="stat-icon">📋</span>
        <div class="stat-info">
            <div class="stat-value"><?= (int)$data['total_records'] ?></div>
            <div class="stat-label">Medical Records</div>
        </div>
    </div>
    <div class="stat-card" id="stat-prescriptions">
        <span class="stat-icon">💊</span>
        <div class="stat-info">
            <div class="stat-value"><?= (int)$data['total_prescriptions'] ?></div>
            <div class="stat-label">Prescriptions</div>
        </div>
    </div>
</div>

<!-- Recent Records -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Recent Medical Records</h2>
        <a href="<?= APP_URL ?>/pages/admin/users.php" class="btn btn-outline btn-sm">Manage Users →</a>
    </div>

    <?php if (!empty($data['recent_records'])): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Diagnosis</th>
                        <th>Visit Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['recent_records'] as $record): ?>
                        <tr>
                            <td><?= sanitize($record['patient_name']) ?></td>
                            <td><?= sanitize($record['doctor_name']) ?></td>
                            <td><?= sanitize(substr($record['diagnosis'], 0, 50)) ?><?= strlen($record['diagnosis']) > 50 ? '...' : '' ?></td>
                            <td><?= formatDate($record['visit_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>No records yet</h3>
            <p>Medical records will appear here once doctors start adding them.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
