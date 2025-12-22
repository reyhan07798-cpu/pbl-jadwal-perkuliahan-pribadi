<?php
require_once '../koneksi.php';
require_once 'fungsi.php';

 $page_title = 'Kelola Catatan';

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    showToast("Catatan berhasil dihapus.", "success");
    header("Location: kelola_catatan.php");
    exit;
}

 $stmt = $conn->prepare("SELECT n.id, n.title, n.content, n.created_at, u.username FROM notes n JOIN users u ON n.user_id = u.id ORDER BY n.created_at DESC");
 $stmt->execute();
 $notes = $stmt->get_result();

 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Semua Catatan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pembuat</th>
                        <th>Judul</th>
                        <th>Isi (Ringkasan)</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($notes->num_rows > 0): ?>
                        <?php while($note = $notes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $note['id']; ?></td>
                            <td><?php echo htmlspecialchars($note['username']); ?></td>
                            <td><?php echo htmlspecialchars($note['title']); ?></td>
                            <td><?php echo htmlspecialchars(substr($note['content'], 0, 50)) . '...'; ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($note['created_at'])); ?></td>
                            <td>
                                <a href="kelola_catatan.php?delete_id=<?php echo $note['id']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus catatan ini?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada catatan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'tema.php';
?>