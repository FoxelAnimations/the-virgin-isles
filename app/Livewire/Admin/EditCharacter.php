<?php

namespace App\Livewire\Admin;

use App\Models\Character;
use App\Models\CharacterSocialLink;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
    public $profile_image_hover;
    public $full_body_image;
    public $full_body_image_hover;
    public $profile_photo;
    public $profile_photo_hover;
    public $chat_image;
    public $background_image;

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
            'character_social_links.*.url' => ['required', 'string', 'starts_with:https://,http://', 'max:255'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'profile_image_hover' => ['nullable', 'image', 'max:2048'],
            'full_body_image' => ['nullable', 'image', 'max:2048'],
            'full_body_image_hover' => ['nullable', 'image', 'max:2048'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'profile_photo_hover' => ['nullable', 'image', 'max:2048'],
            'chat_image' => ['nullable', 'image', 'max:2048'],
            'background_image' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function save(): void
    {
        try {
            $validated = $this->validate();

            DB::transaction(function () use ($validated) {
                $imageData = [];

                if ($this->profile_image) {
                    $imageData['profile_image_path'] = $this->profile_image->store('characters/profile', 'public');
                }
                if ($this->profile_image_hover) {
                    $imageData['profile_image_hover_path'] = $this->profile_image_hover->store('characters/profile', 'public');
                }
                if ($this->full_body_image) {
                    $imageData['full_body_image_path'] = $this->full_body_image->store('characters/full-body', 'public');
                }
                if ($this->full_body_image_hover) {
                    $imageData['full_body_image_hover_path'] = $this->full_body_image_hover->store('characters/full-body', 'public');
                }
                if ($this->profile_photo) {
                    $imageData['profile_photo_path'] = $this->profile_photo->store('characters/profile-photo', 'public');
                }
                if ($this->profile_photo_hover) {
                    $imageData['profile_photo_hover_path'] = $this->profile_photo_hover->store('characters/profile-photo', 'public');
                }
                if ($this->chat_image) {
                    $imageData['chat_image_path'] = $this->chat_image->store('characters/chat', 'public');
                }
                if ($this->background_image) {
                    $imageData['background_image_path'] = $this->background_image->store('characters/background', 'public');
                }

                $this->character->update(array_merge([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'] ?? null,
                    'nick_name' => $validated['nick_name'] ?? null,
                    'age' => $validated['age'] ?? null,
                    'job_id' => $validated['job_id'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                    'personality' => $validated['personality'] ?? null,
                    'speaking_style' => $validated['speaking_style'] ?? null,
                    'backstory' => $validated['backstory'] ?? null,
                    'example_phrases' => $validated['example_phrases'] ?? null,
                    'chat_instructions' => $validated['chat_instructions'] ?? null,
                    'chat_enabled' => $validated['chat_enabled'],
                    'chat_mode' => $validated['chat_mode'],
                    'chat_online' => $validated['chat_online'],
                ], $imageData));

                foreach ($this->character_social_links as $linkData) {
                    CharacterSocialLink::where('id', $linkData['id'])->update([
                        'title' => $linkData['title'],
                        'url' => $linkData['url'],
                    ]);
                }
            });

            $this->character->refresh();
            session()->flash('status', 'Character updated successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Save failed: ' . $e->getMessage());
        }
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

    public function removeImage(string $field): void
    {
        $allowedFields = [
            'profile_image_path',
            'profile_image_hover_path',
            'full_body_image_path',
            'full_body_image_hover_path',
            'profile_photo_path',
            'profile_photo_hover_path',
            'chat_image_path',
            'background_image_path',
        ];

        if (!in_array($field, $allowedFields)) {
            return;
        }

        $path = $this->character->{$field};

        if ($path) {
            Storage::disk('public')->delete($path);
            $this->character->update([$field => null]);
        }
    }

    public function render()
    {
        return view('livewire.admin.edit-character', [
            'jobs' => Job::orderBy('title')->get(),
        ])->layout('layouts.admin');
    }
}
