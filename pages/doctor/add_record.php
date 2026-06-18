<?php
// ============================================
// Doctor Add Record - Searchable Patient Dropdown (No Style Changes)
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Doctor.php';
require_once __DIR__ . '/../../includes/helpers.php';

Middleware::requireRole('doctor');

$doctor = new Doctor((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
$patients = $doctor->getAllPatients();
$errors = [];
$preSelectedPatient = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = (int)($_POST['patient_id'] ?? 0);
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $notes     = trim($_POST['notes'] ?? '');
    $visitDate = $_POST['visit_date'] ?? '';

    if (!$patientId) $errors[] = 'Patient is required.';
    if (empty($diagnosis)) $errors[] = 'Diagnosis is required.';
    if (empty($visitDate)) $errors[] = 'Visit date is required.';

    if (empty($errors)) {
        if ($doctor->addRecord($patientId, $diagnosis, $notes ?: null, $visitDate)) {
            setFlash('success', 'Medical record added successfully.');
            redirect(APP_URL . '/pages/doctor/records.php?patient_id=' . $patientId);
        } else {
            $errors[] = 'Failed to add record.';
        }
    }
}

$pageTitle = 'Add Medical Record';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>➕ Add Medical Record</h1>
    <p>Create a new medical record for a patient</p>
</div>

<div class="card" style="max-width:650px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><span class="alert-icon">✕</span><span class="alert-message"><?php foreach ($errors as $e) echo sanitize($e) . '<br>'; ?></span></div>
    <?php endif; ?>

    <?php if (empty($patients)): ?>
        <div class="empty-state"><div class="empty-state-icon">👥</div><h3>No patients in the system</h3><p>Ask an admin to add patients first.</p></div>
    <?php else: ?>
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group" style="flex: 1; position: relative;">
                    <label class="form-label" for="patient_id">Patient</label>
                    
                    <input type="text" id="patient_search" class="form-control" placeholder="🔍 Type name or phone to filter..." style="margin-bottom: 5px; height: 35px; font-size: 0.9rem;">
                    
                    <select id="patient_id" name="patient_id" class="form-control" size="5" style="height: auto; max-height: 160px;" required>
                        <option value="" style="font-weight: bold; color: #888;">-- Select patient from filtered list --</option>
                        <?php foreach ($patients as $p): ?>
                            <option value="<?= (int)$p['id'] ?>" <?= ($preSelectedPatient === (int)$p['id'] || ($patientId ?? 0) === (int)$p['id']) ? 'selected' : '' ?>>
                                <?= sanitize($p['name']) ?> (<?= sanitize($p['phone'] ?? 'No Phone') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="visit_date">Visit Date</label>
                    <input type="date" id="visit_date" name="visit_date" class="form-control" value="<?= sanitize($visitDate ?? date('Y-m-d')) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="diagnosis">Diagnosis</label>
                <textarea id="diagnosis" name="diagnosis" class="form-control" required placeholder="Enter diagnosis details..."><?= sanitize($diagnosis ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="notes">Notes (Optional)</label>
                <textarea id="notes" name="notes" class="form-control" placeholder="Additional notes..."><?= sanitize($notes ?? '') ?></textarea>
            </div>
            <div class="flex-between mt-2">
                <a href="<?= APP_URL ?>/pages/doctor/records.php" class="btn btn-outline">← Back</a>
                <button type="submit" class="btn btn-primary">Save Record</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
document.getElementById('patient_search').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let select = document.getElementById('patient_id');
    let options = select.options;

    for (let i = 1; i < options.length; i++) {
        let text = options[i].text.toLowerCase();
        if (text.includes(filter)) {
            options[i].style.display = "";
        } else {
            options[i].style.display = "none";
        }
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>