<?php
// ============================================
// Auth Check - bey-protect el pages ely me7taga login
// Include el file da fe awal kol restricted page
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Middleware.php';
require_once __DIR__ . '/helpers.php';

// Check law el user logged in
Middleware::requireLogin();
