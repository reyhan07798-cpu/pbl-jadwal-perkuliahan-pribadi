<?php
// delete_note.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
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

if (!empty($data->id)) {
    $sql = "DELETE FROM notes WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $data->id, $user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200);
                echo json_encode(array("message" => "Catatan berhasil dihapus."));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Catatan tidak ditemukan atau bukan milik Anda."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Gagal menghapus catatan."));
        }
        $stmt->close();
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "ID catatan tidak disertakan."));
}

 $conn->close();
?>