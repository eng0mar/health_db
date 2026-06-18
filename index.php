<?php
// ============================================
// Index - Entry point, redirect based on role
// ============================================
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    $dashboards = [
        'admin'   => APP_URL . '/pages/admin/dashboard.php',
        'doctor'  => APP_URL . '/pages/doctor/dashboard.php',
        'patient' => APP_URL . '/pages/patient/dashboard.php',
    ];
    redirect($dashboards[$role] ?? APP_URL . '/pages/auth/login.php');
} else {
    redirect(APP_URL . '/pages/auth/login.php');
}
