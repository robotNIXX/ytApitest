<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Базовый API маршрут
Route::get('/', function () {
    return response()->json([
        'message' => 'API is working!',
        'version' => '1.0.0',
        'timestamp' => now()
    ]);
});
