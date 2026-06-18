<?php
// ============================================
// Doctor Patients - List + Search (Bonus +2)
// ============================================
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../classes/Doctor.php';

Middleware::requireRole('doctor');

$doctor = new Doctor((int)$_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);

$searchKeyword = trim($_GET['search'] ?? '');
if ($searchKeyword) {
    $patients = $doctor->searchPatients($searchKeyword);
} else {
    $patients = $doctor->getMyPatients();
}

$pageTitle = 'My Patients';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header page-header-flex">
    <div><h1>🩺 My Patients</h1><p>Patients you have treated</p></div>
    <a href="<?= APP_URL ?>/pages/doctor/add_record.php" class="btn btn-primary">+ New Record</a>
</div>

<div class="search-bar">
    <form method="GET" action="" style="display:flex;gap:0.5rem;flex:1;">
        <input type="text" name="search" class="form-control" placeholder="Search patients by name or email..." value="<?= sanitize($searchKeyword) ?>">
        <button type="submit" class="btn btn-outline">Search</button>
        <?php if ($searchKeyword): ?><a href="<?= APP_URL ?>/pages/doctor/patients.php" class="btn btn-outline">Clear</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <?php if (!empty($patients)): ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td><?= sanitize($p['name']) ?></td>
                            <td><?= sanitize($p['email']) ?></td>
                            <td><?= sanitize($p['phone'] ?? '—') ?></td>
                            <td><a href="<?= APP_URL ?>/pages/doctor/records.php?patient_id=<?= (int)$p['id'] ?>" class="btn btn-outline btn-sm">View Records</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state"><div class="empty-state-icon">🩺</div><h3><?= $searchKeyword ? 'No results' : 'No patients yet' ?></h3><p><?= $searchKeyword ? 'Try a different search term.' : 'Add a medical record to start.' ?></p></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
