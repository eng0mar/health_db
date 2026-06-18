<?php
// ============================================
// Doctor Dashboard - Stats + recent records (Bonus +3)
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Doctor.php';

Middleware::requireRole('doctor');

$doctor = new Doctor((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$data = $doctor->getDashboardData();

$pageTitle = 'Doctor Dashboard';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>📊 Doctor Dashboard</h1>
    <p>Welcome back, Dr. <?= sanitize($_SESSION['user_name']) ?></p>
</div>

<div class="stats-grid">
    <div class="stat-card"><span class="stat-icon">🧑‍🤝‍🧑</span><div class="stat-info"><div class="stat-value"><?= (int)$data['my_patients_count'] ?></div><div class="stat-label">My Patients</div></div></div>
    <div class="stat-card"><span class="stat-icon">📋</span><div class="stat-info"><div class="stat-value"><?= (int)$data['my_records_count'] ?></div><div class="stat-label">Medical Records</div></div></div>
    <div class="stat-card"><span class="stat-icon">💊</span><div class="stat-info"><div class="stat-value"><?= (int)$data['my_prescriptions_count'] ?></div><div class="stat-label">Prescriptions</div></div></div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Recent Records</h2>
        <a href="<?= APP_URL ?>/pages/doctor/add_record.php" class="btn btn-primary btn-sm">+ New Record</a>
    </div>
    <?php if (!empty($data['recent_records'])): ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Patient</th><th>Diagnosis</th><th>Visit Date</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($data['recent_records'] as $r): ?>
                        <tr>
                            <td><?= sanitize($r['patient_name']) ?></td>
                            <td><?= sanitize(substr($r['diagnosis'], 0, 60)) ?></td>
                            <td><?= formatDate($r['visit_date']) ?></td>
                            <td>
                                <a href="<?= APP_URL ?>/pages/doctor/edit_record.php?id=<?= (int)$r['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="<?= APP_URL ?>/pages/doctor/prescriptions.php?record_id=<?= (int)$r['id'] ?>" class="btn btn-outline btn-sm">Rx</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state"><div class="empty-state-icon">📋</div><h3>No records yet</h3><p>Start by adding a medical record.</p></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
