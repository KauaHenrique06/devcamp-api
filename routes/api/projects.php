<?php

use App\Http\Controllers\Project\Project;
use Illuminate\Support\Facades\Route;

/*
| Rotas escopadas pela organização atual (header X-Organization-Id, middleware `organization`).
| As permissões são verificadas na ProjectPolicy, chamada pelo controller com authorize().
*/
Route::middleware(['auth.api', 'organization'])->group(function () {
    Route::get('/', [Project::class, 'index']);
    Route::post('/', [Project::class, 'store']);
    Route::get('/{id}', [Project::class, 'show']);
    Route::put('/{id}', [Project::class, 'update']);
    Route::delete('/{id}', [Project::class, 'delete']);
    Route::patch('/{id}/restore', [Project::class, 'restore']);
    Route::patch('/{id}/archive', [Project::class, 'toggleArchive']);
    Route::delete('/{id}/force', [Project::class, 'forceDelete']);
});
