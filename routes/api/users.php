<?php

use App\Http\Controllers\User\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.api', 'can:user.view'])->group(function () {
    Route::get('/', [User::class, 'index']);
    Route::get('/{id}', [User::class, 'show']);
});

Route::middleware(['auth.api', 'can:user.create'])->group(function () {
    Route::post('/', [User::class, 'store']);
});

Route::middleware(['auth.api', 'can:user.update'])->group(function () {
    Route::put('/{id}', [User::class, 'update']);
});

Route::middleware(['auth.api', 'can:user.delete'])->group(function () {
    Route::delete('/{id}', [User::class, 'delete']);
});
