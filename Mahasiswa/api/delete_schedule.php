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

 $data = json_decode(file_get_contents("php://input"));

if (!empty($data->course_id)) {
    $course_id = $data->course_id;

    // Karena ada ON DELETE CASCADE, menghapus dari tabel courses akan otomatis menghapus
    // data yang terkait di tabel schedules.
    $sql = "DELETE FROM courses WHERE id = ? AND user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $course_id, $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(array("message" => "Jadwal berhasil dihapus."));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Jadwal tidak ditemukan atau bukan milik Anda."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Gagal menghapus jadwal."));
        }
        $stmt->close();
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Data tidak lengkap. Course ID tidak ditemukan."));
}

 $conn->close();
?>