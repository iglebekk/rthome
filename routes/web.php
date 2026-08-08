<?php

use App\Http\Controllers\Auth\IdentifyAccountController;
use App\Http\Controllers\Auth\MemberActivationController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
})->name('welcome');

Route::middleware('guest')->group(function (): void {
    Route::post('/login/identify', IdentifyAccountController::class)
        ->middleware('throttle:6,1')
        ->name('login.identify');
    Route::view('/activation/sent', 'auth.activation-sent')->name('activation.sent');
    Route::get('/activation/{activation}/{token}', [MemberActivationController::class, 'show'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('activation.show');
    Route::post('/activation/{activation}/{token}', [MemberActivationController::class, 'store'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('activation.store');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/home', [DashboardController::class, 'home'])->name('home');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::resource('clubs', ClubController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/clubs/{club}/dashboard', [DashboardController::class, 'show'])->name('clubs.dashboard');
    Route::resource('clubs.members', MemberController::class)->except('show');
    Route::resource('clubs.positions', PositionController::class);
    Route::resource('clubs.events', EventController::class);
    Route::resource('clubs.links', LinkController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});
