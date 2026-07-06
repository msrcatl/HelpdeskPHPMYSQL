-- Helpdesk Ticketing System Database Schema
-- Pastikan database sudah dibuat sebelum import file ini

-- Drop tables if exist (untuk reset database)
DROP TABLE IF EXISTS `attachments`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `users`;

-- Create users table
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin', 'Support Agent', 'User') NOT NULL DEFAULT 'User',
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tickets table
CREATE TABLE `tickets` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `assigned_to` INT DEFAULT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `deskripsi` LONGTEXT NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `prioritas` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
  `status` ENUM('Open', 'In Progress', 'Resolved', 'Closed') NOT NULL DEFAULT 'Open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_assigned_to` (`assigned_to`),
  INDEX `idx_status` (`status`),
  INDEX `idx_kategori` (`kategori`),
  INDEX `idx_prioritas` (`prioritas`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create comments table
CREATE TABLE `comments` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `komentar` LONGTEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_ticket_id` (`ticket_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create attachments table
CREATE TABLE `attachments` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` INT NOT NULL,
  `file_type` VARCHAR(50) NOT NULL,
  `uploaded_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_ticket_id` (`ticket_id`),
  INDEX `idx_uploaded_by` (`uploaded_by`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default users (password: password123 hashed)
-- Password hash untuk 'password123' adalah: $2y$10$YIjlrBJWb2Lz8PjYrKcCwuXWj8n4ZxZL/qXE6nZr6QbqXQKqx5nly
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `status`) VALUES
('Administrator', 'admin@example.com', '$2y$10$YIjlrBJWb2Lz8PjYrKcCwuXWj8n4ZxZL/qXE6nZr6QbqXQKqx5nly', 'Admin', 'Active'),
('Support Agent', 'support@example.com', '$2y$10$YIjlrBJWb2Lz8PjYrKcCwuXWj8n4ZxZL/qXE6nZr6QbqXQKqx5nly', 'Support Agent', 'Active'),
('Regular User', 'user@example.com', '$2y$10$YIjlrBJWb2Lz8PjYrKcCwuXWj8n4ZxZL/qXE6nZr6QbqXQKqx5nly', 'User', 'Active');

-- Insert sample tickets
INSERT INTO `tickets` (`user_id`, `assigned_to`, `judul`, `deskripsi`, `kategori`, `prioritas`, `status`) VALUES
(3, 2, 'Login tidak bisa', 'Saya tidak bisa login ke akun saya, selalu error', 'Technical Support', 'High', 'Open'),
(3, 2, 'Minta reset password', 'Saya lupa password dan ingin di reset', 'Account', 'Medium', 'In Progress'),
(3, NULL, 'Fitur export data', 'Minta fitur untuk export data ke Excel', 'Feature Request', 'Low', 'Open');

-- Insert sample comments
INSERT INTO `comments` (`ticket_id`, `user_id`, `komentar`) VALUES
(1, 2, 'Kami sudah mulai investigasi masalah login Anda. Tim teknis sedang memeriksa server.'),
(2, 2, 'Password sudah di-reset. Email berisi password baru sudah dikirim ke inbox Anda.');

-- Create views for statistics (optional)
CREATE VIEW ticket_stats AS
SELECT 
    status,
    COUNT(*) as total,
    DATE(created_at) as date
FROM tickets
GROUP BY status, DATE(created_at);

CREATE VIEW user_ticket_stats AS
SELECT 
    u.id,
    u.nama,
    u.email,
    COUNT(t.id) as total_tickets,
    SUM(CASE WHEN t.status = 'Open' THEN 1 ELSE 0 END) as open_tickets,
    SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tickets,
    SUM(CASE WHEN t.status = 'Resolved' THEN 1 ELSE 0 END) as resolved_tickets,
    SUM(CASE WHEN t.status = 'Closed' THEN 1 ELSE 0 END) as closed_tickets
FROM users u
LEFT JOIN tickets t ON u.id = t.user_id
WHERE u.role = 'User'
GROUP BY u.id, u.nama, u.email;
