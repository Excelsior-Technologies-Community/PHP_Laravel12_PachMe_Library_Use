<?php

use Illuminate\Support\Facades\Route;
use Rukhsar\PackMe\PackMeHelper;
use App\Http\Controllers\PackMe\PackageDashboardController;

Route::get('/packme-test', function () {
    return "PackMe Loaded Successfully!";
});

Route::get('/packages', [PackageDashboardController::class, 'index'])->name('packages.index');
Route::get('/packages/{vendor}/{name}', [PackageDashboardController::class, 'show'])->name('packages.show');
Route::post('/packages/{vendor}/{name}/publish', [PackageDashboardController::class, 'publish'])->name('packages.publish');
Route::post('/packages/{vendor}/{name}/git-init', [PackageDashboardController::class, 'gitInit'])->name('packages.git-init');
Route::get('/packages/{vendor}/{name}/packagist', [PackageDashboardController::class, 'packagist'])->name('packages.packagist');

Route::get('/', function () {
    return redirect('/packages');
});
