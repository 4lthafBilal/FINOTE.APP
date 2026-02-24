<?php
session_start();
include 'koneksi.php';

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $amount = $_POST['amount'];
    $type = $_POST['type'];

    // Pastikan query menyertakan user_id
    $query = "INSERT INTO transactions (user_id, title, amount, type) VALUES ('$user_id', '$title', '$amount', '$type')";
    mysqli_query($conn, $query);
    echo "success";
}
?>