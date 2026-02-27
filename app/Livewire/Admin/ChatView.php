<?php

namespace App\Livewire\Admin;

use App\Models\ChatBlock;
use App\Models\ChatConversation;
use Livewire\Component;

class ChatView extends Component
{
    public ChatConversation $conversation;
    public string $reply = '';
    public string $visitorName = '';
    public bool $editingName = false;
    public int $limit = 30;
    public int $lastKnownCount = 0;
    public ?string $lastBlockedAttempt = null;
    public bool $showBlockModal = false;
    public string $blockReason = '';
    public string $blockDuration = 'indefinite';

    public function mount(ChatConversation $conversation): void
    {
        $this->conversation = $conversation;
        $this->visitorName = $conversation->visitor_name ?? '';
        $this->conversation->update(['unread_count' => 0]);
    }

    public function loadMore(): void
    {
        $this->limit += 30;
    }

    public function saveVisitorName(): void
    {
        $this->validate([
            'visitorName' => ['nullable', 'string', 'max:100'],
        ]);

        $name = trim($this->visitorName);
        $this->conversation->update(['visitor_name' => $name ?: null]);
        $this->editingName = false;
    }

    public function sendReply(): void
    {
        if ($this->conversation->status !== 'open') {
            return;
        }

        $this->validate([
            'reply' => ['required', 'string', 'max:2000'],
        ]);

        $this->conversation->messages()->create([
            'sender' => 'character',
            'content' => $this->reply,
            'is_ai' => false,
        ]);

        $this->conversation->update(['last_message_at' => now()]);

        $this->reply = '';
        $this->lastKnownCount++;
        $this->dispatch('scroll-chat');
    }

    public function openBlockModal(): void
    {
        $this->reset(['blockReason', 'blockDuration']);
        $this->blockDuration = 'indefinite';
        $this->showBlockModal = true;
    }

    public function closeBlockModal(): void
    {
        $this->showBlockModal = false;
        $this->reset(['blockReason', 'blockDuration']);
    }

    public function blockVisitor(): void
    {
        $this->validate([
            'blockReason' => ['required', 'string', 'max:500'],
            'blockDuration' => ['required', 'in:day,indefinite'],
        ]);

        $ip = $this->conversation->visitor_ip;

        if (!$ip) {
            session()->flash('status', 'Geen IP-adres beschikbaar voor deze bezoeker.');
            $this->closeBlockModal();
            return;
        }

        ChatBlock::create([
            'ip_address' => $ip,
            'visitor_uuid' => $this->conversation->visitor_uuid,
            'reason' => $this->blockReason,
            'blocked_by' => auth()->id(),
            'expires_at' => $this->blockDuration === 'day' ? now()->addDay() : null,
        ]);

        $this->closeBlockModal();
        session()->flash('status', 'Bezoeker geblokkeerd.');
    }

    public function unblockVisitor(): void
    {
        $ip = $this->conversation->visitor_ip;
        $uuid = $this->conversation->visitor_uuid;

        ChatBlock::active()
            ->where(function ($q) use ($ip, $uuid) {
                if ($ip) {
                    $q->where('ip_address', $ip);
                }
                if ($uuid) {
                    $q->orWhere('visitor_uuid', $uuid);
                }
            })
            ->delete();

        session()->flash('status', 'Bezoeker gedeblokkeerd.');
    }

    public function deleteConversation(): void
    {
        $this->conversation->delete();
        session()->flash('status', 'Conversation deleted.');
        $this->redirect(route('admin.chats'));
    }

    public function render()
    {
        $this->conversation->refresh();

        if ($this->conversation->unread_count > 0) {
            $this->conversation->update(['unread_count' => 0]);
        }

        $totalCount = $this->conversation->messages()->count();
        $messages = $this->conversation->messages()
            ->reorder()
            ->latest('id')
            ->take($this->limit)
            ->get()
            ->sortBy('id')
            ->values();

        if ($totalCount > $this->lastKnownCount && $this->lastKnownCount > 0) {
            $this->dispatch('new-message');
        }
        $this->lastKnownCount = $totalCount;
        $this->dispatch('scroll-chat');

        // Detect blocked visitor attempting to send
        $currentAttempt = $this->conversation->blocked_attempt_at?->toISOString();
        if ($currentAttempt && $currentAttempt !== $this->lastBlockedAttempt) {
            if ($this->lastBlockedAttempt !== null) {
                $this->dispatch('blocked-ping');
            }
            $this->lastBlockedAttempt = $currentAttempt;
        }

        $isBlocked = ChatBlock::isBlocked(
            $this->conversation->visitor_ip,
            $this->conversation->visitor_uuid
        );

        return view('livewire.admin.chat-view', [
            'messages' => $messages,
            'character' => $this->conversation->character,
            'hasMore' => $totalCount > $this->limit,
            'isBlocked' => $isBlocked,
        ])->layout('layouts.admin');
    }
}
