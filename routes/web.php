<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WeddingInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('invitation.index');
});

Route::get('/undangan', [WeddingInvitationController::class, 'index'])->name('invitation.index');
Route::post('/undangan/tamu', [WeddingInvitationController::class, 'storeInvitation'])->name('invitation.store');
Route::get('/undangan/{guestName}/{guestInvitation}', [WeddingInvitationController::class, 'show'])->name('invitation.show');
Route::get('/undangan/{guestInvitation}', [WeddingInvitationController::class, 'showLegacy'])->name('invitation.show.legacy');
Route::post('/ucapan', [WeddingInvitationController::class, 'storeWish'])->name('wishes.store');
Route::get('/ucapan/list', [WeddingInvitationController::class, 'listWishes'])->name('wishes.list');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::post('/dashboard/tamu', [DashboardController::class, 'storeInvitation'])
    ->middleware('auth')
    ->name('dashboard.invitation.store');

Route::put('/dashboard/tamu/{guestInvitation}', [DashboardController::class, 'updateInvitation'])
    ->middleware('auth')
    ->name('dashboard.invitation.update');

Route::delete('/dashboard/tamu/{guestInvitation}', [DashboardController::class, 'destroyInvitation'])
    ->middleware('auth')
    ->name('dashboard.invitation.destroy');
