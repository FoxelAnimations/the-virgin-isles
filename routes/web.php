<?php

use App\Livewire\Characters\Index as CharacterIndex;
use App\Livewire\Episodes\Show as EpisodeShow;
use App\Livewire\UserDashboard;
use App\Livewire\Home;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Characters as AdminCharacters;
use App\Livewire\Admin\Episodes as AdminEpisodes;
use App\Livewire\Admin\CreateCharacter;
use App\Livewire\Admin\EditCharacter;
use App\Livewire\Admin\CreateJob;
use App\Livewire\Admin\Login as AdminLogin;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Admin\BlockedVisitors;
use App\Livewire\Admin\ChatList;
use App\Livewire\Admin\ChatView;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/characters', CharacterIndex::class)->name('characters.index');
Route::get('/episodes', EpisodeShow::class)->name('episodes.index');

Route::get('/admin/login', AdminLogin::class)->middleware('guest')->name('admin.login');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', UserDashboard::class)->name('dashboard');

    Route::middleware('can:access-admin')->group(function () {
        Route::get('/admin', AdminDashboard::class)->name('admin.dashboard');
        Route::get('/admin/characters', AdminCharacters::class)->name('admin.characters');
        Route::get('/admin/characters/create', CreateCharacter::class)->name('characters.create');
        Route::get('/admin/characters/{character}/edit', EditCharacter::class)->name('characters.edit');
        Route::get('/admin/jobs/create', CreateJob::class)->name('jobs.create');
        Route::get('/admin/episodes', AdminEpisodes::class)->name('admin.episodes');
        Route::get('/admin/users', AdminUsers::class)->name('admin.users');
        Route::get('/admin/chats', ChatList::class)->name('admin.chats');
        Route::get('/admin/chats/blocked', BlockedVisitors::class)->name('admin.chats.blocked');
        Route::get('/admin/chats/{conversation}', ChatView::class)->name('admin.chats.view');
    });
});
