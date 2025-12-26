<?php
// File: kirim_pesan.php
header('Content-Type: application/json');

// Koneksi ke database
// Sesuaikan path jika file koneksi.php Anda berada di folder lain
if (file_exists('../koneksi.php')) {
    require_once '../koneksi.php';
} else {
    echo json_encode(['success' => false, 'message' => 'File koneksi tidak ditemukan']);
    exit;
}

// Ambil data JSON yang dikirim dari form
 $data = json_decode(file_get_contents("php://input"), true);

// Validasi input sederhana
if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Semua field (Nama, Email, Pesan) wajib diisi.'
    ]);
    exit;
}

// Siapkan statement untuk menghindari SQL Injection
 $stmt = $conn->prepare(
    "INSERT INTO contact_messages (name, email, message, status, created_at)
     VALUES (?, ?, ?, 'unread', NOW())"
);

if ($stmt) {
    // Bind parameter: sss = string, string, string
    $stmt->bind_param("sss", $data['name'], $data['email'], $data['message']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Pesan berhasil dikirim!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan database query.']);
}

 $conn->close();
?>