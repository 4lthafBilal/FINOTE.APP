<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Auth - FINOTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#F8FAFC] flex items-center justify-center min-h-screen p-6" x-data="{ isLogin: true }">
    <div class="bg-white p-10 rounded-[40px] shadow-sm border border-gray-100 w-full max-w-md text-center">
        <div class="flex items-center gap-2 justify-center mb-8"><div class="bg-[#2563EB] text-white w-8 h-8 rounded-lg flex items-center justify-center font-bold">F</div><h1 class="text-[#2563EB] text-2xl font-black uppercase tracking-tighter">Finote</h1></div>
        
        <div class="flex bg-gray-100 p-1 rounded-2xl mb-8">
            <button @click="isLogin = true" :class="isLogin ? 'bg-white shadow-sm text-blue-600' : 'text-gray-400'" class="flex-1 py-3 rounded-xl font-bold transition-all text-sm">MASUK</button>
            <button @click="isLogin = false" :class="!isLogin ? 'bg-white shadow-sm text-blue-600' : 'text-gray-400'" class="flex-1 py-3 rounded-xl font-bold transition-all text-sm">DAFTAR</button>
        </div>

        <form action="proses_auth.php" method="POST" class="space-y-6 text-left">
            <input type="hidden" name="type" :value="isLogin ? 'login' : 'register'">
            
            <template x-if="!isLogin">
                <div><label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Alamat Email</label><input type="email" name="email" required class="w-full border-b py-3 outline-none focus:border-blue-500 font-bold text-sm bg-transparent"></div>
            </template>

            <div><label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Username</label><input type="text" name="username" required class="w-full border-b py-3 outline-none focus:border-blue-500 font-bold text-sm bg-transparent"></div>
            <div><label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Password</label><input type="password" name="password" required class="w-full border-b py-3 outline-none focus:border-blue-500 font-bold text-sm bg-transparent"></div>
            
            <button type="submit" class="w-full bg-[#2563EB] text-white py-4 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:scale-[1.02] transition-all uppercase text-sm tracking-widest" x-text="isLogin ? 'Masuk' : 'Buat Akun'"></button>
        </form>
    </div>
</body>
</html>