<?php

namespace App\Livewire\Admin;

use App\Models\ChatBlock;
use App\Models\ChatConversation;
use Livewire\Component;

class BlockedVisitors extends Component
{
    public function unblock(int $id): void
    {
        ChatBlock::findOrFail($id)->delete();
        session()->flash('status', 'Bezoeker gedeblokkeerd.');
    }

    public function deleteBlock(int $id): void
    {
        ChatBlock::findOrFail($id)->delete();
        session()->flash('status', 'Blokkering verwijderd.');
    }

    public function render()
    {
        $blocks = ChatBlock::with('blockedByUser')
            ->orderByDesc('created_at')
            ->get();

        $activeBlocks = $blocks->filter(fn ($b) => $b->isActive());
        $expiredBlocks = $blocks->filter(fn ($b) => !$b->isActive());

        // Find conversations for each block
        foreach ($activeBlocks as $block) {
            $block->conversations = ChatConversation::with('character')
                ->withCount('messages')
                ->where(function ($q) use ($block) {
                    if ($block->ip_address) {
                        $q->where('visitor_ip', $block->ip_address);
                    }
                    if ($block->visitor_uuid) {
                        $q->orWhere('visitor_uuid', $block->visitor_uuid);
                    }
                })
                ->orderByDesc('last_message_at')
                ->get();
        }

        return view('livewire.admin.blocked-visitors', [
            'activeBlocks' => $activeBlocks,
            'expiredBlocks' => $expiredBlocks,
        ])->layout('layouts.admin');
    }
}
