<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected $rules = [
        'email' => ['required', 'email'],
        'password' => ['required'],
    ];

    public function authenticate(): void
    {
        $this->validate();

        $user = User::where('email', $this->email)->first();

        if (!$user || !Hash::check($this->password, $user->password)) {
            $this->addError('email', __('These credentials do not match our records.'));
            return;
        }

        if ($user->is_blocked) {
            $this->addError('email', __('Your account has been blocked.'));
            return;
        }

        if (!$user->is_admin) {
            $this->addError('email', __('Access denied.'));
            return;
        }

        Auth::login($user, $this->remember);
        session()->regenerate();

        $this->redirect(route('admin.dashboard'));
    }

    public function render()
    {
        return view('livewire.admin.login')
            ->layout('layouts.guest');
    }
}
