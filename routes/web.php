<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ToiletController;
use App\Http\Controllers\ToiletSessionController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Theme preference
Route::post('/theme/toggle', [ThemeController::class, 'toggle'])->name('theme.toggle');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Game dashboard
    Route::get('/', [ToiletController::class, 'index'])->name('home');

    // Toilet management
    Route::post('/toilets', [ToiletController::class, 'store'])->name('toilets.store');
    Route::get('/toilets/{toilet}/upgrade-cost', [ToiletController::class, 'getUpgradeCost'])->name('toilets.upgrade-cost');
    Route::post('/toilets/{toilet}/upgrade', [ToiletController::class, 'upgrade'])->name('toilets.upgrade');
    Route::delete('/reset-progress', [ToiletController::class, 'resetProgress'])->name('reset-progress');

    // Toilet session management
    Route::post('/sessions', [ToiletSessionController::class, 'createSession'])->name('sessions.create');
    Route::patch('/sessions/{id}/end', [ToiletSessionController::class, 'endRunningSession'])->name('sessions.end');
    Route::get('/sessions/active', [ToiletSessionController::class, 'getActiveSessions'])->name('sessions.active');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
