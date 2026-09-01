<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PackMe\PackageDashboardController;

Route::get('/packme-test', function () {
    return "PackMe Loaded Successfully!";
});

/*
|--------------------------------------------------------------------------
| PackMe Package Dashboard
|--------------------------------------------------------------------------
*/

Route::get(
    '/packages',
    [PackageDashboardController::class, 'index']
)->name('packages.index');

/*
|--------------------------------------------------------------------------
| Package Details
|--------------------------------------------------------------------------
*/

Route::get(
    '/packages/{vendor}/{name}',
    [PackageDashboardController::class, 'show']
)->name('packages.show');

/*
|--------------------------------------------------------------------------
| Publish Package
|--------------------------------------------------------------------------
*/

Route::post(
    '/packages/{vendor}/{name}/publish',
    [PackageDashboardController::class, 'publish']
)->name('packages.publish');

/*
|--------------------------------------------------------------------------
| Initialize Git
|--------------------------------------------------------------------------
*/

Route::post(
    '/packages/{vendor}/{name}/git-init',
    [PackageDashboardController::class, 'gitInit']
)->name('packages.git-init');

/*
|--------------------------------------------------------------------------
| Packagist Guide
|--------------------------------------------------------------------------
*/

Route::get(
    '/packages/{vendor}/{name}/packagist',
    [PackageDashboardController::class, 'packagist']
)->name('packages.packagist');

/*
|--------------------------------------------------------------------------
| Download Package ZIP
|--------------------------------------------------------------------------
*/

Route::get(
    '/packages/{vendor}/{name}/download',
    [PackageDashboardController::class, 'download']
)->name('packages.download');

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('packages.index');
});