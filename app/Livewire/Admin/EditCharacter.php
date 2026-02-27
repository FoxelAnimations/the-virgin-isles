<?php

namespace App\Livewire\Admin;

use App\Models\Character;
use App\Models\CharacterSocialLink;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditCharacter extends Component
{
    use WithFileUploads;

    public Character $character;

    public $first_name = '';
    public $last_name = '';
    public $nick_name = '';
    public $age;
    public $job_id;
    public $bio = '';
    public $personality = '';
    public $speaking_style = '';
    public $backstory = '';
    public $example_phrases = '';
    public $chat_instructions = '';
    public bool $chat_enabled = false;
    public string $chat_mode = 'ai';
    public bool $chat_online = true;
    public array $character_social_links = [];
    public $profile_image;
    public $full_body_image;
    public $profile_photo;

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'nick_name' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'between:0,255'],
            'job_id' => ['nullable', 'exists:character_jobs,id'],
            'bio' => ['nullable', 'string'],
            'personality' => ['nullable', 'string', 'max:2000'],
            'speaking_style' => ['nullable', 'string', 'max:2000'],
            'backstory' => ['nullable', 'string', 'max:2000'],
            'example_phrases' => ['nullable', 'string', 'max:2000'],
            'chat_instructions' => ['nullable', 'string', 'max:2000'],
            'chat_enabled' => ['boolean'],
            'chat_mode' => ['required', 'string', 'in:ai,manual'],
            'chat_online' => ['boolean'],
            'character_social_links.*.title' => ['required', 'string', 'max:255'],
            'character_social_links.*.url' => ['required', 'url', 'max:255'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'full_body_image' => ['nullable', 'image', 'max:2048'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $this->character->first_name = $validated['first_name'];
            $this->character->last_name = $validated['last_name'] ?? null;
            $this->character->nick_name = $validated['nick_name'] ?? null;
            $this->character->age = $validated['age'] ?? null;
            $this->character->job_id = $validated['job_id'] ?? null;
            $this->character->bio = $validated['bio'] ?? null;
            $this->character->personality = $validated['personality'] ?? null;
            $this->character->speaking_style = $validated['speaking_style'] ?? null;
            $this->character->backstory = $validated['backstory'] ?? null;
            $this->character->example_phrases = $validated['example_phrases'] ?? null;
            $this->character->chat_instructions = $validated['chat_instructions'] ?? null;
            $this->character->chat_enabled = $validated['chat_enabled'];
            $this->character->chat_mode = $validated['chat_mode'];
            $this->character->chat_online = $validated['chat_online'];

            if ($this->profile_image) {
                $this->character->profile_image_path = $this->profile_image->store('characters/profile', 'public');
            }

            if ($this->full_body_image) {
                $this->character->full_body_image_path = $this->full_body_image->store('characters/full-body', 'public');
            }

            if ($this->profile_photo) {
                $this->character->profile_photo_path = $this->profile_photo->store('characters/profile-photo', 'public');
            }

            $this->character->save();

            foreach ($this->character_social_links as $linkData) {
                CharacterSocialLink::where('id', $linkData['id'])->update([
                    'title' => $linkData['title'],
                    'url' => $linkData['url'],
                ]);
            }
        });

        session()->flash('status', 'Character updated successfully.');
    }

    public function mount(Character $character): void
    {
        $this->character = $character;
        $this->first_name = $character->first_name;
        $this->last_name = $character->last_name;
        $this->nick_name = $character->nick_name;
        $this->age = $character->age;
        $this->job_id = $character->job_id;
        $this->bio = $character->bio;
        $this->personality = $character->personality ?? '';
        $this->speaking_style = $character->speaking_style ?? '';
        $this->backstory = $character->backstory ?? '';
        $this->example_phrases = $character->example_phrases ?? '';
        $this->chat_instructions = $character->chat_instructions ?? '';
        $this->chat_enabled = $character->chat_enabled ?? false;
        $this->chat_mode = $character->chat_mode ?? 'ai';
        $this->chat_online = $character->chat_online ?? true;
        $this->loadCharacterSocialLinks();
    }

    protected function loadCharacterSocialLinks(): void
    {
        $this->character_social_links = $this->character->socialLinks
            ->map(fn ($link) => [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
            ])
            ->toArray();
    }

    public function addCharacterSocialLink(): void
    {
        $this->character->socialLinks()->create([
            'title' => 'New Link',
            'url' => 'https://',
            'sort_order' => count($this->character_social_links),
        ]);
        $this->loadCharacterSocialLinks();
    }

    public function removeCharacterSocialLink(int $id): void
    {
        CharacterSocialLink::where('id', $id)
            ->where('character_id', $this->character->id)
            ->delete();
        $this->loadCharacterSocialLinks();
    }

    public function render()
    {
        return view('livewire.admin.edit-character', [
            'jobs' => Job::orderBy('title')->get(),
        ])->layout('layouts.admin');
    }
}
