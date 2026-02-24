<?php
session_start();
include 'koneksi.php';
$id = $_SESSION['user_id'];
$username = mysqli_real_escape_string($conn, $_POST['username']);
$job = mysqli_real_escape_string($conn, $_POST['job']);
$photo = $_POST['photo']; // Data gambar Base64

$query = "UPDATE users SET username='$username', job='$job', photo='$photo' WHERE id='$id'";
if (mysqli_query($conn, $query)) { echo "success"; } else { echo "error: " . mysqli_error($conn); }
?>