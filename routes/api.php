<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('users', [UserController::class, 'index']);
Route::get('users/{user}', [UserController::class, 'show']);
Route::get('users/{user}/transactions', [UserController::class, 'transactions']);
Route::get('transactions', [TransactionController::class, 'index']);
Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
