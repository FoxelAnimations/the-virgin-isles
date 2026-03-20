<?php

use App\Http\Controllers\BeaconController;
use App\Livewire\Characters\Index as CharacterIndex;
use App\Livewire\Episodes\Show as EpisodeShow;
use App\Livewire\UserDashboard;
use App\Livewire\Blog;
use App\Livewire\BlogShow;
use App\Livewire\CollabIndex;
use App\Livewire\CollabShow;
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
use App\Livewire\Admin\BlogPosts as AdminBlogPosts;
use App\Livewire\Admin\Collabs as AdminCollabs;
use App\Livewire\Admin\Quotes as AdminQuotes;
use App\Livewire\Admin\ContentBlocks;
use App\Livewire\Admin\Cameras as AdminCameras;
use App\Livewire\Admin\CameraPlanner;
use App\Livewire\Admin\CameraSettings;
use App\Livewire\Admin\Beacons as AdminBeacons;
use App\Livewire\Admin\BeaconDetail;
use App\Livewire\Admin\BeaconTypes;
use App\Livewire\Admin\BeaconLogs;
use App\Livewire\Admin\BeaconAnalytics;
use App\Livewire\Cameras\Index as CameraIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/personages', CharacterIndex::class)->name('characters.index');
Route::get('/episodes', EpisodeShow::class)->name('episodes.index');
Route::get('/blog', Blog::class)->name('blog');
Route::get('/blog/{slug}', BlogShow::class)->name('blog.show');
Route::get('/collabs', CollabIndex::class)->name('collabs');
Route::get('/collabs/{slug}', CollabShow::class)->name('collabs.show');
Route::get('/cameras', CameraIndex::class)->name('cameras.index');

// Public beacon scan endpoint (rate limiting handled inside controller to allow logging with flag)
Route::get('/beacon/{guid}', [BeaconController::class, 'scan'])
    ->name('beacon.scan');

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
        Route::get('/admin/content-blocks', ContentBlocks::class)->name('admin.content-blocks');
        Route::get('/admin/blog', AdminBlogPosts::class)->name('admin.blog');
        Route::get('/admin/collabs', AdminCollabs::class)->name('admin.collabs');
        Route::get('/admin/quotes', AdminQuotes::class)->name('admin.quotes');
        Route::get('/admin/cameras', AdminCameras::class)->name('admin.cameras');
        Route::get('/admin/cameras/settings', CameraSettings::class)->name('admin.camera-settings');
        Route::get('/admin/cameras/{camera}/planner', CameraPlanner::class)->name('admin.camera-planner');

        // Beacons
        Route::get('/admin/beacons', AdminBeacons::class)->name('admin.beacons');
        Route::get('/admin/beacons/types', BeaconTypes::class)->name('admin.beacon-types');
        Route::get('/admin/beacons/logs', BeaconLogs::class)->name('admin.beacon-logs');
        Route::get('/admin/beacons/analytics', BeaconAnalytics::class)->name('admin.beacon-analytics');
        Route::get('/admin/beacons/{beacon}', BeaconDetail::class)->name('admin.beacon-detail');

        // Admin beacon scan lookup — redirects to detail page without registering a scan
        Route::get('/admin/scan/goto/{guid}', function (string $guid) {
            $beacon = \App\Models\Beacon::where('guid', $guid)->first();
            if (! $beacon) {
                return redirect()->route('admin.beacons');
            }
            return redirect()->route('admin.beacon-detail', $beacon);
        })->name('admin.scan-goto');
    });
});
