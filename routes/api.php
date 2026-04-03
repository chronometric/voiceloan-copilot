<?php

use App\Http\Controllers\Api\BorrowerAssetController;
use App\Http\Controllers\Api\BorrowerController;
use App\Http\Controllers\Api\BorrowerDeclarationController;
use App\Http\Controllers\Api\BorrowerEmploymentController;
use App\Http\Controllers\Api\BorrowerIdentityController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\VoiceSessionController;
use App\Http\Controllers\Api\VoiceToolController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);

Route::middleware('voice.bridge')->prefix('voice')->group(function () {
    Route::post('/sessions', [VoiceSessionController::class, 'store']);
    Route::get('/sessions/{callSid}/borrower', [VoiceSessionController::class, 'showBorrower']);
    Route::patch('/sessions/{callSid}/borrower', [VoiceSessionController::class, 'updateBorrower']);
    Route::post('/tools', [VoiceToolController::class, 'execute']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::get('/borrowers/{borrower}', [BorrowerController::class, 'show']);
    Route::patch('/borrowers/{borrower}', [BorrowerController::class, 'update']);
    Route::delete('/borrowers/{borrower}', [BorrowerController::class, 'destroy']);

    Route::get('/borrowers/{borrower}/identity', [BorrowerIdentityController::class, 'show']);
    Route::patch('/borrowers/{borrower}/identity', [BorrowerIdentityController::class, 'update']);

    Route::get('/borrowers/{borrower}/employments', [BorrowerEmploymentController::class, 'index']);
    Route::post('/borrowers/{borrower}/employments', [BorrowerEmploymentController::class, 'store']);
    Route::patch('/borrowers/{borrower}/employments/{employment}', [BorrowerEmploymentController::class, 'update']);
    Route::delete('/borrowers/{borrower}/employments/{employment}', [BorrowerEmploymentController::class, 'destroy']);

    Route::get('/borrowers/{borrower}/assets', [BorrowerAssetController::class, 'index']);
    Route::post('/borrowers/{borrower}/assets', [BorrowerAssetController::class, 'store']);
    Route::patch('/borrowers/{borrower}/assets/{asset}', [BorrowerAssetController::class, 'update']);
    Route::delete('/borrowers/{borrower}/assets/{asset}', [BorrowerAssetController::class, 'destroy']);

    Route::get('/borrowers/{borrower}/declaration', [BorrowerDeclarationController::class, 'show']);
    Route::patch('/borrowers/{borrower}/declaration', [BorrowerDeclarationController::class, 'update']);
});
