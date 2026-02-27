<?php

use App\Http\Controllers\Api\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('chat')->middleware('throttle:chat')->group(function () {
    Route::get('/characters', [ChatController::class, 'characters']);
    Route::post('/send', [ChatController::class, 'send'])->middleware('throttle:chat-send');
    Route::get('/history', [ChatController::class, 'history']);
});

Route::prefix('chat')->middleware('throttle:120,1')->group(function () {
    Route::get('/poll', [ChatController::class, 'poll']);
});
