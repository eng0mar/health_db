<?php
// ============================================
// Middleware Class - RBAC Permission Checker (Bonus +2)
// Bey-check el permissions abl ma ay action ye7sal
// ============================================

require_once __DIR__ . '/Database.php';

class Middleware {

    // ============================================
    // requireLogin - lazem el user ykon logged in
    // ============================================
    public static function requireLogin(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'You must be logged in to access this page.';
            header('Location: ' . APP_URL . '/pages/auth/login.php');
            exit;
        }
    }

    // ============================================
    // requireRole - lazem el user ykon role mo3ayan
    // Accepts string aw array of roles
    // ============================================
    public static function requireRole($roles): void {
        self::requireLogin();

        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (!in_array($_SESSION['user_role'], $roles)) {
            $_SESSION['flash_error'] = 'You do not have permission to access this page.';
            // Redirect le el dashboard beta3 el role beta3o
            self::redirectToDashboard();
            exit;
        }
    }

    // ============================================
    // requireOwnership - lazem el record ykon beta3 el user da
    // Bey-verify en el patient_id aw doctor_id match el session
    // ============================================
    public static function requireOwnership(int $recordId, string $ownerField = 'patient_id'): void {
        self::requireLogin();

        // Whitelist el column names 3ashan ne-prevent SQL injection
        $allowedFields = ['patient_id', 'doctor_id'];
        if (!in_array($ownerField, $allowedFields)) {
            $ownerField = 'patient_id';
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT {$ownerField} FROM medical_records WHERE id = :id");
        $stmt->execute([':id' => $recordId]);
        $record = $stmt->fetch();

        if (!$record || (int)$record[$ownerField] !== (int)$_SESSION['user_id']) {
            // Admin y2dar yshoof kol 7aga
            if ($_SESSION['user_role'] !== 'admin') {
                $_SESSION['flash_error'] = 'You do not have permission to access this record.';
                self::redirectToDashboard();
                exit;
            }
        }
    }

    // ============================================
    // canAccessRecord - check law el user y2dar yshoof el record da
    // Returns true/false men gher redirect
    // ============================================
    public static function canAccessRecord(int $recordId): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Admin y2dar yshoof kol 7aga
        if ($_SESSION['user_role'] === 'admin') {
            return true;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT patient_id, doctor_id FROM medical_records WHERE id = :id");
        $stmt->execute([':id' => $recordId]);
        $record = $stmt->fetch();

        if (!$record) {
            return false;
        }

        $userId = (int)$_SESSION['user_id'];
        $role = $_SESSION['user_role'];

        // Doctor yshoof bs el records beto3o
        if ($role === 'doctor' && (int)$record['doctor_id'] === $userId) {
            return true;
        }

        // Patient yshoof bs el records beto3o
        if ($role === 'patient' && (int)$record['patient_id'] === $userId) {
            return true;
        }

        return false;
    }

    // ============================================
    // redirectToDashboard - ye7awel el user le el dashboard beta3o
    // ============================================
    public static function redirectToDashboard(): void {
        $role = $_SESSION['user_role'] ?? 'patient';
        $dashboards = [
            'admin'   => APP_URL . '/pages/admin/dashboard.php',
            'doctor'  => APP_URL . '/pages/doctor/dashboard.php',
            'patient' => APP_URL . '/pages/patient/dashboard.php',
        ];
        $url = $dashboards[$role] ?? APP_URL . '/pages/auth/login.php';
        header('Location: ' . $url);
        exit;
    }

    // ============================================
    // isLoggedIn - check law el user logged in wala la2
    // ============================================
    public static function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    // ============================================
    // getCurrentRole - get el role beta3 el current user
    // ============================================
    public static function getCurrentRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }
}
