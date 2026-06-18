<?php
// ============================================
// Patient Prescriptions - View all prescriptions
// BS beta3to - SECURITY filtered by patient_id
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Patient.php';

Middleware::requireRole('patient');

$patient = new Patient((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$prescriptions = $patient->getMyPrescriptions();

$pageTitle = 'My Prescriptions';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header"><h1>💊 My Prescriptions</h1><p>All prescribed medications</p></div>

<div class="card">
    <?php if (!empty($prescriptions)): ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Medication</th><th>Dosage</th><th>Instructions</th><th>Doctor</th><th>Visit Date</th><th>Prescribed</th></tr></thead>
                <tbody>
                    <?php foreach ($prescriptions as $rx): ?>
                        <tr>
                            <td><strong><?= sanitize($rx['medication_name']) ?></strong></td>
                            <td><?= sanitize($rx['dosage']) ?></td>
                            <td><?= sanitize($rx['instructions'] ?? '—') ?></td>
                            <td>Dr. <?= sanitize($rx['doctor_name']) ?></td>
                            <td><?= formatDate($rx['visit_date']) ?></td>
                            <td><?= formatDate($rx['prescribed_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state"><div class="empty-state-icon">💊</div><h3>No prescriptions</h3><p>Prescriptions will appear here once a doctor adds them.</p></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
