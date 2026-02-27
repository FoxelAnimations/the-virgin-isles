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

    public function mount(): void
    {
        $this->loadSocialLinks();
        $this->loadHeroContent();
        $this->loadAgeGate();
        $this->loadSiteSettings();
        $this->loadChatSettings();
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

    protected function rules(): array
    {
        return [
            'video' => ['required', 'mimes:mp4,webm,mov'],
        ];
    }

    public function uploadVideo(): void
    {
        $this->validate();

        $existing = HeroVideo::latest()->first();

        if ($existing) {
            Storage::disk('public')->delete($existing->video_path);
            $existing->delete();
        }

        $path = $this->video->store('videos/hero', 'public');

        HeroVideo::create(['video_path' => $path]);

        $this->reset('video');
        session()->flash('status', 'Hero video uploaded successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    public function removeVideo(): void
    {
        $heroVideo = HeroVideo::latest()->first();

        if ($heroVideo) {
            Storage::disk('public')->delete($heroVideo->video_path);
            $heroVideo->delete();
        }

        session()->flash('status', 'Hero video removed.');
        $this->redirect(route('admin.dashboard'));
    }

    public function saveHeroContent(): void
    {
        $this->validate([
            'heroPreTitle' => ['required', 'string', 'max:255'],
            'heroTitle' => ['required', 'string', 'max:255'],
            'heroDescription' => ['required', 'string', 'max:1000'],
        ]);

        HeroContent::first()?->update([
            'pre_title' => $this->heroPreTitle,
            'title' => $this->heroTitle,
            'description' => $this->heroDescription,
        ]);

        session()->flash('status', 'Hero content updated successfully.');
        $this->redirect(route('admin.dashboard'));
    }

    protected function loadSiteSettings(): void
    {
        $settings = SiteSetting::first();
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

    protected function loadChatSettings(): void
    {
        $settings = SiteSetting::first();
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

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'heroVideo' => HeroVideo::latest()->first(),
            'chatEnabledCharacters' => Character::where('chat_enabled', true)->orderBy('first_name')->get(),
        ])->layout('layouts.admin');
    }
}
