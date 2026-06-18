<?php
// ============================================
// Patient Dashboard
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Patient.php';

Middleware::requireRole('patient');

$patient = new Patient((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$data = $patient->getDashboardData();

$pageTitle = 'Patient Dashboard';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header"><h1>📊 My Dashboard</h1><p>Welcome, <?= sanitize($_SESSION['user_name']) ?></p></div>

<div class="stats-grid">
    <div class="stat-card"><span class="stat-icon">📋</span><div class="stat-info"><div class="stat-value"><?= (int)$data['my_records_count'] ?></div><div class="stat-label">Medical Records</div></div></div>
    <div class="stat-card"><span class="stat-icon">💊</span><div class="stat-info"><div class="stat-value"><?= (int)$data['my_prescriptions_count'] ?></div><div class="stat-label">Prescriptions</div></div></div>
    <div class="stat-card"><span class="stat-icon">🩺</span><div class="stat-info"><div class="stat-value"><?= (int)$data['my_doctors_count'] ?></div><div class="stat-label">Doctors</div></div></div>
</div>

<?php if ($data['latest_record']): ?>
<div class="card">
    <div class="card-header"><h2 class="card-title">Latest Visit</h2></div>
    <p><strong>Date:</strong> <?= formatDate($data['latest_record']['visit_date']) ?></p>
    <p><strong>Doctor:</strong> <?= sanitize($data['latest_record']['doctor_name']) ?></p>
    <p><strong>Diagnosis:</strong> <?= sanitize($data['latest_record']['diagnosis']) ?></p>
    <div class="mt-2"><a href="<?= APP_URL ?>/pages/patient/records.php" class="btn btn-outline btn-sm">View All Records →</a></div>
</div>
<?php else: ?>
<div class="card"><div class="empty-state"><div class="empty-state-icon">📋</div><h3>No medical records yet</h3><p>Your records will appear here once a doctor adds them.</p></div></div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
