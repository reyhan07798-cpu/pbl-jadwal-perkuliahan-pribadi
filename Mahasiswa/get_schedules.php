<?php
header('Content-Type: application/json');
require_once "../config.php";

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { http_response_code(401); exit; }

 $user_id = $_SESSION['id'];
 $schedules = [];

 $sql = "SELECT s.id, s.day_of_week, s.start_time, s.end_time, s.room, c.course_name 
        FROM schedules s
        JOIN courses c ON s.course_id = c.id
        WHERE s.user_id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
    $stmt->close();
}
 $conn->close();
echo json_encode($schedules);
?>