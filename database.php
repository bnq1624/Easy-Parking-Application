<?php
class Database {
    private $servername = "localhost";
    private $username = "root";     
    private $password = "csit115";
    private $dbname = "easyparking";
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    # make query
    public function query($sql) {
        return $this->conn->query($sql);
    }

    # prepared statements
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function close() {
        $this->conn->close();
    }
}
?>
