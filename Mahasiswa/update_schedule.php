<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

// Cek login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])){
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak."));
    exit();
}
 $user_id = $_SESSION["id"];
require_once '../koneksi.php';

 $data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->course_id) &&
    !empty($data->new_day_of_week) &&
    !empty($data->new_start_time)
){
    $sql = "UPDATE schedules SET day_of_week = ?, start_time = ? WHERE course_id = ? AND user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssii", $data->new_day_of_week, $data->new_start_time, $data->course_id, $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200); // OK
                echo json_encode(array("message" => "Jadwal berhasil diperbarui."));
            } else {
                http_response_code(404); // Not Found
                echo json_encode(array("message" => "Jadwal tidak ditemukan atau tidak ada perubahan."));
            }
        } else {
            http_response_code(503); // Service Unavailable
            echo json_encode(array("message" => "Gagal memperbarui jadwal."));
        }
        
        $stmt->close();
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Gagal memperbarui. Data tidak lengkap."));
}
 $conn->close();
?>