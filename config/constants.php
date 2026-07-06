<?php
/**
 * Global Constants
 */

// Application
define('APP_NAME', 'Helpdesk Ticketing System');
define('APP_URL', 'http://localhost/helpdesk');
define('APP_VERSION', '1.0.0');

// Paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads/attachments/');

// Session
define('SESSION_TIMEOUT', 3600); // 1 hour

// Upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip']);

// Ticket status
define('TICKET_STATUS_OPEN', 'Open');
define('TICKET_STATUS_IN_PROGRESS', 'In Progress');
define('TICKET_STATUS_RESOLVED', 'Resolved');
define('TICKET_STATUS_CLOSED', 'Closed');

$TICKET_STATUSES = [
    'Open' => 'badge-primary',
    'In Progress' => 'badge-warning',
    'Resolved' => 'badge-success',
    'Closed' => 'badge-secondary'
];

// Ticket priority
define('PRIORITY_LOW', 'Low');
define('PRIORITY_MEDIUM', 'Medium');
define('PRIORITY_HIGH', 'High');
define('PRIORITY_URGENT', 'Urgent');

$TICKET_PRIORITIES = [
    'Low' => 'badge-info',
    'Medium' => 'badge-warning',
    'High' => 'badge-danger',
    'Urgent' => 'badge-danger'
];

// Ticket categories
$TICKET_CATEGORIES = [
    'Technical Support',
    'Billing',
    'Account',
    'Feature Request',
    'Bug Report',
    'General Inquiry'
];

// User roles
define('ROLE_ADMIN', 'Admin');
define('ROLE_AGENT', 'Support Agent');
define('ROLE_USER', 'User');

$USER_ROLES = [
    'Admin' => 'Admin',
    'Support Agent' => 'Support Agent',
    'User' => 'User'
];

?>
