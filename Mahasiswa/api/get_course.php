<?php
// get_course.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Memulai session
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(array("message" => "Akses ditolak."));
    exit();
}

// Ambil user_id dari session
 $user_id = $_SESSION["id"];

// Sertakan file koneksi
// Path ini naik dua tingkat dari folder api ke folder utama
require_once '../../koneksi.php';

// Cek apakah parameter 'id' ada di URL
if (isset($_GET['id'])) {
    $course_id = $_GET['id'];

    // Query untuk mengambil data course berdasarkan ID dan user
    $sql = "SELECT c.id, c.course_name, c.sks, c.dosen, c.room, s.day_of_week, s.start_time, s.end_time
            FROM courses c
            LEFT JOIN schedules s ON c.id = s.course_id
            WHERE c.id = ? AND c.user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $course_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Jika data ditemukan, kembalikan dalam format JSON
            echo json_encode($row);
        } else {
            // Jika tidak ditemukan, kirim response 404
            http_response_code(404);
            echo json_encode(array("message" => "Mata kuliah tidak ditemukan atau bukan milik Anda."));
        }
        $stmt->close();
    }
} else {
    // Jika parameter ID tidak dikirim, kirim response 400
    http_response_code(400);
    echo json_encode(array("message" => "Parameter ID tidak disertakan dalam permintaan."));
}

// Tutup koneksi
 $conn->close();
?>