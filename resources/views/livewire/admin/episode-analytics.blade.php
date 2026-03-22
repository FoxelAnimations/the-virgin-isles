<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS — Episodes</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">Video Analytics</h1>
            </div>
            <a href="{{ route('admin.episodes') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                Back
            </a>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500 mb-1">Total Views</p>
                <p class="text-3xl font-bold text-accent">{{ number_format($totalViews) }}</p>
            </div>
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500 mb-1">Unique Viewers</p>
                <p class="text-3xl font-bold text-accent">{{ number_format($uniqueViewers) }}</p>
            </div>
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500 mb-1">Today</p>
                <p class="text-3xl font-bold text-accent">{{ number_format($viewsToday) }}</p>
            </div>
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500 mb-1">This Week</p>
                <p class="text-3xl font-bold text-accent">{{ number_format($viewsThisWeek) }}</p>
            </div>
        </div>

        {{-- Views Per Day Chart --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-400">Views Per Day</h2>
                <select wire:model.live="period" class="bg-zinc-800 border border-zinc-700 text-white px-3 py-1 text-sm focus:border-accent focus:ring-accent rounded-sm">
                    <option value="7">7 days</option>
                    <option value="30">30 days</option>
                    <option value="90">90 days</option>
                </select>
            </div>

            @php $maxDay = max(1, max(array_values($viewsPerDay))); @endphp

            <div class="flex items-end gap-[2px] h-40">
                @foreach ($viewsPerDay as $date => $count)
                    <div class="flex-1 group relative flex flex-col items-center justify-end h-full">
                        <div class="w-full bg-accent/80 rounded-t-sm transition-all hover:bg-accent"
                            style="height: {{ ($count / $maxDay) * 100 }}%"></div>
                        <div class="absolute bottom-full mb-1 hidden group-hover:block bg-zinc-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                            {{ $date }}: {{ $count }} views
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between mt-2 text-xs text-zinc-600">
                <span>{{ array_key_first($viewsPerDay) }}</span>
                <span>{{ array_key_last($viewsPerDay) }}</span>
            </div>
        </div>

        {{-- Top 10 Episodes --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden mb-8">
            <div class="px-4 py-4 border-b border-zinc-800">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-400">Top 10 Most Viewed</h2>
            </div>

            @if ($topEpisodes->isEmpty())
                <div class="p-8 text-center text-zinc-600">No views recorded yet.</div>
            @else
                <table class="min-w-full divide-y divide-zinc-800">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-zinc-500">
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Episode</th>
                            <th class="px-4 py-3 text-left">Category</th>
                            <th class="px-4 py-3 text-center">Views</th>
                            <th class="px-4 py-3 text-center">Unique</th>
                            <th class="px-4 py-3 text-center">Avg Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @foreach ($topEpisodes as $index => $ep)
                            <tr class="hover:bg-zinc-800/50 transition">
                                <td class="px-4 py-3 text-zinc-500 text-sm">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-white font-medium">{{ $ep->title }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold uppercase tracking-wider {{ $ep->category === 'episode' ? 'bg-blue-900/30 text-blue-400' : ($ep->category === 'short' ? 'bg-purple-900/30 text-purple-400' : ($ep->category === 'special' ? 'bg-amber-900/30 text-amber-400' : 'bg-emerald-900/30 text-emerald-400')) }}">
                                        {{ $ep->category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-accent font-semibold">{{ number_format($ep->views_count) }}</td>
                                <td class="px-4 py-3 text-center text-zinc-400">{{ number_format($ep->unique_viewers) }}</td>
                                <td class="px-4 py-3 text-center text-zinc-400">
                                    @if ($ep->ratings_avg_rating)
                                        {{ number_format($ep->ratings_avg_rating, 1) }} / 5
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Category Breakdown --}}
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-4">By Category</h2>
                @if ($viewsByCategory->isEmpty())
                    <p class="text-zinc-600 text-sm">No data yet.</p>
                @else
                    <div class="space-y-3">
                        @php $maxCat = max(1, $viewsByCategory->max('view_count')); @endphp
                        @foreach ($viewsByCategory as $cat)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-zinc-300 uppercase tracking-wider">{{ $cat->category }}</span>
                                    <span class="text-sm text-accent font-semibold">{{ number_format($cat->view_count) }}</span>
                                </div>
                                <div class="h-2 bg-zinc-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-accent/80 rounded-full" style="width: {{ ($cat->view_count / $maxCat) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Device Breakdown --}}
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-4">By Device</h2>
                @php $totalDevices = max(1, array_sum($devices)); @endphp
                <div class="space-y-3">
                    @foreach (['mobile' => 'Mobile', 'desktop' => 'Desktop', 'tablet' => 'Tablet'] as $key => $label)
                        @php $count = $devices[$key] ?? 0; @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-zinc-300">{{ $label }}</span>
                                <span class="text-sm text-zinc-400">{{ number_format($count) }} <span class="text-zinc-600">({{ $totalDevices > 0 ? round(($count / $totalDevices) * 100) : 0 }}%)</span></span>
                            </div>
                            <div class="h-2 bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-accent/80 rounded-full" style="width: {{ ($count / $totalDevices) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Peak Hours --}}
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-4">Peak Hours</h2>
                @php $maxHour = max(1, max($peakHours)); @endphp
                <div class="flex items-end gap-[2px] h-32">
                    @foreach ($peakHours as $hour => $count)
                        <div class="flex-1 group relative flex flex-col items-center justify-end h-full">
                            <div class="w-full bg-accent/80 rounded-t-sm transition-all hover:bg-accent"
                                style="height: {{ $count > 0 ? max(3, ($count / $maxHour) * 100) : 0 }}%"></div>
                            <div class="absolute bottom-full mb-1 hidden group-hover:block bg-zinc-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                                {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00 — {{ $count }} views
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between mt-2 text-xs text-zinc-600">
                    <span>00:00</span>
                    <span>12:00</span>
                    <span>23:00</span>
                </div>
            </div>
        </div>
    </div>
</div>
