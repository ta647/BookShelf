<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\IsbnController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index'])->name('home');

// 書籍
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/create', [BookController::class, 'create'])->middleware('auth')->name('books.create');
Route::post('/books', [BookController::class, 'store'])->middleware('auth')->name('books.store');
// ISBN検索（★応用）— {book}ワイルドカードより前に定義する必要がある
Route::get('/books/isbn/{isbn}', [IsbnController::class, 'search'])->middleware('auth')->name('books.isbn');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::get('/books/{book}/edit', [BookController::class, 'edit'])->middleware('auth')->name('books.edit');
Route::put('/books/{book}', [BookController::class, 'update'])->middleware('auth')->name('books.update');
Route::delete('/books/{book}', [BookController::class, 'destroy'])->middleware('auth')->name('books.destroy');

// お気に入り
Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->middleware('auth')->name('favorites.toggle');
Route::get('/favorites', [FavoriteController::class, 'index'])->middleware('auth')->name('favorites.index');

// レビュー
Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->middleware('auth')->name('reviews.store');
Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->middleware('auth')->name('reviews.edit');
Route::put('/reviews/{review}', [ReviewController::class, 'update'])->middleware('auth')->name('reviews.update');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->middleware('auth')->name('reviews.destroy');

// いいね
Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->middleware('auth')->name('reviews.like');

// ジャンル
Route::middleware('auth')->group(function () {
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');
    Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    Route::put('/genres/{genre}', [GenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');
});

// ランキング
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// マイ読書レポート（★応用）
Route::get('/reports', [ReportController::class, 'index'])->middleware('auth')->name('reports.index');

// 読書計画（★応用）
Route::middleware('auth')->group(function () {
    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
    Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
    Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');
    Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
    Route::put('/reading-plans/{plan}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');
    Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'destroy'])->name('reading-plans.destroy');
    Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');
});

// 通知（★応用）
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
});
