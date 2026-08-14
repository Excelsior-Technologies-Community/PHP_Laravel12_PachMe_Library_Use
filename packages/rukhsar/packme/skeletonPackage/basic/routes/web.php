<?php

use Illuminate\Support\Facades\Route;
use :VendorName\:PackageName\Http\Controllers\__PACKAGE_UC__Controller;

Route::middleware('web')
    ->prefix(':package_name')
    ->group(function () {
        Route::get('/', [__PACKAGE_UC__Controller::class, 'index']);
    });
