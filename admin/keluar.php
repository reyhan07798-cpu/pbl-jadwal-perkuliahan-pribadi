<?php
session_start();
require_once 'fungsi.php';

// Hapus semua variabel session
 $_SESSION = array();

// Hancurkan session
session_destroy();

// Arahkan ke halaman login (asumsikan di luar folder admin)
header('location:../Mahasiswa/login_mahasiswa.php');
exit;
?>