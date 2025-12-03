<?php
include "config.php";

$id = $_POST['id'];
$course_name = $_POST['course_name'];
$room = $_POST['room'];
$day = $_POST['day'];
$start = $_POST['start_time'];
$end = $_POST['end_time'];

if ($id == "") {
    // INSERT
    $sql = "INSERT INTO schedule (course_name, room, day, start_time, end_time)
            VALUES ('$course_name', '$room', '$day', '$start', '$end')";
} else {
    // UPDATE
    $sql = "UPDATE schedule SET
            course_name='$course_name',
            room='$room',
            day='$day',
            start_time='$start',
            end_time='$end'
            WHERE id=$id";
}

mysqli_query($conn, $sql);
header("Location: index.php");
?>
