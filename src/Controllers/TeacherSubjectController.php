<?php
namespace Src\Controllers;

use Src\Core\Request;
use Src\Database\Connection;
use Src\Helpers\ValidationHelper;

class TeacherSubjectController {

    private $conn;

    public function __construct() {
        $this->conn = Connection::make();
        session_start();
    }

    // ---------------- SUBJECT CREATE ----------------
    public function createSubject() {
        $tid  = $_SESSION['user_id'];
        $data = Request::body();

        if (!ValidationHelper::validate(['subname'], $data)) return;

        $subname = $data['subname'];

        $stmt = $this->conn->prepare("INSERT INTO subjects (subname, tid) VALUES (?, ?)");
        $stmt->bind_param("si", $subname, $tid);

        if ($stmt->execute()) {
            echo json_encode(['status'=>'success','message'=>'Subject created','subid'=>$stmt->insert_id]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to create subject']);
        }
    }

    // ---------------- SUBJECT DELETE ----------------
    public function deleteSubject() {
        $tid = $_SESSION['user_id'];
        $data = $_GET;

        if (!ValidationHelper::validate(['id'], $data)) return;

        $subid = $data['id'];

        $stmt = $this->conn->prepare("DELETE FROM subjects WHERE subid=? AND tid=?");
        $stmt->bind_param("ii", $subid, $tid);

        if ($stmt->execute()) {
            echo json_encode(['status'=>'success','message'=>'Subject deleted']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to delete subject']);
        }
    }

    // ---------------- ASSIGN STUDENT ----------------
    public function assignStudent() {
        $tid  = $_SESSION['user_id'];
        $data = Request::body();

        if (!ValidationHelper::validate(['studid', 'subid'], $data)) return;

        $studid = $data['studid'];
        $subid  = $data['subid'];

        $stmt = $this->conn->prepare("SELECT subid FROM subjects WHERE subid=? AND tid=? LIMIT 1");
        $stmt->bind_param("ii", $subid, $tid);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            echo json_encode(['status'=>'error','message'=>'Subject not found or unauthorized']);
            return;
        }

        $stmt = $this->conn->prepare("INSERT INTO sub_stud (studid, subid) VALUES (?, ?)");
        $stmt->bind_param("ii", $studid, $subid);

        if ($stmt->execute()) {
            echo json_encode(['status'=>'success','message'=>'Student assigned']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to assign student']);
        }
    }

    // ---------------- REMOVE STUDENT ----------------
    public function removeStudent() {
        $tid = $_SESSION['user_id'];
        $data = $_GET;

        if (!ValidationHelper::validate(['sid'], $data)) return;

        $studid = $data['sid'];

        $stmt = $this->conn->prepare("
            DELETE ss FROM sub_stud ss
            INNER JOIN subjects s ON ss.subid = s.subid
            WHERE ss.studid=? AND s.tid=?
        ");
        $stmt->bind_param("ii", $studid, $tid);

        if ($stmt->execute()) {
            echo json_encode(['status'=>'success','message'=>'Student removed']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to remove student']);
        }
    }

    // ---------------- GET ALL STUDENTS ----------------
    public function getAllStudents() {
        $stmt = $this->conn->prepare("SELECT id, name, mail FROM users WHERE type=2");
        $stmt->execute();
        $result = $stmt->get_result();

        echo json_encode([
            'status'=>'success',
            'students'=>$result->fetch_all(MYSQLI_ASSOC)
        ]);
    }

    // ---------------- GET STUDENTS OF ONE SUBJECT ----------------
    public function getSubjectStudents() {
        $tid = $_SESSION['user_id'];
        $data = $_GET;

        if (!ValidationHelper::validate(['subid'], $data)) return;

        $subid = $data['subid'];

        $stmt = $this->conn->prepare("
            SELECT u.id, u.name, u.mail
            FROM sub_stud ss
            INNER JOIN users u ON ss.studid = u.id
            INNER JOIN subjects s ON ss.subid = s.subid
            WHERE ss.subid=? AND s.tid=?
        ");
        $stmt->bind_param("ii", $subid, $tid);
        $stmt->execute();
        $result = $stmt->get_result();

        echo json_encode([
            'status'=>'success',
            'students'=>$result->fetch_all(MYSQLI_ASSOC)
        ]);
    }
}
