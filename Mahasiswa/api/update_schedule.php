<?php
// Header untuk CORS dan JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Memulai session
session_start();

// Cek login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])){
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak."));
    exit();
}
 $user_id = $_SESSION["id"];

// Sertakan file koneksi
require_once '../koneksi.php';

// Ambil data yang dikirim (dalam format JSON)
 $data = json_decode(file_get_contents("php://input"));

// Pastikan data tidak kosong
if(
    !empty($data->course_id) &&
    !empty($data->new_day_of_week) &&
    !empty($data->new_start_time)
){
    // Query untuk update jadwal
    $sql = "UPDATE schedules SET day_of_week = ?, start_time = ? WHERE course_id = ? AND user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind variable
        $stmt->bind_param("ssii", $data->new_day_of_week, $data->new_start_time, $data->course_id, $user_id);
        
        // Eksekusi query
        if ($stmt->execute()) {
            // Cek apakah ada baris yang berubah
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
        
        // Tutup statement
        $stmt->close();
    }
} else {
    // Data tidak lengkap
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Gagal memperbarui. Data tidak lengkap."));
}

// Tutup koneksi
 $conn->close();
?>