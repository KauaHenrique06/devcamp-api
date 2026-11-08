<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/auth')->group(base_path('routes/api/auth.php'));
Route::prefix('/social-auth')->group(base_path('routes/api/socialAuth.php'));
Route::prefix('/permissions')->group(base_path('routes/api/permissions.php'));
Route::prefix('/roles')->group(base_path('routes/api/roles.php'));
Route::prefix('/users')->group(base_path('routes/api/users.php'));
Route::prefix('/organizations')->group(base_path('routes/api/organizations.php'));
