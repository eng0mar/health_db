<?php
// ============================================
// Helper Functions
// Utility functions used across el app
// ============================================

// ============================================
// sanitize - bey-clean el output le XSS prevention
// ============================================
function sanitize(string $data): string {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// ============================================
// redirect - bey-redirect le URL tany
// ============================================
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ============================================
// setFlash - bey-set flash message fe session
// Type: success, error, warning, info
// ============================================
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_' . $type] = $message;
}

// ============================================
// getFlash - bey-geeb w bey-delete flash message
// Returns el message w bey-unset it
// ============================================
function getFlash(string $type): ?string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}

// ============================================
// displayFlashMessages - bey-3rad kol el flash messages
// ============================================
function displayFlashMessages(): string {
    $html = '';
    $types = ['success', 'error', 'warning', 'info'];
    
    foreach ($types as $type) {
        $message = getFlash($type);
        if ($message) {
            $iconMap = [
                'success' => '✓',
                'error'   => '✕',
                'warning' => '⚠',
                'info'    => 'ℹ',
            ];
            $icon = $iconMap[$type] ?? '';
            $html .= '<div class="alert alert-' . $type . '" id="flash-' . $type . '">';
            $html .= '<span class="alert-icon">' . $icon . '</span>';
            $html .= '<span class="alert-message">' . sanitize($message) . '</span>';
            $html .= '<button class="alert-close" onclick="this.parentElement.remove()">×</button>';
            $html .= '</div>';
        }
    }
    
    return $html;
}

// ============================================
// validateRequired - bey-check en kol el fields filled
// Returns array of errors aw empty array
// ============================================
function validateRequired(array $fields, array $data): array {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    return $errors;
}

// ============================================
// validateEmail - bey-validate email format
// ============================================
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ============================================
// validatePassword - minimum 6 characters
// ============================================
function validatePassword(string $password): bool {
    return strlen($password) >= 6;
}

// ============================================
// isCurrentPage - bey-check law el page da active
// Used le navbar active state
// ============================================
function isCurrentPage(string $page): bool {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $page;
}

// ============================================
// formatDate - bey-format el date
// ============================================
function formatDate(string $date, string $format = 'M d, Y'): string {
    return date($format, strtotime($date));
}

// ============================================
// getTimeAgo - bey-7aseb el time ago
// ============================================
function getTimeAgo(string $datetime): string {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}
