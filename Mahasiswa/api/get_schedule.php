<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak."));
    exit();
}

 $user_id = $_SESSION["id"];

require_once '../../koneksi.php';

 $sql = "SELECT s.id as schedule_id, c.id as course_id, c.course_name, c.sks, c.dosen, c.room, s.day_of_week, s.start_time, s.end_time
        FROM schedules s
        JOIN courses c ON s.course_id = c.id
        WHERE c.user_id = ?
        ORDER BY s.day_of_week, s.start_time";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $schedules_arr = array();
    while ($row = $result->fetch_assoc()) {
        $schedules_arr[] = $row;
    }

    http_response_code(200);
    echo json_encode($schedules_arr);
    $stmt->close();
}

 $conn->close();
?>