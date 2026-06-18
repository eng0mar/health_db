<?php
// ============================================
// User Class - Abstract Base Class
// Kol el roles (Admin, Doctor, Patient) bey-extend menha
// Encapsulation: properties protected, exposed via methods
// ============================================

require_once __DIR__ . '/Database.php';

abstract class User {
    protected ?int $id;
    protected string $name;
    protected string $email;
    protected string $password;
    protected string $role;
    protected ?string $phone;
    protected ?string $created_at;
    protected PDO $db;

    // ============================================
    // Constructor - bey-set el basic properties
    // ============================================
    public function __construct(?int $id = null, string $name = '', string $email = '', string $role = '') {
        $this->db = Database::getInstance()->getConnection();
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->phone = null;
        $this->created_at = null;
    }

    // ============================================
    // Getters - Encapsulation
    // ============================================
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
    public function getPhone(): ?string { return $this->phone; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    // ============================================
    // register - bey-save user gedid fel database
    // Bey-hash el password w bey-validate el data
    // ============================================
    public function register(string $name, string $email, string $password, string $role, ?string $phone = null): bool {
        // Validate el role 3ashan ma7adsh ye-inject role gheir sa7
        $validRoles = ['admin', 'doctor', 'patient'];
        if (!in_array($role, $validRoles)) {
            return false;
        }

        // Check law el email already mawgood
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            return false;  // Email already exists
        }

        // Hash el password w save
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role, phone) VALUES (:name, :email, :password, :role, :phone)"
        );

        return $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $hashedPassword,
            ':role'     => $role,
            ':phone'    => $phone,
        ]);
    }

    // ============================================
    // login - bey-verify el credentials w bey-start session
    // Static method 3ashan ne3mel call men gher instance
    // ============================================
    public static function login(string $email, string $password): ?array {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Start session w store user data
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            // Regenerate session ID 3ashan ne-prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];

            return $user;
        }

        return null;  // Credentials wrong
    }

    // ============================================
    // logout - bey-destroy el session completely
    // ============================================
    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    // ============================================
    // updateProfile - bey-update el user data
    // ============================================
    public function updateProfile(int $userId, string $name, string $email, ?string $phone = null, ?string $newPassword = null): bool {
        // Check law el email already mawgood le user tany
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute([':email' => $email, ':id' => $userId]);
        if ($stmt->fetch()) {
            return false;  // Email taken by another user
        }

        if ($newPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare(
                "UPDATE users SET name = :name, email = :email, phone = :phone, password = :password WHERE id = :id"
            );
            return $stmt->execute([
                ':name'     => $name,
                ':email'    => $email,
                ':phone'    => $phone,
                ':password' => $hashedPassword,
                ':id'       => $userId,
            ]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id"
            );
            return $stmt->execute([
                ':name'  => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':id'    => $userId,
            ]);
        }
    }

    // ============================================
    // getUserById - bey-geeb user wa7ed bel ID
    // ============================================
    public static function getUserById(int $id): ?array {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name, email, role, phone, created_at FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    // ============================================
    // Abstract method - kol role lazem ye-implement dashboard data
    // Polymorphism: kol class haye-override el method da
    // ============================================
    abstract public function getDashboardData(): array;
}
