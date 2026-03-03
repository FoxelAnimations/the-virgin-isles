<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS — Beacons</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">Beacon Analytics</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.beacons') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    Back
                </a>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500 mb-1">Last 7 days</p>
                <p class="text-3xl font-bold text-accent">{{ number_format($totalScans7) }}</p>
                <p class="text-sm text-zinc-500 mt-1">scans</p>
            </div>
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500 mb-1">Last 30 days</p>
                <p class="text-3xl font-bold text-accent">{{ number_format($totalScans30) }}</p>
                <p class="text-sm text-zinc-500 mt-1">scans</p>
            </div>
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500 mb-1">Last 90 days</p>
                <p class="text-3xl font-bold text-accent">{{ number_format($totalScans90) }}</p>
                <p class="text-sm text-zinc-500 mt-1">scans</p>
            </div>
        </div>

        {{-- Scans Per Day Chart --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-400">Scans Per Day</h3>
                <select wire:model.live="period" class="bg-zinc-800 border border-zinc-700 text-white px-3 py-1 text-sm focus:border-accent focus:ring-accent rounded-sm">
                    <option value="7">7 days</option>
                    <option value="30">30 days</option>
                    <option value="90">90 days</option>
                </select>
            </div>

            @php
                $maxCount = max(1, max(array_values($scansPerDay)));
            @endphp

            <div class="flex items-end gap-[2px] h-40">
                @foreach ($scansPerDay as $date => $count)
                    <div class="flex-1 group relative flex flex-col items-center justify-end h-full">
                        <div class="w-full bg-accent/80 rounded-t-sm transition-all hover:bg-accent"
                            style="height: {{ ($count / $maxCount) * 100 }}%"></div>
                        <div class="absolute bottom-full mb-1 hidden group-hover:block bg-zinc-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                            {{ $date }}: {{ $count }} scans
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between mt-2 text-xs text-zinc-600">
                <span>{{ array_key_first($scansPerDay) }}</span>
                <span>{{ array_key_last($scansPerDay) }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Top 10 Beacons --}}
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-4">Top 10 Beacons (30 days)</h3>
                @if ($topBeacons->isEmpty())
                    <p class="text-zinc-600 text-sm">No data yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($topBeacons as $item)
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    @if ($item->beacon)
                                        <a href="{{ route('admin.beacon-detail', $item->beacon) }}" class="text-sm text-white hover:text-accent transition truncate block">
                                            {{ $item->beacon->title }}
                                        </a>
                                    @else
                                        <span class="text-sm text-zinc-500">Deleted beacon</span>
                                    @endif
                                </div>
                                <span class="text-accent font-semibold text-sm ml-4">{{ $item->scan_count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Top Referrers --}}
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-4">Top Referrers (30 days)</h3>
                @if ($topReferrers->isEmpty())
                    <p class="text-zinc-600 text-sm">No referrer data yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($topReferrers as $ref)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-300 truncate flex-1 min-w-0" title="{{ $ref->referrer }}">{{ Str::limit($ref->referrer, 50) }}</span>
                                <span class="text-accent font-semibold text-sm ml-4">{{ $ref->count }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Device Breakdown --}}
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-4">Device Breakdown (30 days)</h3>
                @php $deviceTotal = max(1, array_sum($devices)); @endphp
                <div class="space-y-3">
                    @foreach ($devices as $device => $count)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-zinc-300 capitalize">{{ $device }}</span>
                                <span class="text-sm text-zinc-500">{{ $count }} ({{ round(($count / $deviceTotal) * 100) }}%)</span>
                            </div>
                            <div class="h-2 bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-accent rounded-full" style="width: {{ ($count / $deviceTotal) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Known vs Unknown --}}
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-4">Known vs Unknown (30 days)</h3>
                @php $knownTotal = max(1, $knownCount + $unknownCount); @endphp
                <div class="space-y-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-green-400">Known</span>
                            <span class="text-sm text-zinc-500">{{ $knownCount }} ({{ round(($knownCount / $knownTotal) * 100) }}%)</span>
                        </div>
                        <div class="h-2 bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" style="width: {{ ($knownCount / $knownTotal) * 100 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-zinc-400">Unknown</span>
                            <span class="text-sm text-zinc-500">{{ $unknownCount }} ({{ round(($unknownCount / $knownTotal) * 100) }}%)</span>
                        </div>
                        <div class="h-2 bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full bg-zinc-600 rounded-full" style="width: {{ ($unknownCount / $knownTotal) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
