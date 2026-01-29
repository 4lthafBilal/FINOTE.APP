<?php
include '../db.php';
session_start();

if (isset($_POST['add_t']) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $amt = $_POST['amount'];
    $type = $_POST['type'];
    mysqli_query($conn, "INSERT INTO transactions (user_id, title, amount, type) VALUES ('$uid', '$title', '$amt', '$type')");
}
header("Location: ../index.php?page=transaksi");