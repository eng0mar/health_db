<?php
// ============================================
// Admin Class - extends User
// Bey-manage kol el users (doctors + patients)
// ============================================

require_once __DIR__ . '/User.php';

class Admin extends User {

    public function __construct(?int $id = null, string $name = '', string $email = '') {
        parent::__construct($id, $name, $email, 'admin');
    }

    // ============================================
    // getUsers - bey-geeb kol el users
    // Optional: filter by role
    // ============================================
    public function getUsers(?string $roleFilter = null): array {
        if ($roleFilter) {
            $stmt = $this->db->prepare("SELECT id, name, email, role, phone, created_at FROM users WHERE role = :role ORDER BY created_at DESC");
            $stmt->execute([':role' => $roleFilter]);
        } else {
            $stmt = $this->db->prepare("SELECT id, name, email, role, phone, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    // ============================================
    // addDoctor - bey-add doctor gedid
    // ============================================
    public function addDoctor(string $name, string $email, string $password, ?string $phone = null): bool {
        return $this->register($name, $email, $password, 'doctor', $phone);
    }

    // ============================================
    // addPatient - bey-add patient gedid
    // ============================================
    public function addPatient(string $name, string $email, string $password, ?string $phone = null): bool {
        return $this->register($name, $email, $password, 'patient', $phone);
    }

    // ============================================
    // deleteUser - bey-delete user bs mesh nafs el admin
    // Ma-yen3fash admin ye-delete nafsoh
    // ============================================
    public function deleteUser(int $userId): bool {
        // Check en msh bey-delete nafsoh
        if ($userId === $this->id) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $userId]);
    }

    // ============================================
    // searchUsers - search by name aw email (Bonus)
    // ============================================
    public function searchUsers(string $keyword): array {
        $searchTerm = '%' . $keyword . '%';
        $stmt = $this->db->prepare(
            "SELECT id, name, email, role, phone, created_at FROM users WHERE name LIKE :keyword OR email LIKE :keyword2 ORDER BY created_at DESC"
        );
        $stmt->execute([':keyword' => $searchTerm, ':keyword2' => $searchTerm]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getDashboardData - Polymorphism (Bonus +3)
    // Admin dashboard: total counts le kol 7aga
    // ============================================
    public function getDashboardData(): array {
        $data = [];

        // Total users count
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users");
        $stmt->execute();
        $data['total_users'] = $stmt->fetch()['total'];

        // Count per role
        $stmt = $this->db->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        $stmt->execute();
        $roleCounts = $stmt->fetchAll();
        $data['role_counts'] = [];
        foreach ($roleCounts as $rc) {
            $data['role_counts'][$rc['role']] = $rc['count'];
        }

        // Total medical records
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM medical_records");
        $stmt->execute();
        $data['total_records'] = $stmt->fetch()['total'];

        // Total prescriptions
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM prescriptions");
        $stmt->execute();
        $data['total_prescriptions'] = $stmt->fetch()['total'];

        // Recent records (last 5)
        $stmt = $this->db->prepare(
            "SELECT mr.id, mr.diagnosis, mr.visit_date, 
                    p.name as patient_name, d.name as doctor_name
             FROM medical_records mr
             JOIN users p ON mr.patient_id = p.id
             JOIN users d ON mr.doctor_id = d.id
             ORDER BY mr.created_at DESC LIMIT 5"
        );
        $stmt->execute();
        $data['recent_records'] = $stmt->fetchAll();

        return $data;
    }
}
