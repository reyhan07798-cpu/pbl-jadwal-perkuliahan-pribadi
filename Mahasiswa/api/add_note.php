<?php
// add_note.php
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
if (!empty($data->title) && !empty($data->content) && !empty($data->note_date)) {
    // PERUBAHAN: Tambahkan note_date ke query
    $sql = "INSERT INTO notes (user_id, title, content, note_date) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        // PERUBAHAN: Tambahkan 's' untuk tipe string note_date
        $stmt->bind_param("isss", $user_id, $data->title, $data->content, $data->note_date);
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Catatan berhasil ditambahkan."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Gagal menambah catatan."));
        }
        $stmt->close();
    }
} else {
    // PERUBAHAN: Perbarui pesan error
    http_response_code(400);
    echo json_encode(array("message" => "Data tidak lengkap. Judul, konten, dan tanggal catatan diperlukan."));
}

 $conn->close();
?>