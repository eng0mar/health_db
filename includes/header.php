<?php
// ============================================
// Header Template - HTML head + Navigation
// Included fe kol page
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? '';
$userName = $_SESSION['user_name'] ?? '';
$pageTitle = $pageTitle ?? 'National Health DB';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="National Health Database System - Secure medical records management">
    <title><?= sanitize($pageTitle) ?> — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="main-navbar">
        <div class="nav-container">
            <a href="<?= APP_URL ?>" class="nav-brand" id="nav-brand">
                <span class="brand-icon">🏥</span>
                <span class="brand-text"><?= APP_NAME ?></span>
            </a>

            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>

            <div class="nav-menu" id="nav-menu">
                <?php if ($isLoggedIn): ?>
                    <?php if ($userRole === 'admin'): ?>
                        <a href="<?= APP_URL ?>/pages/admin/dashboard.php" class="nav-link <?= isCurrentPage('dashboard.php') ? 'active' : '' ?>" id="nav-admin-dashboard">
                            <span class="nav-icon">📊</span> Dashboard
                        </a>
                        <a href="<?= APP_URL ?>/pages/admin/users.php" class="nav-link <?= isCurrentPage('users.php') ? 'active' : '' ?>" id="nav-admin-users">
                            <span class="nav-icon">👥</span> Users
                        </a>
                    <?php elseif ($userRole === 'doctor'): ?>
                        <a href="<?= APP_URL ?>/pages/doctor/dashboard.php" class="nav-link <?= isCurrentPage('dashboard.php') ? 'active' : '' ?>" id="nav-doctor-dashboard">
                            <span class="nav-icon">📊</span> Dashboard
                        </a>
                        <a href="<?= APP_URL ?>/pages/doctor/patients.php" class="nav-link <?= isCurrentPage('patients.php') ? 'active' : '' ?>" id="nav-doctor-patients">
                            <span class="nav-icon">🩺</span> My Patients
                        </a>
                        <a href="<?= APP_URL ?>/pages/doctor/records.php" class="nav-link <?= isCurrentPage('records.php') ? 'active' : '' ?>" id="nav-doctor-records">
                            <span class="nav-icon">📋</span> Records
                        </a>
                    <?php elseif ($userRole === 'patient'): ?>
                        <a href="<?= APP_URL ?>/pages/patient/dashboard.php" class="nav-link <?= isCurrentPage('dashboard.php') ? 'active' : '' ?>" id="nav-patient-dashboard">
                            <span class="nav-icon">📊</span> Dashboard
                        </a>
                        <a href="<?= APP_URL ?>/pages/patient/records.php" class="nav-link <?= isCurrentPage('records.php') ? 'active' : '' ?>" id="nav-patient-records">
                            <span class="nav-icon">📋</span> My Records
                        </a>
                        <a href="<?= APP_URL ?>/pages/patient/prescriptions.php" class="nav-link <?= isCurrentPage('prescriptions.php') ? 'active' : '' ?>" id="nav-patient-prescriptions">
                            <span class="nav-icon">💊</span> Prescriptions
                        </a>
                        <a href="<?= APP_URL ?>/pages/patient/profile.php" class="nav-link <?= isCurrentPage('profile.php') ? 'active' : '' ?>" id="nav-patient-profile">
                            <span class="nav-icon">👤</span> Profile
                        </a>
                    <?php endif; ?>

                    <div class="nav-user" id="nav-user">
                        <span class="user-badge role-<?= sanitize($userRole) ?>"><?= sanitize(ucfirst($userRole)) ?></span>
                        <span class="user-name"><?= sanitize($userName) ?></span>
                        <a href="<?= APP_URL ?>/pages/auth/logout.php" class="nav-link logout-link" id="nav-logout">
                            <span class="nav-icon">🚪</span> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/pages/auth/login.php" class="nav-link <?= isCurrentPage('login.php') ? 'active' : '' ?>" id="nav-login">Login</a>
                    <a href="<?= APP_URL ?>/pages/auth/register.php" class="nav-link btn-nav <?= isCurrentPage('register.php') ? 'active' : '' ?>" id="nav-register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="main-content">
        <div class="container">
            <?= displayFlashMessages() ?>
