<?php
// Koneksi ke Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_finote";

$conn = mysqli_connect($host, $user, $pass, $db);

// Logika Simpan Transaksi
if (isset($_POST['simpan_transaksi'])) {
    $nama = $_POST['nama_user'];
    $akun = $_POST['akun_user'];
    $tipe = $_POST['tipe'];
    $nominal = $_POST['nominal'];
    $keterangan = $_POST['keterangan'];

    $query = "INSERT INTO transaksi (nama_user, akun_user, tipe, nominal, keterangan) 
              VALUES ('$nama', '$akun', '$tipe', '$nominal', '$keterangan')";
    mysqli_query($conn, $query);
    header("Location: index.php?nama=$nama&akun=$akun"); // Refresh halaman
}

// Ambil Data Transaksi jika sudah login
$nama_login = $_GET['nama'] ?? '';
$akun_login = $_GET['akun'] ?? '';
$transaksi = [];
$total_saldo = 0;

if ($nama_login) {
    $result = mysqli_query($conn, "SELECT * FROM transaksi WHERE nama_user='$nama_login' ORDER BY tanggal DESC");
    while ($row = mysqli_fetch_assoc($result)) {
        $transaksi[] = $row;
        if ($row['tipe'] == 'pemasukan') $total_saldo += $row['nominal'];
        else $total_saldo -= $row['nominal'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>FINOTE - PHP Version</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hidden-page { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <div id="loginPage" class="<?= $nama_login ? 'hidden-page' : 'flex' ?> items-center justify-center min-h-screen bg-slate-600">
        <div class="bg-slate-400 p-10 rounded-3xl shadow-2xl w-full max-w-md border-4 border-slate-500">
            <h2 class="text-4xl font-bold text-center mb-2 tracking-widest">FINOTE</h2>
            <form action="" method="GET" class="space-y-6 mt-8">
                <input type="text" name="nama" required class="w-full p-4 rounded-xl outline-none" placeholder="Nama Lengkap">
                <input type="text" name="akun" required class="w-full p-4 rounded-xl outline-none" placeholder="Account (Email/ID)">
                <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-bold uppercase tracking-widest">Masuk ke Dashboard</button>
            </form>
        </div>
    </div>

    <div id="mainPage" class="<?= $nama_login ? 'flex' : 'hidden-page' ?> min-h-screen">
        <aside class="w-80 bg-white p-8 border-r shadow-xl">
            <div class="flex items-center gap-5 mb-16">
                <div class="w-16 h-16 bg-gray-300 rounded-full"></div>
                <div class="overflow-hidden">
                    <h3 class="font-bold text-2xl uppercase"><?= htmlspecialchars($nama_login) ?></h3>
                    <p class="text-gray-400 text-sm"><?= htmlspecialchars($akun_login) ?></p>
                </div>
            </div>
            <nav class="space-y-8 text-black font-bold text-xl">
                <p><i class="fa-solid fa-circle-dollar-to-slot mr-4"></i> Transaksi</p>
                <p><i class="fa-solid fa-hourglass-half mr-4"></i> Riwayat</p>
                <p><i class="fa-solid fa-gear mr-4"></i> Pengaturan</p>
                <a href="index.php" class="block text-red-500 mt-20">Keluar</a>
            </nav>
        </aside>

        <main class="flex-1 p-12">
            <div class="flex gap-8 mb-12">
                <div class="bg-black text-white p-10 rounded-[2.5rem] flex-1 shadow-2xl">
                    <p class="text-lg opacity-70 mb-2">Total Saldo</p>
                    <h1 class="text-6xl font-bold">Rp<?= number_format($total_saldo, 0, ',', '.') ?></h1>
                </div>
                <button onclick="toggleModal('pemasukan')" class="bg-green-600 text-white rounded-[2.5rem] w-44 flex flex-col items-center justify-center gap-2 shadow-lg">
                    <span class="font-bold">Pemasukan</span> <i class="fa-solid fa-plus text-3xl"></i>
                </button>
                <button onclick="toggleModal('pengeluaran')" class="bg-red-600 text-white rounded-[2.5rem] w-44 flex flex-col items-center justify-center gap-2 shadow-lg">
                    <span class="font-bold">Pengeluaran</span> <i class="fa-solid fa-minus text-3xl"></i>
                </button>
            </div>

            <div class="bg-white rounded-[2.5rem] p-10 shadow-sm min-h-[400px]">
                <h3 class="font-bold text-2xl mb-8">Transaksi Terakhir</h3>
                <div class="space-y-6">
                    <?php if (empty($transaksi)): ?>
                        <p class="text-gray-400">Belum ada transaksi.</p>
                    <?php endif; ?>
                    <?php foreach ($transaksi as $t): ?>
                    <div class="flex items-center justify-between py-4 border-b">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center">
                                <i class="fa-solid <?= $t['tipe'] == 'pemasukan' ? 'fa-arrow-up text-green-600' : 'fa-arrow-down text-red-600' ?>"></i>
                            </div>
                            <div>
                                <p class="font-bold text-xl"><?= htmlspecialchars($t['keterangan']) ?></p>
                                <p class="text-gray-400 text-sm italic uppercase"><?= $t['tipe'] ?> • <?= date('d M, H:i', strtotime($t['tanggal'])) ?></p>
                            </div>
                        </div>
                        <p class="font-bold text-xl <?= $t['tipe'] == 'pemasukan' ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $t['tipe'] == 'pemasukan' ? '+' : '-' ?>Rp<?= number_format($t['nominal'], 0, ',', '.') ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="modalInput" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm items-center justify-center z-50 p-4">
        <form action="" method="POST" class="bg-white p-8 rounded-3xl w-full max-w-sm shadow-2xl">
            <h3 id="modalTitle" class="text-2xl font-bold mb-6 text-center">Tambah Data</h3>
            <input type="hidden" name="nama_user" value="<?= htmlspecialchars($nama_login) ?>">
            <input type="hidden" name="akun_user" value="<?= htmlspecialchars($akun_login) ?>">
            <input type="hidden" name="tipe" id="tipeInput">
            
            <input type="number" name="nominal" required class="w-full p-4 border-2 mb-4 rounded-xl outline-none" placeholder="Nominal Rp">
            <input type="text" name="keterangan" required class="w-full p-4 border-2 mb-6 rounded-xl outline-none" placeholder="Keterangan (Gaji, Makan, dll)">
            
            <div class="flex gap-4">
                <button type="button" onclick="toggleModal()" class="flex-1 py-4 bg-gray-100 rounded-xl">Batal</button>
                <button type="submit" name="simpan_transaksi" class="flex-1 py-4 bg-black text-white rounded-xl">Simpan</button>
            </div>
        </form>
    </div>

    <script>
        function toggleModal(type = '') {
            const modal = document.getElementById('modalInput');
            if (type) {
                document.getElementById('tipeInput').value = type;
                document.getElementById('modalTitle').innerText = 'Tambah ' + type.charAt(0).toUpperCase() + type.slice(1);
                modal.classList.add('flex');
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
    </script>
</body>
</html>