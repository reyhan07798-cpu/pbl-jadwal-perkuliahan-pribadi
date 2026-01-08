<?php
session_start();
require_once 'fungsi.php';
 $_SESSION = array();
 
session_destroy();

header('location:../Mahasiswa/login_mahasiswa.php');
exit;
?>