<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/users');

Route::livewire('/users', 'pages::users')->name('users.index');
Route::livewire('/phones', 'pages::phone-checker')->name('phones.checker');
