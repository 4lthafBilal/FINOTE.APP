<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinoteController extends Controller
{
    /**
     * Menampilkan Halaman Dashboard
     * Mengambil data berdasarkan session user_id yang login.
     */
    public function index()
    {
        // 1. Ambil ID User dari Session (setelah login berhasil)
        $userId = session('user_id');

        // 2. Jika tidak ada session, lempar kembali ke halaman login
        if (!$userId) {
            return redirect()->route('login');
        }

        // 3. Ambil data user dari tabel users_lama
        $user = DB::table('users_lama')->where('id', $userId)->first();

        // Jaga-jaga jika data user terhapus di database
        if (!$user) {
            session()->forget('user_id');
            return redirect()->route('login');
        }

        // 4. Ambil data transaksi milik user tersebut
        $trxs = DB::table('transactions')
                    ->where('user_id', $user->id)
                    ->orderBy('date_recorded', 'desc')
                    ->get()
                    ->map(function($r) {
                        return [
                            'id' => $r->id,
                            't' => $r->title,
                            'a' => (int)$r->amount,
                            'tp' => $r->type,
                            'd' => date('d M Y', strtotime($r->date_recorded)),
                            'fd' => $r->date_recorded
                        ];
                    });

        // 5. Tampilkan view dashboard dengan data user dan transaksi
        return view('dashboard', ['u' => $user, 'trxs' => $trxs]);
    }

    /**
     * Menyimpan Transaksi Baru
     */
    public function storeTransaction(Request $request)
    {
        $userId = session('user_id');

        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        DB::table('transactions')->insert([
            'user_id' => $userId,
            'title' => $request->title,
            'amount' => $request->amount,
            'type' => $request->type,
            'date_recorded' => now(),
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Memperbarui Profil User
     */
    public function updateProfile(Request $request)
    {
        $userId = session('user_id');

        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        DB::table('users_lama')->where('id', $userId)->update([
            'username' => $request->username,
            'job' => $request->job,
            'email' => $request->email,
            'photo' => $request->photo, // Menyimpan base64 foto
        ]);

        return response()->json(['status' => 'success']);
    }
}