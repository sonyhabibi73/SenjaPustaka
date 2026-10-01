<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::get('/books/recommended', [BookController::class, 'recommended']);
    Route::get('/books/progress', [BookController::class, 'progress']);
});
