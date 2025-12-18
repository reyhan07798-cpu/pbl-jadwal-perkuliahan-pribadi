<?php
require_once '../koneksi.php';
require_once 'functions.php';

 $page_title = 'Kelola Jadwal';

// --- Kode untuk mengelola jadwal akan ada di sini ---

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Kelola Jadwal</h6>
    </div>
    <div class="card-body">
        <p>Halaman untuk mengelola jadwal mata kuliah. Fitur ini akan segera hadir.</p>
        <!-- Anda bisa menambahkan tabel atau form di sini nanti -->
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'template.php';
?>