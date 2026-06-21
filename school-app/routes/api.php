<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Sanctum)
|--------------------------------------------------------------------------
| These routes will be consumed by the Flutter mobile app in Phase 8.
| For now, just a health-check endpoint.
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'Hanara Schools Management System',
        'version' => '1.0.0',
    ]);
});
