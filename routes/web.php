<?php

use App\Http\Controllers\ClubController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('clubs', ClubController::class)->only('store');
Route::resource('clubs.members', MemberController::class)
    ->only('destroy')
    ->scoped();
