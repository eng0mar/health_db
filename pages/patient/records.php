<?php
// ============================================
// Patient Records - Medical history + Timeline (Bonus +3)
// SECURITY: patient yshoof BS el records beto3o
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Patient.php';
require_once __DIR__ . '/../../classes/Prescription.php';

Middleware::requireRole('patient');

$patient = new Patient((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$timeline = $patient->getTimeline();
$prescriptionModel = new Prescription();

$pageTitle = 'My Medical Records';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header"><h1>📋 My Medical History</h1><p>Timeline of your medical records and treatment progression</p></div>

<?php if (!empty($timeline)): ?>
<div class="timeline" id="medical-timeline">
    <?php foreach ($timeline as $entry): ?>
        <div class="timeline-item">
            <div class="timeline-date"><?= formatDate($entry['visit_date'], 'F j, Y') ?></div>
            <div class="timeline-title"><?= sanitize($entry['diagnosis']) ?></div>
            <div class="timeline-doctor">🩺 Dr. <?= sanitize($entry['doctor_name']) ?></div>
            <?php if ($entry['notes']): ?>
                <div class="timeline-notes">📝 <?= sanitize($entry['notes']) ?></div>
            <?php endif; ?>
            <?php if ((int)$entry['prescription_count'] > 0): ?>
                <?php $rxList = $patient->getRecordPrescriptions((int)$entry['id']); ?>
                <div class="timeline-prescriptions">
                    <strong style="font-size:0.8rem;color:var(--text-secondary);">💊 Prescriptions:</strong>
                    <?php foreach ($rxList as $rx): ?>
                        <div class="timeline-rx">• <?= sanitize($rx['medication_name']) ?> — <?= sanitize($rx['dosage']) ?><?= $rx['instructions'] ? ' (' . sanitize($rx['instructions']) . ')' : '' ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card"><div class="empty-state"><div class="empty-state-icon">📋</div><h3>No medical records</h3><p>Your records will appear once a doctor adds them.</p></div></div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
