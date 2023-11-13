<?php

use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/items', [TodoController::class, 'items'])->name('items.items');
Route::get('/items/{id}', [TodoController::class, 'getItem'])->name('items.getItem');
Route::post('/items', [TodoController::class, 'create'])->name('items.create');
Route::match(['put', 'patch'], '/items/{id}', [TodoController::class, 'update'])->name('items.update');
Route::delete('/items/{id}', [TodoController::class, 'delete'])->name('items.delete');