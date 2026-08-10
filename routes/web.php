<?php

use App\Http\Controllers\Auth\IdentifyAccountController;
use App\Http\Controllers\Auth\MemberActivationController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ClubInvitationController;
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

Route::get('/join/{token}', [ClubInvitationController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('club-invitations.show');

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
    Route::get('/clubs/{club}/settings/events', [ClubController::class, 'settingsEvents'])->name('clubs.settings.events');
    Route::post('/clubs/{club}/settings/events/import', [ClubController::class, 'importEvents'])->name('clubs.settings.events.import');
    Route::get('/clubs/{club}/settings/invitations', [ClubInvitationController::class, 'index'])->name('clubs.settings.invitations');
    Route::post('/clubs/{club}/settings/invitations', [ClubInvitationController::class, 'store'])->name('clubs.settings.invitations.store');
    Route::delete('/club-invitations/{invitation}', [ClubInvitationController::class, 'destroy'])->name('club-invitations.destroy');
    Route::post('/join/{token}/confirm', [ClubInvitationController::class, 'confirm'])->name('club-invitations.confirm');
    Route::resource('clubs.members', MemberController::class)->except('show');
    Route::resource('clubs.positions', PositionController::class);
    Route::resource('clubs.events', EventController::class);
    Route::resource('clubs.links', LinkController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});
