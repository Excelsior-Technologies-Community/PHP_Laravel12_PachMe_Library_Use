<?php

use Illuminate\Support\Facades\Route;
use :VendorName\:PackageName\Http\Controllers\Api\__PACKAGE_UC__Controller;

Route::middleware('api')
    ->prefix('api/:package_name')
    ->group(function () {
        Route::get('/', [__PACKAGE_UC__ApiController::class, 'index']);
        Route::post('/', [__PACKAGE_UC__ApiController::class, 'store']);
    });
