<?php
header('Content-Type: application/json');
require_once "../config.php";

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { http_response_code(401); exit; }

 $data = json_decode(file_get_contents("php://input"));
 $note_id = $data->id;
 $user_id = $_SESSION['id'];

 $sql = "DELETE FROM notes WHERE id = ? AND user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $note_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
}
 $conn->close();
?>