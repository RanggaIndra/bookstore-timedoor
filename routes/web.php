<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{BookController, AuthorController, RatingController};

Route::get('/', fn() => redirect()->route('books.index'));

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/authors/top', [AuthorController::class, 'leaderboard'])->name('authors.top');

Route::get('/ratings/create', [RatingController::class, 'create'])->name('ratings.create');
Route::post('/ratings', [RatingController::class, 'store'])->name('ratings.store');

Route::get('/ajax/authors', [RatingController::class, 'ajaxAuthors'])->name('ajax.authors');
Route::get('/ajax/books', [RatingController::class, 'ajaxBooks'])->name('ajax.books');
