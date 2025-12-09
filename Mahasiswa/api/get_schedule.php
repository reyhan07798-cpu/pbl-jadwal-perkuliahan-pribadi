<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak."));
    exit();
}
 $user_id = $_SESSION["id"];

// PATH SUDAH DIPERBAIKI SESUAI STRUKTUR ANDA
require_once '../../koneksi.php';

if (isset($_GET['id'])) {
    $course_id = $_GET['id'];

    // Gunakan LEFT JOIN agar data course tetap diambil meskipun schedule-nya tidak ada
    $sql = "SELECT c.id, c.course_name, c.sks, c.dosen, c.room, s.day_of_week, s.start_time, s.end_time
            FROM courses c
            LEFT JOIN schedules s ON c.id = s.course_id
            WHERE c.id = ? AND c.user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $course_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Mata kuliah tidak ditemukan atau bukan milik Anda."));
        }
        $stmt->close();
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "ID tidak disertakan."));
}

 $conn->close();
?>