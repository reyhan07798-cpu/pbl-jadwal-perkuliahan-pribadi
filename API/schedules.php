<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized.']);
    exit();
}

 $user_id = $_SESSION['id'];
require_once '../config/database.php';

// Join schedules dengan courses untuk mendapatkan nama mata kuliah
 $sql = "SELECT s.*, c.course_name, c.sks, c.dosen 
        FROM schedules s 
        JOIN courses c ON s.course_id = c.id 
        WHERE s.user_id = ? 
        ORDER BY FIELD(s.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), s.start_time";
 $stmt = $conn->prepare($sql);
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $result = $stmt->get_result();
echo json_encode($result->fetch_all(MYSQLI_ASSOC));

 $conn->close();
?>