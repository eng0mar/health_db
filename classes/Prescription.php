<?php
// ============================================
// Prescription Class - Entity class le prescriptions
// Kol prescription marbout b medical record
// ============================================

require_once __DIR__ . '/Database.php';

class Prescription {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ============================================
    // add - bey-add prescription le record
    // ============================================
    public function add(int $recordId, string $medicationName, string $dosage, ?string $instructions = null): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO prescriptions (record_id, medication_name, dosage, instructions)
             VALUES (:record_id, :medication_name, :dosage, :instructions)"
        );
        return $stmt->execute([
            ':record_id'       => $recordId,
            ':medication_name' => $medicationName,
            ':dosage'          => $dosage,
            ':instructions'    => $instructions,
        ]);
    }

    // ============================================
    // getByRecord - prescriptions le record wa7ed
    // ============================================
    public function getByRecord(int $recordId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM prescriptions WHERE record_id = :record_id ORDER BY prescribed_at DESC"
        );
        $stmt->execute([':record_id' => $recordId]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getByPatient - kol prescriptions le patient
    // Through medical records
    // ============================================
    public function getByPatient(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT p.*, mr.diagnosis, mr.visit_date, d.name as doctor_name
             FROM prescriptions p
             JOIN medical_records mr ON p.record_id = mr.id
             JOIN users d ON mr.doctor_id = d.id
             WHERE mr.patient_id = :patient_id
             ORDER BY p.prescribed_at DESC"
        );
        $stmt->execute([':patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    // ============================================
    // delete - bey-delete prescription
    // ============================================
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM prescriptions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
