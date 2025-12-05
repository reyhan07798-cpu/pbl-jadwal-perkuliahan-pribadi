<?php
// BARIS INI DITAMBAHKAN UNTUK MEMATIKAN WARNING PHP YANG BISA MERUSAK FORMAT JSON
error_reporting(0);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Cek apakah user sudah login
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized. User not logged in.']);
    exit();
}

 $user_id = $_SESSION['id'];

// --- KODE KONEKSI DATABASE DI-SALIN LANGSUNG KE SINI UNTUK DEBUGGING ---
// Kami sementara TIDAK menggunakan require_once
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'db_jadwal');

 $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['message' => 'ERROR: Tidak dapat terhubung ke database. ' . $conn->connect_error]);
    exit();
}
// --- AKHIR KODE KONEKSI ---


// Ambil data yang dikirim dalam format JSON
 $data = json_decode(file_get_contents("php://input"));

// Validasi data yang diterima
if (
    !isset($data->course_name) ||
    !isset($data->sks) ||
    !isset($data->dosen) ||
    !isset($data->day_of_week) ||
    !isset($data->start_time) ||
    !isset($data->room)
) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data.']);
    exit();
}

// Gunakan transaksi untuk memastikan kedua query berhasil atau gagal bersama-sama
 $conn->begin_transaction();

try {
    // 1. Insert data mata kuliah ke tabel 'courses'
    $sql_course = "INSERT INTO courses (course_name, sks, dosen) VALUES (?, ?, ?)";
    $stmt_course = $conn->prepare($sql_course);
    $stmt_course->bind_param("sis", $data->course_name, $data->sks, $data->dosen);
    
    if (!$stmt_course->execute()) {
        throw new Exception("Gagal menyimpan mata kuliah: " . $stmt_course->error);
    }
    
    // Dapatkan ID dari mata kuliah yang baru saja dibuat
    $course_id = $conn->insert_id;

    // 2. Insert data jadwal ke tabel 'schedules'
    $sql_schedule = "INSERT INTO schedules (user_id, course_id, day_of_week, start_time, end_time, room) VALUES (?, ?, ?, ?, ?, ?)";
    
    // Jika jam selesai tidak dikirim, set otomatis 2 jam setelah jam mulai
    $end_time = isset($data->end_time) ? $data->end_time : date('H:i:s', strtotime($data->start_time) + 7200);
    
    $stmt_schedule = $conn->prepare($sql_schedule);
    $stmt_schedule->bind_param("iissss", $user_id, $course_id, $data->day_of_week, $data->start_time, $end_time, $data->room);

    if (!$stmt_schedule->execute()) {
        throw new Exception("Gagal menyimpan jadwal: " . $stmt_schedule->error);
    }

    // Jika semua query berhasil, commit transaksi
    $conn->commit();
    
    echo json_encode(['message' => 'Course and schedule saved successfully.', 'course_id' => $course_id]);

} catch (Exception $e) {
    // Jika ada error, rollback semua perubahan
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}

 $stmt_course->close();
 $stmt_schedule->close();
 $conn->close();
?>