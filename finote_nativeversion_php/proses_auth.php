<?php
session_start();
include 'koneksi.php';

$type = $_POST['type'];
$user = mysqli_real_escape_string($conn, $_POST['username']);
$pass = $_POST['password'];

if ($type === 'register') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    // Masukkan email ke dalam query INSERT
    mysqli_query($conn, "INSERT INTO users (username, password, email, job) VALUES ('$user', '$pass', '$email', 'Software Developer')");
    echo "<script>alert('Akun berhasil dibuat! Silakan masuk.'); window.location='auth.php';</script>";
} else {
    $res = mysqli_query($conn, "SELECT * FROM users WHERE username='$user' AND password='$pass'");
    if (mysqli_num_rows($res) > 0) {
        $d = mysqli_fetch_assoc($res);
        $_SESSION['user_id'] = $d['id'];
        header("Location: index.php");
    } else {
        echo "<script>alert('Username atau Password salah!'); window.location='auth.php';</script>";
    }
}
?>