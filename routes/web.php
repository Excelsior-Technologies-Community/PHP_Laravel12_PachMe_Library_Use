<?php

use Illuminate\Support\Facades\Route;
use Rukhsar\PackMe\PackMeHelper;

Route::get('/packme-test', function () {
    return "PackMe Loaded Successfully!";
});


Route::get('/', function () {
    return view('welcome');
});
