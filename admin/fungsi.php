<?php
function showToast($pesan, $tipe = 'success') {
    $_SESSION['toast'] = [
        'pesan' => $pesan,
        'tipe' => $tipe
    ];
}
?>