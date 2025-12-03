<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../config.php';
session_start();

if (!isset($_SESSION["loggedin"])) {
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["success" => false, "msg" => "Method not allowed"]);
    exit;
}

$nama_mk   = $_POST['course_name'];
$sks       = $_POST['sks'];
$dosen     = $_POST['dosen'];
$ruangan   = $_POST['ruangan'];
$hari      = $_POST['hari'];
$jam_mulai = $_POST['jamMulai'];

$sql_course = "INSERT INTO courses (nama_mk, sks, dosen, ruangan) VALUES (?,?,?,?)";
$stmt = $conn->prepare($sql_course);
$stmt->bind_param("siss", $nama_mk, $sks, $dosen, $ruangan);

if ($stmt->execute()) {

    $course_id = $conn->insert_id;

    $sql_schedule = "INSERT INTO schedules (course_id, hari, jam_mulai) VALUES (?,?,?)";
    $stmt2 = $conn->prepare($sql_schedule);
    $stmt2->bind_param("iss", $course_id, $hari, $jam_mulai);

    if ($stmt2->execute()) {
        echo json_encode(["success" => true, "msg" => "Jadwal berhasil ditambahkan"]);
    } else {
        echo json_encode(["success" => false, "msg" => "Gagal tambah jadwal"]);
    }

    $stmt2->close();
}

$stmt->close();
$conn->close();
?>
