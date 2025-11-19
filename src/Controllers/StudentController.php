<?php
namespace Src\Controllers;

use Src\Core\Request;
use Src\Database\Connection;
use Src\Helpers\AuthHelper;

class StudentController {

    private $conn;

    public function __construct() {
        $this->conn = Connection::make();
        session_start();
    }

    public function getMySubjects() {
        $studid = $_SESSION['user_id'];

        $stmt = $this->conn->prepare("
            SELECT s.subname
            FROM sub_stud ss
            INNER JOIN subjects s ON ss.subid = s.subid
            WHERE ss.studid = ?
        ");
        $stmt->bind_param("i", $studid);
        $stmt->execute();

        $result = $stmt->get_result();
        $subjects = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            'status' => 'success',
            'subjects' => $subjects
        ]);
    }
}
