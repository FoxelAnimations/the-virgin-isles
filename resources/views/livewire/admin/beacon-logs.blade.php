<div class="py-10">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS — Beacons</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">Beacon Logs</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.beacons') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    Back
                </a>
                <button wire:click="exportCsv"
                    class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    Export CSV
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <input type="text" wire:model.live.debounce.300ms="filterGuid" placeholder="GUID contains..."
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm w-48">

            <select wire:model.live="filterKnown" class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">All (known + unknown)</option>
                <option value="known">Known only</option>
                <option value="unknown">Unknown only</option>
            </select>

            <select wire:model.live="filterTypeId" class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">All Types</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus" class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">All Statuses</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="out_of_action">Out of Action</option>
            </select>

            <input type="date" wire:model.live="filterDateFrom" class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
            <span class="text-zinc-600">to</span>
            <input type="date" wire:model.live="filterDateTo" class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
        </div>

        {{-- Table --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            @if ($scans->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-800">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-zinc-500">
                                <th class="px-4 py-3 text-left">Timestamp</th>
                                <th class="px-4 py-3 text-left">GUID</th>
                                <th class="px-4 py-3 text-left">Beacon</th>
                                <th class="px-4 py-3 text-center">Known</th>
                                <th class="px-4 py-3 text-left">Hashed IP</th>
                                <th class="px-4 py-3 text-left">User Agent</th>
                                <th class="px-4 py-3 text-left">Referrer</th>
                                <th class="px-4 py-3 text-left">Redirect</th>
                                <th class="px-4 py-3 text-left">UTM</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            @foreach ($scans as $scan)
                                <tr class="hover:bg-zinc-800/50 transition text-sm">
                                    <td class="px-4 py-3 text-zinc-300 whitespace-nowrap">{{ $scan->scanned_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-4 py-3"><code class="text-xs text-zinc-500 font-mono">{{ Str::limit($scan->guid, 12) }}</code></td>
                                    <td class="px-4 py-3 text-zinc-300">
                                        @if ($scan->beacon)
                                            <a href="{{ route('admin.beacon-detail', $scan->beacon) }}" class="hover:text-accent transition">{{ $scan->beacon->title }}</a>
                                        @else
                                            <span class="text-zinc-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-sm {{ $scan->is_known ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-zinc-800 text-zinc-500 border border-zinc-700' }}">
                                            {{ $scan->is_known ? 'Known' : 'Unknown' }}
                                        </span>
                                        @if ($scan->rate_limited)
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-sm bg-orange-900/30 text-orange-400 border border-orange-800 ml-1">RL</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3"><code class="text-xs text-zinc-500 font-mono">{{ $scan->short_hashed_ip }}</code></td>
                                    <td class="px-4 py-3 text-zinc-400 max-w-[150px] truncate" title="{{ $scan->user_agent }}">{{ Str::limit($scan->user_agent, 30) }}</td>
                                    <td class="px-4 py-3 text-zinc-400 max-w-[120px] truncate" title="{{ $scan->referrer }}">{{ $scan->referrer ?: '—' }}</td>
                                    <td class="px-4 py-3 text-zinc-400 max-w-[120px] truncate" title="{{ $scan->redirect_url_used }}">{{ $scan->redirect_url_used }}</td>
                                    <td class="px-4 py-3 text-zinc-500 text-xs max-w-[120px] truncate">
                                        @if ($scan->utm_source)
                                            {{ $scan->utm_source }}
                                            @if ($scan->utm_medium) / {{ $scan->utm_medium }} @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button wire:click="deleteScan({{ $scan->id }})"
                                            wire:confirm="Delete this scan log entry?"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-red-900/30 text-red-400 border border-red-800 rounded-sm transition hover:bg-red-900/50">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-zinc-800">
                    {{ $scans->links() }}
                </div>
            @else
                <div class="p-8 text-center text-zinc-600">
                    No scan logs found.
                </div>
            @endif
        </div>
    </div>
</div>
