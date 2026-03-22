<?php

namespace App\Livewire\Admin;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Users extends Component
{
    public ?int $editingId = null;
    public string $editingName = '';
    public string $editingEmail = '';
    public bool $editingIsAdmin = false;

    // Block modal
    public bool $showBlockModal = false;
    public ?int $blockUserId = null;
    public string $blockType = 'account';
    public string $blockDuration = 'indefinite';
    public string $blockReason = '';

    // Badge management modal
    public bool $showBadgeModal = false;
    public ?int $badgeUserId = null;
    public ?int $assignBadgeId = null;
    public int $assignBadgeCount = 1;

    public bool $showCreateModal = false;
    public string $newName = '';
    public string $newEmail = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public bool $newIsAdmin = false;

    public function openCreate(): void
    {
        $this->reset(['newName', 'newEmail', 'newPassword', 'newPasswordConfirmation', 'newIsAdmin']);
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->showCreateModal = false;
        $this->reset(['newName', 'newEmail', 'newPassword', 'newPasswordConfirmation', 'newIsAdmin']);
    }

    public function create(): void
    {
        $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newEmail' => ['required', 'email', 'max:255', 'unique:users,email'],
            'newPassword' => ['required', 'string', 'min:8', 'same:newPasswordConfirmation'],
            'newPasswordConfirmation' => ['required'],
        ]);

        User::create([
            'name' => $this->newName,
            'email' => $this->newEmail,
            'password' => $this->newPassword,
            'is_admin' => $this->newIsAdmin,
        ]);

        $this->closeCreate();
        session()->flash('status', 'User created successfully.');
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->editingName = $user->name;
        $this->editingEmail = $user->email;
        $this->editingIsAdmin = $user->is_admin;
    }

    public function update(): void
    {
        $this->validate([
            'editingName' => ['required', 'string', 'max:255'],
            'editingEmail' => ['required', 'email', 'max:255', 'unique:users,email,' . $this->editingId],
        ]);

        $user = User::findOrFail($this->editingId);

        $data = [
            'name' => $this->editingName,
            'email' => $this->editingEmail,
        ];

        if ($user->id !== Auth::id()) {
            $data['is_admin'] = $this->editingIsAdmin;
        }

        $user->update($data);

        $this->cancelEdit();
        session()->flash('status', 'User updated successfully.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editingName', 'editingEmail', 'editingIsAdmin']);
    }

    public function toggleAdmin(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            session()->flash('status', 'You cannot change your own admin status.');
            return;
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $status = $user->is_admin ? 'User promoted to admin.' : 'Admin rights removed.';
        session()->flash('status', $status);
    }

    public function openBlockModal(int $userId, string $type = 'account'): void
    {
        $this->blockUserId = $userId;
        $this->blockType = $type;
        $this->blockDuration = 'indefinite';
        $this->blockReason = '';
        $this->showBlockModal = true;
    }

    public function closeBlockModal(): void
    {
        $this->showBlockModal = false;
        $this->reset(['blockUserId', 'blockType', 'blockDuration', 'blockReason']);
    }

    public function blockUser(): void
    {
        $this->validate([
            'blockReason' => ['required', 'string', 'max:500'],
            'blockDuration' => ['required', 'in:day,indefinite'],
            'blockType' => ['required', 'in:account,comment'],
        ]);

        $user = User::findOrFail($this->blockUserId);

        if ($user->id === Auth::id()) {
            session()->flash('status', 'You cannot block yourself.');
            $this->closeBlockModal();
            return;
        }

        $expiresAt = $this->blockDuration === 'day' ? now()->addDay() : null;

        $reason = strip_tags($this->blockReason);

        if ($this->blockType === 'account') {
            DB::transaction(function () use ($user, $expiresAt, $reason) {
                $user->update([
                    'is_blocked' => true,
                    'blocked_until' => $expiresAt,
                    'block_reason' => $reason,
                    'blocked_by' => Auth::id(),
                ]);
                // Revoke all API tokens
                $user->tokens()->delete();
                // Invalidate all sessions
                DB::table('sessions')->where('user_id', $user->id)->delete();
            });
            $label = $expiresAt ? 'Account blocked for 24 hours.' : 'Account permanently blocked.';
        } else {
            $user->update([
                'is_comment_blocked' => true,
                'comment_blocked_until' => $expiresAt,
                'comment_block_reason' => $reason,
                'comment_blocked_by' => Auth::id(),
            ]);
            $label = $expiresAt ? 'Comments blocked for 24 hours.' : 'Comments permanently blocked.';
        }

        $this->closeBlockModal();
        session()->flash('status', $label);
    }

    public function unblockAccount(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_blocked' => false,
            'blocked_until' => null,
            'block_reason' => null,
            'blocked_by' => null,
        ]);
        session()->flash('status', 'Account unblocked.');
    }

    public function unblockComments(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_comment_blocked' => false,
            'comment_blocked_until' => null,
            'comment_block_reason' => null,
            'comment_blocked_by' => null,
        ]);
        session()->flash('status', 'Comments unblocked.');
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            session()->flash('status', 'You cannot delete yourself.');
            return;
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->delete();

        session()->flash('status', 'User deleted successfully.');
    }

    public function openBadges(int $id): void
    {
        $this->badgeUserId = $id;
        $this->assignBadgeId = null;
        $this->assignBadgeCount = 1;
        $this->showBadgeModal = true;
    }

    public function closeBadges(): void
    {
        $this->showBadgeModal = false;
        $this->reset(['badgeUserId', 'assignBadgeId', 'assignBadgeCount']);
    }

    public function assignBadge(): void
    {
        $this->validate([
            'assignBadgeId' => ['required', 'exists:badges,id'],
            'assignBadgeCount' => ['required', 'integer', 'min:1'],
        ]);

        $user = User::findOrFail($this->badgeUserId);

        $existing = DB::table('badge_user')
            ->where('badge_id', $this->assignBadgeId)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            DB::table('badge_user')
                ->where('badge_id', $this->assignBadgeId)
                ->where('user_id', $user->id)
                ->update(['count' => $this->assignBadgeCount, 'updated_at' => now()]);
        } else {
            DB::table('badge_user')->insert([
                'badge_id' => $this->assignBadgeId,
                'user_id' => $user->id,
                'count' => $this->assignBadgeCount,
                'collected_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->assignBadgeId = null;
        $this->assignBadgeCount = 1;
        session()->flash('status', 'Badge assigned.');
    }

    public function updateBadgeCount(int $badgeId, int $count): void
    {
        if ($count < 0) $count = 0;

        DB::table('badge_user')
            ->where('badge_id', $badgeId)
            ->where('user_id', $this->badgeUserId)
            ->update(['count' => $count, 'updated_at' => now()]);
    }

    public function removeBadge(int $badgeId): void
    {
        DB::table('badge_user')
            ->where('badge_id', $badgeId)
            ->where('user_id', $this->badgeUserId)
            ->delete();

        session()->flash('status', 'Badge removed from user.');
    }

    public function render()
    {
        $data = [
            'users' => User::orderBy('name')->get(),
        ];

        if ($this->showBadgeModal && $this->badgeUserId) {
            $data['badgeUser'] = User::find($this->badgeUserId);
            $data['userBadges'] = DB::table('badge_user')
                ->join('badges', 'badges.id', '=', 'badge_user.badge_id')
                ->where('badge_user.user_id', $this->badgeUserId)
                ->whereNull('badges.deleted_at')
                ->select('badges.id', 'badges.title', 'badges.image_path', 'badge_user.count', 'badge_user.collected_at')
                ->get();
            $data['allBadges'] = Badge::orderBy('title')->get();
        }

        return view('livewire.admin.users', $data)->layout('layouts.admin');
    }
}
