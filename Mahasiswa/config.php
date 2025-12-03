<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "jadwal_mahasiswa";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database gagal: " . mysqli_connect_error());
}
?>
