<?php

use App\Http\Controllers\Organization\Organization;
use App\Http\Controllers\Organization\OrganizationMember;
use Illuminate\Support\Facades\Route;

/*
| As permissões (Spatie) e o papel do usuário em cada organização são
| verificados na OrganizationPolicy, chamada pelos services.
*/
Route::middleware(['auth.api'])->group(function () {
    Route::get('/', [Organization::class, 'index']);
    Route::post('/', [Organization::class, 'store']);
    Route::get('/{id}', [Organization::class, 'show']);
    Route::put('/{id}', [Organization::class, 'update']);
    Route::delete('/{id}', [Organization::class, 'delete']);

    Route::prefix('/{id}/members')->group(function () {
        Route::get('/', [OrganizationMember::class, 'index']);
        Route::post('/', [OrganizationMember::class, 'store']);
        Route::put('/{userId}', [OrganizationMember::class, 'update']);
        Route::delete('/{userId}', [OrganizationMember::class, 'delete']);
    });
});
