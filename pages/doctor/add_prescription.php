<?php
// ============================================
// Doctor Add Prescription - le record mo3ayan
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

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medication   = trim($_POST['medication_name'] ?? '');
    $dosage       = trim($_POST['dosage'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    if (empty($medication)) $errors[] = 'Medication name is required.';
    if (empty($dosage)) $errors[] = 'Dosage is required.';

    if (empty($errors)) {
        if ($doctor->addPrescription($recordId, $medication, $dosage, $instructions ?: null)) {
            setFlash('success', 'Prescription added successfully.');
            redirect(APP_URL . '/pages/doctor/prescriptions.php?record_id=' . $recordId);
        } else {
            $errors[] = 'Failed to add prescription.';
        }
    }
}

$pageTitle = 'Add Prescription';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header"><h1>💊 Add Prescription</h1><p>For <?= sanitize($record['patient_name']) ?> — <?= formatDate($record['visit_date']) ?></p></div>

<div class="card" style="max-width:600px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><span class="alert-icon">✕</span><span class="alert-message"><?php foreach ($errors as $e) echo sanitize($e) . '<br>'; ?></span></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label" for="medication_name">Medication Name</label>
            <input type="text" id="medication_name" name="medication_name" class="form-control" value="<?= sanitize($medication ?? '') ?>" required placeholder="e.g. Amoxicillin">
        </div>
        <div class="form-group">
            <label class="form-label" for="dosage">Dosage</label>
            <input type="text" id="dosage" name="dosage" class="form-control" value="<?= sanitize($dosage ?? '') ?>" required placeholder="e.g. 500mg 3x daily">
        </div>
        <div class="form-group">
            <label class="form-label" for="instructions">Instructions (Optional)</label>
            <textarea id="instructions" name="instructions" class="form-control" placeholder="Take after meals..."><?= sanitize($instructions ?? '') ?></textarea>
        </div>
        <div class="flex-between mt-2">
            <a href="<?= APP_URL ?>/pages/doctor/prescriptions.php?record_id=<?= $recordId ?>" class="btn btn-outline">← Back</a>
            <button type="submit" class="btn btn-primary">Add Prescription</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
