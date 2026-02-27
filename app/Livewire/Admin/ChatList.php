<?php

namespace App\Livewire\Admin;

use App\Models\Character;
use App\Models\ChatBlock;
use App\Models\ChatConversation;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatList extends Component
{
    use WithFileUploads;

    public array $editingName = [];
    public int $lastKnownUnread = -1;
    public ?int $blockingConversationId = null;
    public string $blockReason = '';
    public string $blockDuration = 'indefinite';

    // Sound settings
    public $newBlockedSound;
    public $newNotificationSound;

    public function toggleOnline(int $characterId): void
    {
        $character = Character::findOrFail($characterId);
        $character->update(['chat_online' => !$character->chat_online]);
    }

    public function updateVisitorName(int $id): void
    {
        $this->validate([
            "editingName.{$id}" => ['nullable', 'string', 'max:100'],
        ]);

        $name = trim($this->editingName[$id] ?? '');

        ChatConversation::findOrFail($id)->update([
            'visitor_name' => $name ?: null,
        ]);

        $this->editingName = [];
    }

    public function openBlockModal(int $conversationId): void
    {
        $this->blockingConversationId = $conversationId;
        $this->reset(['blockReason', 'blockDuration']);
        $this->blockDuration = 'indefinite';
    }

    public function closeBlockModal(): void
    {
        $this->blockingConversationId = null;
        $this->reset(['blockReason', 'blockDuration']);
    }

    public function blockVisitor(): void
    {
        $this->validate([
            'blockReason' => ['required', 'string', 'max:500'],
            'blockDuration' => ['required', 'in:day,indefinite'],
        ]);

        $conversation = ChatConversation::findOrFail($this->blockingConversationId);

        if (!$conversation->visitor_ip) {
            session()->flash('status', 'Geen IP-adres beschikbaar.');
            $this->closeBlockModal();
            return;
        }

        ChatBlock::create([
            'ip_address' => $conversation->visitor_ip,
            'visitor_uuid' => $conversation->visitor_uuid,
            'reason' => $this->blockReason,
            'blocked_by' => auth()->id(),
            'expires_at' => $this->blockDuration === 'day' ? now()->addDay() : null,
        ]);

        $this->closeBlockModal();
        session()->flash('status', 'Bezoeker geblokkeerd.');
    }

    public function saveBlockedSound(): void
    {
        $this->validate([
            'newBlockedSound' => ['required', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:2048'],
        ]);

        $settings = SiteSetting::first();

        // Delete old file
        if ($settings->chat_blocked_sound) {
            Storage::disk('public')->delete($settings->chat_blocked_sound);
        }

        $path = $this->newBlockedSound->store('chat-sounds', 'public');
        $settings->update(['chat_blocked_sound' => $path]);
        $this->reset('newBlockedSound');
        session()->flash('settings-status', 'Geblokkeerd geluid opgeslagen.');
    }

    public function removeBlockedSound(): void
    {
        $settings = SiteSetting::first();
        if ($settings->chat_blocked_sound) {
            Storage::disk('public')->delete($settings->chat_blocked_sound);
            $settings->update(['chat_blocked_sound' => null]);
        }
        session()->flash('settings-status', 'Geblokkeerd geluid verwijderd.');
    }

    public function saveNotificationSound(): void
    {
        $this->validate([
            'newNotificationSound' => ['required', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:2048'],
        ]);

        $settings = SiteSetting::first();

        // Delete old file
        if ($settings->chat_notification_sound) {
            Storage::disk('public')->delete($settings->chat_notification_sound);
        }

        $path = $this->newNotificationSound->store('chat-sounds', 'public');
        $settings->update(['chat_notification_sound' => $path]);
        $this->reset('newNotificationSound');
        session()->flash('settings-status', 'Notificatiegeluid opgeslagen.');
    }

    public function removeNotificationSound(): void
    {
        $settings = SiteSetting::first();
        if ($settings->chat_notification_sound) {
            Storage::disk('public')->delete($settings->chat_notification_sound);
            $settings->update(['chat_notification_sound' => null]);
        }
        session()->flash('settings-status', 'Notificatiegeluid verwijderd.');
    }

    public function deleteConversation(int $id): void
    {
        ChatConversation::findOrFail($id)->delete();
    }

    public function render()
    {
        // Get actively blocked IPs and UUIDs
        $blockedIps = ChatBlock::active()->pluck('ip_address')->filter()->unique()->toArray();
        $blockedUuids = ChatBlock::active()->pluck('visitor_uuid')->filter()->unique()->toArray();

        $conversations = ChatConversation::with('character')
            ->withCount('messages')
            ->orderByDesc('unread_count')
            ->orderBy('last_message_at')
            ->get()
            ->each(function ($conv) use ($blockedIps, $blockedUuids) {
                $conv->is_blocked = ($conv->visitor_ip && in_array($conv->visitor_ip, $blockedIps))
                    || in_array($conv->visitor_uuid, $blockedUuids);
            });

        $totalUnread = (int) $conversations->where('is_blocked', false)->sum('unread_count');

        if ($totalUnread > $this->lastKnownUnread && $this->lastKnownUnread > -1) {
            $this->dispatch('new-message');
        }
        $this->lastKnownUnread = $totalUnread;

        $chatCharacters = Character::where('chat_enabled', true)
            ->orderBy('sort_order')
            ->get(['id', 'first_name', 'last_name', 'profile_image_path', 'profile_photo_path', 'chat_online']);

        $settings = SiteSetting::first();

        return view('livewire.admin.chat-list', [
            'conversations' => $conversations,
            'totalUnread' => $totalUnread,
            'chatCharacters' => $chatCharacters,
            'settings' => $settings,
        ])->layout('layouts.admin');
    }
}
