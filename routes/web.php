<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [TodoController::class, 'index']);
    Route::get('/todos/search', [TodoController::class, 'search'])
        ->name('todos.search');
    Route::post('/todos', [TodoController::class, 'store'])
        ->name('todos.store');
    Route::patch('/todos/reorder', [TodoController::class, 'reorder'])
        ->name('todos.reorder');
    Route::patch('/todos/update', [TodoController::class, 'update'])
        ->name('todos.update');
    Route::delete('/todos/delete', [TodoController::class, 'destroy'])
        ->name('todos.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])
        ->name('categories.index');

    Route::post('/categories', [CategoryController::class, 'store'])
        ->name('categories.store');

    Route::patch('/categories/update', [CategoryController::class, 'update'])
        ->name('categories.update');

    Route::delete('/categories/delete', [CategoryController::class, 'destroy'])
        ->name('categories.destroy');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
