<?php
session_start();
require 'koneksi.php';

// Jika sudah login, langsung lempar ke index
if (isset($_SESSION['nama'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['login_btn'])) {
    $akun_input = mysqli_real_escape_string($conn, $_POST['akun']);
    $pass_input = $_POST['password'];

    $query = "SELECT * FROM users WHERE akun = '$akun_input'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($pass_input, $row['password'])) {
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['akun'] = $row['akun'];
            header("Location: index.php");
            exit();
        } else { $error = "Password salah!"; }
    } else { $error = "Akun tidak ditemukan!"; }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - FINOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-600 flex items-center justify-center min-h-screen">
    <div class="bg-slate-400 p-10 rounded-3xl shadow-2xl w-full max-w-md border-4 border-slate-500 text-black">
        <h2 class="text-4xl font-bold text-center mb-8 tracking-widest uppercase">Finote</h2>
        
        <?php if(isset($error)): ?>
            <p class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center font-bold"><?= $error ?></p>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <input type="text" name="akun" required class="w-full p-4 rounded-xl outline-none" placeholder="Email / ID Akun">
            <input type="password" name="password" required class="w-full p-4 rounded-xl outline-none" placeholder="Password">
            <button type="submit" name="login_btn" class="w-full bg-black text-white py-4 rounded-xl font-bold uppercase hover:bg-gray-900 transition shadow-lg">Masuk Dashboard</button>
        </form>
    </div>
</body>
</html>