<?php

use Illuminate\Support\Facades\Route;
use Demo\MyPackage\Http\Controllers\__PACKAGE_UC__Controller;

Route::middleware('web')
    ->prefix('my-package')
    ->group(function () {
        Route::get('/', [__PACKAGE_UC__Controller::class, 'index']);
    });
