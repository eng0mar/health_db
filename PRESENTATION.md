# 🏥 National Health Database System — Presentation

## 1. What is this project?
A secure medical records system built with **native PHP OOP + MySQL**.  
3 Roles: **Admin**, **Doctor**, **Patient** — each with strict access control.

---

## 2. Key Concepts

### 2.1 Singleton Pattern — Database Class
**Problem:** We need ONE database connection for the whole app.  
**Solution:** Singleton — private constructor + static `getInstance()`.  
```php
class Database {
    private static ?Database $instance = null;
    private function __construct() { /* PDO connection */ }
    public static function getInstance(): Database {
        if (self::$instance === null) self::$instance = new Database();
        return self::$instance;
    }
}
```
**Why?** So we don't open 10 connections — one is enough.

---

### 2.2 OOP Inheritance — Abstract User Class
**Problem:** Admin, Doctor, Patient all share common properties (name, email, etc).  
**Solution:** Abstract `User` class with shared methods + abstract `getDashboardData()`.

```
User (abstract)
├── Admin extends User
├── Doctor extends User
└── Patient extends User
```

**Polymorphism:** Same method `getDashboardData()`, different result per role:
- Admin → total counts for the whole system
- Doctor → their patients + records count
- Patient → their own records + prescriptions only

---

### 2.3 Role-Based Access Control (RBAC) — Middleware Pattern (Bonus +2)
**Problem:** How to prevent a patient from accessing doctor pages?  
**Solution:** Middleware class with static methods, checked at the top of every page.

```php
// First line in every restricted page:
Middleware::requireRole('doctor');  // If not doctor → redirect!
```

3 levels of protection:
1. `requireLogin()` → Are you logged in?
2. `requireRole('doctor')` → Are you actually a doctor?
3. `requireOwnership($recordId)` → Is this record yours?

---

### 2.4 Critical Security — Patient Data Isolation
**Problem:** Patient must ONLY see their own records — never another patient's.  
**Solution:** Every SQL query in Patient class filters by `patient_id = :patient_id`.

```php
// Always filter by the logged-in patient's ID
$stmt->execute([':patient_id' => $this->id]);
```

**Never** take patient_id from the URL — always from `$_SESSION['user_id']`.

---

### 2.5 Prepared Statements — SQL Injection Prevention
**Problem:** Putting user input directly in SQL string = hackable.  
**Solution:** PDO Prepared Statements with named parameters in every query.

```php
// ❌ WRONG - SQL Injection possible!
$sql = "SELECT * FROM users WHERE email = '$email'";

// ✅ CORRECT - Prepared Statement
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
```

---

### 2.6 Password Security — Hashing
**Problem:** Never store passwords as plain text — if DB is stolen, all passwords are exposed.  
**Solution:** `password_hash()` for storing, `password_verify()` for login.

```php
// Store: hash before saving
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Login: compare input with stored hash
if (password_verify($inputPassword, $storedHash)) { /* success! */ }
```

---

### 2.7 XSS Prevention — Output Sanitization
**Problem:** If someone types `<script>` in their name — it runs in everyone's browser!  
**Solution:** `htmlspecialchars()` on every output.

```php
function sanitize(string $data): string {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
// In HTML: <?= sanitize($user['name']) ?>
```

---

### 2.8 Session Management
**Problem:** HTTP is stateless — every request forgets who you are.  
**Solution:** PHP Sessions + `session_regenerate_id()` on login.

```php
// On login:
session_regenerate_id(true);  // Change the key — prevents Session Fixation
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_name'] = $user['name'];
```

---

### 2.9 Medical History Timeline (Bonus +3)
**Problem:** Patient wants to see their treatment history sorted by date.  
**Solution:** Records sorted by `visit_date ASC` + CSS timeline component.

```php
// Timeline query - sorted by date
ORDER BY mr.visit_date ASC
```

Each point shows: Date → Diagnosis → Doctor name → Prescriptions.

---

### 2.10 Patient Search with SQL LIKE (Bonus +2)
**Problem:** Doctors need to find patients quickly.  
**Solution:** `LIKE` with `%keyword%` — still using Prepared Statements for safety.

```php
$searchTerm = '%' . $keyword . '%';
$stmt = $db->prepare("SELECT * FROM users WHERE name LIKE :keyword");
$stmt->execute([':keyword' => $searchTerm]);
```

---

### 2.11 Dashboard Statistics (Bonus +3)
**Problem:** Each role needs different numbers on their dashboard.  
**Solution:** Polymorphic `getDashboardData()` — same method, different data per role.

| Role | Dashboard Shows |
|------|----------------|
| Admin | Total users, doctors, patients, records, prescriptions |
| Doctor | My patients count, my records, my prescriptions |
| Patient | My records, my prescriptions, my doctors |

---

## 3. Database Design

```
users (id, name, email, password, role, phone, created_at)
  ↓ FK                    ↓ FK
medical_records (id, patient_id, doctor_id, diagnosis, notes, visit_date)
  ↓ FK
prescriptions (id, record_id, medication_name, dosage, instructions)
```

- `ON DELETE CASCADE` → deleting a user removes all their records too
- `ENUM('admin','doctor','patient')` → only valid roles at DB level
- `UNIQUE` email → no duplicate accounts

---

## 4. File Structure

```
health_db/
├── config/database.php      → Connection settings
├── classes/                  → OOP classes (8 classes)
│   ├── Database.php          → Singleton PDO
│   ├── User.php              → Abstract base class
│   ├── Admin.php             → User management
│   ├── Doctor.php            → Records + prescriptions
│   ├── Patient.php           → View own data
│   ├── MedicalRecord.php     → Record CRUD
│   ├── Prescription.php      → Prescription CRUD
│   └── Middleware.php        → RBAC permission checker
├── includes/                 → Shared templates
├── pages/                    → All pages by role
│   ├── auth/                 → Login, Register, Logout
│   ├── admin/                → Dashboard, Users, Add User
│   ├── doctor/               → Dashboard, Patients, Records, Prescriptions
│   └── patient/              → Dashboard, Records, Prescriptions, Profile
└── assets/                   → CSS + JS
```

---

## 5. Grading Coverage

| Criteria | Marks | Status |
|----------|-------|--------|
| Authentication & Sessions | 4/4 | ✅ Register, Login, Logout, Sessions |
| Role-Based Access Control | 4/4 | ✅ Middleware + per-page checks |
| CRUD Operations | 4/4 | ✅ Users, Records, Prescriptions |
| OOP Code Structure | 3/3 | ✅ 8 classes, inheritance, polymorphism |
| Database Design | 3/3 | ✅ 3 tables, FKs, ENUMs, CASCADE |
| Security & Validation | 2/2 | ✅ PDO, validation, hashing, XSS |
| **Core Total** | **20/20** | ✅ |
| Patient Search (LIKE) | +2 | ✅ |
| Dashboard Statistics | +3 | ✅ |
| Medical History Timeline | +3 | ✅ |
| Advanced RBAC Middleware | +2 | ✅ |
| **Bonus Total** | **+10** | ✅ |
| **GRAND TOTAL** | **30/30** | ✅ |

---

## 6. Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@health.com | admin123 |
| Doctor | (register as doctor) | — |
| Patient | (register as patient) | — |
