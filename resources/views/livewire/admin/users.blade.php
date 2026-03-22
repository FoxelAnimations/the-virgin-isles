<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Users') }}</h1>
            </div>
            <button wire:click="openCreate" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                {{ __('New User') }}
            </button>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="px-4 py-4 border-b border-zinc-800">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Management</p>
                <h2 class="text-lg font-semibold uppercase tracking-wider">{{ __('All Users') }}</h2>
            </div>

            @if($users->isEmpty())
                <div class="p-8 text-center text-zinc-600">{{ __('No users found.') }}</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-800">
                        <thead class="bg-zinc-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Email') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Role') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            @foreach($users as $user)
                                <tr class="hover:bg-zinc-800/50 transition" wire:key="user-{{ $user->id }}">
                                    @if($editingId === $user->id)
                                        <td class="px-6 py-3" colspan="5">
                                            <form wire:submit="update" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                                <div class="flex-1 w-full sm:w-auto">
                                                    <input type="text" wire:model="editingName" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-1.5 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="{{ __('Name') }}">
                                                    @error('editingName') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="flex-1 w-full sm:w-auto">
                                                    <input type="email" wire:model="editingEmail" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-1.5 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="{{ __('Email') }}">
                                                    @error('editingEmail') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                @if($user->id !== auth()->id())
                                                    <label class="flex items-center gap-2 shrink-0 cursor-pointer">
                                                        <input type="checkbox" wire:model="editingIsAdmin" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                                        <span class="text-xs font-semibold text-zinc-300">{{ __('Admin') }}</span>
                                                    </label>
                                                @endif
                                                <div class="flex gap-2 shrink-0">
                                                    <button type="submit" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold tracking-wider uppercase transition hover:brightness-90">{{ __('Save') }}</button>
                                                    <button type="button" wire:click="cancelEdit" class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:text-white transition">{{ __('Cancel') }}</button>
                                                </div>
                                            </form>
                                        </td>
                                    @else
                                        <td class="px-6 py-4 whitespace-nowrap font-semibold text-white">
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span class="text-xs text-accent ml-1">({{ __('you') }})</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-400">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($user->is_admin)
                                                <span class="text-accent">{{ __('Admin') }}</span>
                                            @else
                                                <span class="text-zinc-500">{{ __('User') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-y-1">
                                            @if($user->isAccountBlocked())
                                                <span class="text-red-400 block">{{ $user->accountBlockLabel() }}</span>
                                            @endif
                                            @if($user->isCommentBlocked())
                                                <span class="text-orange-400 block">{{ $user->commentBlockLabel() }}</span>
                                            @endif
                                            @if(!$user->isAccountBlocked() && !$user->isCommentBlocked())
                                                <span class="text-green-400">{{ __('Active') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button wire:click="openBadges({{ $user->id }})" class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-accent hover:text-accent transition">
                                                {{ __('Badges') }}
                                            </button>
                                            <button wire:click="edit({{ $user->id }})" class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-accent hover:text-accent transition">
                                                {{ __('Edit') }}
                                            </button>

                                            @if($user->id !== auth()->id())
                                                <button
                                                    wire:click="toggleAdmin({{ $user->id }})"
                                                    wire:confirm="{{ $user->is_admin ? __('Are you sure you want to remove admin rights from this user?') : __('Are you sure you want to make this user an admin?') }}"
                                                    class="inline-flex items-center border {{ $user->is_admin ? 'border-accent/50 text-accent hover:bg-accent/10' : 'border-blue-900 text-blue-400 hover:bg-blue-900/30' }} px-3 py-1.5 text-xs font-semibold transition"
                                                >
                                                    {{ $user->is_admin ? __('Remove Admin') : __('Make Admin') }}
                                                </button>

                                                @if($user->isAccountBlocked())
                                                    <button
                                                        wire:click="unblockAccount({{ $user->id }})"
                                                        wire:confirm="{{ __('Are you sure you want to unblock this account?') }}"
                                                        class="inline-flex items-center border border-green-900 text-green-400 hover:bg-green-900/30 px-3 py-1.5 text-xs font-semibold transition"
                                                    >
                                                        {{ __('Unblock Account') }}
                                                    </button>
                                                @endif
                                                @if($user->isCommentBlocked())
                                                    <button
                                                        wire:click="unblockComments({{ $user->id }})"
                                                        wire:confirm="{{ __('Are you sure you want to unblock comments for this user?') }}"
                                                        class="inline-flex items-center border border-green-900 text-green-400 hover:bg-green-900/30 px-3 py-1.5 text-xs font-semibold transition"
                                                    >
                                                        {{ __('Unblock Comments') }}
                                                    </button>
                                                @endif
                                                @if(!$user->isAccountBlocked())
                                                    <button
                                                        wire:click="openBlockModal({{ $user->id }}, 'account')"
                                                        class="inline-flex items-center border border-red-900 text-red-400 hover:bg-red-900/30 px-3 py-1.5 text-xs font-semibold transition"
                                                    >
                                                        {{ __('Block Account') }}
                                                    </button>
                                                @endif
                                                @if(!$user->isCommentBlocked())
                                                    <button
                                                        wire:click="openBlockModal({{ $user->id }}, 'comment')"
                                                        class="inline-flex items-center border border-orange-900 text-orange-400 hover:bg-orange-900/30 px-3 py-1.5 text-xs font-semibold transition"
                                                    >
                                                        {{ __('Block Comments') }}
                                                    </button>
                                                @endif

                                                <button
                                                    wire:click="delete({{ $user->id }})"
                                                    wire:confirm="{{ __('Are you sure you want to delete this user? This action cannot be undone.') }}"
                                                    class="inline-flex items-center border border-red-900 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-900/30 transition"
                                                >
                                                    {{ __('Delete') }}
                                                </button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Create User Modal --}}
    @if ($showCreateModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8"
            x-data
            x-init="document.body.classList.add('overflow-hidden')"
            @keydown.escape.window="$wire.closeCreate(); document.body.classList.remove('overflow-hidden')"
        >
            <div class="absolute inset-0" wire:click="closeCreate" x-on:click="document.body.classList.remove('overflow-hidden')"></div>

            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-lg" @click.stop>
                <div class="sticky top-0 z-10 bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>{{ __('New User') }}</span>
                    <button wire:click="closeCreate" x-on:click="document.body.classList.remove('overflow-hidden')" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5">
                    <form wire:submit="create" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Name') }} *</label>
                            <input type="text" wire:model="newName" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            @error('newName') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Email') }} *</label>
                            <input type="email" wire:model="newEmail" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            @error('newEmail') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Password') }} *</label>
                            <input type="password" wire:model="newPassword" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            @error('newPassword') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Confirm Password') }} *</label>
                            <input type="password" wire:model="newPasswordConfirmation" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="newIsAdmin" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                            <span class="text-sm font-medium text-white">{{ __('Admin') }}</span>
                        </label>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                                {{ __('Create User') }}
                            </button>
                            <button type="button" wire:click="closeCreate" x-on:click="document.body.classList.remove('overflow-hidden')" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Badge Management Modal --}}
    @if ($showBadgeModal && isset($badgeUser))
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8 overflow-y-auto"
            x-data
            @keydown.escape.window="$wire.closeBadges()"
        >
            <div class="absolute inset-0" wire:click="closeBadges"></div>

            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-lg my-8" @click.stop>
                <div class="sticky top-0 z-10 bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>Badges — {{ $badgeUser->name }}</span>
                    <button wire:click="closeBadges" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    {{-- Current badges --}}
                    @if (isset($userBadges) && $userBadges->isNotEmpty())
                        <div class="space-y-2">
                            <h3 class="text-xs uppercase tracking-wider text-zinc-500">Current Badges</h3>
                            @foreach ($userBadges as $ub)
                                <div class="flex items-center gap-3 bg-zinc-800 rounded-sm p-3">
                                    @if ($ub->image_path)
                                        <img src="{{ Storage::url($ub->image_path) }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                    @endif
                                    <span class="text-sm text-white font-medium flex-1">{{ $ub->title }}</span>
                                    <input type="number" min="0" value="{{ $ub->count }}"
                                        wire:change="updateBadgeCount({{ $ub->id }}, $event.target.value)"
                                        class="w-16 bg-zinc-700 border border-zinc-600 text-white px-2 py-1 text-sm text-center rounded-sm focus:border-accent focus:ring-accent">
                                    <button wire:click="removeBadge({{ $ub->id }})"
                                        wire:confirm="Remove this badge from user?"
                                        class="text-red-400 hover:text-red-300 transition text-xs font-semibold uppercase">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-zinc-600">No badges assigned yet.</p>
                    @endif

                    {{-- Assign new badge --}}
                    <div class="border-t border-zinc-800 pt-4">
                        <h3 class="text-xs uppercase tracking-wider text-zinc-500 mb-2">Assign Badge</h3>
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <select wire:model="assignBadgeId"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                    <option value="">Select badge...</option>
                                    @foreach ($allBadges as $badge)
                                        <option value="{{ $badge->id }}">{{ $badge->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-20">
                                <input type="number" wire:model="assignBadgeCount" min="1"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm text-center"
                                    placeholder="Count">
                            </div>
                            <button wire:click="assignBadge"
                                class="px-4 py-2 text-sm font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                                Assign
                            </button>
                        </div>
                        @error('assignBadgeId') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Block User Modal --}}
    @if ($showBlockModal && $blockUserId)
        @php $blockUser = \App\Models\User::find($blockUserId); @endphp
        @if ($blockUser)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8"
            x-data
            x-init="document.body.classList.add('overflow-hidden')"
            @keydown.escape.window="$wire.closeBlockModal(); document.body.classList.remove('overflow-hidden')"
        >
            <div class="absolute inset-0" wire:click="closeBlockModal" x-on:click="document.body.classList.remove('overflow-hidden')"></div>

            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-lg" @click.stop>
                <div class="sticky top-0 z-10 bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>{{ $blockType === 'account' ? __('Block Account') : __('Block Comments & Ratings') }}</span>
                    <button wire:click="closeBlockModal" x-on:click="document.body.classList.remove('overflow-hidden')" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <div class="bg-zinc-800 rounded-sm p-3">
                        <p class="text-sm text-zinc-400">{{ __('User') }}</p>
                        <p class="text-white font-semibold">{{ $blockUser->name }} <span class="text-zinc-500 font-normal">({{ $blockUser->email }})</span></p>
                    </div>

                    @if($blockType === 'account')
                        <p class="text-sm text-red-400">{{ __('This will log the user out and prevent them from logging in.') }}</p>
                    @else
                        <p class="text-sm text-orange-400">{{ __('This will prevent the user from posting comments, ratings, and chat messages.') }}</p>
                    @endif

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-2 uppercase tracking-wider">{{ __('Duration') }}</label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="blockDuration" value="day" class="border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                <span class="text-sm text-white">{{ __('24 uur') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="blockDuration" value="indefinite" class="border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                <span class="text-sm text-white">{{ __('Permanent') }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1 uppercase tracking-wider">{{ __('Reason') }} *</label>
                        <textarea wire:model="blockReason" rows="3" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="{{ __('Reden voor blokkering...') }}"></textarea>
                        @error('blockReason') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button
                            wire:click="blockUser"
                            wire:confirm="{{ __('Are you sure you want to block this user?') }}"
                            class="inline-flex items-center {{ $blockType === 'account' ? 'bg-red-600 hover:bg-red-700' : 'bg-orange-600 hover:bg-orange-700' }} text-white px-4 py-2 text-sm font-semibold tracking-wider uppercase transition"
                        >
                            {{ __('Blokkeren') }}
                        </button>
                        <button wire:click="closeBlockModal" x-on:click="document.body.classList.remove('overflow-hidden')" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                            {{ __('Annuleren') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
