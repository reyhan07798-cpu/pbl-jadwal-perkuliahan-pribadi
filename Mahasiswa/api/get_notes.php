<?php
// get_notes.php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak. Anda harus login."));
    exit();
}
 $user_id = $_SESSION["id"];

require_once '../../koneksi.php';

// PERUBAHAN: Tambahkan note_date ke SELECT dan urutkan berdasarkan tanggal
 $sql = "SELECT id, title, content, note_date, created_at, updated_at FROM notes WHERE user_id = ? ORDER BY note_date DESC, updated_at DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notes_arr = array();
    while ($row = $result->fetch_assoc()) {
        $notes_arr[] = $row;
    }

    http_response_code(200);
    echo json_encode($notes_arr);
    $stmt->close();
}

 $conn->close();
?>