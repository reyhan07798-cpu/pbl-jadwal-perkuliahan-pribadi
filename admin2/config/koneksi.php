<?php
 $host = 'localhost';
 $user = 'root';
 $pass = ''; // Kosongkan jika tidak ada password
 $db   = 'db_dashboard'; // Pastikan nama database ini benar

 $conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>