<div class="py-10">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.cameras') }}" class="text-zinc-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">Camera's</p>
                    <h1 class="text-3xl font-bold uppercase tracking-wider">Dagdeel Instellingen</h1>
                </div>
            </div>
            <button type="button" wire:click="save"
                class="inline-flex items-center bg-accent text-black px-6 py-2.5 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90"
                wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Opslaan</span>
                <span wire:loading wire:target="save">Opslaan...</span>
            </button>
        </div>

        {{-- Flash Message --}}
        @if (session('status'))
            <div class="mb-4 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Global error --}}
        @error('slots')
            <div class="mb-4 rounded-sm bg-red-900/20 border border-red-800/30 px-4 py-3 text-sm text-red-400">
                {{ $message }}
            </div>
        @enderror

        {{-- Settings Form --}}
        <form wire:submit="save" class="space-y-4">

            {{-- Weather Toggle --}}
            <div class="bg-zinc-900 border border-zinc-800 rounded-sm">
                <div class="px-5 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-accent">Weer effecten</h2>
                        <p class="text-xs text-zinc-500 mt-1">Toon wolken en regen op basis van het actuele weer in Kortrijk.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="weatherEnabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                    </label>
                </div>
            </div>

            {{-- Manual Weather Controls --}}
            <div class="bg-zinc-900 border border-zinc-800 rounded-sm" x-data="{ mode: @entangle('rainMode') }">
                <div class="px-5 py-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-accent mb-1">Weer besturing</h2>
                    <p class="text-xs text-zinc-500 mb-4">Kies of regen, wolken en wind automatisch worden bepaald door het weer in Kortrijk, of handmatig met onderstaande schuifregelaars.</p>

                    {{-- Auto / Manual toggle --}}
                    <div class="flex items-center gap-4 mb-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="rainMode" value="automatic" class="text-accent focus:ring-accent bg-zinc-800 border-zinc-600">
                            <span class="text-sm text-zinc-300">Automatisch</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="rainMode" value="manual" class="text-accent focus:ring-accent bg-zinc-800 border-zinc-600">
                            <span class="text-sm text-zinc-300">Handmatig</span>
                        </label>
                    </div>

                    {{-- Sliders --}}
                    <div class="space-y-4" :class="{ 'opacity-40 pointer-events-none': mode === 'automatic' }">
                        {{-- Rain intensity --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs text-zinc-500 uppercase tracking-wider">Regen intensiteit</label>
                                <span class="text-xs text-zinc-400 font-mono" x-text="$wire.manualRainIntensity + '%'"></span>
                            </div>
                            <input type="range" wire:model.live="manualRainIntensity" min="0" max="100" step="1"
                                class="w-full h-2 bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-blue-500"
                                :disabled="mode === 'automatic'">
                            <div class="flex justify-between text-[10px] text-zinc-600 mt-0.5">
                                <span>Geen</span>
                                <span>Licht</span>
                                <span>Matig</span>
                                <span>Zwaar</span>
                            </div>
                        </div>

                        {{-- Cloud cover --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs text-zinc-500 uppercase tracking-wider">Wolken dekking</label>
                                <span class="text-xs text-zinc-400 font-mono" x-text="$wire.manualCloudCover + '%'"></span>
                            </div>
                            <input type="range" wire:model.live="manualCloudCover" min="0" max="100" step="1"
                                class="w-full h-2 bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-gray-400"
                                :disabled="mode === 'automatic'">
                            <div class="flex justify-between text-[10px] text-zinc-600 mt-0.5">
                                <span>Helder</span>
                                <span>Half bewolkt</span>
                                <span>Volledig</span>
                            </div>
                        </div>

                        {{-- Wind speed --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs text-zinc-500 uppercase tracking-wider">Wind snelheid</label>
                                <span class="text-xs text-zinc-400 font-mono" x-text="$wire.manualWindSpeed + '%'"></span>
                            </div>
                            <input type="range" wire:model.live="manualWindSpeed" min="0" max="100" step="1"
                                class="w-full h-2 bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-teal-500"
                                :disabled="mode === 'automatic'">
                            <div class="flex justify-between text-[10px] text-zinc-600 mt-0.5">
                                <span>Windstil</span>
                                <span>Lichte bries</span>
                                <span>Storm</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-zinc-900 border border-zinc-800 rounded-sm">
                <div class="px-5 py-3 border-b border-zinc-800">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-accent">Dagdelen</h2>
                    <p class="text-xs text-zinc-500 mt-1">Pas de eindtijden aan — starttijden worden automatisch overgenomen van het vorige dagdeel.</p>
                </div>

                <div class="divide-y divide-zinc-800">
                    @foreach ($slots as $index => $slot)
                        <div class="p-5">
                            {{-- Row 1: Label, Start, End --}}
                            <div class="grid grid-cols-3 gap-4">
                                {{-- Label --}}
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wider">Label</label>
                                    <input type="text" wire:model="slots.{{ $index }}.label"
                                        class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm rounded-sm focus:border-accent focus:ring-accent">
                                    @error("slots.{$index}.label") <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Start Time (auto-derived from previous slot) --}}
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wider">
                                        Starttijd <span class="text-zinc-600 normal-case">(auto)</span>
                                    </label>
                                    <div class="w-full bg-zinc-800/50 border border-zinc-700/50 text-zinc-400 px-3 py-2 text-sm rounded-sm flex items-center gap-2">
                                        <svg class="w-3 h-3 text-zinc-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        {{ $slot['start_time'] }}
                                    </div>
                                    <p class="text-[10px] text-zinc-600 mt-1">
                                        = eindtijd van {{ $slots[($index - 1 + count($slots)) % count($slots)]['label'] }}
                                    </p>
                                </div>

                                {{-- End Time (always editable) --}}
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wider">Eindtijd</label>
                                    <input type="text" wire:model.live="slots.{{ $index }}.end_time"
                                        class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm rounded-sm focus:border-accent focus:ring-accent font-mono"
                                        placeholder="HH:MM" maxlength="5">
                                    @error("slots.{$index}.end_time") <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Row 2: Colors + Transition --}}
                            <div class="grid grid-cols-3 gap-4 mt-3">
                                {{-- Background Color --}}
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wider">Achtergrondkleur</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" wire:model.live="slots.{{ $index }}.bg_color"
                                            class="h-9 w-12 bg-zinc-800 border border-zinc-700 rounded-sm cursor-pointer p-0.5">
                                        <input type="text" wire:model.live="slots.{{ $index }}.bg_color"
                                            class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm rounded-sm focus:border-accent focus:ring-accent font-mono"
                                            placeholder="#000000" maxlength="7">
                                    </div>
                                    @error("slots.{$index}.bg_color") <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Overlay Color --}}
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wider">Overlay kleur</label>
                                    <div class="flex items-center gap-2"
                                        x-data="{ overlayRgb: '{{ substr($slot['overlay_color'], 0, 7) }}' }">
                                        <input type="color"
                                            x-model="overlayRgb"
                                            x-on:input="
                                                let alpha = $wire.slots[{{ $index }}].overlay_color.length > 7
                                                    ? $wire.slots[{{ $index }}].overlay_color.slice(7)
                                                    : 'FF';
                                                $wire.set('slots.{{ $index }}.overlay_color', $event.target.value + alpha)
                                            "
                                            class="h-9 w-12 bg-zinc-800 border border-zinc-700 rounded-sm cursor-pointer p-0.5">
                                        <input type="text" wire:model.live="slots.{{ $index }}.overlay_color"
                                            class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm rounded-sm focus:border-accent focus:ring-accent font-mono"
                                            placeholder="#RRGGBBAA" maxlength="9">
                                    </div>
                                    <p class="text-[10px] text-zinc-600 mt-1">Laatste 2 tekens = transparantie (00=onzichtbaar, FF=volledig)</p>
                                    @error("slots.{$index}.overlay_color") <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Cloud Color --}}
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1 uppercase tracking-wider">Wolkenkleur</label>
                                    <div class="flex items-center gap-2"
                                        x-data="{ cloudRgb: '{{ substr($slot['cloud_color'], 0, 7) }}' }">
                                        <input type="color"
                                            x-model="cloudRgb"
                                            x-on:input="
                                                let alpha = $wire.slots[{{ $index }}].cloud_color.length > 7
                                                    ? $wire.slots[{{ $index }}].cloud_color.slice(7)
                                                    : 'FF';
                                                $wire.set('slots.{{ $index }}.cloud_color', $event.target.value + alpha)
                                            "
                                            class="h-9 w-12 bg-zinc-800 border border-zinc-700 rounded-sm cursor-pointer p-0.5">
                                        <input type="text" wire:model.live="slots.{{ $index }}.cloud_color"
                                            class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm rounded-sm focus:border-accent focus:ring-accent font-mono"
                                            placeholder="#RRGGBBAA" maxlength="9">
                                    </div>
                                    <p class="text-[10px] text-zinc-600 mt-1">Kleur van de wolken in dit dagdeel.</p>
                                    @error("slots.{$index}.cloud_color") <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Row 3: Transition toggle --}}
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-zinc-800/50">
                                <div>
                                    <span class="text-xs text-zinc-500 uppercase tracking-wider">Overgang</span>
                                    <p class="text-[10px] text-zinc-600 mt-0.5">
                                        @if ($slot['is_transition'] ?? false)
                                            Kleur verloopt geleidelijk van vorig naar volgend dagdeel.
                                        @else
                                            Kleur blijft vast gedurende dit dagdeel.
                                        @endif
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="slots.{{ $index }}.is_transition" class="sr-only peer">
                                    <div class="w-11 h-6 bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                </label>
                            </div>

                            {{-- Row 4: Weather audio toggles --}}
                            <div class="grid grid-cols-2 gap-4 mt-3 pt-3 border-t border-zinc-800/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-xs text-zinc-500 uppercase tracking-wider">Regen geluid</span>
                                        <p class="text-[10px] text-zinc-600 mt-0.5">Regengeluid afspelen tijdens dit dagdeel.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer ml-3">
                                        <input type="checkbox" wire:model.live="slots.{{ $index }}.rain_enabled" class="sr-only peer">
                                        <div class="w-11 h-6 bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-xs text-zinc-500 uppercase tracking-wider">Wind geluid</span>
                                        <p class="text-[10px] text-zinc-600 mt-0.5">Windgeluid afspelen tijdens dit dagdeel.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer ml-3">
                                        <input type="checkbox" wire:model.live="slots.{{ $index }}.wind_enabled" class="sr-only peer">
                                        <div class="w-11 h-6 bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-500"></div>
                                    </label>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Visual Timeline Preview --}}
            <div class="bg-zinc-900 border border-zinc-800 rounded-sm">
                <div class="px-5 py-3 border-b border-zinc-800">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-accent">Preview (24 uur)</h2>
                </div>
                <div class="p-5 space-y-2">
                    {{-- Background color bar --}}
                    <p class="text-[10px] text-zinc-500 uppercase tracking-wider">Achtergrond</p>
                    <div class="flex h-10 rounded-sm overflow-hidden">
                        @php
                            // Build a linear 00:00→24:00 timeline from the circular slots
                            $timelineSlots = [];
                            foreach ($slots as $slot) {
                                $startParts = explode(':', $slot['start_time']);
                                $endParts = explode(':', $slot['end_time']);
                                $startMin = (int)$startParts[0] * 60 + (int)($startParts[1] ?? 0);
                                $endMin = $slot['end_time'] === '24:00' ? 1440 : (int)$endParts[0] * 60 + (int)($endParts[1] ?? 0);

                                if ($endMin > $startMin) {
                                    // Normal slot
                                    $timelineSlots[] = ['start' => $startMin, 'end' => $endMin, 'slot' => $slot];
                                } else {
                                    // Wrapping slot: split into two parts
                                    if ($startMin < 1440) {
                                        $timelineSlots[] = ['start' => $startMin, 'end' => 1440, 'slot' => $slot];
                                    }
                                    if ($endMin > 0) {
                                        $timelineSlots[] = ['start' => 0, 'end' => $endMin, 'slot' => $slot];
                                    }
                                }
                            }
                            usort($timelineSlots, fn($a, $b) => $a['start'] <=> $b['start']);
                        @endphp
                        @foreach ($timelineSlots as $ts)
                            @php $widthPercent = (($ts['end'] - $ts['start']) / 1440) * 100; @endphp
                            <div class="flex items-center justify-center relative"
                                style="width: {{ $widthPercent }}%; background-color: {{ $ts['slot']['bg_color'] ?? '#333' }};"
                                title="{{ $ts['slot']['label'] }}: {{ $ts['slot']['start_time'] }} - {{ $ts['slot']['end_time'] }}{{ ($ts['slot']['is_transition'] ?? false) ? ' (overgang)' : '' }}">
                                @if ($widthPercent > 5)
                                    <span class="text-[10px] font-semibold text-white/80 uppercase tracking-wider truncate px-1 drop-shadow-sm">
                                        {{ $ts['slot']['label'] }}
                                        @if ($ts['slot']['is_transition'] ?? false)
                                            <span class="text-white/50 normal-case">~</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Overlay color bar --}}
                    <p class="text-[10px] text-zinc-500 uppercase tracking-wider mt-3">Overlay</p>
                    <div class="flex h-10 rounded-sm overflow-hidden bg-zinc-700">
                        @foreach ($timelineSlots as $ts)
                            @php
                                $widthPercent = (($ts['end'] - $ts['start']) / 1440) * 100;
                                $oc = $ts['slot']['overlay_color'] ?? '#00000000';
                                $ocRgb = substr($oc, 0, 7);
                                $ocAlpha = strlen($oc) > 7 ? hexdec(substr($oc, 7, 2)) / 255 : 1;
                                $r = hexdec(substr($ocRgb, 1, 2));
                                $g = hexdec(substr($ocRgb, 3, 2));
                                $b = hexdec(substr($ocRgb, 5, 2));
                            @endphp
                            <div class="flex items-center justify-center relative"
                                style="width: {{ $widthPercent }}%; background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, {{ round($ocAlpha, 3) }});"
                                title="{{ $ts['slot']['label'] }} overlay: {{ $oc }}">
                                @if ($widthPercent > 5)
                                    <span class="text-[10px] font-semibold text-white/60 uppercase tracking-wider truncate px-1 drop-shadow-sm">
                                        {{ $ts['slot']['label'] }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Cloud color bar --}}
                    <p class="text-[10px] text-zinc-500 uppercase tracking-wider mt-3">Wolken</p>
                    <div class="flex h-10 rounded-sm overflow-hidden bg-zinc-700">
                        @foreach ($timelineSlots as $ts)
                            @php
                                $widthPercent = (($ts['end'] - $ts['start']) / 1440) * 100;
                                $cc = $ts['slot']['cloud_color'] ?? '#FFFFFF66';
                                $ccRgb = substr($cc, 0, 7);
                                $ccAlpha = strlen($cc) > 7 ? hexdec(substr($cc, 7, 2)) / 255 : 1;
                                $cr = hexdec(substr($ccRgb, 1, 2));
                                $cg = hexdec(substr($ccRgb, 3, 2));
                                $cb = hexdec(substr($ccRgb, 5, 2));
                            @endphp
                            <div class="flex items-center justify-center relative"
                                style="width: {{ $widthPercent }}%; background-color: rgba({{ $cr }}, {{ $cg }}, {{ $cb }}, {{ round($ccAlpha, 3) }});"
                                title="{{ $ts['slot']['label'] }} wolken: {{ $cc }}">
                                @if ($widthPercent > 5)
                                    <span class="text-[10px] font-semibold text-white/60 uppercase tracking-wider truncate px-1 drop-shadow-sm">
                                        {{ $ts['slot']['label'] }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Hour markers --}}
                    <div class="flex justify-between mt-1">
                        @for ($h = 0; $h <= 24; $h += 6)
                            <span class="text-[10px] text-zinc-600">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</span>
                        @endfor
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
