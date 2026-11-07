<?php

use App\Http\Controllers\Permission\Permission;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.api', 'can:permissions.view'])->group(function () {
    Route::get('/', [Permission::class, 'index']);
});
