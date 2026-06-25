<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('/login', function () {
    return view('Login');
})->name('login');

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::post('/login', function (Request $request) {
    return redirect()->route('home');
})->name('login.submit');


