<div class="py-10">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.cameras') }}" class="text-zinc-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">Bewerken</p>
                    <div class="flex items-center gap-2" x-data="{ editing: false, name: @js($camera->name) }">
                        <h1 x-show="!editing" class="text-3xl font-bold uppercase tracking-wider" x-text="name" @click="editing = true; $nextTick(() => $refs.nameInput.focus())"></h1>
                        <input x-show="editing" x-ref="nameInput" x-model="name" type="text"
                            class="text-3xl font-bold uppercase tracking-wider bg-transparent border-b-2 border-accent text-white outline-none py-0 px-0"
                            @keydown.enter="editing = false; $wire.updateName(name)"
                            @keydown.escape="editing = false; name = @js($camera->name)"
                            @click.away="editing = false; $wire.updateName(name)"
                            style="display: none;"
                        >
                        <button x-show="!editing" @click="editing = true; $nextTick(() => $refs.nameInput.focus())" class="text-zinc-600 hover:text-zinc-400 transition" title="Naam bewerken">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <label class="text-xs text-zinc-500 uppercase tracking-wider">Snap:</label>
                <select wire:model.live="snapMinutes" class="bg-zinc-800 border border-zinc-700 text-white text-sm px-2 py-1 rounded-sm focus:border-accent focus:ring-accent">
                    <option value="5">5 min</option>
                    <option value="15">15 min</option>
                    <option value="30">30 min</option>
                    <option value="60">60 min</option>
                </select>
            </div>
        </div>

        {{-- Flash Message --}}
        @if (session('status'))
            <div class="mb-4 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Time-of-Day Color Bar --}}
        <div class="mb-4 bg-zinc-900 border border-zinc-800 rounded-sm"
            x-data="{
                nowPercent: 0,
                nowLabel: '',
                init() {
                    this.tick();
                    setInterval(() => this.tick(), 30000);
                },
                tick() {
                    const now = new Date();
                    const minutes = now.getHours() * 60 + now.getMinutes();
                    this.nowPercent = (minutes / 1440) * 100;
                    this.nowLabel = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                }
            }"
        >
            <div class="px-4 py-2 border-b border-zinc-800 flex items-center justify-between">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-accent">Huidige tijd</h3>
                <span class="text-xs font-mono text-white" x-text="nowLabel"></span>
            </div>
            <div class="p-4">
                <div class="relative">
                    {{-- Color bar --}}
                    <div class="flex h-8 rounded-sm overflow-hidden">
                        @php
                            $slots = \App\Models\CameraSlotSetting::getSlots();
                            $timelineSlots = [];
                            foreach ($slots as $key => $slot) {
                                $startParts = explode(':', $slot['start']);
                                $endParts = explode(':', $slot['end']);
                                $startMin = (int)$startParts[0] * 60 + (int)($startParts[1] ?? 0);
                                $endMin = $slot['end'] === '24:00' ? 1440 : (int)$endParts[0] * 60 + (int)($endParts[1] ?? 0);

                                if ($endMin > $startMin) {
                                    $timelineSlots[] = ['start' => $startMin, 'end' => $endMin, 'slot' => $slot, 'key' => $key];
                                } else {
                                    if ($startMin < 1440) {
                                        $timelineSlots[] = ['start' => $startMin, 'end' => 1440, 'slot' => $slot, 'key' => $key];
                                    }
                                    if ($endMin > 0) {
                                        $timelineSlots[] = ['start' => 0, 'end' => $endMin, 'slot' => $slot, 'key' => $key];
                                    }
                                }
                            }
                            usort($timelineSlots, fn($a, $b) => $a['start'] <=> $b['start']);
                        @endphp
                        @foreach ($timelineSlots as $ts)
                            @php $widthPercent = (($ts['end'] - $ts['start']) / 1440) * 100; @endphp
                            <div class="flex items-center justify-center relative"
                                style="width: {{ $widthPercent }}%; background-color: {{ $ts['slot']['bg_color'] ?? '#333' }};"
                                title="{{ $ts['slot']['label'] }}: {{ $ts['slot']['start'] }} – {{ $ts['slot']['end'] }}">
                                @if ($widthPercent > 8)
                                    <span class="text-[10px] font-semibold text-white/80 uppercase tracking-wider truncate px-1 drop-shadow-[0_1px_2px_rgba(0,0,0,0.8)]">
                                        {{ $ts['slot']['label'] }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Current time indicator --}}
                    <div class="absolute top-0 bottom-0 pointer-events-none" :style="'left: ' + nowPercent + '%'">
                        <div class="absolute -top-1.5 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[5px] border-r-[5px] border-t-[6px] border-l-transparent border-r-transparent" style="border-top-color: #E7FF57;"></div>
                        <div class="absolute top-0 bottom-0 left-1/2 -translate-x-[0.5px] w-[1px]" style="background-color: #E7FF57;"></div>
                        <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[5px] border-r-[5px] border-b-[6px] border-l-transparent border-r-transparent" style="border-bottom-color: #E7FF57;"></div>
                    </div>
                </div>

                {{-- Hour markers --}}
                <div class="flex justify-between mt-1">
                    @for ($h = 0; $h <= 24; $h += 3)
                        <span class="text-[9px] text-zinc-600 font-mono">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</span>
                    @endfor
                </div>
            </div>
        </div>

        <div class="flex gap-6">
            {{-- LEFT SIDEBAR --}}
            <div class="w-64 shrink-0 space-y-4">

                {{-- Background --}}
                <div class="bg-zinc-900 border border-zinc-800 rounded-sm">
                    <div class="px-4 py-3 border-b border-zinc-800">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-accent">Achtergrond</h3>
                    </div>
                    <div class="p-3">
                        @if ($camera->background_path)
                            <div class="mb-3">
                                @if ($camera->backgroundIsVideo())
                                    <video src="{{ $camera->backgroundUrl() }}"
                                           class="w-full aspect-video object-cover rounded-sm bg-black"
                                           autoplay loop muted playsinline></video>
                                @else
                                    <img src="{{ $camera->backgroundUrl() }}"
                                         class="w-full aspect-video object-cover rounded-sm bg-black"
                                         alt="Achtergrond">
                                @endif
                                <button wire:click="removeBackground"
                                        wire:confirm="Achtergrond verwijderen?"
                                        class="mt-2 w-full bg-red-900/30 text-red-400 border border-red-800 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider transition hover:bg-red-900/50 rounded-sm">
                                    Verwijderen
                                </button>
                            </div>
                        @else
                            <p class="text-xs text-zinc-600 mb-2">Geen achtergrond ingesteld.</p>
                        @endif

                        <input type="file" wire:model="backgroundUpload"
                               accept="image/jpeg,image/png,image/gif,video/webm"
                               class="block w-full text-xs text-zinc-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
                        <div wire:loading wire:target="backgroundUpload" class="text-xs text-zinc-500 mt-1">Uploaden...</div>
                        @error('backgroundUpload') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        @if ($backgroundUpload)
                            <button wire:click="uploadBackground"
                                    class="mt-2 w-full bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider transition hover:brightness-90">
                                Upload
                            </button>
                        @endif
                        <p class="text-[10px] text-zinc-600 mt-2">JPG, PNG, GIF of WebM. WebM met alpha-transparantie toont de luchtkleur erdoorheen.</p>
                    </div>
                </div>

                {{-- Video Library --}}
                <div class="bg-zinc-900 border border-zinc-800 rounded-sm">
                    <div class="px-4 py-3 border-b border-zinc-800">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-accent">Videobibliotheek</h3>
                    </div>

                    {{-- Upload --}}
                    <div class="p-3 border-b border-zinc-800">
                        <input type="file" wire:model="videoUpload" accept="video/mp4,video/webm,video/quicktime"
                            class="block w-full text-xs text-zinc-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
                        <div wire:loading wire:target="videoUpload" class="text-xs text-zinc-500 mt-1">Uploaden...</div>
                        @error('videoUpload') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        @if ($videoUpload)
                            <button wire:click="uploadVideo" class="mt-2 w-full bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider transition hover:brightness-90">
                                Upload
                            </button>
                        @endif
                    </div>

                    {{-- Video list --}}
                    <div class="p-2 space-y-1 max-h-[40vh] overflow-y-auto">
                        @forelse ($videos as $video)
                            <div class="bg-zinc-800 rounded-sm group"
                                draggable="true"
                                ondragstart="event.dataTransfer.setData('video_id', '{{ $video->id }}'); event.dataTransfer.setData('video_name', '{{ $video->filename }}')"
                            >
                                <div class="flex items-center justify-between px-2 py-1.5">
                                    <span class="text-xs text-white truncate flex-1 cursor-grab" title="{{ $video->filename }}">{{ $video->filename }}</span>
                                    <button wire:click="deleteVideo({{ $video->id }})"
                                        wire:confirm="Weet je zeker dat je deze video wilt verwijderen?"
                                        class="ml-2 text-zinc-600 hover:text-red-400 transition opacity-0 group-hover:opacity-100 shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                {{-- Audio --}}
                                <div class="px-2 pb-1.5">
                                    @if ($video->audio_path)
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-accent shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M11 5L6 9H2v6h4l5 4V5z"/></svg>
                                            <span class="text-[10px] text-zinc-400 truncate flex-1">Audio gekoppeld</span>
                                            <button wire:click="removeAudio({{ $video->id }})" wire:confirm="Audio verwijderen?"
                                                class="text-zinc-600 hover:text-red-400 transition shrink-0">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @else
                                        <div>
                                            <input type="file" wire:model="audioUploads.{{ $video->id }}" accept="audio/mpeg,audio/wav,audio/ogg,audio/aac,audio/mp4"
                                                class="block w-full text-[10px] text-zinc-500 file:mr-1 file:py-0.5 file:px-2 file:rounded-sm file:border-0 file:text-[10px] file:font-semibold file:bg-zinc-700 file:text-zinc-300 hover:file:bg-zinc-600">
                                            <div wire:loading wire:target="audioUploads.{{ $video->id }}" class="text-[10px] text-zinc-500 mt-0.5">Uploaden...</div>
                                            @error("audioUploads.{$video->id}") <span class="text-red-400 text-[10px]">{{ $message }}</span> @enderror
                                            @if (isset($audioUploads[$video->id]) && $audioUploads[$video->id])
                                                <button wire:click="uploadAudio({{ $video->id }})" class="mt-1 w-full bg-zinc-700 text-white px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider transition hover:bg-zinc-600 rounded-sm">
                                                    Audio koppelen
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-600 text-center py-4">Nog geen video's</p>
                        @endforelse
                    </div>
                </div>

                {{-- Default Videos --}}
                <div class="bg-zinc-900 border border-zinc-800 rounded-sm">
                    <div class="px-4 py-3 border-b border-zinc-800">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-accent">Standaard video's</h3>
                    </div>
                    <div class="p-3">
                        @foreach (\App\Models\CameraDefaultBlock::slots() as $slot => $bounds)
                            <div class="mb-2">
                                <label class="block text-xs text-zinc-500 mb-0.5">{{ $bounds['label'] }} ({{ $bounds['start'] }}–{{ $bounds['end'] === '24:00' ? '00:00' : $bounds['end'] }})</label>
                                <select wire:model="defaultSelections.{{ $slot }}"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white text-xs px-2 py-1.5 rounded-sm focus:border-accent focus:ring-accent">
                                    <option value="">— Geen video —</option>
                                    @foreach ($videos as $video)
                                        <option value="{{ $video->id }}">{{ $video->filename }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach

                        <button wire:click="saveDefaults"
                            class="mt-3 w-full bg-accent text-black px-3 py-2 text-xs font-semibold uppercase tracking-wider transition hover:brightness-90">
                            Opslaan
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT: WEEKLY CALENDAR GRID --}}
            <div class="flex-1 overflow-x-auto" wire:ignore
                x-data="cameraPlanner({{ Js::from($scheduleData) }}, {{ $snapMinutes }})"
            >
                {{-- Day Headers --}}
                <div class="flex border-b border-zinc-800 sticky top-0 z-20 bg-black">
                    <div class="w-14 shrink-0"></div>
                    @foreach (\App\Models\CameraDefaultBlock::DAY_LABELS as $i => $label)
                        <div class="flex-1 text-center py-2 text-xs font-semibold uppercase tracking-wider {{ $i < 6 ? 'border-r border-zinc-800' : '' }} text-zinc-400">
                            {{ $label }}
                        </div>
                    @endforeach
                </div>

                {{-- Calendar Body --}}
                <div class="flex relative" style="height: 1440px;">
                    {{-- Current time line across all columns --}}
                    <div class="absolute left-14 right-0 z-30 pointer-events-none flex items-center"
                        :style="'top: ' + nowMinutes + 'px'">
                        <div class="w-2 h-2 rounded-full -ml-1 shrink-0" style="background-color: #E7FF57;"></div>
                        <div class="flex-1 h-[1px]" style="background-color: #E7FF57; opacity: 0.7;"></div>
                    </div>

                    {{-- Time Labels --}}
                    <div class="w-14 shrink-0 relative">
                        @for ($h = 0; $h < 24; $h++)
                            <div class="absolute left-0 right-0 text-right pr-2 text-[10px] text-zinc-600" style="top: {{ $h * 60 }}px; transform: translateY(-6px);">
                                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
                            </div>
                        @endfor
                    </div>

                    {{-- Day Columns --}}
                    @for ($day = 0; $day < 7; $day++)
                        <div class="flex-1 relative {{ $day < 6 ? 'border-r border-zinc-800' : '' }}"
                            data-day-col="{{ $day }}"
                            @dragover.prevent="handleDragOver($event, {{ $day }})"
                            @dragleave.prevent="dropPreview = null"
                            @drop.prevent="handleDrop($event, {{ $day }})"
                        >
                            {{-- Hour lines --}}
                            @for ($h = 0; $h < 24; $h++)
                                <div class="absolute left-0 right-0 border-t border-zinc-800/50" style="top: {{ $h * 60 }}px;"></div>
                            @endfor

                            {{-- Default block backgrounds --}}
                            @foreach (\App\Models\CameraDefaultBlock::slots() as $slot => $bounds)
                                @php
                                    $startMin = intval(substr($bounds['start'], 0, 2)) * 60 + intval(substr($bounds['start'], 3, 2));
                                    $endMin = $bounds['end'] === '24:00' ? 1440 : intval(substr($bounds['end'], 0, 2)) * 60 + intval(substr($bounds['end'], 3, 2));
                                    $colors = [
                                        'nacht' => 'bg-indigo-900/20 border-indigo-800/30',
                                        'ochtend' => 'bg-amber-900/20 border-amber-800/30',
                                        'dag' => 'bg-yellow-900/15 border-yellow-800/30',
                                        'avond' => 'bg-purple-900/20 border-purple-800/30',
                                    ];
                                    $wraps = $endMin <= $startMin;
                                @endphp
                                @if ($wraps)
                                    {{-- Wrapping slot: render two blocks --}}
                                    @if ($startMin < 1440)
                                        <div class="absolute left-0 right-0 {{ $colors[$slot] ?? '' }} border-t border-b flex items-start justify-center pt-1"
                                            style="top: {{ $startMin }}px; height: {{ 1440 - $startMin }}px;">
                                            <span class="text-[9px] uppercase tracking-wider text-zinc-600 font-semibold">{{ $bounds['label'] }}</span>
                                        </div>
                                    @endif
                                    @if ($endMin > 0)
                                        <div class="absolute left-0 right-0 {{ $colors[$slot] ?? '' }} border-t border-b flex items-start justify-center pt-1"
                                            style="top: 0px; height: {{ $endMin }}px;">
                                            <span class="text-[9px] uppercase tracking-wider text-zinc-600 font-semibold">{{ $bounds['label'] }}</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="absolute left-0 right-0 {{ $colors[$slot] ?? '' }} border-t border-b flex items-start justify-center pt-1"
                                        style="top: {{ $startMin }}px; height: {{ $endMin - $startMin }}px;">
                                        <span class="text-[9px] uppercase tracking-wider text-zinc-600 font-semibold">{{ $bounds['label'] }}</span>
                                    </div>
                                @endif
                            @endforeach

                            {{-- Scheduled video blocks (event delegation via init, no Alpine event bindings here) --}}
                            <template x-for="block in getBlocksForDay({{ $day }})" :key="block.id">
                                <div
                                    class="absolute left-1 right-1 bg-accent/80 rounded-sm cursor-pointer z-10 group overflow-hidden select-none"
                                    :data-block-id="block.id"
                                    :style="'top: ' + timeToPixels(block.start_time) + 'px; height: ' + Math.max(20, timeToPixels(block.end_time) - timeToPixels(block.start_time)) + 'px;'"
                                >
                                    <div class="px-1.5 py-0.5 text-[10px] font-semibold text-black truncate" x-text="block.video_name || 'Video'"></div>
                                    <div class="px-1.5 text-[9px] text-black/70" x-text="block.start_time + ' – ' + block.end_time"></div>

                                    {{-- Resize handle --}}
                                    <div class="absolute bottom-0 left-0 right-0 h-3 cursor-s-resize bg-black/20 opacity-0 group-hover:opacity-100 transition"
                                        data-resize-handle>
                                    </div>
                                </div>
                            </template>

                            {{-- Drop preview --}}
                            <div x-show="dropPreview && dropPreview.day === {{ $day }}"
                                class="absolute left-1 right-1 border-2 border-dashed border-accent/50 rounded-sm z-5 pointer-events-none"
                                :style="dropPreview && dropPreview.day === {{ $day }} ? 'top: ' + dropPreview.top + 'px; height: ' + dropPreview.height + 'px;' : ''"
                                style="display: none;">
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Edit Modal --}}
    @if ($showScheduleModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
            x-data @keydown.escape.window="$wire.closeScheduleModal()">
            <div class="absolute inset-0" wire:click="closeScheduleModal"></div>
            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-md" @click.stop>
                <div class="bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>{{ $editingScheduleId ? 'Blok Bewerken' : 'Nieuw Blok' }}</span>
                    <button wire:click="closeScheduleModal" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form wire:submit="saveSchedule">
                        {{-- Video --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Video *</label>
                            <select wire:model="scheduleVideoId" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm rounded-sm focus:border-accent focus:ring-accent">
                                <option value="">— Selecteer video —</option>
                                @foreach ($videos as $video)
                                    <option value="{{ $video->id }}">{{ $video->filename }}</option>
                                @endforeach
                            </select>
                            @error('scheduleVideoId') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Day --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Dag *</label>
                            <select wire:model="scheduleDayOfWeek" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm rounded-sm focus:border-accent focus:ring-accent">
                                @foreach (\App\Models\CameraDefaultBlock::DAY_LABELS as $i => $label)
                                    <option value="{{ $i }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Start / End Time --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Starttijd *</label>
                                <input type="time" wire:model="scheduleStartTime"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm rounded-sm focus:border-accent focus:ring-accent">
                                @error('scheduleStartTime') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Eindtijd *</label>
                                <input type="time" wire:model="scheduleEndTime"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm rounded-sm focus:border-accent focus:ring-accent">
                                @error('scheduleEndTime') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex gap-3 justify-between">
                            <div>
                                @if ($editingScheduleId)
                                    <button type="button" wire:click="deleteScheduledVideo({{ $editingScheduleId }})"
                                        class="px-3 py-2 text-xs font-semibold text-red-400 border border-red-800 uppercase tracking-wider transition hover:bg-red-900/30">
                                        Verwijderen
                                    </button>
                                @endif
                            </div>
                            <div class="flex gap-3">
                                <button type="button" wire:click="closeScheduleModal"
                                    class="px-4 py-2 text-sm font-semibold text-zinc-400 border border-zinc-700 uppercase tracking-wider transition hover:text-white">
                                    Annuleren
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                                    Opslaan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
Alpine.data('cameraPlanner', (initialData, initialSnap) => ({
    scheduled: initialData.scheduled || [],
    snap: initialSnap,
    dropPreview: null,
    _busy: false,
    nowMinutes: 0,

    init() {
        // Current time line
        const updateNow = () => {
            const d = new Date();
            this.nowMinutes = d.getHours() * 60 + d.getMinutes();
        };
        updateNow();
        setInterval(updateNow, 30000);

        // Sync data from Livewire (wire:ignore blocks DOM updates)
        Livewire.on('schedule-updated', (...args) => {
            const params = args[0];
            const d = Array.isArray(params) ? params[0] : params;
            if (d && d.scheduled) {
                this.scheduled = [...d.scheduled];
            }
        });
        Livewire.on('snap-updated', (...args) => {
            const params = args[0];
            const d = Array.isArray(params) ? params[0] : params;
            if (d && d.snap != null) {
                this.snap = Number(d.snap);
            }
        });

        // Event delegation: single native mousedown on the root element
        // This bypasses Alpine's x-for event binding issues with wire:ignore
        this.$el.addEventListener('mousedown', (e) => {
            if (e.button !== 0 || this._busy) return;

            const blockEl = e.target.closest('[data-block-id]');
            if (!blockEl) return;

            e.preventDefault();
            e.stopPropagation();

            const blockId = Number(blockEl.dataset.blockId);
            const isResize = !!e.target.closest('[data-resize-handle]');

            if (isResize) {
                this._handleResize(e, blockId);
            } else {
                this._handleDrag(e, blockId);
            }
        });
    },

    // ─── Helpers ───

    getBlocksForDay(day) {
        return this.scheduled.filter(b => Number(b.day_of_week) === Number(day));
    },

    timeToPixels(time) {
        const parts = String(time).split(':');
        return Number(parts[0]) * 60 + Number(parts[1] || 0);
    },

    pixelsToTime(px) {
        const total = Math.max(0, Math.min(1440, Math.round(px / this.snap) * this.snap));
        return String(Math.floor(total / 60)).padStart(2, '0') + ':' + String(total % 60).padStart(2, '0');
    },

    findDayColumn(clientX) {
        const cols = this.$el.querySelectorAll('[data-day-col]');
        for (const col of cols) {
            const r = col.getBoundingClientRect();
            if (clientX >= r.left && clientX <= r.right) {
                return { el: col, day: Number(col.dataset.dayCol), rect: r };
            }
        }
        return null;
    },

    findBlock(blockId) {
        return this.scheduled.find(b => Number(b.id) === Number(blockId));
    },

    updateBlock(blockId, changes) {
        const idx = this.scheduled.findIndex(b => Number(b.id) === Number(blockId));
        if (idx === -1) return false;
        this.scheduled.splice(idx, 1, { ...this.scheduled[idx], ...changes });
        return true;
    },

    hasOverlap(dayOfWeek, startTime, endTime, excludeBlockId) {
        const start = this.timeToPixels(startTime);
        const end = this.timeToPixels(endTime);
        return this.scheduled.some(b => {
            if (Number(b.id) === Number(excludeBlockId)) return false;
            if (Number(b.day_of_week) !== Number(dayOfWeek)) return false;
            return start < this.timeToPixels(b.end_time) && end > this.timeToPixels(b.start_time);
        });
    },

    // ─── Drag (move block) ───

    _handleDrag(event, blockId) {
        const block = this.findBlock(blockId);
        if (!block) return;

        const col = this.findDayColumn(event.clientX);
        if (!col) return;

        const blockTopPx = this.timeToPixels(block.start_time);
        const duration = this.timeToPixels(block.end_time) - blockTopPx;
        const offsetY = event.clientY - col.rect.top - blockTopPx;

        this._busy = true;
        let moved = false;

        document.body.style.userSelect = 'none';
        document.body.style.cursor = 'grabbing';

        const onMove = (e) => {
            e.preventDefault();
            moved = true;

            const target = this.findDayColumn(e.clientX);
            if (!target) return;

            const y = e.clientY - target.rect.top - offsetY;
            const newStart = this.pixelsToTime(y);
            const newStartPx = this.timeToPixels(newStart);
            const newEnd = this.pixelsToTime(newStartPx + duration);

            if (!this.hasOverlap(target.day, newStart, newEnd, blockId)) {
                this.updateBlock(blockId, {
                    start_time: newStart,
                    end_time: newEnd,
                    day_of_week: target.day,
                });
            }
        };

        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            document.body.style.userSelect = '';
            document.body.style.cursor = '';

            if (moved) {
                const updated = this.findBlock(blockId);
                if (updated) {
                    this.$wire.updateScheduledPosition(
                        updated.id, updated.day_of_week, updated.start_time, updated.end_time
                    );
                }
            } else {
                // No movement = click → open edit modal
                this.$wire.openScheduleEdit(blockId);
            }

            setTimeout(() => { this._busy = false; }, 50);
        };

        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    },

    // ─── Resize (change end time) ───

    _handleResize(event, blockId) {
        const block = this.findBlock(blockId);
        if (!block) return;

        this._busy = true;
        let moved = false;
        const dayOfWeek = Number(block.day_of_week);

        document.body.style.userSelect = 'none';
        document.body.style.cursor = 's-resize';

        const onMove = (e) => {
            e.preventDefault();
            moved = true;

            const col = this.$el.querySelector('[data-day-col="' + dayOfWeek + '"]');
            if (!col) return;

            const rect = col.getBoundingClientRect();
            const y = e.clientY - rect.top;
            const newEnd = this.pixelsToTime(y);

            const current = this.findBlock(blockId);
            if (!current) return;

            const minEndPx = this.timeToPixels(current.start_time) + this.snap;
            if (this.timeToPixels(newEnd) >= minEndPx) {
                if (!this.hasOverlap(dayOfWeek, current.start_time, newEnd, blockId)) {
                    this.updateBlock(blockId, { end_time: newEnd });
                }
            }
        };

        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            document.body.style.userSelect = '';
            document.body.style.cursor = '';

            if (moved) {
                const updated = this.findBlock(blockId);
                if (updated) {
                    this.$wire.updateScheduledPosition(
                        updated.id, updated.day_of_week, updated.start_time, updated.end_time
                    );
                }
            }

            setTimeout(() => { this._busy = false; }, 50);
        };

        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    },

    // ─── Drop from video library ───

    handleDragOver(event, day) {
        const rect = event.currentTarget.getBoundingClientRect();
        const y = event.clientY - rect.top;
        const snappedTime = this.pixelsToTime(y);
        const px = this.timeToPixels(snappedTime);
        this.dropPreview = { day: day, top: px, height: 60 };
    },

    handleDrop(event, day) {
        const videoId = event.dataTransfer.getData('video_id');
        if (!videoId) { this.dropPreview = null; return; }

        const rect = event.currentTarget.getBoundingClientRect();
        const y = event.clientY - rect.top;
        const startTime = this.pixelsToTime(y);
        const startPx = this.timeToPixels(startTime);
        const endTime = this.pixelsToTime(startPx + 60);

        if (this.hasOverlap(day, startTime, endTime, null)) {
            this.dropPreview = null;
            return;
        }

        this.$wire.createFromDrop(Number(videoId), day, startTime, endTime);
        this.dropPreview = null;
    },
}));
</script>
@endscript
