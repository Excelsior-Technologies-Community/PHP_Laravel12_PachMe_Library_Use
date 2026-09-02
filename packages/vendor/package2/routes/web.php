<?php

use Illuminate\Support\Facades\Route;
use Vendor\Package2\Http\Controllers\__PACKAGE_UC__Controller;

Route::middleware('web')
    ->prefix('package2')
    ->group(function () {
        Route::get('/', [__PACKAGE_UC__Controller::class, 'index']);
    });
