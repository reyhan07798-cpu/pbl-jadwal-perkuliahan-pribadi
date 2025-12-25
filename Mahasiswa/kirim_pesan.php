<?php
// Set header untuk respons JSON
header('Content-Type: application/json');
require_once '../koneksi.php';

// Ambil data POST yang dikirim dalam format JSON
 $json = file_get_contents('php://input');
 $data = json_decode($json, true);

// Validasi data sederhana
if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi.']);
    exit;
}

// Bersihkan input untuk keamanan
 $name = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
 $email = htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8');
 $message = htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8');

// Siapkan query untuk menyimpan ke database
 $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, status, created_at) VALUES (?, ?, ?, 'unread', NOW())");
 $stmt->bind_param("sss", $name, $email, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Pesan Anda berhasil dikirim.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim pesan. Silakan coba lagi.']);
}
?>