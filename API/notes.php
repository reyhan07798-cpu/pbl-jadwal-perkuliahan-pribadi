<?php
// ... (Header dan session check sama seperti courses.php) ...
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized.']);
    exit();
}

 $user_id = $_SESSION['id'];
require_once '../config/database.php';

 $method = $_SERVER['REQUEST_METHOD'];
 $input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM notes WHERE user_id = ? ORDER BY date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'POST':
        $sql = "INSERT INTO notes (user_id, title, content, date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $input['title'], $input['content'], $input['date']);
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['message' => 'Note created.', 'id' => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create note.']);
        }
        break;

    case 'PUT':
        $sql = "UPDATE notes SET title = ?, content = ?, date = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $input['title'], $input['content'], $input['date'], $input['id'], $user_id);
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Note updated.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update note.']);
        }
        break;

    case 'DELETE':
        $note_id = $_GET['id'];
        $sql = "DELETE FROM notes WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $note_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['message' => 'Note deleted.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete note.']);
        }
        break;
}

 $conn->close();
?>