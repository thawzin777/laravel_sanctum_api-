<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [\App\Http\Controllers\API\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\API\AuthController::class, 'login']);
Route::get('/login', function () {
    return response()->json(['message' => 'Please login'], 401);
})->name('login');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\API\AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return response()->json(['message' => 'Authenticated', 'user' => $request->user()], 200);
    });
    Route::get('/profile', [\App\Http\Controllers\API\AuthController::class, 'profile']);
    Route::post('/profile', [\App\Http\Controllers\API\AuthController::class, 'updateProfile']);
});

Route::middleware('auth:sanctum')->group(function(){
    Route::Resource('/products', \App\Http\Controllers\API\PostController::class);
    Route::get('/orders', [\App\Http\Controllers\API\OrderController::class, 'index']);
    Route::post('/orders', [\App\Http\Controllers\API\OrderController::class, 'store']);
    Route::get('/orders/{id}', [\App\Http\Controllers\API\OrderController::class, 'show']);
});