<?php
// ============================================
// Doctor Records - View records for a patient
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Doctor.php';
require_once __DIR__ . '/../../classes/MedicalRecord.php';

Middleware::requireRole('doctor');

$doctor = new Doctor((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

if ($patientId) {
    $records = $doctor->getPatientRecords($patientId);
    $patientInfo = User::getUserById($patientId);
} else {
    // Show all records by this doctor
    $recordModel = new MedicalRecord();
    $records = $recordModel->getByDoctor((int)$_SESSION['user_id']);
    $patientInfo = null;
}

$pageTitle = $patientInfo ? 'Records: ' . $patientInfo['name'] : 'All Records';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header page-header-flex">
    <div>
        <h1>📋 <?= $patientInfo ? 'Records for ' . sanitize($patientInfo['name']) : 'All My Records' ?></h1>
        <p>Medical records <?= $patientInfo ? 'of this patient' : 'you have created' ?></p>
    </div>
    <a href="<?= APP_URL ?>/pages/doctor/add_record.php<?= $patientId ? '?patient_id=' . $patientId : '' ?>" class="btn btn-primary">+ New Record</a>
</div>

<div class="card">
    <?php if (!empty($records)): ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Date</th><th>Patient</th><th>Diagnosis</th><th>Notes</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><?= formatDate($r['visit_date']) ?></td>
                            <td><?= sanitize($r['patient_name']) ?></td>
                            <td><?= sanitize(substr($r['diagnosis'], 0, 50)) ?></td>
                            <td><?= sanitize(substr($r['notes'] ?? '', 0, 40)) ?></td>
                            <td class="table-actions">
                                <a href="<?= APP_URL ?>/pages/doctor/edit_record.php?id=<?= (int)$r['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="<?= APP_URL ?>/pages/doctor/prescriptions.php?record_id=<?= (int)$r['id'] ?>" class="btn btn-outline btn-sm">Rx</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state"><div class="empty-state-icon">📋</div><h3>No records</h3></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
