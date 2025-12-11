<?php
// edit_note.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak. Anda harus login."));
    exit();
}
 $user_id = $_SESSION["id"];

require_once '../../koneksi.php';

 $data = json_decode(file_get_contents("php://input"));

// PERUBAHAN: Tambahkan validasi untuk note_date
if (!empty($data->id) && !empty($data->title) && !empty($data->content) && !empty($data->note_date)) {
    // PERUBAHAN: Tambahkan note_date ke query UPDATE
    $sql = "UPDATE notes SET title = ?, content = ?, note_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        // PERUBAHAN: Tambahkan 's' untuk tipe string note_date dan sesuaikan urutan
        $stmt->bind_param("sssii", $data->title, $data->content, $data->note_date, $data->id, $user_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                http_response_code(200);
                echo json_encode(array("message" => "Catatan berhasil diperbarui."));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Catatan tidak ditemukan atau bukan milik Anda."));
            }
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Gagal memperbarui catatan."));
        }
        $stmt->close();
    }
} else {
    // PERUBAHAN: Perbarui pesan error
    http_response_code(400);
    echo json_encode(array("message" => "Data tidak lengkap. ID, judul, konten, dan tanggal catatan diperlukan."));
}

 $conn->close();
?>