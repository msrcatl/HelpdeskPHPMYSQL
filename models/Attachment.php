<?php
/**
 * Attachment Model
 * Menangani operasi database untuk tabel attachments
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Attachment {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Upload and save attachment
     */
    public function uploadAttachment($ticket_id, $file, $user_id) {
        // Validate file
        if (!isset($file['tmp_name']) || !isset($file['name'])) {
            return ['success' => false, 'message' => 'File tidak valid'];
        }

        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB)'];
        }

        // Get file extension
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Check allowed extensions
        if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
        }

        // Create unique filename
        $new_filename = uniqid('attachment_') . '.' . $file_ext;
        $upload_path = UPLOAD_PATH . $new_filename;

        // Create upload directory if not exist
        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            return ['success' => false, 'message' => 'Gagal upload file'];
        }

        // Save to database
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_type = $file['type'];
        $file_path = 'uploads/attachments/' . $new_filename;

        $this->db->prepare('
            INSERT INTO attachments (ticket_id, file_name, file_path, file_size, file_type, uploaded_by) 
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $this->db->bind('i', $ticket_id);
        $this->db->bind('s', $file_name);
        $this->db->bind('s', $file_path);
        $this->db->bind('i', $file_size);
        $this->db->bind('s', $file_type);
        $this->db->bind('i', $user_id);
        $this->db->execute();

        return [
            'success' => true,
            'message' => 'File berhasil diupload',
            'id' => $this->db->lastInsertId(),
            'file_path' => $file_path
        ];
    }

    /**
     * Get attachment by ID
     */
    public function getAttachmentById($id) {
        $this->db->prepare('
            SELECT a.*, u.nama as uploaded_by_name
            FROM attachments a
            LEFT JOIN users u ON a.uploaded_by = u.id
            WHERE a.id = ?
        ');
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->fetchRow();
    }

    /**
     * Get attachments by ticket ID
     */
    public function getAttachmentsByTicketId($ticket_id) {
        $this->db->prepare('
            SELECT a.id, a.file_name, a.file_path, a.file_size, a.file_type, a.created_at, u.nama as uploaded_by_name
            FROM attachments a
            LEFT JOIN users u ON a.uploaded_by = u.id
            WHERE a.ticket_id = ?
            ORDER BY a.created_at DESC
        ');
        $this->db->bind('i', $ticket_id);
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment($id) {
        // Get attachment info
        $attachment = $this->getAttachmentById($id);

        if (!$attachment) {
            return false;
        }

        // Delete file
        $file_path = PUBLIC_PATH . '/' . $attachment['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete from database
        $this->db->prepare('DELETE FROM attachments WHERE id = ?');
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    /**
     * Count attachments by ticket
     */
    public function countByTicketId($ticket_id) {
        $this->db->prepare('SELECT COUNT(*) as count FROM attachments WHERE ticket_id = ?');
        $this->db->bind('i', $ticket_id);
        $this->db->execute();

        $result = $this->db->fetchRow();
        return $result['count'] ?? 0;
    }
}
?>