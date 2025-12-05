<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized.']);
    exit();
}

 $user_id = $_SESSION['id'];
require_once '../config/database.php';

 $method = $_SERVER['REQUEST_METHOD'];
 $input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM courses WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'POST':
        $sql = "INSERT INTO courses (user_id, course_name, sks, dosen) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isis", $user_id, $input['course_name'], $input['sks'], $input['dosen']);
        
        if ($stmt->execute()) {
            $new_course_id = $conn->insert_id;
            // Tambahkan juga ke schedules
            $schedule_sql = "INSERT INTO schedules (user_id, course_id, day_of_week, start_time, end_time, room) VALUES (?, ?, ?, ?, ?, ?)";
            $schedule_stmt = $conn->prepare($schedule_sql);
            $schedule_stmt->bind_param("iissss", $user_id, $new_course_id, $input['day_of_week'], $input['start_time'], $input['end_time'], $input['room']);
            $schedule_stmt->execute();

            http_response_code(201);
            echo json_encode(['message' => 'Course created.', 'id' => $new_course_id]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create course.']);
        }
        break;

    case 'PUT':
        $sql = "UPDATE courses SET course_name = ?, sks = ?, dosen = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisii", $input['course_name'], $input['sks'], $input['dosen'], $input['id'], $user_id);
        
        if ($stmt->execute()) {
             // Update juga schedules
             $schedule_sql = "UPDATE schedules SET day_of_week = ?, start_time = ?, end_time = ?, room = ? WHERE course_id = ? AND user_id = ?";
             $schedule_stmt = $conn->prepare($schedule_sql);
             $schedule_stmt->bind_param("ssssii", $input['day_of_week'], $input['start_time'], $input['end_time'], $input['room'], $input['id'], $user_id);
             $schedule_stmt->execute();

            echo json_encode(['message' => 'Course updated.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update course.']);
        }
        break;

    case 'DELETE':
        $course_id = $_GET['id'];
        // Menghapus dari courses akan otomatis menghapus dari schedules karena ON DELETE CASCADE
        $sql = "DELETE FROM courses WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $course_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Course deleted.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete course.']);
        }
        break;
}

 $conn->close();
?>