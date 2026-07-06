<?php
/**
 * Email Configuration
 * Konfigurasi PHPMailer untuk SMTP
 */

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'your-email@gmail.com');
define('FROM_NAME', 'Helpdesk Ticketing System');
define('SMTP_SECURE', 'tls');
define('SMTP_DEBUG', 0);

// Email template paths
define('EMAIL_TEMPLATE_PATH', __DIR__ . '/../views/emails/');

// Log file
define('EMAIL_LOG_PATH', __DIR__ . '/../logs/email.log');

?>
