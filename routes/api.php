<?php

use App\Http\Controllers\API\LamaranMasukController;
use App\Http\Controllers\API\LokerController;
use App\Http\Controllers\API\NotifikasiController;
use App\Http\Controllers\API\ProfilController;
use App\Http\Controllers\API\RatingController;
use App\Http\Controllers\API\StatusKerjaController;
use App\Http\Controllers\API\StatusPekerjaanController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is working 🚀'
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| LOKER
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/lokers', [LokerController::class, 'index']);
    Route::post('/lokers', [LokerController::class, 'store']);
    Route::get('/lokers/{id}', [LokerController::class, 'show']);
    Route::put('/lokers/{id}', [LokerController::class, 'update']);
    Route::delete('/lokers/{id}', [LokerController::class, 'destroy']);
});

/*
|-------------------------------------------------------------------------
| LAMARAN & STATUS KERJA
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/lamar', [StatusKerjaController::class, 'store']);
    Route::put('/lamar/{id}/status/{status}', [LamaranMasukController::class, 'updateStatus']);
    Route::get('/pelamar/{loker_id}', [StatusKerjaController::class, 'pelamarList']);
    Route::get('/riwayat-kerja', [StatusKerjaController::class, 'index']);
});


/*
|--------------------------------------------------------------------------
| STATUS PEKERJAAN
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pekerjaan', [StatusPekerjaanController::class, 'store']);
    Route::put('/pekerjaan/{id}/status/{status}', [StatusPekerjaanController::class, 'updateStatus']);
    Route::get('/status-pekerjaan/riwayat', [StatusPekerjaanController::class, 'riwayat']);
    Route::delete('/pekerjaan/tolak/{id}', [StatusPekerjaanController::class, 'tolak']);
});

Route::prefix('pekerjaan')->middleware('auth:sanctum')->group(function () {
    Route::post('/store', [StatusPekerjaanController::class, 'store']);
    Route::get('/riwayat', [StatusPekerjaanController::class, 'riwayat']);
    Route::put('/{id}/status/{status}', [StatusPekerjaanController::class, 'updateStatus']);
    Route::post('/tolak', [StatusPekerjaanController::class, 'tolak']);
    Route::middleware('auth:sanctum')->get('/riwayat-gabungan', [StatusPekerjaanController::class, 'riwayatGabungan']);

});

/*
|--------------------------------------------------------------------------
| LAMARAN MASUK
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/lamaran-masuk', [LamaranMasukController::class, 'index']);
    Route::post('/lamaran-masuk/setujui', [LamaranMasukController::class, 'setujui']);
});

Route::put('/lamar/{id}/status/{status}', [StatusKerjaController::class, 'updateStatus']);

/*
|--------------------------------------------------------------------------
| NOTIFIKASI
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::post('/notifikasi', [NotifikasiController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| RATING
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ratings', [RatingController::class, 'index']);
    Route::post('/ratings', [RatingController::class, 'store']);
    Route::get('/ratings/{id}', [RatingController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| PROFIL
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profil', [ProfilController::class, 'show']);
    Route::post('/profil', [ProfilController::class, 'storeOrUpdate']);
    Route::post('/profil/upload-foto', [ProfilController::class, 'uploadFoto']);
});
