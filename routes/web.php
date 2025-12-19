<?php

use App\Livewire\Characters\Create as CreateCharacter;
use App\Livewire\Characters\Index as CharacterIndex;
use App\Livewire\Home;
use App\Livewire\Jobs\Create as CreateJob;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/characters', CharacterIndex::class)->name('characters.index');

Route::any('/register', fn() => abort(404));

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/characters/create', CreateCharacter::class)->name('characters.create');
    Route::get('/jobs/create', CreateJob::class)->name('jobs.create');
});
