<?php

use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\ChildController;
use App\Http\Controllers\Api\PlayController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
| Asahi memakai session cookie biasa (same-origin), jadi API diletakkan di web.php
| supaya mendapat session + proteksi CSRF. Frontend mengirim header X-CSRF-TOKEN.
*/

Route::get('/', [AppController::class, 'show'])->name('home');
Route::get('/privasi', [AppController::class, 'privacy'])->name('privacy');

// Login Google
Route::get('/auth/google', [AuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'callback'])->name('google.callback');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/login', fn () => redirect('/'))->name('login');   // dipakai middleware auth untuk redirect

// Bank soal publik (bisa dipakai tanpa login / mode tamu)
Route::get('/api/banks/{slug}', [BankController::class, 'show'])
    ->where('slug', '[A-Za-z0-9_]+')
    ->middleware('throttle:60,1');
Route::get('/banks/{slug}/{file}', [BankController::class, 'asset'])
    ->where(['slug' => '[A-Za-z0-9_]+', 'file' => '[A-Za-z0-9_\-]+\.(jpg|png|webp)']);

// API keluarga (wajib login)
Route::prefix('api')->middleware(['auth', 'throttle:120,1'])->group(function () {
    Route::get('/me', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::post('/children', [ChildController::class, 'store']);
    Route::patch('/children/{child}', [ChildController::class, 'update']);
    Route::delete('/children/{child}', [ChildController::class, 'destroy']);

    Route::get('/children/{child}/plays', [PlayController::class, 'index']);
    Route::post('/children/{child}/plays', [PlayController::class, 'store']);
    Route::patch('/plays/{play}', [PlayController::class, 'update']);
});
