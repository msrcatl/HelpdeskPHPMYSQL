<?php
/**
 * Ticket Controller
 * Menangani operasi tiket
 */

require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Attachment.php';
require_once __DIR__ . '/../models/Email.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class TicketController {
    private $ticketModel;
    private $commentModel;
    private $attachmentModel;
    private $emailModel;
    private $userModel;

    public function __construct() {
        $this->ticketModel = new Ticket();
        $this->commentModel = new Comment();
        $this->attachmentModel = new Attachment();
        $this->emailModel = new Email();
        $this->userModel = new User();
    }

    /**
     * Show all tickets
     */
    public function showAllTickets() {
        requireLogin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $tickets = $this->ticketModel->getAllTickets($limit, $offset);
        
        include __DIR__ . '/../views/tickets/list.php';
    }

    /**
     * Show my tickets (for users)
     */
    public function showMyTickets() {
        requireLogin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $user_id = AuthController::getCurrentUserId();
        $tickets = $this->ticketModel->getTicketsByUserId($user_id, $limit, $offset);
        
        include __DIR__ . '/../views/tickets/my-tickets.php';
    }

    /**
     * Show assigned tickets (for agents)
     */
    public function showAssignedTickets() {
        requireAgent();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $agent_id = AuthController::getCurrentUserId();
        $tickets = $this->ticketModel->getTicketsByAgentId($agent_id, $limit, $offset);
        
        include __DIR__ . '/../views/tickets/assigned.php';
    }

    /**
     * Show ticket detail
     */
    public function showDetail() {
        requireLogin();
        
        $ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($ticket_id <= 0) {
            $_SESSION['error'] = 'Tiket tidak ditemukan';
            redirect('tickets');
        }

        $ticket = $this->ticketModel->getTicketById($ticket_id);
        
        if (!$ticket) {
            $_SESSION['error'] = 'Tiket tidak ditemukan';
            redirect('tickets');
        }

        // Check access
        $user_id = AuthController::getCurrentUserId();
        if ($ticket['user_id'] != $user_id && $ticket['assigned_to'] != $user_id && !AuthController::isAdmin()) {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke tiket ini';
            redirect('tickets');
        }

        $comments = $this->commentModel->getCommentsByTicketId($ticket_id);
        $attachments = $this->attachmentModel->getAttachmentsByTicketId($ticket_id);
        
        include __DIR__ . '/../views/tickets/detail.php';
    }

    /**
     * Show create ticket form
     */
    public function showCreateForm() {
        requireLogin();
        
        global $TICKET_CATEGORIES, $TICKET_PRIORITIES;
        
        include __DIR__ . '/../views/tickets/create.php';
    }

    /**
     * Create new ticket
     */
    public function create() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('tickets/create');
        }

        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $kategori = sanitize($_POST['kategori'] ?? '');
        $prioritas = sanitize($_POST['prioritas'] ?? 'Medium');

        // Validation
        if (empty($judul) || empty($deskripsi) || empty($kategori)) {
            $_SESSION['error'] = 'Semua field harus diisi';
            redirect('tickets/create');
        }

        if (strlen($judul) < 5) {
            $_SESSION['error'] = 'Judul minimal 5 karakter';
            redirect('tickets/create');
        }

        if (strlen($deskripsi) < 10) {
            $_SESSION['error'] = 'Deskripsi minimal 10 karakter';
            redirect('tickets/create');
        }

        $user_id = AuthController::getCurrentUserId();
        $ticket_id = $this->ticketModel->createTicket($user_id, $judul, $deskripsi, $kategori, $prioritas);

        if ($ticket_id) {
            // Send email notification
            $user = $this->userModel->getUserById($user_id);
            $this->emailModel->sendTicketCreatedNotification($user['email'], $user['nama'], $ticket_id, $judul);

            $_SESSION['success'] = 'Tiket berhasil dibuat. ID: #' . $ticket_id;
            redirect('tickets/detail?id=' . $ticket_id);
        } else {
            $_SESSION['error'] = 'Gagal membuat tiket';
            redirect('tickets/create');
        }
    }

    /**
     * Update ticket status
     */
    public function updateStatus() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('tickets');
        }

        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $new_status = sanitize($_POST['status'] ?? '');

        if ($ticket_id <= 0 || empty($new_status)) {
            $_SESSION['error'] = 'Data tidak valid';
            redirect('tickets');
        }

        $ticket = $this->ticketModel->getTicketById($ticket_id);
        
        if (!$ticket) {
            $_SESSION['error'] = 'Tiket tidak ditemukan';
            redirect('tickets');
        }

        // Check permission
        $user_id = AuthController::getCurrentUserId();
        if ($ticket['assigned_to'] != $user_id && !AuthController::isAdmin()) {
            $_SESSION['error'] = 'Anda tidak memiliki izin mengubah tiket ini';
            redirect('tickets/detail?id=' . $ticket_id);
        }

        if ($this->ticketModel->updateStatus($ticket_id, $new_status)) {
            // Send email notification
            $user = $this->userModel->getUserById($ticket['user_id']);
            $this->emailModel->sendStatusChangeNotification($user['email'], $user['nama'], $ticket_id, $ticket['judul'], $new_status);

            $_SESSION['success'] = 'Status tiket berhasil diupdate';
        } else {
            $_SESSION['error'] = 'Gagal mengupdate status tiket';
        }

        redirect('tickets/detail?id=' . $ticket_id);
    }

    /**
     * Assign ticket to agent
     */
    public function assignAgent() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('tickets');
        }

        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $agent_id = isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : 0;

        if ($ticket_id <= 0 || $agent_id <= 0) {
            $_SESSION['error'] = 'Data tidak valid';
            redirect('tickets');
        }

        $ticket = $this->ticketModel->getTicketById($ticket_id);
        
        if (!$ticket) {
            $_SESSION['error'] = 'Tiket tidak ditemukan';
            redirect('tickets');
        }

        if ($this->ticketModel->assignToAgent($ticket_id, $agent_id)) {
            // Send email notification
            $agent = $this->userModel->getUserById($agent_id);
            $this->emailModel->sendTicketAssignmentNotification($agent['email'], $agent['nama'], $ticket_id, $ticket['judul']);

            $_SESSION['success'] = 'Tiket berhasil ditugaskan ke support agent';
        } else {
            $_SESSION['error'] = 'Gagal menugaskan tiket';
        }

        redirect('tickets/detail?id=' . $ticket_id);
    }

    /**
     * Add comment
     */
    public function addComment() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('tickets');
        }

        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
        $komentar = sanitize($_POST['komentar'] ?? '');

        if ($ticket_id <= 0 || empty($komentar)) {
            $_SESSION['error'] = 'Komentar tidak boleh kosong';
            redirect('tickets/detail?id=' . $ticket_id);
        }

        $ticket = $this->ticketModel->getTicketById($ticket_id);
        
        if (!$ticket) {
            $_SESSION['error'] = 'Tiket tidak ditemukan';
            redirect('tickets');
        }

        $user_id = AuthController::getCurrentUserId();
        
        if ($this->commentModel->createComment($ticket_id, $user_id, $komentar)) {
            // Send email notification
            $user = $this->userModel->getUserById($user_id);
            
            // Notify ticket creator if commenter is not the creator
            if ($ticket['user_id'] != $user_id) {
                $ticket_creator = $this->userModel->getUserById($ticket['user_id']);
                $this->emailModel->sendNewCommentNotification($ticket_creator['email'], $ticket_creator['nama'], $ticket_id, $ticket['judul'], $user['nama']);
            }

            // Notify assigned agent if commenter is not the agent
            if ($ticket['assigned_to'] && $ticket['assigned_to'] != $user_id) {
                $agent = $this->userModel->getUserById($ticket['assigned_to']);
                $this->emailModel->sendNewCommentNotification($agent['email'], $agent['nama'], $ticket_id, $ticket['judul'], $user['nama']);
            }

            $_SESSION['success'] = 'Komentar berhasil ditambahkan';
        } else {
            $_SESSION['error'] = 'Gagal menambahkan komentar';
        }

        redirect('tickets/detail?id=' . $ticket_id);
    }

    /**
     * Upload attachment
     */
    public function uploadAttachment() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('tickets');
        }

        $ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;

        if ($ticket_id <= 0) {
            $_SESSION['error'] = 'Tiket tidak ditemukan';
            redirect('tickets');
        }

        $ticket = $this->ticketModel->getTicketById($ticket_id);
        
        if (!$ticket) {
            $_SESSION['error'] = 'Tiket tidak ditemukan';
            redirect('tickets');
        }

        if (!isset($_FILES['file'])) {
            $_SESSION['error'] = 'File tidak ditemukan';
            redirect('tickets/detail?id=' . $ticket_id);
        }

        $user_id = AuthController::getCurrentUserId();
        $result = $this->attachmentModel->uploadAttachment($ticket_id, $_FILES['file'], $user_id);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        redirect('tickets/detail?id=' . $ticket_id);
    }

    /**
     * Download attachment
     */
    public function downloadAttachment() {
        requireLogin();
        
        $attachment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($attachment_id <= 0) {
            $_SESSION['error'] = 'File tidak ditemukan';
            redirect('tickets');
        }

        $attachment = $this->attachmentModel->getAttachmentById($attachment_id);

        if (!$attachment) {
            $_SESSION['error'] = 'File tidak ditemukan';
            redirect('tickets');
        }

        $file_path = PUBLIC_PATH . '/' . $attachment['file_path'];

        if (!file_exists($file_path)) {
            $_SESSION['error'] = 'File tidak ditemukan di server';
            redirect('tickets');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . mime_content_type($file_path));
        header('Content-Disposition: attachment; filename="' . $attachment['file_name'] . '"');
        header('Content-Length: ' . filesize($file_path));

        readfile($file_path);
        exit;
    }
}
?>