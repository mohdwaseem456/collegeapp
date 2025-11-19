<?php
// src/Database/Connection.php
namespace Src\Database;

class Connection {
    public static function make() {
        $host = "localhost";
        $db   = "college";
        $user = "root";
        $pass = "";
        $conn = new \mysqli($host, $user, $pass, $db);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
