<?php
// ============================================
// Doctor Class - extends User
// Bey-manage medical records w prescriptions
// ============================================

require_once __DIR__ . '/User.php';

class Doctor extends User {

    public function __construct(?int $id = null, string $name = '', string $email = '') {
        parent::__construct($id, $name, $email, 'doctor');
    }

    // ============================================
    // addRecord - bey-add medical record le patient
    // ============================================
    public function addRecord(int $patientId, string $diagnosis, ?string $notes, string $visitDate): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO medical_records (patient_id, doctor_id, diagnosis, notes, visit_date)
             VALUES (:patient_id, :doctor_id, :diagnosis, :notes, :visit_date)"
        );
        return $stmt->execute([
            ':patient_id' => $patientId,
            ':doctor_id'  => $this->id,
            ':diagnosis'  => $diagnosis,
            ':notes'      => $notes,
            ':visit_date' => $visitDate,
        ]);
    }

    // ============================================
    // updateDiagnosis - bey-update diagnosis le record mawgood
    // Lazem el doctor ykon el owner beta3 el record
    // ============================================
    public function updateDiagnosis(int $recordId, string $diagnosis, ?string $notes = null): bool {
        $stmt = $this->db->prepare(
            "UPDATE medical_records SET diagnosis = :diagnosis, notes = :notes
             WHERE id = :id AND doctor_id = :doctor_id"
        );
        return $stmt->execute([
            ':diagnosis' => $diagnosis,
            ':notes'     => $notes,
            ':id'        => $recordId,
            ':doctor_id' => $this->id,
        ]);
    }

    // ============================================
    // addPrescription - bey-add prescription le medical record
    // Lazem el record ykon beta3 el doctor da
    // ============================================
    public function addPrescription(int $recordId, string $medicationName, string $dosage, ?string $instructions = null): bool {
        // Verify en el record beta3 el doctor da
        $stmt = $this->db->prepare("SELECT id FROM medical_records WHERE id = :id AND doctor_id = :doctor_id");
        $stmt->execute([':id' => $recordId, ':doctor_id' => $this->id]);
        if (!$stmt->fetch()) {
            return false;  // Record msh beta3 el doctor da
        }

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
    // getMyPatients - bey-geeb kol el patients beto3 el doctor da
    // Patients ely 3andhom records ma3 el doctor da
    // ============================================
    public function getMyPatients(): array {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT u.id, u.name, u.email, u.phone, u.created_at
             FROM users u
             JOIN medical_records mr ON u.id = mr.patient_id
             WHERE mr.doctor_id = :doctor_id
             ORDER BY u.name ASC"
        );
        $stmt->execute([':doctor_id' => $this->id]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getAllPatients - bey-geeb kol el patients (le add record)
    // ============================================
    public function getAllPatients(): array {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, phone FROM users WHERE role = 'patient' ORDER BY name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ============================================
    // getPatientRecords - bey-geeb records beta3 patient mo3ayan
    // Bs el records ely el doctor da katebha
    // ============================================
    public function getPatientRecords(int $patientId): array {
        $stmt = $this->db->prepare(
            "SELECT mr.*, u.name as patient_name
             FROM medical_records mr
             JOIN users u ON mr.patient_id = u.id
             WHERE mr.doctor_id = :doctor_id AND mr.patient_id = :patient_id
             ORDER BY mr.visit_date DESC"
        );
        $stmt->execute([
            ':doctor_id'  => $this->id,
            ':patient_id' => $patientId,
        ]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getRecordById - bey-geeb record wa7ed
    // Lazem ykon beta3 el doctor da
    // ============================================
    public function getRecordById(int $recordId): ?array {
        $stmt = $this->db->prepare(
            "SELECT mr.*, u.name as patient_name
             FROM medical_records mr
             JOIN users u ON mr.patient_id = u.id
             WHERE mr.id = :id AND mr.doctor_id = :doctor_id"
        );
        $stmt->execute([
            ':id'        => $recordId,
            ':doctor_id' => $this->id,
        ]);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    // ============================================
    // getPrescriptionsByRecord - bey-geeb prescriptions le record
    // ============================================
    public function getPrescriptionsByRecord(int $recordId): array {
        // Verify en el record beta3 el doctor da
        $stmt = $this->db->prepare("SELECT id FROM medical_records WHERE id = :id AND doctor_id = :doctor_id");
        $stmt->execute([':id' => $recordId, ':doctor_id' => $this->id]);
        if (!$stmt->fetch()) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM prescriptions WHERE record_id = :record_id ORDER BY prescribed_at DESC"
        );
        $stmt->execute([':record_id' => $recordId]);
        return $stmt->fetchAll();
    }

    // ============================================
    // searchPatients - (Bonus +2)
    // Search by name aw email using LIKE
    // ============================================
    public function searchPatients(string $keyword): array {
        $searchTerm = '%' . $keyword . '%';
        $stmt = $this->db->prepare(
            "SELECT id, name, email, phone, created_at FROM users
             WHERE role = 'patient' AND (name LIKE :keyword OR email LIKE :keyword2)
             ORDER BY name ASC"
        );
        $stmt->execute([':keyword' => $searchTerm, ':keyword2' => $searchTerm]);
        return $stmt->fetchAll();
    }

    // ============================================
    // getDashboardData - Polymorphism (Bonus +3)
    // Doctor dashboard: patient count + recent records
    // ============================================
    public function getDashboardData(): array {
        $data = [];

        // My patients count
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT patient_id) as total FROM medical_records WHERE doctor_id = :doctor_id"
        );
        $stmt->execute([':doctor_id' => $this->id]);
        $data['my_patients_count'] = $stmt->fetch()['total'];

        // My total records
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM medical_records WHERE doctor_id = :doctor_id"
        );
        $stmt->execute([':doctor_id' => $this->id]);
        $data['my_records_count'] = $stmt->fetch()['total'];

        // My total prescriptions
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM prescriptions p
             JOIN medical_records mr ON p.record_id = mr.id
             WHERE mr.doctor_id = :doctor_id"
        );
        $stmt->execute([':doctor_id' => $this->id]);
        $data['my_prescriptions_count'] = $stmt->fetch()['total'];

        // Recent records (last 5)
        $stmt = $this->db->prepare(
            "SELECT mr.id, mr.diagnosis, mr.visit_date, mr.created_at,
                    u.name as patient_name
             FROM medical_records mr
             JOIN users u ON mr.patient_id = u.id
             WHERE mr.doctor_id = :doctor_id
             ORDER BY mr.created_at DESC LIMIT 5"
        );
        $stmt->execute([':doctor_id' => $this->id]);
        $data['recent_records'] = $stmt->fetchAll();

        return $data;
    }
}
