<?php
// BARIS INI DITAMBAHKAN UNTUK MEMATIKAN WARNING PHP YANG BISA MERUSAK FORMAT JSON
error_reporting(0);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Cek apakah user sudah login
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized. User not logged in.']);
    exit();
}

 $user_id = $_SESSION['id'];
require_once '../config/database.php';

// Ambil data yang dikirim dalam format JSON
 $data = json_decode(file_get_contents("php://input"));

// Validasi data yang diterima
if (
    !isset($data->course_id) ||
    !isset($data->new_day_of_week) ||
    !isset($data->new_start_time)
) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data for update.']);
    exit();
}

try {
    // Update data jadwal di tabel 'schedules'
    $sql = "UPDATE schedules SET day_of_week = ?, start_time = ? WHERE user_id = ? AND course_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $data->new_day_of_week, $data->new_start_time, $user_id, $data->course_id);
    
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Schedule updated successfully.']);
    } else {
        throw new Exception("Gagal memperbarui jadwal: " . $stmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}

 $stmt->close();
 $conn->close();
?>