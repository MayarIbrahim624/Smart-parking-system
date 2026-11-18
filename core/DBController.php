<?php
class DBController
{
    public $conn;
    public $dbHost = "localhost";
    public $dbUser = "root";
    public $dbPassword = "";
    public $dbName = "smartparking";

    public function __construct()
    {
        $this->conn = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function select($qry)
    {
        $result = $this->conn->query($qry);
        if (!$result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert($qry)
    {
        if ($this->conn->query($qry)) return $this->conn->insert_id;
        return false;
    }

    public function execute($qry)
    {
        return $this->conn->query($qry);
    }

    public function escape($str)
    {
        return $this->conn->real_escape_string($str);
    }
}