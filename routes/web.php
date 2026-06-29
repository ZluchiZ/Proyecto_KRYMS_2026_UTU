<?php

use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/test-cliente', function () {
    return DB::table('Cliente')->get();
});


Route::get('/login', function () {
    return view('Login');
})->name('login');

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::post('/Cliente', [ClienteController::class, 'store'])->name('cliente.store');

use App\Http\Controllers\LoginController;

Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::get('/verificar/{token}', [ClienteController::class, 'verificar']);