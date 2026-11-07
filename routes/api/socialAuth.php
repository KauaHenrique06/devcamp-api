<?php

use App\Http\Controllers\Auth\SocialAuth;
use Illuminate\Support\Facades\Route;

Route::prefix('{provider}')->middleware(['feature:social_login'])->group(function () {
    Route::get('redirect', [SocialAuth::class, 'redirect']);
    Route::post('callback', [SocialAuth::class, 'callback']);
});