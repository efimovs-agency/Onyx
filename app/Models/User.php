<?php
require_once 'Database.php';

/* ==========================================================
   USER MODEL
   Handles database operations related to user identity,
   authentication, registration, and profile preferences.
========================================================== */
class User {
    
    /**
     * @var PDO Database connection instance.
     */
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /* ==========================================================
       IDENTITY VERIFICATION
       Validates the uniqueness of user credentials during 
       the registration flow.
    ========================================================== */
    public function userExists($email, $login) {
        $sql = "SELECT id FROM users WHERE email = :email OR login = :login LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':login' => $login
        ]);
        
        return $stmt->fetch() !== false;
    }

    /* ==========================================================
       ACCOUNT CREATION
       Securely hashes credentials and persists new user 
       records to the database. Theme defaults are handled 
       at the database schema level.
    ========================================================== */
    public function register($email, $login, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (email, login, password) VALUES (:email, :login, :password)";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            ':email' => $email,
            ':login' => $login,
            ':password' => $hashedPassword 
        ])) {
            // Возвращаем ID только что созданной записи для моментальной авторизации
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /* ==========================================================
       AUTHENTICATION
       Retrieves user records and validates cryptographic 
       password hashes to establish authenticated sessions.
    ========================================================== */
    public function login($login, $password) {
        $sql = "SELECT id, login, password, theme FROM users WHERE login = :login LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }   

    /* ==========================================================
       PREFERENCES MANAGEMENT
       Updates user-specific interface settings, such as 
       the active visual theme.
    ========================================================== */
    public function updateTheme($userId, $theme) {
        $sql = "UPDATE users SET theme = :theme WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':theme' => $theme,
            ':id' => $userId
        ]);
    }
}