<?php

use Illuminate\Support\Facades\Route;
use Vendor\Package1\Http\Controllers\Api\__PACKAGE_UC__Controller;

Route::middleware('api')
    ->prefix('api/package1')
    ->group(function () {
        Route::get('/', [__PACKAGE_UC__ApiController::class, 'index']);
        Route::post('/', [__PACKAGE_UC__ApiController::class, 'store']);
    });
