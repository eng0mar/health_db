<?php
// ============================================
// Doctor Prescriptions - View + Add for a record
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Doctor.php';

Middleware::requireRole('doctor');

$doctor = new Doctor((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$recordId = isset($_GET['record_id']) ? (int)$_GET['record_id'] : 0;

if (!$recordId) {
    setFlash('error', 'Invalid record.');
    redirect(APP_URL . '/pages/doctor/records.php');
}

$record = $doctor->getRecordById($recordId);
if (!$record) {
    setFlash('error', 'Record not found or access denied.');
    redirect(APP_URL . '/pages/doctor/records.php');
}

$prescriptions = $doctor->getPrescriptionsByRecord($recordId);
$pageTitle = 'Prescriptions';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header page-header-flex">
    <div>
        <h1>💊 Prescriptions</h1>
        <p>For <?= sanitize($record['patient_name']) ?> — <?= formatDate($record['visit_date']) ?></p>
    </div>
    <a href="<?= APP_URL ?>/pages/doctor/add_prescription.php?record_id=<?= $recordId ?>" class="btn btn-primary">+ Add Prescription</a>
</div>

<div class="card mb-2">
    <div class="card-header"><h2 class="card-title">Record Details</h2></div>
    <p><strong>Diagnosis:</strong> <?= sanitize($record['diagnosis']) ?></p>
    <?php if ($record['notes']): ?><p class="mt-1"><strong>Notes:</strong> <?= sanitize($record['notes']) ?></p><?php endif; ?>
</div>

<div class="card">
    <div class="card-header"><h2 class="card-title">Prescriptions</h2></div>
    <?php if (!empty($prescriptions)): ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Medication</th><th>Dosage</th><th>Instructions</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($prescriptions as $rx): ?>
                        <tr>
                            <td><strong><?= sanitize($rx['medication_name']) ?></strong></td>
                            <td><?= sanitize($rx['dosage']) ?></td>
                            <td><?= sanitize($rx['instructions'] ?? '—') ?></td>
                            <td><?= formatDate($rx['prescribed_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state"><div class="empty-state-icon">💊</div><h3>No prescriptions</h3><p>Add a prescription for this record.</p></div>
    <?php endif; ?>
</div>

<div class="mt-2"><a href="<?= APP_URL ?>/pages/doctor/records.php?patient_id=<?= (int)$record['patient_id'] ?>" class="btn btn-outline">← Back to Records</a></div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
