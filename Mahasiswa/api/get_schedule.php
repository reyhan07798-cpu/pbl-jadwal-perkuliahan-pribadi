<?php
// Tambahkan baris ini untuk menampilkan semua error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mengizinkan request dari domain manapun (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Memulai session untuk mendapatkan user_id
session_start();

// Cek apakah user sudah login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    // Jika tidak, kirim response error
    http_response_code(401); // Unauthorized
    echo json_encode(array("message" => "Akses ditolak. User tidak login."));
    exit();
}

// Mendapatkan user_id dari session. Asumsikan Anda menyimpan user_id di session saat login.
// Jika tidak, Anda perlu query username ke tabel users untuk mendapatkan ID-nya.
// Di sini saya asumsikan user_id sudah ada di session.
 $user_id = $_SESSION["id"]; // PASTIKAN ANDA MENYIMPAN user_id SAAT LOGIN

// Sertakan file koneksi
require_once('../../koneksi.php');

// Query untuk mengambil data jadwal dan mata kuliah
// JOIN tabel schedules dengan courses berdasarkan course_id
 $sql = "SELECT 
            s.id AS schedule_id,
            s.course_id,
            s.day_of_week,
            s.start_time,
            s.end_time,
            c.course_name,
            c.sks,
            c.dosen,
            c.room
        FROM 
            schedules s
        JOIN 
            courses c ON s.course_id = c.id
        WHERE 
            s.user_id = ?
        ORDER BY 
            FIELD(s.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), s.start_time";

if ($stmt = $conn->prepare($sql)) {
    // Bind variable user_id ke parameter
    $stmt->bind_param("i", $user_id);
    
    // Eksekusi query
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        $schedule_arr = array();
        $schedule_arr["records"] = array();
        
        while ($row = $result->fetch_assoc()) {
            extract($row);
            
            $schedule_item = array(
                "schedule_id" => $schedule_id,
                "course_id" => $course_id,
                "course_name" => $course_name,
                "sks" => $sks,
                "dosen" => $dosen,
                "room" => $room,
                "day_of_week" => $day_of_week,
                "start_time" => $start_time,
                "end_time" => $end_time
            );
            
            array_push($schedule_arr["records"], $schedule_item);
        }
        
        // Set response code 200 OK
        http_response_code(200);
        
        // Tampilkan data dalam format JSON
        echo json_encode($schedule_arr["records"]);
        
    } else {
        // Set response code 500 Internal Server Error
        http_response_code(500);
        echo json_encode(array("message" => "Tidak dapat mengambil jadwal."));
    }
    
    // Tutup statement
    $stmt->close();
}

// Tutup koneksi
 $conn->close();
?>