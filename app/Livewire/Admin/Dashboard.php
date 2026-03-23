<?php

namespace App\Livewire\Admin;

use App\Models\AgeGate;
use App\Models\Character;
use App\Models\HeroContent;
use App\Models\HeroVideo;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Dashboard extends Component
{
    use WithFileUploads;

    public $video;
    public $heroImage;
    public array $socialLinks = [];
    public string $heroPreTitle = '';
    public string $heroTitle = '';
    public string $heroDescription = '';
    public bool $loginEnabled = true;
    public bool $registerEnabled = false;
    public bool $ageGateEnabled = true;
    public string $ageGateMessage = '';
    public string $ageGateConfirmText = '';
    public string $ageGateDenyText = '';
    public string $ageGateDenyUrl = '';
    public bool $chatEnabled = false;
    public $defaultChatCharacterId = null;
    public bool $showEpisodes = true;
    public bool $showShorts = true;
    public bool $showMinis = true;
    public bool $showSpecials = true;
    public bool $showCollabs = false;
    public bool $showQuotes = true;
    public string $carouselTitle = '';
    public string $dashboardWelcomeTitle = '';
    public string $dashboardWelcomeText = '';
    public array $dashboardNewsItems = [];
    public string $newNewsItem = '';
    public string $badgeSectionLabel = '';
    public string $badgeSectionTitle = '';
    public string $badgeSectionText = '';

    public function mount(): void
    {
        $this->loadSocialLinks();
        $this->loadHeroContent();
        $this->loadAgeGate();

        $settings = SiteSetting::first();
        $this->loadSiteSettings($settings);
        $this->loadChatSettings($settings);
        $this->loadHomepageSettings($settings);
        $this->loadDashboardSettings($settings);
    }

    protected function loadHeroContent(): void
    {
        $content = HeroContent::first();
        if ($content) {
            $this->heroPreTitle = $content->pre_title;
            $this->heroTitle = $content->title;
            $this->heroDescription = $content->description;
        }
    }

    protected function loadSocialLinks(): void
    {
        $this->socialLinks = SocialLink::orderBy('sort_order')
            ->get()
            ->map(fn ($link) => [
                'id' => $link->id,
                'label' => $link->label,
                'url' => $link->url ?? '',
            ])
            ->toArray();
    }

    public function uploadVideo(): void
    {
        $this->validate(['video' => ['required', 'mimes:mp4,webm,mov']]);

        $hero = HeroVideo::latest()->first();

        if ($hero && $hero->video_path) {
            Storage::disk('public')->delete($hero->video_path);
        }

        $path = $this->video->store('videos/hero', 'public');

        if ($hero) {
            $hero->update(['video_path' => $path]);
        } else {
            HeroVideo::create(['video_path' => $path]);
        }

        $this->reset('video');
        session()->flash('status', 'Hero video uploaded successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    public function removeVideo(): void
    {
        $hero = HeroVideo::latest()->first();

        if ($hero && $hero->video_path) {
            Storage::disk('public')->delete($hero->video_path);
            $hero->update(['video_path' => null]);

            // Delete the record entirely if both are empty
            if (! $hero->image_path) {
                $hero->delete();
            }
        }

        session()->flash('status', 'Hero video removed.');
        $this->redirect(route('admin.dashboard'));
    }

    public function uploadImage(): void
    {
        $this->validate(['heroImage' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif']]);

        $hero = HeroVideo::latest()->first();

        if ($hero && $hero->image_path) {
            Storage::disk('public')->delete($hero->image_path);
        }

        $path = $this->heroImage->store('images/hero', 'public');

        if ($hero) {
            $hero->update(['image_path' => $path]);
        } else {
            HeroVideo::create(['image_path' => $path]);
        }

        $this->reset('heroImage');
        session()->flash('status', 'Hero image uploaded successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    public function removeImage(): void
    {
        $hero = HeroVideo::latest()->first();

        if ($hero && $hero->image_path) {
            Storage::disk('public')->delete($hero->image_path);
            $hero->update(['image_path' => null]);

            // Delete the record entirely if both are empty
            if (! $hero->video_path) {
                $hero->delete();
            }
        }

        session()->flash('status', 'Hero image removed.');
        $this->redirect(route('admin.dashboard'));
    }

    public function saveHeroContent(): void
    {
        $this->validate([
            'heroPreTitle' => ['required', 'string', 'max:255'],
            'heroTitle' => ['required', 'string', 'max:255'],
            'heroDescription' => ['required', 'string', 'max:5000'],
        ]);

        HeroContent::first()?->update([
            'pre_title' => $this->heroPreTitle,
            'title' => $this->heroTitle,
            'description' => $this->heroDescription,
        ]);

        session()->flash('status', 'Hero content updated successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    protected function loadSiteSettings(?SiteSetting $settings = null): void
    {
        $settings ??= SiteSetting::first();
        if ($settings) {
            $this->loginEnabled = $settings->login_enabled;
            $this->registerEnabled = $settings->register_enabled;
        }
    }

    public function saveSiteSettings(): void
    {
        SiteSetting::first()?->update([
            'login_enabled' => $this->loginEnabled,
            'register_enabled' => $this->registerEnabled,
        ]);

        session()->flash('status', 'Site settings updated successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    protected function loadAgeGate(): void
    {
        $ageGate = AgeGate::first();
        if ($ageGate) {
            $this->ageGateEnabled = $ageGate->enabled;
            $this->ageGateMessage = $ageGate->message;
            $this->ageGateConfirmText = $ageGate->confirm_text;
            $this->ageGateDenyText = $ageGate->deny_text;
            $this->ageGateDenyUrl = $ageGate->deny_url;
        }
    }

    public function saveAgeGate(): void
    {
        $this->validate([
            'ageGateMessage' => ['required', 'string', 'max:1000'],
            'ageGateConfirmText' => ['required', 'string', 'max:255'],
            'ageGateDenyText' => ['required', 'string', 'max:255'],
            'ageGateDenyUrl' => ['required', 'url', 'max:255'],
        ]);

        AgeGate::first()?->update([
            'enabled' => $this->ageGateEnabled,
            'message' => $this->ageGateMessage,
            'confirm_text' => $this->ageGateConfirmText,
            'deny_text' => $this->ageGateDenyText,
            'deny_url' => $this->ageGateDenyUrl,
        ]);

        session()->flash('status', 'Age gate settings updated successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    public function saveSocialLinks(): void
    {
        $this->validate([
            'socialLinks.*.label' => ['required', 'string', 'max:255'],
            'socialLinks.*.url' => ['nullable', 'url', 'max:255'],
        ]);

        foreach ($this->socialLinks as $linkData) {
            SocialLink::where('id', $linkData['id'])->update([
                'label' => $linkData['label'],
                'url' => $linkData['url'] ?: null,
            ]);
        }

        session()->flash('status', 'Social links updated successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    public function addSocialLink(): void
    {
        $maxSort = SocialLink::max('sort_order') ?? -1;

        $link = SocialLink::create([
            'label' => 'New Link',
            'url' => null,
            'sort_order' => $maxSort + 1,
        ]);

        $this->loadSocialLinks();
    }

    public function removeSocialLink(int $id): void
    {
        SocialLink::where('id', $id)->delete();
        $this->loadSocialLinks();

        session()->flash('status', 'Social link removed.');
        $this->redirect(route('admin.dashboard'));
    }

    protected function loadChatSettings(?SiteSetting $settings = null): void
    {
        $settings ??= SiteSetting::first();
        if ($settings) {
            $this->chatEnabled = $settings->chat_enabled ?? false;
            $this->defaultChatCharacterId = $settings->default_chat_character_id;
        }
    }

    public function saveChatSettings(): void
    {
        SiteSetting::first()?->update([
            'chat_enabled' => $this->chatEnabled,
            'default_chat_character_id' => $this->defaultChatCharacterId ?: null,
        ]);

        session()->flash('status', 'Chat settings updated successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    protected function loadHomepageSettings(?SiteSetting $settings = null): void
    {
        $settings ??= SiteSetting::first();
        if ($settings) {
            $this->showEpisodes = $settings->show_episodes ?? true;
            $this->showShorts = $settings->show_shorts ?? true;
            $this->showMinis = $settings->show_minis ?? true;
            $this->showSpecials = $settings->show_specials ?? true;
            $this->showCollabs = $settings->show_collabs ?? false;
            $this->showQuotes = $settings->show_quotes ?? true;
            $this->carouselTitle = $settings->carousel_title ?? '';
        }
    }

    public function saveHomepageSettings(): void
    {
        SiteSetting::first()?->update([
            'show_episodes' => $this->showEpisodes,
            'show_shorts' => $this->showShorts,
            'show_minis' => $this->showMinis,
            'show_specials' => $this->showSpecials,
            'show_collabs' => $this->showCollabs,
            'show_quotes' => $this->showQuotes,
            'carousel_title' => $this->carouselTitle ?: null,
        ]);

        session()->flash('status', 'Homepage settings updated successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    protected function loadDashboardSettings(?SiteSetting $settings = null): void
    {
        $settings ??= SiteSetting::first();
        if ($settings) {
            $this->dashboardWelcomeTitle = $settings->dashboard_welcome_title ?? '';
            $this->dashboardWelcomeText = $settings->dashboard_welcome_text ?? '';
            $this->dashboardNewsItems = $settings->dashboard_news_items ?? [];
            $this->badgeSectionLabel = $settings->badge_section_label ?? '';
            $this->badgeSectionTitle = $settings->badge_section_title ?? '';
            $this->badgeSectionText = $settings->badge_section_text ?? '';
        }
    }

    public function saveDashboardWelcome(): void
    {
        $this->validate([
            'dashboardWelcomeTitle' => ['nullable', 'string', 'max:255'],
            'dashboardWelcomeText' => ['nullable', 'string', 'max:5000'],
        ]);

        SiteSetting::first()?->update([
            'dashboard_welcome_title' => $this->dashboardWelcomeTitle ?: null,
            'dashboard_welcome_text' => $this->dashboardWelcomeText ?: null,
        ]);

        session()->flash('status', 'Dashboard welcome text updated.');
        $this->redirect(route('admin.dashboard'));
    }

    public function saveBadgeSection(): void
    {
        $this->validate([
            'badgeSectionLabel' => ['nullable', 'string', 'max:255'],
            'badgeSectionTitle' => ['nullable', 'string', 'max:255'],
            'badgeSectionText' => ['nullable', 'string', 'max:500'],
        ]);

        SiteSetting::first()?->update([
            'badge_section_label' => $this->badgeSectionLabel ?: null,
            'badge_section_title' => $this->badgeSectionTitle ?: null,
            'badge_section_text' => $this->badgeSectionText ?: null,
        ]);

        session()->flash('status', 'Badge section text updated.');
        $this->redirect(route('admin.dashboard'));
    }

    public function addNewsItem(): void
    {
        $item = trim($this->newNewsItem);
        if ($item === '') {
            return;
        }

        $this->dashboardNewsItems[] = $item;
        $this->newNewsItem = '';
    }

    public function removeNewsItem(int $index): void
    {
        unset($this->dashboardNewsItems[$index]);
        $this->dashboardNewsItems = array_values($this->dashboardNewsItems);
    }

    public function moveNewsItemUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }
        $items = $this->dashboardNewsItems;
        [$items[$index - 1], $items[$index]] = [$items[$index], $items[$index - 1]];
        $this->dashboardNewsItems = $items;
    }

    public function moveNewsItemDown(int $index): void
    {
        if ($index >= count($this->dashboardNewsItems) - 1) {
            return;
        }
        $items = $this->dashboardNewsItems;
        [$items[$index], $items[$index + 1]] = [$items[$index + 1], $items[$index]];
        $this->dashboardNewsItems = $items;
    }

    public function saveDashboardNews(): void
    {
        $items = array_filter(array_map('trim', $this->dashboardNewsItems), fn ($i) => $i !== '');
        $items = array_values($items);

        SiteSetting::first()?->update([
            'dashboard_news_items' => !empty($items) ? $items : null,
            'dashboard_news_updated_at' => now(),
        ]);

        $this->dashboardNewsItems = $items;
        session()->flash('status', 'Nieuwtjes updated. All users will see the updated news.');
        $this->redirect(route('admin.dashboard'));
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'heroVideo' => HeroVideo::latest()->first(),
            'chatEnabledCharacters' => Character::where('chat_enabled', true)->orderBy('first_name')->get(),
        ])->layout('layouts.admin');
    }
}
