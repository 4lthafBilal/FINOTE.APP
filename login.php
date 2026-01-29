<?php
session_start();
include '../db.php';

if (isset($_POST['login'])) {
    $e = mysqli_real_escape_string($conn, $_POST['email']);
    $p = $_POST['password'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$e'");
    $user = mysqli_fetch_assoc($res);
    if ($user && password_verify($p, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: ../index.php"); 
    }
}
// Tambahkan logika register juga di sini
?>