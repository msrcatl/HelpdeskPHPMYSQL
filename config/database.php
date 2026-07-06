<?php
/**
 * Database Configuration
 * Konfigurasi koneksi MySQL
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'helpdesk_db');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Connection class
class Database {
    private $conn;
    private $stmt;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASSWORD,
            DB_NAME,
            DB_PORT
        );

        // Check connection
        if ($this->conn->connect_error) {
            die('Connection Failed: ' . $this->conn->connect_error);
        }

        // Set charset
        if (!$this->conn->set_charset(DB_CHARSET)) {
            die('Error loading character set utf8mb4: ' . $this->conn->error);
        }
    }

    // Prepare statement
    public function prepare($query) {
        $this->stmt = $this->conn->prepare($query);

        if (!$this->stmt) {
            die('Prepare failed: ' . $this->conn->error);
        }

        return $this;
    }

    // Bind values
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = MYSQLI_TYPE_LONG;
                    break;
                case is_float($value):
                    $type = MYSQLI_TYPE_DOUBLE;
                    break;
                case is_string($value):
                    $type = MYSQLI_TYPE_STRING;
                    break;
                default:
                    $type = MYSQLI_TYPE_STRING;
            }
        }

        $this->stmt->bind_param($type, $value);
        return $this;
    }

    // Execute query
    public function execute() {
        if (!$this->stmt->execute()) {
            die('Execute failed: ' . $this->stmt->error);
        }

        return $this;
    }

    // Get result
    public function getResult() {
        return $this->stmt->get_result();
    }

    // Fetch single row
    public function fetchRow() {
        $result = $this->getResult();
        return $result->fetch_assoc();
    }

    // Fetch all rows
    public function fetchAll() {
        $result = $this->getResult();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    // Get row count
    public function rowCount() {
        return $this->stmt->affected_rows;
    }

    // Get last insert ID
    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    // Close connection
    public function closeConnection() {
        if ($this->stmt) {
            $this->stmt->close();
        }
        $this->conn->close();
    }
}

// Create database instance
$db = new Database();
?>
