<?php
/**
 * Email Model
 * Menangani pengiriman email notifikasi menggunakan PHPMailer
 */

require_once __DIR__ . '/../config/email.php';

// Check if PHPMailer is installed via Composer
$autoload_paths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
];

$autoload_found = false;
foreach ($autoload_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoload_found = true;
        break;
    }
}

if (!$autoload_found) {
    // Fallback for manual PHPMailer installation
    if (file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    private $mailer;
    private $log_file;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->log_file = EMAIL_LOG_PATH;

        // Create logs directory if not exist
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        // Configure SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->Port = SMTP_PORT;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USERNAME;
        $this->mailer->Password = SMTP_PASSWORD;
        $this->mailer->SMTPSecure = SMTP_SECURE;
        $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);
        $this->mailer->CharSet = 'UTF-8';
    }

    /**
     * Send ticket creation notification
     */
    public function sendTicketCreatedNotification($to_email, $to_name, $ticket_id, $ticket_title) {
        try {
            $this->mailer->addAddress($to_email, $to_name);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Tiket Support Baru - #' . $ticket_id;

            $ticket_url = APP_URL . '/tickets/detail?id=' . $ticket_id;

            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; }
                        .header { background: #007bff; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
                        .content { background: white; padding: 20px; }
                        .footer { background: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; }
                        .button { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Tiket Support Baru Telah Dibuat</h2>
                        </div>
                        <div class='content'>
                            <p>Halo $to_name,</p>
                            <p>Tiket support Anda telah berhasil dibuat dengan informasi sebagai berikut:</p>
                            <p><strong>ID Tiket:</strong> #$ticket_id</p>
                            <p><strong>Judul:</strong> $ticket_title</p>
                            <p><strong>Status:</strong> Open</p>
                            <p>Tim support kami akan segera menangani tiket Anda. Anda dapat melihat status tiket Anda dengan mengklik tombol di bawah:</p>
                            <p><a href='$ticket_url' class='button'>Lihat Detail Tiket</a></p>
                            <p>Terima kasih telah menghubungi kami.</p>
                            <p>Salam,<br>Tim Support</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Helpdesk Ticketing System. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $this->mailer->Body = $body;

            if ($this->mailer->send()) {
                $this->log('Ticket created notification sent to ' . $to_email . ' for ticket #' . $ticket_id);
                return true;
            }
        } catch (Exception $e) {
            $this->log('Error sending ticket created notification: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Send ticket assignment notification
     */
    public function sendTicketAssignmentNotification($to_email, $to_name, $ticket_id, $ticket_title) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to_email, $to_name);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Tiket Baru Ditugaskan Kepada Anda - #' . $ticket_id;

            $ticket_url = APP_URL . '/tickets/detail?id=' . $ticket_id;

            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; }
                        .header { background: #28a745; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
                        .content { background: white; padding: 20px; }
                        .footer { background: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; }
                        .button { display: inline-block; background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Tiket Baru Ditugaskan Kepada Anda</h2>
                        </div>
                        <div class='content'>
                            <p>Halo $to_name,</p>
                            <p>Tiket support baru telah ditugaskan kepada Anda:</p>
                            <p><strong>ID Tiket:</strong> #$ticket_id</p>
                            <p><strong>Judul:</strong> $ticket_title</p>
                            <p>Silakan klik tombol di bawah untuk melihat detail tiket dan mulai mengerjakannya:</p>
                            <p><a href='$ticket_url' class='button'>Lihat & Kerjakan Tiket</a></p>
                            <p>Terima kasih atas perhatian Anda.</p>
                            <p>Salam,<br>Tim Support</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Helpdesk Ticketing System. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $this->mailer->Body = $body;

            if ($this->mailer->send()) {
                $this->log('Ticket assignment notification sent to ' . $to_email . ' for ticket #' . $ticket_id);
                return true;
            }
        } catch (Exception $e) {
            $this->log('Error sending ticket assignment notification: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Send status change notification
     */
    public function sendStatusChangeNotification($to_email, $to_name, $ticket_id, $ticket_title, $new_status) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to_email, $to_name);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Status Tiket Berubah - #' . $ticket_id . ' (' . $new_status . ')';

            $ticket_url = APP_URL . '/tickets/detail?id=' . $ticket_id;

            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; }
                        .header { background: #ffc107; color: #333; padding: 20px; border-radius: 5px 5px 0 0; }
                        .content { background: white; padding: 20px; }
                        .footer { background: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; }
                        .button { display: inline-block; background: #ffc107; color: #333; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                        .status-badge { display: inline-block; background: #ffc107; color: #333; padding: 5px 10px; border-radius: 3px; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Status Tiket Anda Telah Berubah</h2>
                        </div>
                        <div class='content'>
                            <p>Halo $to_name,</p>
                            <p>Status tiket support Anda telah diperbarui:</p>
                            <p><strong>ID Tiket:</strong> #$ticket_id</p>
                            <p><strong>Judul:</strong> $ticket_title</p>
                            <p><strong>Status Baru:</strong> <span class='status-badge'>$new_status</span></p>
                            <p>Silakan klik tombol di bawah untuk melihat detail terbaru:</p>
                            <p><a href='$ticket_url' class='button'>Lihat Detail Tiket</a></p>
                            <p>Terima kasih.</p>
                            <p>Salam,<br>Tim Support</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Helpdesk Ticketing System. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $this->mailer->Body = $body;

            if ($this->mailer->send()) {
                $this->log('Status change notification sent to ' . $to_email . ' for ticket #' . $ticket_id);
                return true;
            }
        } catch (Exception $e) {
            $this->log('Error sending status change notification: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Send new comment notification
     */
    public function sendNewCommentNotification($to_email, $to_name, $ticket_id, $ticket_title, $commenter_name) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to_email, $to_name);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Komentar Baru pada Tiket - #' . $ticket_id;

            $ticket_url = APP_URL . '/tickets/detail?id=' . $ticket_id;

            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; background: #f5f5f5; padding: 20px; }
                        .header { background: #17a2b8; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
                        .content { background: white; padding: 20px; }
                        .footer { background: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; }
                        .button { display: inline-block; background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Komentar Baru pada Tiket Anda</h2>
                        </div>
                        <div class='content'>
                            <p>Halo $to_name,</p>
                            <p>Ada komentar baru pada tiket Anda dari $commenter_name:</p>
                            <p><strong>ID Tiket:</strong> #$ticket_id</p>
                            <p><strong>Judul:</strong> $ticket_title</p>
                            <p>Silakan klik tombol di bawah untuk membaca komentar selengkapnya:</p>
                            <p><a href='$ticket_url' class='button'>Lihat Komentar</a></p>
                            <p>Terima kasih.</p>
                            <p>Salam,<br>Tim Support</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2024 Helpdesk Ticketing System. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $this->mailer->Body = $body;

            if ($this->mailer->send()) {
                $this->log('New comment notification sent to ' . $to_email . ' for ticket #' . $ticket_id);
                return true;
            }
        } catch (Exception $e) {
            $this->log('Error sending new comment notification: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Log email activity
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message\n";
        file_put_contents($this->log_file, $log_message, FILE_APPEND);
    }
}
?>