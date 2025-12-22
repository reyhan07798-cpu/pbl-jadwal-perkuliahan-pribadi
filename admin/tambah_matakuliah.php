<?php
require_once '../koneksi.php';
require_once 'fungsi.php';

// --- PERUBAHAN: Judul halaman tetap "Tambah Mata Kuliah" ---
 $page_title = 'Tambah Mata Kuliah';

// --- PERUBAHAN: Hapus semua logika edit ($is_edit) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $dosen = $_POST['dosen'];
    $sks = $_POST['sks'];
    $room = $_POST['room'];

    // --- PERUBAHAN: Hanya ada logika INSERT ---
    $stmt = $conn->prepare("INSERT INTO courses (course_name, dosen, sks, room) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $course_name, $dosen, $sks, $room);

    if ($stmt->execute()) {
        showToast("Mata Kuliah berhasil ditambahkan.", "success");
        header("Location: kelola_matakuliah.php");
        exit;
    } else {
        showToast("Terjadi kesalahan.", "error");
    }
}
 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <!-- --- PERUBAHAN: Judul card menjadi "Tambah Mata Kuliah" --->
        <h6 class="m-0 font-weight-bold text-primary">Tambah Mata Kuliah</h6>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <!-- --- PERUBAHAN: Hapus input hidden untuk ID --- -->
            <div class="mb-3">
                <label for="course_name" class="form-label">Nama Mata Kuliah</label>
                <input type="text" class="form-control" id="course_name" name="course_name" required>
            </div>
            <div class="mb-3">
                <label for="dosen" class="form-label">Dosen Pengajar</label>
                <input type="text" class="form-control" id="dosen" name="dosen" required>
            </div>
            <div class="mb-3">
                <label for="sks" class="form-label">SKS</label>
                <input type="number" class="form-control" id="sks" name="sks" min="1" required>
            </div>
            <div class="mb-3">
                <label for="room" class="form-label">Ruangan</label>
                <input type="text" class="form-control" id="room" name="room" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="kelola_matakuliah.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'tema.php';
?>