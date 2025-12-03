<?php include "config.php"; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Mingguan</title>
    <style>
        body { font-family: Arial; background: #fdf1ff; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #ffb9f7; }
        .btn { padding: 6px 12px; border: none; cursor: pointer; border-radius: 5px; }
        .add-btn { background: #ff6be6; color: white; }
        .edit-btn { background: #ffc107; }
        .del-btn { background: #f44336; color: white; }
        .form-box { padding: 15px; background: white; border-radius: 8px; margin-top: 20px; }
        input, select { padding: 8px; width: 100%; margin: 5px 0; }
    </style>
</head>
<body>

<h1>📅 Jadwal Mingguan</h1>

<button class="btn add-btn" onclick="document.getElementById('form').style.display='block'">
    + Tambah Jadwal
</button>

<div id="form" class="form-box" style="display:none;">
    <h3 id="form-title">Tambah Jadwal</h3>
    
    <form action="save.php" method="POST">
        <input type="hidden" name="id" id="id">
        
        <label>Mata Kuliah:</label>
        <input type="text" name="course_name" id="course_name" required>

        <label>Ruangan:</label>
        <input type="text" name="room" id="room" required>

        <label>Hari:</label>
        <select name="day" id="day">
            <option>Senin</option>
            <option>Selasa</option>
            <option>Rabu</option>
            <option>Kamis</option>
            <option>Jumat</option>
        </select>

        <label>Jam Mulai:</label>
        <input type="time" name="start_time" id="start_time" required>

        <label>Jam Selesai:</label>
        <input type="time" name="end_time" id="end_time" required>

        <button class="btn add-btn" type="submit">Simpan</button>
    </form>
</div>

<table>
    <tr>
        <th>Mata Kuliah</th>
        <th>Ruangan</th>
        <th>Hari</th>
        <th>Jam</th>
        <th>Aksi</th>
    </tr>

    <?php
    $q = mysqli_query($conn, "SELECT * FROM schedule ORDER BY FIELD(day,'Senin','Selasa','Rabu','Kamis','Jumat'), start_time ASC");
    while ($row = mysqli_fetch_assoc($q)):
    ?>
    <tr>
        <td><?= $row['course_name'] ?></td>
        <td><?= $row['room'] ?></td>
        <td><?= $row['day'] ?></td>
        <td><?= substr($row['start_time'],0,5) ?> - <?= substr($row['end_time'],0,5) ?></td>
        <td>
            <button class="btn edit-btn" onclick='editData(<?= json_encode($row) ?>)'>Edit</button>
            <a href="delete.php?id=<?= $row['id'] ?>" class="btn del-btn">Hapus</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<script>
function editData(data){
    document.getElementById("form").style.display = "block";
    document.getElementById("form-title").innerText = "Edit Jadwal";

    document.getElementById("id").value = data.id;
    document.getElementById("course_name").value = data.course_name;
    document.getElementById("room").value = data.room;
    document.getElementById("day").value = data.day;
    document.getElementById("start_time").value = data.start_time;
    document.getElementById("end_time").value = data.end_time;
}
</script>

</body>
</html>
