<?php
// BARIS INI DITAMBAHKAN UNTUK MEMATIKAN WARNING PHP YANG BISA MERUSAK FORMAT JSON
error_reporting(0);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized. User not logged in.']);
    exit();
}

 $user_id = $_SESSION['id'];
require_once '../config/database.php';

// Query untuk mengambil jadwal beserta detail mata kuliahnya
 $sql = "SELECT s.*, c.course_name, c.sks, c.dosen 
        FROM schedules s 
        LEFT JOIN courses c ON s.course_id = c.id 
        WHERE s.user_id = ? 
        ORDER BY FIELD(s.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), s.start_time";

 $stmt = $conn->prepare($sql);
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $result = $stmt->get_result();

 $schedules = array();
while($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

echo json_encode($schedules);

 $stmt->close();
 $conn->close();
?>