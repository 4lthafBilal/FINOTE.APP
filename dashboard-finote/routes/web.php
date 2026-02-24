<?php

use App\Http\Controllers\FinoteController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// 1. Halaman Utama
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Tampilan Login & Register
Route::get('/login', function () { return view('auth.login'); })->name('login');
Route::get('/register', function () { return view('auth.register'); })->name('register');

// 3. Proses Login (Perbaikan halaman putih)
Route::post('/login', function (Request $request) {
    $user = DB::table('users_lama')
                ->where('email', $request->email)
                ->where('password', $request->password)
                ->first();

    if ($user) {
        session(['user_id' => $user->id]);
        return redirect()->route('dashboard'); // Pastikan diarahkan ke dashboard
    }

    return back()->withErrors(['message' => 'Email atau Password salah']);
})->name('login.post');

// 4. Proses Register (Tanpa Pekerjaan & Perbaikan Error 500)
Route::post('/register', function (Request $request) {
    // Gunakan insert biasa agar tidak error 500
    DB::table('users_lama')->insert([
        'username' => $request->username,
        'email'    => $request->email,
        'password' => $request->password,
        'job'      => 'User', // Nilai default karena kolom ini ada di database
        'photo'    => 'https://ui-avatars.com/api/?name=' . urlencode($request->username)
    ]);

    // Ambil data user yang baru dibuat untuk login otomatis
    $user = DB::table('users_lama')->where('email', $request->email)->first();

    if ($user) {
        session(['user_id' => $user->id]);
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('register.post');

// 5. Logout & Dashboard
Route::post('/logout', function () {
    session()->forget('user_id');
    return redirect()->route('login');
})->name('logout');

Route::get('/dashboard', [FinoteController::class, 'index'])->name('dashboard');
Route::post('/transaction/store', [FinoteController::class, 'storeTransaction'])->name('transaction.store');
Route::post('/profile/update', [FinoteController::class, 'updateProfile'])->name('profile.update');