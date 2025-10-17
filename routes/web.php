<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/authorize', fn() => redirect('/oauth/authorize?' . http_build_query(request()->all())));

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return 'Dashboard - You are logged in as ' . auth()->user()->email;
    })->name('dashboard');
});

require __DIR__.'/auth.php';
