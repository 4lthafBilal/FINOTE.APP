<?php
session_start();
include 'koneksi.php';
if(!isset($_SESSION['user_id'])) { header("Location: auth.php"); exit(); }

$id = $_SESSION['user_id'];
// Mengambil data user lengkap termasuk email
$u_q = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
$u = mysqli_fetch_assoc($u_q);

// KUNCI: Query ini sudah dipisah per user agar transaksi tidak tercampur
$t_q = mysqli_query($conn, "SELECT * FROM transactions WHERE user_id='$id' ORDER BY date_recorded DESC");
$trxs = [];
while ($r = mysqli_fetch_assoc($t_q)) {
    $trxs[] = [
        'id' => $r['id'],
        't' => $r['title'],
        'a' => (int)$r['amount'],
        'tp' => $r['type'],
        'd' => date('d M Y', strtotime($r['date_recorded'])),
        'fd' => $r['date_recorded']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINOTE - Locked Version</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .s-active { background: #F1F5F9; color: #2563EB; border-radius: 12px; }
        .t-active { border-bottom: 3px solid #2563EB; color: #111827; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-gray-800" x-data="app()" x-cloak>
    <div class="flex min-h-screen">
        <aside class="w-64 bg-white border-r flex flex-col p-6 fixed h-full shadow-sm">
            <div class="flex items-center gap-2 mb-10">
                <div class="bg-[#2563EB] text-white w-8 h-8 rounded flex items-center justify-center font-bold shadow-sm">F</div>
                <h1 class="text-[#2563EB] text-xl font-bold tracking-tight">FINOTE</h1>
            </div>
            <nav class="space-y-2 flex-1">
                <button @click="v='h'" :class="v=='h'?'s-active':''" class="flex items-center gap-3 w-full p-3 font-semibold text-gray-500 hover:text-blue-600 transition-all text-sm">🏠 Beranda</button>
                <button @click="v='t'" :class="v=='t'?'s-active':''" class="flex items-center gap-3 w-full p-3 font-semibold text-gray-500 hover:text-blue-600 transition-all text-sm">🔄 Transaksi</button>
                <button @click="v='r'" :class="v=='r'?'s-active':''" class="flex items-center gap-3 w-full p-3 font-semibold text-gray-500 hover:text-blue-600 transition-all text-sm">📊 Riwayat</button>
                <button @click="v='p'" :class="v=='p'?'s-active':''" class="flex items-center gap-3 w-full p-3 font-semibold text-gray-500 hover:text-blue-600 transition-all text-sm">👤 Profil</button>
            </nav>
            <div class="border-t pt-4">
                <a href="logout.php" class="text-red-500 font-bold p-3 hover:bg-red-50 rounded-xl transition-all text-sm block">Keluar</a>
            </div>
        </aside>

        <main class="flex-1 ml-64 p-10">
            <header class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 uppercase" x-text="v=='h'?'Dashboard':(v=='t'?'Tambah Data':(v=='r'?'Riwayat Keuangan':'Pengaturan Profil'))"></h2>
                <div class="flex items-center gap-3 bg-white p-2 px-4 rounded-full border shadow-sm">
                    <span class="text-xs font-bold text-gray-600" x-text="'Halo, ' + user.n"></span>
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs overflow-hidden border shadow-inner">
                        <template x-if="user.ph"><img :src="user.ph" class="w-full h-full object-cover"></template>
                        <template x-if="!user.ph"><span x-text="user.n[0]"></span></template>
                    </div>
                </div>
            </header>

            <div x-show="v=='h'" class="space-y-6">
                <div class="bg-[#2563EB] rounded-[32px] p-8 text-white shadow-xl relative overflow-hidden">
                    <p class="text-xs opacity-80 uppercase tracking-widest font-bold">Total Saldo Tersedia</p>
                    <h3 class="text-4xl font-bold mt-2 tracking-tight" x-text="rp(totalIn - totalOut)"></h3>
                    <div class="flex gap-8 mt-8 pt-6 border-t border-white/20">
                        <div><p class="text-[9px] uppercase opacity-70 font-black mb-1">Pemasukan</p><p class="text-lg font-bold" x-text="rp(totalIn)"></p></div>
                        <div><p class="text-[9px] uppercase opacity-70 font-black mb-1">Pengeluaran</p><p class="text-lg font-bold" x-text="rp(totalOut)"></p></div>
                    </div>
                </div>
                <div class="bg-white rounded-[24px] p-6 border shadow-sm">
                    <h4 class="text-xs font-black text-gray-400 uppercase mb-4 tracking-widest">Transaksi Terbaru</h4>
                    <div class="space-y-3">
                        <template x-for="i in trxs.slice(0,3)">
                            <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div :class="i.tp=='in'?'text-blue-600 bg-blue-100':'text-red-600 bg-red-100'" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs" x-text="i.tp=='in'?'+':'-'"></div>
                                    <div><p class="font-bold text-xs" x-text="i.t"></p><p class="text-[9px] text-gray-400 font-medium" x-text="i.d"></p></div>
                                </div>
                                <span :class="i.tp=='in'?'text-blue-600':'text-red-500'" class="font-bold text-xs" x-text="rp(i.a)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div x-show="v=='t'" class="max-w-md mx-auto bg-white p-10 rounded-[32px] border shadow-sm transition-all">
                <div class="space-y-6">
                    <div><label class="text-[10px] font-black text-gray-400 uppercase">Keterangan Transaksi</label><input type="text" x-model="nt.t" placeholder="Contoh: Gaji" class="w-full border-b py-2 outline-none focus:border-blue-500 font-bold text-sm bg-transparent"></div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase">Nominal (Rp)</label><input type="number" x-model="nt.a" placeholder="0" class="w-full border-b py-2 outline-none focus:border-blue-500 font-bold text-sm bg-transparent"></div>
                    <div><label class="text-[10px] font-black text-gray-400 uppercase">Jenis</label><select x-model="nt.tp" class="w-full border-b py-2 outline-none font-bold text-sm bg-transparent"><option value="in">Pemasukan (+)</option><option value="out">Pengeluaran (-)</option></select></div>
                    <button @click="saveT()" class="w-full bg-[#2563EB] text-white py-4 rounded-xl font-bold shadow-md hover:bg-blue-700 transition-all text-sm uppercase tracking-widest">Simpan Data</button>
                </div>
            </div>

            <div x-show="v=='r'" class="bg-white rounded-[32px] border shadow-sm overflow-hidden">
                <div class="flex border-b text-[10px] font-black text-gray-400 text-center uppercase tracking-widest">
                    <button @click="tab='D'" :class="tab=='D'?'t-active':''" class="flex-1 py-4">Harian</button>
                    <button @click="tab='W'" :class="tab=='W'?'t-active':''" class="flex-1 py-4">Mingguan</button>
                    <button @click="tab='M'" :class="tab=='M'?'t-active':''" class="flex-1 py-4">Bulanan</button>
                </div>
                <div class="p-6 grid grid-cols-3 bg-gray-50 border-b text-center gap-2">
                    <div class="bg-white p-3 rounded-2xl border border-gray-100 shadow-sm"><p class="text-[8px] text-gray-400 font-black uppercase mb-1">Masuk</p><p class="text-blue-600 font-bold text-xs" x-text="rp(getS().i)"></p></div>
                    <div class="bg-white p-3 rounded-2xl border border-gray-100 shadow-sm"><p class="text-[8px] text-gray-400 font-black uppercase mb-1">Keluar</p><p class="text-red-500 font-bold text-xs" x-text="rp(getS().o)"></p></div>
                    <div class="bg-white p-3 rounded-2xl border border-gray-100 shadow-sm"><p class="text-[8px] text-gray-400 font-black uppercase mb-1">Selisih</p><p class="text-gray-800 font-bold text-xs" x-text="rp(getS().i - getS().o)"></p></div>
                </div>
                <div class="p-4 max-h-[400px] overflow-y-auto">
                    <template x-for="i in getF()"><div class="flex justify-between items-center py-4 border-b last:border-0 px-4 hover:bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-4">
                            <div :class="i.tp=='in'?'text-blue-600 bg-blue-50':'text-red-600 bg-red-50'" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs" x-text="i.tp=='in'?'+':'-'"></div>
                            <div><p class="font-bold text-xs text-gray-700" x-text="i.t"></p><p class="text-[9px] text-gray-400 font-bold" x-text="i.d"></p></div>
                        </div>
                        <span :class="i.tp=='in'?'text-blue-600':'text-red-500'" class="font-bold text-xs" x-text="rp(i.a)"></span>
                    </div></template>
                </div>
            </div>

            <div x-show="v=='p'" class="max-w-md mx-auto bg-white p-10 rounded-[40px] border shadow-sm relative text-center">
                <button @click="ed=!ed" class="absolute top-8 right-8 text-blue-600 font-black text-[10px] uppercase tracking-widest" x-text="ed?'Batal':'Edit'"></button>
                <div class="relative w-24 h-24 mx-auto mb-6">
                    <div class="w-full h-full bg-blue-600 rounded-full flex items-center justify-center text-white text-3xl font-bold overflow-hidden border-4 border-white shadow-md">
                        <template x-if="user.ph"><img :src="user.ph" class="w-full h-full object-cover"></template>
                        <template x-if="!user.ph"><span x-text="user.n[0]"></span></template>
                    </div>
                    <template x-if="ed"><label class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center cursor-pointer text-[8px] text-white font-black uppercase">Ubah Foto<input type="file" @change="upPh" class="hidden"></label></template>
                </div>
                <div class="space-y-5 text-left max-w-xs mx-auto">
                    <div><label class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Alamat Email</label><input type="email" x-model="user.e" :disabled="!ed" class="w-full border-b py-2 font-bold text-sm outline-none transition-all" :class="ed?'border-blue-500':'border-transparent'"></div>
                    <div><label class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Nama Lengkap</label><input type="text" x-model="user.n" :disabled="!ed" class="w-full border-b py-2 font-bold text-sm outline-none transition-all" :class="ed?'border-blue-500':'border-transparent'"></div>
                    <div><label class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Pekerjaan</label><input type="text" x-model="user.j" :disabled="!ed" class="w-full border-b py-2 font-bold text-sm text-gray-500 outline-none transition-all" :class="ed?'border-blue-500':'border-transparent'"></div>
                    <template x-if="ed"><button @click="saveP()" class="w-full bg-[#2563EB] text-white py-3 rounded-xl font-bold mt-4 shadow-md text-xs uppercase tracking-widest">Simpan Perubahan</button></template>
                </div>
            </div>
        </main>
    </div>

    <script>
        function app() {
            return {
                v:'h', tab:'D', ed:false,
                user: { 
                    n:'<?= $u['username'] ?>', 
                    j:'<?= $u['job'] ?>', 
                    ph:'<?= $u['photo'] ?>',
                    e:'<?= $u['email'] ?>' 
                },
                trxs: <?= json_encode($trxs) ?>, nt: {t:'', a:'', tp:'in'},
                rp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); },
                get totalIn() { return this.trxs.filter(t=>t.tp=='in').reduce((s,i)=>s+i.a,0); },
                get totalOut() { return this.trxs.filter(t=>t.tp=='out').reduce((s,i)=>s+i.a,0); },
                getF() {
                    const now = new Date();
                    return this.trxs.filter(t => {
                        const d = new Date(t.fd);
                        if(this.tab=='D') return d.toDateString() === now.toDateString();
                        if(this.tab=='W') { let w = new Date(); w.setDate(now.getDate()-7); return d >= w; }
                        if(this.tab=='M') return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
                        return true;
                    });
                },
                getS() { let f=this.getF(), i=0, o=0; f.forEach(t=>{if(t.tp=='in')i+=t.a;else o+=t.a;}); return {i,o}; },
                upPh(e) { 
                    const f=e.target.files[0]; 
                    if(f){ 
                        const r=new FileReader(); 
                        r.onload=(ev)=>{this.user.ph=ev.target.result;}; 
                        r.readAsDataURL(f); 
                    } 
                },
                async saveP() { 
                    const fd = new FormData(); 
                    fd.append('username',this.user.n); fd.append('job',this.user.j); 
                    fd.append('photo',this.user.ph||''); fd.append('email',this.user.e);
                    const r = await fetch('update_profile.php',{method:'POST',body:fd});
                    if((await r.text()).trim()==="success") { alert('Profil Diperbarui!'); location.reload(); } else { alert('Gagal Simpan Profil'); }
                },
                async saveT() {
                    if(!this.nt.t || !this.nt.a) return alert('Isi data dengan lengkap!');
                    const fd = new FormData(); 
                    fd.append('title',this.nt.t); fd.append('amount',this.nt.a); fd.append('type',this.nt.tp);
                    await fetch('proses_simpan.php',{method:'POST',body:fd}); location.reload();
                }
            }
        }
    </script>
</body>
</html>