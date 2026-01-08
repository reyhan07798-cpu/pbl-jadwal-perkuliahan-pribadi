<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak."));
    exit();
}

 $user_id = $_SESSION["id"];

require_once '../../koneksi.php';

 $inputJSON = file_get_contents("php://input");
 $input = json_decode($inputJSON);

if (!empty($input)) {
    
    $conn->begin_transaction();
    try {
        $stmt_course = $conn->prepare("UPDATE courses SET course_name = ?, sks = ?, dosen = ?, room = ? WHERE id = ? AND user_id = ?");
        $stmt_course->bind_param("sisisi", 
            $input->course_name, 
            $input->sks, 
            $input->dosen, 
            $input->room,         
            $input->id, 
            $user_id
        );
        
        $executeCourse = $stmt_course->execute();

        if (!$executeCourse) {
            throw new Exception("Gagal update tabel courses: " . $stmt_course->error);
        }

        $stmt_schedule = $conn->prepare("UPDATE schedules SET day_of_week = ?, start_time = ?, end_time = ? WHERE course_id = ? AND user_id = ?");
        $stmt_schedule->bind_param("sssii", 
            $input->day_of_week, 
            $input->start_time, 
            $input->end_time, 
            $input->id, 
            $user_id
        );
        
        $executeSchedule = $stmt_schedule->execute();

        if (!$executeSchedule) {
            throw new Exception("Gagal update tabel schedules: " . $stmt_schedule->error);
        }

        $conn->commit();
        echo json_encode(array("message" => "Jadwal berhasil diperbarui.", "debug_data" => $input)); // Saya tambahkan debug data

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(503);
        echo json_encode(array("message" => "Terjadi kesalahan: " . $e->getMessage()));
    }
    
    $stmt_course->close();
    $stmt_schedule->close();

} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data JSON kosong."));
}

 $conn->close();
?>