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

require_once '../koneksi.php';

 $data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->course_id) &&
    !empty($data->course_name) &&
    !empty($data->sks) &&
    !empty($data->dosen) &&
    !empty($data->room) &&
    !empty($data->day_of_week) &&
    !empty($data->start_time) &&
    !empty($data->end_time)
) {
    $conn->begin_transaction();
    try {
        // Update tabel courses
        $sql_course = "UPDATE courses SET course_name = ?, sks = ?, dosen = ?, room = ? WHERE id = ? AND user_id = ?";
        $stmt_course = $conn->prepare($sql_course);
        $stmt_course->bind_param("sisiii", $data->course_name, $data->sks, $data->dosen, $data->room, $data->course_id, $user_id);
        $stmt_course->execute();

        // Update tabel schedules
        $sql_schedule = "UPDATE schedules SET day_of_week = ?, start_time = ?, end_time = ? WHERE course_id = ? AND user_id = ?";
        $stmt_schedule = $conn->prepare($sql_schedule);
        $stmt_schedule->bind_param("sssii", $data->day_of_week, $data->start_time, $data->end_time, $data->course_id, $user_id);
        $stmt_schedule->execute();

        $conn->commit();
        echo json_encode(array("message" => "Jadwal berhasil diperbarui."));

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(503);
        echo json_encode(array("message" => "Gagal memperbarui jadwal. Error: " . $e->getMessage()));
    }
    $stmt_course->close();
    $stmt_schedule->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data tidak lengkap. Mohon periksa kembali inputan Anda."));
}

 $conn->close();
?>