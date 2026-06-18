<?php
// ============================================
// MedicalRecord Class - Entity class le medical records
// CRUD methods with role checks
// ============================================

require_once __DIR__ . '/Database.php';

class MedicalRecord {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ============================================
    // create - bey-create record gedid
    // ============================================
    public function create(int $patientId, int $doctorId, string $diagnosis, ?string $notes, string $visitDate): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO medical_records (patient_id, doctor_id, diagnosis, notes, visit_date)
             VALUES (:patient_id, :doctor_id, :diagnosis, :notes, :visit_date)"
        );
        return $stmt->execute([
            ':patient_id' => $patientId,
            ':doctor_id'  => $doctorId,
            ':diagnosis'  => $diagnosis,
            ':notes'      => $notes,
            ':visit_date' => $visitDate,
        ]);
    }

    // ============================================
    // update - bey-update record
    // ============================================
    public function update(int $id, string $diagnosis, ?string $notes): bool {
        $stmt = $this->db->prepare(
            "UPDATE medical_records SET diagnosis = :diagnosis, notes = :notes WHERE id = :id"
        );
        return $stmt->execute([
            ':diagnosis' => $diagnosis,
            ':notes'     => $notes,
            ':id'        => $id,
        ]);
    }

    // ============================================
    // getByPatient - records beta3 patient mo3ayan
    // ============================================
    public function getByPatient(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT mr.*, d.name as doctor_name
             FROM medical_records mr
             JOIN users d ON mr.doctor_id = d.id
             WHERE mr.patient_id = :patient_id
             ORDER BY mr.visit_date DESC"
        );
        $stmt->execute([':patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getByDoctor - records beta3 doctor mo3ayan
    // ============================================
    public function getByDoctor(int $doctorId): array {
        $stmt = $this->db->prepare(
            "SELECT mr.*, u.name as patient_name
             FROM medical_records mr
             JOIN users u ON mr.patient_id = u.id
             WHERE mr.doctor_id = :doctor_id
             ORDER BY mr.visit_date DESC"
        );
        $stmt->execute([':doctor_id' => $doctorId]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getById - record wa7ed bel ID
    // ============================================
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT mr.*, 
                    p.name as patient_name, p.email as patient_email,
                    d.name as doctor_name
             FROM medical_records mr
             JOIN users p ON mr.patient_id = p.id
             JOIN users d ON mr.doctor_id = d.id
             WHERE mr.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    // ============================================
    // delete - bey-delete record
    // ============================================
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM medical_records WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
