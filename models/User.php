<?php
/**
 * User Model
 * Menangani operasi database untuk tabel users
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Register user baru
     */
    public function register($nama, $email, $password, $role = 'User') {
        // Check if email already exists
        $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $this->db->bind('s', $email);
        $this->db->execute();
        
        if ($this->db->rowCount() > 0) {
            return false; // Email sudah terdaftar
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user baru
        $this->db->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)');
        $this->db->bind('s', $nama);
        $this->db->bind('s', $email);
        $this->db->bind('s', $hashed_password);
        $this->db->bind('s', $role);
        $this->db->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $this->db->prepare('SELECT * FROM users WHERE email = ? AND status = "Active"');
        $this->db->bind('s', $email);
        $this->db->execute();

        $user = $this->db->fetchRow();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $this->db->prepare('SELECT id, nama, email, role, status, created_at FROM users WHERE id = ?');
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->fetchRow();
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $this->db->bind('s', $email);
        $this->db->execute();

        return $this->db->fetchRow();
    }

    /**
     * Get all users
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        $this->db->prepare('SELECT id, nama, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT ?, ?');
        $this->db->bind('i', $offset);
        $this->db->bind('i', $limit);
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        $this->db->prepare('SELECT id, nama, email, role, status FROM users WHERE role = ? ORDER BY nama');
        $this->db->bind('s', $role);
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Update user
     */
    public function updateUser($id, $nama, $email, $role = null) {
        if ($role) {
            $this->db->prepare('UPDATE users SET nama = ?, email = ? , role = ? WHERE id = ?');
            $this->db->bind('s', $nama);
            $this->db->bind('s', $email);
            $this->db->bind('s', $role);
            $this->db->bind('i', $id);
        } else {
            $this->db->prepare('UPDATE users SET nama = ?, email = ? WHERE id = ?');
            $this->db->bind('s', $nama);
            $this->db->bind('s', $email);
            $this->db->bind('i', $id);
        }

        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Update password
     */
    public function updatePassword($id, $old_password, $new_password) {
        $user = $this->getUserById($id);

        if (!$user) {
            return false;
        }

        // Verify old password
        $this->db->prepare('SELECT password FROM users WHERE id = ?');
        $this->db->bind('i', $id);
        $this->db->execute();

        $row = $this->db->fetchRow();

        if (!password_verify($old_password, $row['password'])) {
            return false;
        }

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update password
        $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $this->db->bind('s', $hashed_password);
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    /**
     * Change user status
     */
    public function changeStatus($id, $status) {
        $this->db->prepare('UPDATE users SET status = ? WHERE id = ?');
        $this->db->bind('s', $status);
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    /**
     * Delete user
     */
    public function deleteUser($id) {
        $this->db->prepare('DELETE FROM users WHERE id = ?');
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    /**
     * Count users by role
     */
    public function countUsersByRole($role) {
        $this->db->prepare('SELECT COUNT(*) as count FROM users WHERE role = ?');
        $this->db->bind('s', $role);
        $this->db->execute();

        $result = $this->db->fetchRow();
        return $result['count'] ?? 0;
    }
}
?>