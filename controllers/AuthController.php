<?php
/**
 * Auth Controller
 * Menangani authentikasi dan register user
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Email.php';

class AuthController {
    private $userModel;
    private $emailModel;

    public function __construct() {
        $this->userModel = new User();
        $this->emailModel = new Email();
    }

    /**
     * Show login page
     */
    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            redirect('dashboard');
        }
        include __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Show register page
     */
    public function showRegister() {
        if (isset($_SESSION['user_id'])) {
            redirect('dashboard');
        }
        include __DIR__ . '/../views/auth/register.php';
    }

    /**
     * Process login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('login');
        }

        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Email dan password harus diisi';
            redirect('login');
        }

        $user = $this->userModel->login($email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();

            $_SESSION['success'] = 'Login berhasil';
            redirect('dashboard');
        } else {
            $_SESSION['error'] = 'Email atau password salah';
            redirect('login');
        }
    }

    /**
     * Process register
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('register');
        }

        $nama = sanitize($_POST['nama'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($nama) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Semua field harus diisi';
            redirect('register');
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter';
            redirect('register');
        }

        if ($password != $confirm_password) {
            $_SESSION['error'] = 'Password tidak cocok';
            redirect('register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Email tidak valid';
            redirect('register');
        }

        // Check if email already exists
        $existing_user = $this->userModel->getUserByEmail($email);
        if ($existing_user) {
            $_SESSION['error'] = 'Email sudah terdaftar';
            redirect('register');
        }

        // Register user
        $user_id = $this->userModel->register($nama, $email, $password, 'User');

        if ($user_id) {
            $_SESSION['success'] = 'Registrasi berhasil. Silakan login.';
            redirect('login');
        } else {
            $_SESSION['error'] = 'Registrasi gagal. Silakan coba lagi.';
            redirect('register');
        }
    }

    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        $_SESSION['success'] = 'Logout berhasil';
        redirect('login');
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'Admin';
    }

    /**
     * Check if user is support agent
     */
    public static function isAgent() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'Support Agent';
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role
     */
    public static function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
}

/**
 * Helper functions
 */

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . APP_URL . '/' . $path);
    exit;
}

function requireLogin() {
    if (!AuthController::isLoggedIn()) {
        redirect('login');
    }
}

function requireRole($role) {
    if (!AuthController::isLoggedIn() || !AuthController::hasRole($role)) {
        $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini';
        redirect('dashboard');
    }
}

function requireAdmin() {
    if (!AuthController::isAdmin()) {
        $_SESSION['error'] = 'Anda harus admin untuk mengakses halaman ini';
        redirect('dashboard');
    }
}

function requireAgent() {
    if (!AuthController::isAgent()) {
        $_SESSION['error'] = 'Anda harus support agent untuk mengakses halaman ini';
        redirect('dashboard');
    }
}
?>