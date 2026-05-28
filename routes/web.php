<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index'])->name('home');

// 書籍
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/create', [BookController::class, 'create'])->middleware('auth')->name('books.create');
Route::post('/books', [BookController::class, 'store'])->middleware('auth')->name('books.store');
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
