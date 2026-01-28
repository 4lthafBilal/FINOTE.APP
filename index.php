<?php
include 'db.php';
session_start();

// --- 1. LOGIKA AUTH (LOGIN & REGISTER) ---
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit(); }

if (isset($_POST['register'])) {
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $e = mysqli_real_escape_string($conn, $_POST['email']);
    $p = password_hash($_POST['password'], PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, email, password) VALUES ('$u', '$e', '$p')");
}

if (isset($_POST['login'])) {
    $e = mysqli_real_escape_string($conn, $_POST['email']);
    $p = $_POST['password'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$e'");
    $user = mysqli_fetch_assoc($res);
    if ($user && password_verify($p, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php"); exit();
    }
}

// --- 2. LOGIKA TRANSAKSI ---
if (isset($_POST['add_t']) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $amt = $_POST['amount'];
    $type = $_POST['type']; 
    mysqli_query($conn, "INSERT INTO transactions (user_id, title, amount, type) VALUES ('$uid', '$title', '$amt', '$type')");
    header("Location: index.php"); exit();
}

// --- 3. LOGIKA UPDATE PROFIL ---
if (isset($_POST['update_profile']) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $new_user = mysqli_real_escape_string($conn, $_POST['username']);
    $new_job = mysqli_real_escape_string($conn, $_POST['job']);
    $new_address = mysqli_real_escape_string($conn, $_POST['address']);
    $new_photo = $_POST['photo_base64']; 

    if (!empty($new_photo)) {
        mysqli_query($conn, "UPDATE users SET username='$new_user', job='$new_job', address='$new_address', photo='$new_photo' WHERE id='$uid'");
    } else {
        mysqli_query($conn, "UPDATE users SET username='$new_user', job='$new_job', address='$new_address' WHERE id='$uid'");
    }
    header("Location: index.php"); exit();
}

// --- 4. DATA UNTUK UI (HITUNG SALDO OTOMATIS) ---
$uData = null; 
$trans = [];
$total_income = 0;
$total_expense = 0;

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $uData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$uid'"));
    
    $res_t = mysqli_query($conn, "SELECT * FROM transactions WHERE user_id='$uid' ORDER BY created_at DESC");
    if($res_t) {
        while($row = mysqli_fetch_assoc($res_t)) { 
            $trans[] = $row; 
            if($row['type'] == 'income') $total_income += $row['amount'];
            else $total_expense += $row['amount'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>FINOTE - Pengelola Keuangan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --blue: #2d66ed; --bg: #f0f4f8; --text: #1e293b; --danger: #ef4444; --success: #10b981; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: var(--bg); color: var(--text); }

        /* Auth Layout */
        .auth-bg { background: #1e293b; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { background: white; padding: 40px; border-radius: 12px; width: 400px; text-align: center; }
        .form-control { width: 100%; padding: 12px; margin: 8px 0 16px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; }
        .btn-blue { background: var(--blue); color: white; border: none; width: 100%; padding: 14px; border-radius: 8px; font-weight: bold; cursor: pointer; }

        /* Sidebar & Navigation */
        .wrapper { display: flex; height: 100vh; }
        .sidebar { width: 240px; background: white; padding: 25px 0; display: flex; flex-direction: column; border-right: 1px solid #e2e8f0; }
        .logo { color: var(--blue); font-weight: 800; font-size: 1.5rem; padding-left: 25px; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .nav-item { padding: 12px 25px; color: #64748b; text-decoration: none; cursor: pointer; display: flex; align-items: center; gap: 12px; font-weight: 500; }
        .nav-item.active { background: #eff6ff; color: var(--blue); border-right: 4px solid var(--blue); }
        .logout { margin-top: auto; color: var(--danger); }

        /* Main Area */
        .main { flex: 1; padding: 40px; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .white-box { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .blue-card { background: var(--blue); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
        
        /* Profile & Modal */
        .avatar-lg { width: 120px; height: 120px; border: 3px solid var(--blue); border-radius: 50%; object-fit: cover; margin-bottom: 15px; }
        #editModal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); }
        .modal-content { background:white; width:400px; margin:5% auto; padding:30px; border-radius:12px; }

        .section { display: none; } .active-sec { display: block; }
        .tab-btn { padding: 10px; border: none; border-radius: 8px; cursor: pointer; flex: 1; font-weight: 600; background: #f1f5f9; }
        .tab-btn.active { background: white; color: var(--blue); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<?php if(!$uData): ?>
    <div class="auth-bg">
        <div class="auth-card">
            <h1 style="color:var(--blue); margin-bottom:10px;">FINOTE</h1>
            <p style="color:#64748b; margin-bottom:20px;">Kelola keuangan dengan bijak</p>
            <form method="POST" id="lForm">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <button type="submit" name="login" class="btn-blue">Masuk</button>
                <p onclick="swap()" style="margin-top:15px; cursor:pointer; color:var(--blue); font-size:0.9rem;">Belum punya akun? Daftar</p>
            </form>
            <form method="POST" id="rForm" style="display:none">
                <input type="text" name="username" class="form-control" placeholder="Nama Lengkap" required>
                <input type="email" name="email" class="form-control" placeholder="Email" required>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <button type="submit" name="register" class="btn-blue">Daftar Akun</button>
                <p onclick="swap()" style="margin-top:15px; cursor:pointer; color:var(--blue); font-size:0.9rem;">Sudah punya akun? Login</p>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="logo"><i class="fas fa-wallet"></i> FINOTE</div>
            <div class="nav-item active" onclick="nav('home', this)"><i class="fas fa-home"></i> Home</div>
            <div class="nav-item" onclick="nav('dompet', this)"><i class="fas fa-credit-card"></i> Dompet Saya</div>
            <div class="nav-item" onclick="nav('transaksi', this)"><i class="fas fa-exchange-alt"></i> Transaksi</div>
            <div class="nav-item" onclick="nav('analisis', this)"><i class="fas fa-chart-pie"></i> Analisis</div>
            <div class="nav-item" onclick="nav('profil', this)"><i class="fas fa-user"></i> Profil</div>
            <a href="?logout=1" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </aside>

        <main class="main">
            <div class="header">
                <h2 id="title">Overview</h2>
                <div style="display:flex; align-items:center; gap:10px">
                    Halo, <?= htmlspecialchars($uData['username']) ?> 
                    <img src="<?= $uData['photo'] ?: 'https://ui-avatars.com/api/?name='.$uData['username'] ?>" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                </div>
            </div>

            <div id="home" class="section active-sec">
                <div class="blue-card">
                    <p>Total Saldo</p>
                    <h1 style="font-size:2.5rem; margin:10px 0;">Rp <?= number_format($total_income - $total_expense, 0, ',', '.') ?></h1>
                    <div style="display:flex; gap:30px; font-size:0.9rem; opacity:0.9;">
                        <div>Pendapatan: <strong>Rp <?= number_format($total_income, 0, ',', '.') ?></strong></div>
                        <div>Pengeluaran: <strong>Rp <?= number_format($total_expense, 0, ',', '.') ?></strong></div>
                    </div>
                </div>
                <div class="white-box">
                    <h3 style="margin-bottom:20px;">Riwayat Terbaru</h3>
                    <?php if(empty($trans)): ?>
                        <p style="text-align:center; color:#94a3b8; padding:20px;">Belum ada transaksi.</p>
                    <?php else: ?>
                        <?php foreach($trans as $t): ?>
                            <div style="display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f5f9;">
                                <div><strong><?= htmlspecialchars($t['title']) ?></strong><br><small style="color:#94a3b8;"><?= $t['created_at'] ?></small></div>
                                <strong style="color: <?= $t['type']=='income' ? 'var(--success)' : 'var(--danger)' ?>;">
                                    <?= $t['type']=='income' ? '+' : '-' ?> Rp <?= number_format($t['amount'], 0, ',', '.') ?>
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div id="transaksi" class="section">
                <div class="white-box" style="max-width:500px; margin:auto">
                    <h3 style="text-align:center; margin-bottom:20px;">Tambah Transaksi</h3>
                    <form method="POST">
                        <div style="display:flex; background:#f1f5f9; padding:5px; border-radius:10px; margin-bottom:20px;">
                            <button type="button" id="btnInc" class="tab-btn active" onclick="setType('income')">Pendapatan</button>
                            <button type="button" id="btnExp" class="tab-btn" onclick="setType('expense')">Pengeluaran</button>
                        </div>
                        <input type="hidden" name="type" id="trans_type" value="income">
                        <label>Judul Transaksi</label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Gaji" required>
                        <label>Nominal (Rp)</label>
                        <input type="number" name="amount" class="form-control" placeholder="0" required>
                        <button type="submit" name="add_t" class="btn-blue">Simpan Transaksi</button>
                    </form>
                </div>
            </div>

            <div id="profil" class="section">
                <div class="white-box" style="max-width:550px; margin:auto; position:relative;">
                    <button onclick="openModal()" style="position:absolute; right:20px; top:20px; color:var(--blue); background:none; border:1px solid var(--blue); padding:5px 12px; border-radius:6px; cursor:pointer;"><i class="fas fa-edit"></i> Edit Profil</button>
                    <div style="text-align:center;">
                        <img src="<?= $uData['photo'] ?: 'https://ui-avatars.com/api/?name='.$uData['username'] ?>" class="avatar-lg">
                        <h2 style="margin-bottom:5px;"><?= htmlspecialchars($uData['username']) ?></h2>
                        <p style="color:#64748b; margin-bottom:25px;"><?= htmlspecialchars($uData['email']) ?></p>
                        <div style="text-align:left; border-top:1px solid #eee; padding-top:20px;">
                            <p style="font-size:0.8rem; color:#64748b">Pekerjaan</p><strong><?= htmlspecialchars($uData['job'] ?? '-') ?></strong><br><br>
                            <p style="font-size:0.8rem; color:#64748b">Tempat Tinggal</p><strong><?= htmlspecialchars($uData['address'] ?? '-') ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div id="dompet" class="section"><div class="white-box">Fitur Dompet Segera Hadir</div></div>
            <div id="analisis" class="section"><div class="white-box">Fitur Analisis Segera Hadir</div></div>
        </main>
    </div>

    <div id="editModal">
        <div class="modal-content">
            <h3 style="margin-bottom:20px">Edit Profil</h3>
            <form method="POST">
                <div style="text-align:center; margin-bottom:20px">
                    <img id="preview" src="<?= $uData['photo'] ?: 'https://ui-avatars.com/api/?name='.$uData['username'] ?>" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:2px solid var(--blue)"><br>
                    <label for="fileInput" style="color:var(--blue); cursor:pointer; font-size:0.8rem">Ganti Foto</label>
                    <input type="file" id="fileInput" hidden onchange="convertToBase64()">
                    <input type="hidden" name="photo_base64" id="photo_base64">
                </div>
                <label>Username</label><input type="text" name="username" class="form-control" value="<?= htmlspecialchars($uData['username']) ?>" required>
                <label>Pekerjaan</label><input type="text" name="job" class="form-control" value="<?= htmlspecialchars($uData['job'] ?? '') ?>">
                <label>Alamat</label><input type="text" name="address" class="form-control" value="<?= htmlspecialchars($uData['address'] ?? '') ?>">
                <button type="submit" name="update_profile" class="btn-blue">Simpan</button>
                <button type="button" onclick="closeModal()" style="width:100%; margin-top:10px; border:none; padding:10px; border-radius:8px; cursor:pointer;">Batal</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
    function nav(id, el) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active-sec'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        document.getElementById(id).classList.add('active-sec');
        el.classList.add('active');
        document.getElementById('title').innerText = id.charAt(0).toUpperCase() + id.slice(1);
    }
    function setType(t) {
        document.getElementById('trans_type').value = t;
        document.getElementById('btnInc').classList.toggle('active', t === 'income');
        document.getElementById('btnExp').classList.toggle('active', t === 'expense');
    }
    function swap() {
        const l = document.getElementById('lForm'), r = document.getElementById('rForm');
        l.style.display = l.style.display === 'none' ? 'block' : 'none';
        r.style.display = r.style.display === 'none' ? 'block' : 'none';
    }
    function openModal() { document.getElementById('editModal').style.display = 'block'; }
    function closeModal() { document.getElementById('editModal').style.display = 'none'; }
    function convertToBase64() {
        const file = document.getElementById('fileInput').files[0];
        const reader = new FileReader();
        reader.onloadend = function() {
            document.getElementById('photo_base64').value = reader.result;
            document.getElementById('preview').src = reader.result;
        }
        if (file) reader.readAsDataURL(file);
    }
</script>
</body>
</html>
