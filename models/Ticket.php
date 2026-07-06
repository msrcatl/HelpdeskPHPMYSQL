<?php
/**
 * Ticket Model
 * Menangani operasi database untuk tabel tickets
 */

require_once __DIR__ . '/../config/database.php';

class Ticket {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create new ticket
     */
    public function createTicket($user_id, $judul, $deskripsi, $kategori, $prioritas) {
        $status = 'Open';

        $this->db->prepare('INSERT INTO tickets (user_id, judul, deskripsi, kategori, prioritas, status) VALUES (?, ?, ?, ?, ?, ?)');
        $this->db->bind('i', $user_id);
        $this->db->bind('s', $judul);
        $this->db->bind('s', $deskripsi);
        $this->db->bind('s', $kategori);
        $this->db->bind('s', $prioritas);
        $this->db->bind('s', $status);
        $this->db->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Get ticket by ID
     */
    public function getTicketById($id) {
        $this->db->prepare('
            SELECT t.*, u.nama as user_nama, u.email as user_email, 
                   a.nama as agent_nama, a.email as agent_email
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN users a ON t.assigned_to = a.id
            WHERE t.id = ?
        ');
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->fetchRow();
    }

    /**
     * Get all tickets
     */
    public function getAllTickets($limit = 50, $offset = 0) {
        $this->db->prepare('
            SELECT t.id, t.user_id, t.judul, t.kategori, t.prioritas, t.status, 
                   t.created_at, u.nama, u.email, a.nama as agent_nama
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN users a ON t.assigned_to = a.id
            ORDER BY t.created_at DESC
            LIMIT ?, ?
        ');
        $this->db->bind('i', $offset);
        $this->db->bind('i', $limit);
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Get tickets by user ID
     */
    public function getTicketsByUserId($user_id, $limit = 50, $offset = 0) {
        $this->db->prepare('
            SELECT t.id, t.judul, t.kategori, t.prioritas, t.status, t.created_at, t.updated_at
            FROM tickets t
            WHERE t.user_id = ?
            ORDER BY t.created_at DESC
            LIMIT ?, ?
        ');
        $this->db->bind('i', $user_id);
        $this->db->bind('i', $offset);
        $this->db->bind('i', $limit);
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Get tickets assigned to agent
     */
    public function getTicketsByAgentId($agent_id, $limit = 50, $offset = 0) {
        $this->db->prepare('
            SELECT t.id, t.judul, t.kategori, t.prioritas, t.status, t.created_at, u.nama, u.email
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.assigned_to = ?
            ORDER BY t.prioritas DESC, t.created_at DESC
            LIMIT ?, ?
        ');
        $this->db->bind('i', $agent_id);
        $this->db->bind('i', $offset);
        $this->db->bind('i', $limit);
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Get tickets by status
     */
    public function getTicketsByStatus($status, $limit = 50, $offset = 0) {
        $this->db->prepare('
            SELECT t.id, t.judul, t.kategori, t.prioritas, t.status, t.created_at, u.nama, u.email
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.status = ?
            ORDER BY t.created_at DESC
            LIMIT ?, ?
        ');
        $this->db->bind('s', $status);
        $this->db->bind('i', $offset);
        $this->db->bind('i', $limit);
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Get tickets by category
     */
    public function getTicketsByCategory($kategori, $limit = 50, $offset = 0) {
        $this->db->prepare('
            SELECT t.id, t.judul, t.kategori, t.prioritas, t.status, t.created_at, u.nama, u.email
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.kategori = ?
            ORDER BY t.created_at DESC
            LIMIT ?, ?
        ');
        $this->db->bind('s', $kategori);
        $this->db->bind('i', $offset);
        $this->db->bind('i', $limit);
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Search tickets
     */
    public function searchTickets($keyword, $limit = 50, $offset = 0) {
        $search = '%' . $keyword . '%';

        $this->db->prepare('
            SELECT t.id, t.judul, t.kategori, t.prioritas, t.status, t.created_at, u.nama, u.email
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.judul LIKE ? OR t.deskripsi LIKE ? OR u.nama LIKE ?
            ORDER BY t.created_at DESC
            LIMIT ?, ?
        ');
        $this->db->bind('s', $search);
        $this->db->bind('s', $search);
        $this->db->bind('s', $search);
        $this->db->bind('i', $offset);
        $this->db->bind('i', $limit);
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Update ticket status
     */
    public function updateStatus($id, $status) {
        $this->db->prepare('UPDATE tickets SET status = ? WHERE id = ?');
        $this->db->bind('s', $status);
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    /**
     * Assign ticket to agent
     */
    public function assignToAgent($id, $agent_id) {
        $this->db->prepare('UPDATE tickets SET assigned_to = ? WHERE id = ?');
        $this->db->bind('i', $agent_id);
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    /**
     * Update ticket
     */
    public function updateTicket($id, $judul, $deskripsi, $kategori, $prioritas) {
        $this->db->prepare('UPDATE tickets SET judul = ?, deskripsi = ?, kategori = ?, prioritas = ? WHERE id = ?');
        $this->db->bind('s', $judul);
        $this->db->bind('s', $deskripsi);
        $this->db->bind('s', $kategori);
        $this->db->bind('s', $prioritas);
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    /**
     * Delete ticket
     */
    public function deleteTicket($id) {
        $this->db->prepare('DELETE FROM tickets WHERE id = ?');
        $this->db->bind('i', $id);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    /**
     * Get ticket statistics
     */
    public function getStatistics() {
        $this->db->prepare('
            SELECT 
                status,
                COUNT(*) as count
            FROM tickets
            GROUP BY status
        ');
        $this->db->execute();

        return $this->db->fetchAll();
    }

    /**
     * Count tickets by status
     */
    public function countByStatus($status) {
        $this->db->prepare('SELECT COUNT(*) as count FROM tickets WHERE status = ?');
        $this->db->bind('s', $status);
        $this->db->execute();

        $result = $this->db->fetchRow();
        return $result['count'] ?? 0;
    }

    /**
     * Count total tickets
     */
    public function countTotal() {
        $this->db->prepare('SELECT COUNT(*) as count FROM tickets');
        $this->db->execute();

        $result = $this->db->fetchRow();
        return $result['count'] ?? 0;
    }
}
?>