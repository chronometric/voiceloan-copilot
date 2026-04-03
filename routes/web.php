<?php

use App\Http\Controllers\BorrowerController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('borrowers', BorrowerController::class)->except(['show']);

    Route::patch('borrowers/{borrower}/identity', [BorrowerController::class, 'updateIdentity'])
        ->name('borrowers.identity.update');
    Route::patch('borrowers/{borrower}/declaration', [BorrowerController::class, 'updateDeclaration'])
        ->name('borrowers.declaration.update');

    Route::post('borrowers/{borrower}/employments', [BorrowerController::class, 'storeEmployment'])
        ->name('borrowers.employments.store');
    Route::patch('borrowers/{borrower}/employments/{employment}', [BorrowerController::class, 'updateEmployment'])
        ->name('borrowers.employments.update');
    Route::delete('borrowers/{borrower}/employments/{employment}', [BorrowerController::class, 'destroyEmployment'])
        ->name('borrowers.employments.destroy');

    Route::post('borrowers/{borrower}/assets', [BorrowerController::class, 'storeAsset'])
        ->name('borrowers.assets.store');
    Route::patch('borrowers/{borrower}/assets/{asset}', [BorrowerController::class, 'updateAsset'])
        ->name('borrowers.assets.update');
    Route::delete('borrowers/{borrower}/assets/{asset}', [BorrowerController::class, 'destroyAsset'])
        ->name('borrowers.assets.destroy');
});

require __DIR__.'/auth.php';
