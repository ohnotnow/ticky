<?php

use App\Livewire\HomePage;
use App\Livewire\TriageChat;
use Illuminate\Support\Facades\Route;

require __DIR__.'/sso-auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/', HomePage::class)->name('home');
    Route::get('/triage', TriageChat::class)->name('triage');
});
