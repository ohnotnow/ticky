<?php

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\TriageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])
    ->prefix('v1')
    ->name('api.')
    ->group(function (): void {
        Route::get('/conversations', [ConversationController::class, 'index'])
            ->name('conversations.index');

        Route::post('/triage', [TriageController::class, 'store'])
            ->name('triage.store');
    });
