<?php
namespace Src\Controllers;

use Src\Core\Request;
use Src\Database\Connection;
use Src\Helpers\ValidationHelper;

class TeacherStudentController {

    private $conn;

    public function __construct() {
        $this->conn = Connection::make();
        session_start();
    }

    public function createStudent() {
        $data = Request::body();
        if (!ValidationHelper::validate(['name','mail','password'], $data)) return;

        $name = $data['name'];
        $mail = $data['mail'];
        $password = $data['password'];

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE mail=? LIMIT 1");
        $stmt->bind_param("s", $mail);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['status'=>'error','message'=>'Email already exists']);
            return;
        }

        $stmt = $this->conn->prepare("INSERT INTO users (name, mail, password, type) VALUES (?, ?, ?, 2)");
        $stmt->bind_param("sss", $name, $mail, $password);
        if ($stmt->execute()) {
            echo json_encode(['status'=>'success','message'=>'Student created','student_id'=>$stmt->insert_id]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to create student']);
        }
    }

    public function deleteStudent() {
        $data = $_GET;
        if (!ValidationHelper::validate(['id'], $data)) return;

        $studid = $data['id'];

        $stmt = $this->conn->prepare("SELECT id FROM users WHERE id=? AND type=2 LIMIT 1");
        $stmt->bind_param("i", $studid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            echo json_encode(['status'=>'error','message'=>'Student not found']);
            return;
        }

        $stmt = $this->conn->prepare("DELETE FROM users WHERE id=? AND type=2");
        $stmt->bind_param("i", $studid);
        if ($stmt->execute()) {
            echo json_encode(['status'=>'success','message'=>'Student deleted']);
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to delete student']);
        }
    }

    public function markAttendence() {
        $data = Request::body();
        if (!ValidationHelper::validate(['subid','att_date','hour','records'], $data)) return;

        $subid = $data['subid'];
        $att_date = $data['att_date'];
        $hour = $data['hour'];
        $records = $data['records'];
        $tid = $_SESSION['user_id'];

        $stmt = $this->conn->prepare("SELECT subid FROM subjects WHERE subid=? AND tid=? LIMIT 1");
        $stmt->bind_param("ii", $subid, $tid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            echo json_encode(['status'=>'error','message'=>'Subject not found or unauthorized']);
            return;
        }

        $stmt = $this->conn->prepare("
            INSERT INTO attendance (subid, sid, att_date, hour, status)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status=VALUES(status)
        ");

        foreach ($records as $rec) {
            if (!ValidationHelper::validate(['sid','status'], $rec)) return;
            $sid = $rec['sid'] ?? null;
            $status = $rec['status'] ?? null;

            if (!$sid || !in_array($status, ['present','absent'])) continue;

            $check = $this->conn->prepare("SELECT * FROM sub_stud WHERE studid=? AND subid=? LIMIT 1");
            $check->bind_param("ii", $sid, $subid);
            $check->execute();
            if ($check->get_result()->num_rows === 0) continue;

            $stmt->bind_param("iisis", $subid, $sid, $att_date, $hour, $status);
            $stmt->execute();
        }

        echo json_encode(['status'=>'success','message'=>'Attendance recorded']);
    }
}
