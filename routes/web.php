<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannedController;
use App\Http\Controllers\ContactNumberController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile/contact-number', [ContactNumberController::class, 'edit'])->name('contact.edit');
    Route::put('/profile/contact-number', [ContactNumberController::class, 'update'])->name('contact.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('contact')->group(function () {
        Route::get('/approval/pending', [ApprovalController::class, 'pending'])->name('approval.pending');

        Route::middleware('approved')->group(function () {
            Route::get('/dashboard', [BannedController::class, 'index'])->name('dashboard');
            Route::post('/banned', [BannedController::class, 'store'])->name('banned.store');
            Route::patch('/banned/{banned}', [BannedController::class, 'update'])->name('banned.update');
            Route::delete('/banned/{banned}', [BannedController::class, 'destroy'])->name('banned.destroy');

            Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
                Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
                Route::patch('/approvals/{user}', [ApprovalController::class, 'approve'])->name('approvals.approve');
            });
        });
    });
});
