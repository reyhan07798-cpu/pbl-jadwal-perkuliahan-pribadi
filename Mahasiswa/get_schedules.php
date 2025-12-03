<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../config.php';
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT 
            s.id,
            s.hari,
            s.jam_mulai,
            c.nama_mk,
            c.sks,
            c.dosen,
            c.ruangan
        FROM schedules s
        INNER JOIN courses c ON s.course_id = c.id
        ORDER BY FIELD(s.hari, 'Senin','Selasa','Rabu','Kamis','Jumat'), s.jam_mulai";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>
