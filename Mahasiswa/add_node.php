<?php
header('Content-Type: application/json');
require_once "../config.php";

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { http_response_code(401); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $note_date = $_POST['note_date'];

    $sql = "INSERT INTO notes (user_id, title, content, note_date) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isss", $user_id, $title, $content, $note_date);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Catatan berhasil disimpan.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan catatan.']);
        }
        $stmt->close();
    }
}
 $conn->close();
?>