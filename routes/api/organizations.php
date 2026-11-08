<?php

use App\Http\Controllers\Organization\Organization;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.api', 'can:organizations.view'])->group(function () {
    Route::get('/', [Organization::class, 'index']);
    Route::get('/{id}', [Organization::class, 'show']);
});

Route::middleware(['auth.api', 'can:organizations.create'])->group(function () {
    Route::post('/', [Organization::class, 'store']);
});

Route::middleware(['auth.api', 'can:organizations.update'])->group(function () {
    Route::put('/{id}', [Organization::class, 'update']);
});

Route::middleware(['auth.api', 'can:organizations.delete'])->group(function () {
    Route::delete('/{id}', [Organization::class, 'delete']);
});
