<?php

use Illuminate\Support\Facades\Route;
use Demo\MyPackage\Http\Controllers\Api\__PACKAGE_UC__Controller;

Route::middleware('api')
    ->prefix('api/my-package')
    ->group(function () {
        Route::get('/', [__PACKAGE_UC__ApiController::class, 'index']);
        Route::post('/', [__PACKAGE_UC__ApiController::class, 'store']);
    });
