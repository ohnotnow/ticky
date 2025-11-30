<?php

use App\Http\Controllers\ConversationDownloadController;
use App\Livewire\ApiTokens;
use App\Livewire\HomePage;
use App\Livewire\TriageChat;
use Illuminate\Support\Facades\Route;

require __DIR__.'/sso-auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/', HomePage::class)->name('home');
    Route::get('/triage', TriageChat::class)->name('triage');
    Route::get('/api-keys', ApiTokens::class)->name('api-keys');
    Route::get('/conversations/{conversation}/download/json', [ConversationDownloadController::class, 'json'])->name('conversations.download.json');
    Route::get('/conversations/{conversation}/download/markdown', [ConversationDownloadController::class, 'markdown'])->name('conversations.download.markdown');
});
