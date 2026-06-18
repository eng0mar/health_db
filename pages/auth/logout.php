<?php
// ============================================
// Logout - bey-destroy el session w redirect le login
// ============================================
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../../classes/Admin.php';
require_once __DIR__ . '/../../includes/helpers.php';

User::logout();
setFlash('success', 'You have been logged out successfully.');
redirect(APP_URL . '/pages/auth/login.php');
