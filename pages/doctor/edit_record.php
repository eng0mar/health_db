<?php
// ============================================
// Doctor Edit Record - Update diagnosis
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Doctor.php';

Middleware::requireRole('doctor');

$doctor = new Doctor((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$recordId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$recordId) {
    setFlash('error', 'Invalid record.');
    redirect(APP_URL . '/pages/doctor/records.php');
}

$record = $doctor->getRecordById($recordId);
if (!$record) {
    setFlash('error', 'Record not found or access denied.');
    redirect(APP_URL . '/pages/doctor/records.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $notes     = trim($_POST['notes'] ?? '');

    if (empty($diagnosis)) $errors[] = 'Diagnosis is required.';

    if (empty($errors)) {
        if ($doctor->updateDiagnosis($recordId, $diagnosis, $notes ?: null)) {
            setFlash('success', 'Record updated successfully.');
            redirect(APP_URL . '/pages/doctor/records.php?patient_id=' . $record['patient_id']);
        } else {
            $errors[] = 'Failed to update record.';
        }
    }
}

$pageTitle = 'Edit Record';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header"><h1>✏️ Edit Medical Record</h1><p>Update diagnosis for <?= sanitize($record['patient_name']) ?></p></div>

<div class="card" style="max-width:650px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><span class="alert-icon">✕</span><span class="alert-message"><?php foreach ($errors as $e) echo sanitize($e) . '<br>'; ?></span></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Patient</label>
                <input type="text" class="form-control" value="<?= sanitize($record['patient_name']) ?>" disabled>
            </div>
            <div class="form-group">
                <label class="form-label">Visit Date</label>
                <input type="text" class="form-control" value="<?= formatDate($record['visit_date']) ?>" disabled>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="diagnosis">Diagnosis</label>
            <textarea id="diagnosis" name="diagnosis" class="form-control" required><?= sanitize($record['diagnosis']) ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="notes">Notes</label>
            <textarea id="notes" name="notes" class="form-control"><?= sanitize($record['notes'] ?? '') ?></textarea>
        </div>
        <div class="flex-between mt-2">
            <a href="<?= APP_URL ?>/pages/doctor/records.php?patient_id=<?= (int)$record['patient_id'] ?>" class="btn btn-outline">← Back</a>
            <button type="submit" class="btn btn-primary">Update Record</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
