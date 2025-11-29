<?php
header('Content-Type: application/json');
require_once "../config.php";

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { http_response_code(401); exit; }

 $user_id = $_SESSION['id'];
 $notes = [];

 $sql = "SELECT id, title, content, note_date FROM notes WHERE user_id = ? ORDER BY note_date DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
    $stmt->close();
}
 $conn->close();
echo json_encode($notes);
?>