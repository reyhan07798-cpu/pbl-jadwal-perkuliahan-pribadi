<?php
// --- VERSI FINAL: OTOMATIS MENYESUAIIKAN LOKASI VENDOR ---

// 1. Cek login
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Akses tidak diizinkan. Sesi tidak valid.']);
    exit();
}

// 2. Tentukan path ke folder vendor
// Script ini berada di /Mahasiswa/api/, jadi kita naik dua level untuk mencapai folder utama proyek
 $vendorPath = __DIR__ . '/../../vendor/autoload.php';

// 3. Cek apakah file vendor/autoload.php ada
if (!file_exists($vendorPath)) {
    http_response_code(500);
    header('Content-Type: application/json');
    $errorMsg = 'Kesalahan Kritis: File vendor/autoload.php tidak ditemukan. Path yang dicari: ' . $vendorPath . '. ';
    $errorMsg .= 'Pastikan folder "vendor" berada di folder utama proyek (satu level dengan folder "admin" dan "Mahasiswa").';
    error_log($errorMsg); // Catat error di log server
    echo json_encode(['error' => $errorMsg]);
    exit();
}

// 4. Jika file ditemukan, lanjutkan
try {
    require_once $vendorPath;
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    $errorMsg = 'Kesalahan saat memuat library TCPDF: ' . $e->getMessage();
    error_log($errorMsg);
    echo json_encode(['error' => $errorMsg]);
    exit();
}

// 5. Ambil dan validasi data JSON
 $json_data = file_get_contents('php://input');
 $data = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Data JSON tidak valid: ' . json_last_error_msg()]);
    exit();
}

if (!$data || !isset($data['courses']) || !isset($data['schedules'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Struktur data tidak lengkap. Harus mengandung "courses" dan "schedules".']);
    exit();
}

 $courses = $data['courses'];
 $schedules = $data['schedules'];

if (empty($courses) || empty($schedules)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Data jadwal atau mata kuliah kosong. Tidak ada yang bisa diekspor.']);
    exit();
}

// 6. Buat PDF
try {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('Aplikasi Jadwal Perkuliahan');
    $pdf->SetAuthor($_SESSION['username'] ?? 'Mahasiswa');
    $pdf->SetTitle('Jadwal Perkuliahan Mingguan');
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 15, 'Jadwal Perkuliahan Mingguan', 0, 1, 'C', 0, '', 0, false, 'M', 'M');
    $pdf->Ln(10);

    $course_map = [];
    foreach ($courses as $course) {
        if (isset($course['id'])) {
            $course_map[$course['id']] = $course;
        }
    }

    $grouped_schedules = [];
    foreach ($schedules as $schedule) {
        $day = $schedule['day_of_week'];
        if (!isset($grouped_schedules[$day])) {
            $grouped_schedules[$day] = [];
        }
        $grouped_schedules[$day][] = $schedule;
    }

    $days_order = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    $pdf->SetFont('helvetica', 'B', 12);

    foreach ($days_order as $day) {
        if (isset($grouped_schedules[$day]) && !empty($grouped_schedules[$day])) {
            $pdf->Cell(0, 10, $day, 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);

            foreach ($grouped_schedules[$day] as $schedule) {
                $course_id = $schedule['course_id'];
                if (isset($course_map[$course_id])) {
                    $course_detail = $course_map[$course_id];
                    $course_name = $course_detail['nama'] ?? 'Tidak Diketahui';
                    $dosen = $course_detail['dosen'] ?? 'Tidak Diketahui';
                    $ruangan = $course_detail['ruangan'] ?? 'Tidak Diketahui';

                    $pdf->Cell(30, 7, $schedule['start_time'] . ' - ' . $schedule['end_time'], 0, 0, 'L');
                    $pdf->Cell(80, 7, $course_name, 0, 0, 'L');
                    $pdf->Cell(50, 7, $dosen, 0, 0, 'L');
                    $pdf->Cell(30, 7, $ruangan, 0, 1, 'L');
                }
            }
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 12);
        }
    }

    $username = $_SESSION['username'] ?? 'mahasiswa';
    $filename = 'jadwal_perkuliahan_' . $username . '.pdf';
    $pdf->Output($filename, 'D');

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    $errorMsg = 'Terjadi kesalahan fatal saat membuat PDF: ' . $e->getMessage();
    error_log($errorMsg);
    echo json_encode(['error' => $errorMsg]);
}

?>