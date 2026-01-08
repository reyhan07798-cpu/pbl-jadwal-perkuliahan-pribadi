<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak. User tidak login atau session habis."));
    exit();
}

 $user_id = $_SESSION["id"];

require_once '../../koneksi.php';

 $data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->course_name) &&
    !empty($data->sks) &&
    !empty($data->dosen) &&
    !empty($data->day_of_week) &&
    !empty($data->start_time) &&
    !empty($data->end_time) &&
    !empty($data->room)
) {
    $conn->begin_transaction();

    try {
        $sql_course = "INSERT INTO courses (user_id, course_name, sks, dosen, room) VALUES (?, ?, ?, ?, ?)";
        $stmt_course = $conn->prepare($sql_course);
        $stmt_course->bind_param("isiss", $user_id, $data->course_name, $data->sks, $data->dosen, $data->room);
        $stmt_course->execute();

        $course_id = $conn->insert_id;

        $sql_schedule = "INSERT INTO schedules (user_id, course_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?)";
        $stmt_schedule = $conn->prepare($sql_schedule);
        $stmt_schedule->bind_param("iisss", $user_id, $course_id, $data->day_of_week, $data->start_time, $data->end_time);
        $stmt_schedule->execute();
        $conn->commit();

        http_response_code(201);
        echo json_encode(array("message" => "Mata kuliah dan jadwal berhasil ditambahkan."));
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(503);
        echo json_encode(array("message" => "Gagal menambah data. Error: " . $e->getMessage()));
    }

    $stmt_course->close();
    $stmt_schedule->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Gagal menambah data. Data tidak lengkap. Mohon periksa kembali inputan Anda."));
}

 $conn->close();
?>