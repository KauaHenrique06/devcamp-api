<?php

use App\Http\Controllers\Role\Role;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.api', 'can:roles.view'])->group(function () {
    Route::get('/', [Role::class, 'index']);
    Route::get('/{id}', [Role::class, 'show']);

});

Route::middleware(['auth.api', 'can:roles.create'])->group(function () {
    Route::post('/', [Role::class, 'store']);
});

Route::middleware(['auth.api', 'can:roles.update'])->group(function () {
    Route::put('/{id}', [Role::class, 'update']);
    Route::post('/{id}/permissions', [Role::class, 'addPermissions']);
});

Route::middleware(['auth.api', 'can:roles.delete'])->group(function () {
    Route::delete('/{id}', [Role::class, 'delete']);
});
