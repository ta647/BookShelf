<?php

use App\Http\Controllers\Api\BookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // 読み取り系（認証不要）
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{book}', [BookController::class, 'show']);

    // 書き込み系（★応用: Sanctum認証必須）
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/books', [BookController::class, 'store']);
        Route::put('/books/{book}', [BookController::class, 'update']);
        Route::delete('/books/{book}', [BookController::class, 'destroy']);
    });
});
