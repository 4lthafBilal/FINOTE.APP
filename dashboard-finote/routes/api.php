<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Endpoint untuk mengambil semua data user
Route::get('/users', function () {
    $users = DB::table('users_lama')->get();
    
    return response()->json([
        'status' => 'success',
        'message' => 'Data user berhasil diambil',
        'data' => $users
    ]);
});

// Endpoint tambahan untuk cek detail user berdasarkan ID
Route::get('/user/{id}', function ($id) {
    $user = DB::table('users_lama')->where('id', $id)->first();
    
    if ($user) {
        return response()->json(['status' => 'success', 'data' => $user]);
    }
    
    return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan'], 404);
});