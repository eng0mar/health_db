<?php
// ============================================
// Patient Class - extends User
// Bey-shoof bs el records w prescriptions beto3o
// Ma y2darsh yshoof 7aga beta3t patient tany - CRITICAL SECURITY
// ============================================

require_once __DIR__ . '/User.php';

class Patient extends User {

    public function __construct(?int $id = null, string $name = '', string $email = '') {
        parent::__construct($id, $name, $email, 'patient');
    }

    // ============================================
    // getMyRecords - bey-geeb bs el records beta3 el patient da
    // SECURITY: patient_id = $_SESSION['user_id'] ALWAYS
    // ============================================
    public function getMyRecords(): array {
        $stmt = $this->db->prepare(
            "SELECT mr.*, d.name as doctor_name
             FROM medical_records mr
             JOIN users d ON mr.doctor_id = d.id
             WHERE mr.patient_id = :patient_id
             ORDER BY mr.visit_date DESC"
        );
        $stmt->execute([':patient_id' => $this->id]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getMyPrescriptions - bey-geeb kol el prescriptions beta3 el patient
    // Through el medical records beto3o BS
    // ============================================
    public function getMyPrescriptions(): array {
        $stmt = $this->db->prepare(
            "SELECT p.*, mr.diagnosis, mr.visit_date, d.name as doctor_name
             FROM prescriptions p
             JOIN medical_records mr ON p.record_id = mr.id
             JOIN users d ON mr.doctor_id = d.id
             WHERE mr.patient_id = :patient_id
             ORDER BY p.prescribed_at DESC"
        );
        $stmt->execute([':patient_id' => $this->id]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getTimeline - (Bonus +3) Medical History Timeline
    // Records sorted by visit_date showing treatment progression
    // ============================================
    public function getTimeline(): array {
        $stmt = $this->db->prepare(
            "SELECT mr.id, mr.diagnosis, mr.notes, mr.visit_date, mr.created_at,
                    d.name as doctor_name,
                    (SELECT COUNT(*) FROM prescriptions WHERE record_id = mr.id) as prescription_count
             FROM medical_records mr
             JOIN users d ON mr.doctor_id = d.id
             WHERE mr.patient_id = :patient_id
             ORDER BY mr.visit_date ASC"
        );
        $stmt->execute([':patient_id' => $this->id]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getRecordPrescriptions - bey-geeb prescriptions le record mo3ayan
    // Lazem el record ykon beta3 el patient da
    // ============================================
    public function getRecordPrescriptions(int $recordId): array {
        // Verify ownership first
        $stmt = $this->db->prepare("SELECT id FROM medical_records WHERE id = :id AND patient_id = :patient_id");
        $stmt->execute([':id' => $recordId, ':patient_id' => $this->id]);
        if (!$stmt->fetch()) {
            return [];  // Msh beta3o - return empty
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM prescriptions WHERE record_id = :record_id ORDER BY prescribed_at DESC"
        );
        $stmt->execute([':record_id' => $recordId]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getDashboardData - Polymorphism
    // Patient dashboard: record count + prescription count
    // ============================================
    public function getDashboardData(): array {
        $data = [];

        // My records count
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM medical_records WHERE patient_id = :patient_id"
        );
        $stmt->execute([':patient_id' => $this->id]);
        $data['my_records_count'] = $stmt->fetch()['total'];

        // My prescriptions count
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM prescriptions p
             JOIN medical_records mr ON p.record_id = mr.id
             WHERE mr.patient_id = :patient_id"
        );
        $stmt->execute([':patient_id' => $this->id]);
        $data['my_prescriptions_count'] = $stmt->fetch()['total'];

        // My doctors count (unique)
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT doctor_id) as total FROM medical_records WHERE patient_id = :patient_id"
        );
        $stmt->execute([':patient_id' => $this->id]);
        $data['my_doctors_count'] = $stmt->fetch()['total'];

        // Latest record
        $stmt = $this->db->prepare(
            "SELECT mr.*, d.name as doctor_name
             FROM medical_records mr
             JOIN users d ON mr.doctor_id = d.id
             WHERE mr.patient_id = :patient_id
             ORDER BY mr.visit_date DESC LIMIT 1"
        );
        $stmt->execute([':patient_id' => $this->id]);
        $data['latest_record'] = $stmt->fetch() ?: null;

        return $data;
    }
}
