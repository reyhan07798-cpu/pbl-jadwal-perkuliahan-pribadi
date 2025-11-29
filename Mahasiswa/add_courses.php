<?php
header('Content-Type: application/json');
require_once "../config.php";

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { http_response_code(401); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['id'];
    $course_name = $_POST['course_name'];
    $lecturer = $_POST['lecturer'];
    $course_code = $_POST['course_code'];

    $sql = "INSERT INTO courses (user_id, course_name, lecturer, course_code) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isss", $user_id, $course_name, $lecturer, $course_code);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Mata kuliah berhasil ditambahkan.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan mata kuliah.']);
        }
        $stmt->close();
    }
}
 $conn->close();
?>