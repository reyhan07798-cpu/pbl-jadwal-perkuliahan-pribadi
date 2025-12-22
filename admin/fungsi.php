<?php
function showToast($pesan, $tipe = 'success') {
    // Tipe: success, error, warning, info
    $_SESSION['toast'] = [
        'pesan' => $pesan,
        'tipe' => $tipe
    ];
}
?>