<?php

namespace App\Livewire\Admin;

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

    public function toggleBlock(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            session()->flash('status', 'You cannot block yourself.');
            return;
        }

        $user->update(['is_blocked' => !$user->is_blocked]);

        if ($user->is_blocked) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $status = $user->is_blocked ? 'User blocked.' : 'User unblocked.';
        session()->flash('status', $status);
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

    public function render()
    {
        return view('livewire.admin.users', [
            'users' => User::orderBy('name')->get(),
        ])->layout('layouts.admin');
    }
}
