<?php
include '../db.php';
session_start();

if (isset($_POST['update_profile']) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $j = mysqli_real_escape_string($conn, $_POST['job']);
    $a = mysqli_real_escape_string($conn, $_POST['address']);
    $p = $_POST['photo_base64'];

    $sql = "UPDATE users SET username='$u', job='$j', address='$a'";
    if (!empty($p)) { $sql .= ", photo='$p'"; }
    $sql .= " WHERE id='$uid'";
    
    mysqli_query($conn, $sql);
}
header("Location: ../index.php?page=profil");