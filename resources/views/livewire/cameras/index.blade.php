<div class="min-h-screen -mt-16 pt-16 text-white bg-zinc-950"
     x-data="cameraFeed()"
     x-init="init()">

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold uppercase text-center tracking-wider mb-6">
            Camera's
        </h1>

        {{-- Weather info --}}
        <div class="flex items-center justify-center gap-3 mb-8 text-zinc-400 text-sm" x-show="weatherEnabled && weatherData.weather_code !== undefined" x-cloak x-transition.opacity>
            <span class="text-2xl" x-text="getWeatherIcon()"></span>
            <span class="font-mono text-white/80 text-lg" x-text="weatherData.temperature !== null ? Math.round(weatherData.temperature) + '°C' : ''"></span>
            <span class="text-zinc-500">|</span>
            <span x-text="getWeatherDescription()"></span>
            <template x-if="weatherData.rain > 0">
                <span class="text-blue-400/80" x-text="weatherData.rain.toFixed(1) + ' mm/u'"></span>
            </template>
        </div>

        @if ($cameras->isEmpty())
            <p class="text-center text-zinc-600 text-lg">{{ __("Geen camera's beschikbaar.") }}</p>
        @else
            <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($cameras as $camera)
                    <div class="border border-zinc-800 bg-zinc-900/50 rounded-sm overflow-hidden cursor-pointer backdrop-blur-sm"
                        data-camera-id="{{ $camera->id }}"
                        @click="openPopup({{ $camera->id }}, '{{ $camera->name }}')">

                        {{-- Camera Header --}}
                        <div class="flex items-center justify-between px-3 py-2 bg-zinc-800/80">
                            <span class="text-[11px] uppercase tracking-wider font-semibold text-zinc-300 truncate">
                                {{ $camera->name }}
                            </span>
                            <span class="flex items-center gap-1.5 shrink-0"
                                :class="getCameraStatus({{ $camera->id }}) === 'online' ? 'text-green-400' : 'text-red-400'">
                                <span class="w-2 h-2 rounded-full"
                                    :class="getCameraStatus({{ $camera->id }}) === 'online' ? 'bg-green-400 animate-pulse' : 'bg-red-500'"></span>
                                <span class="text-[10px] uppercase tracking-wider font-bold"
                                    x-text="getCameraStatus({{ $camera->id }}) === 'online' ? 'LIVE' : 'OFFLINE'"></span>
                            </span>
                        </div>

                        {{-- Video Area --}}
                        <div class="aspect-video relative overflow-hidden">

                            {{-- Sky background layer --}}
                            <div class="absolute inset-0 z-[0]"
                                :style="{ backgroundColor: skyColor }">
                                <template x-if="getCameraBackgroundIsVideo({{ $camera->id }})">
                                    <video
                                        :src="getCameraBackgroundUrl({{ $camera->id }})"
                                        autoplay loop muted playsinline
                                        class="absolute inset-0 w-full h-full object-cover opacity-90 mix-blend-soft-light"
                                    ></video>
                                </template>
                                <template x-if="!getCameraBackgroundIsVideo({{ $camera->id }}) && getCameraBackgroundUrl({{ $camera->id }})">
                                    <img
                                        :src="getCameraBackgroundUrl({{ $camera->id }})"
                                        class="absolute inset-0 w-full h-full object-cover opacity-90 mix-blend-soft-light"
                                        alt=""
                                    >
                                </template>
                            </div>

                            {{-- Video --}}
                            <video
                                x-ref="cam{{ $camera->id }}"
                                muted playsinline preload="metadata"
                                class="absolute inset-0 w-full h-full object-cover z-[1]"
                                x-show="getCameraStatus({{ $camera->id }}) === 'online' && getCameraVideoUrl({{ $camera->id }})"
                                style="display: none;"
                            ></video>

                            {{-- Subtle dark overlay for cards --}}
                            <div class="absolute inset-0 z-[2] pointer-events-none bg-black/30"></div>

                            {{-- Color overlay --}}
                            <div class="absolute inset-0 z-[3] pointer-events-none"
                                :style="{ backgroundColor: overlayColor }"></div>

                            {{-- Offline overlay --}}
                            <div x-show="getCameraStatus({{ $camera->id }}) !== 'online' || !getCameraVideoUrl({{ $camera->id }})"
                                class="absolute inset-0 z-[4] flex flex-col items-center justify-center bg-zinc-900/80">
                                <svg class="w-10 h-10 text-zinc-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    <line x1="3" y1="21" x2="21" y2="3" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                <p class="text-zinc-500 text-xs uppercase tracking-wider font-semibold">Camera offline</p>
                                <p class="text-zinc-600 text-[10px] uppercase tracking-wider">Geen signaal</p>
                            </div>

                            {{-- Camera static effect --}}
                            <div class="absolute inset-0 pointer-events-none z-[5]" x-show="getCameraStaticEnabled({{ $camera->id }})" x-cloak>
                                <div class="camera-scanlines absolute inset-0" :style="{ opacity: getCameraStaticIntensity({{ $camera->id }}) / 200 }"></div>
                                <canvas class="camera-noise absolute inset-0 w-full h-full" data-camera-id="{{ $camera->id }}" data-static-scope="card" :style="{ opacity: getCameraStaticIntensity({{ $camera->id }}) / 250 }"></canvas>
                            </div>

                            {{-- Timestamp overlay --}}
                            <div class="absolute bottom-1.5 right-2 text-[9px] font-mono text-white/40 tracking-wider z-[6]"
                                x-text="getCurrentTimestamp()"></div>

                            {{-- REC indicator --}}
                            <div class="absolute top-2 left-2 flex items-center gap-1 z-[6]"
                                x-show="getCameraStatus({{ $camera->id }}) === 'online'">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                <span class="text-[9px] font-bold text-red-500/80 tracking-wider">REC</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Popup Modal --}}
                <template x-teleport="body">
                    <div x-show="popup.open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
                        @keydown.escape.window="closePopup()" style="display: none;">
                        <div class="absolute inset-0" @click="closePopup()"></div>

                        <div class="relative max-w-4xl max-h-[90vh]" @click.stop>
                            {{-- Header --}}
                            <div class="flex items-center justify-between bg-zinc-900 border border-zinc-800 border-b-0 px-4 py-3 rounded-t-sm">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm uppercase tracking-wider font-semibold text-white" x-text="popup.name"></span>
                                    <span class="flex items-center gap-1.5"
                                        :class="popup.id && getCameraStatus(popup.id) === 'online' ? 'text-green-400' : 'text-red-400'">
                                        <span class="w-2 h-2 rounded-full"
                                            :class="popup.id && getCameraStatus(popup.id) === 'online' ? 'bg-green-400 animate-pulse' : 'bg-red-500'"></span>
                                        <span class="text-[10px] uppercase tracking-wider font-bold"
                                            x-text="popup.id && getCameraStatus(popup.id) === 'online' ? 'LIVE' : 'OFFLINE'"></span>
                                    </span>
                                    {{-- Weather in popup header --}}
                                    <span class="flex items-center gap-1.5 text-zinc-400 ml-2" x-show="weatherEnabled && weatherData.weather_code !== undefined" x-cloak>
                                        <span class="text-sm" x-text="getWeatherIcon()"></span>
                                        <span class="text-[10px] font-mono text-white/60" x-text="weatherData.temperature !== null ? Math.round(weatherData.temperature) + '°C' : ''"></span>
                                        <span class="text-[10px] text-zinc-500" x-text="getWeatherDescription()"></span>
                                        <span class="text-[10px] text-blue-400/70" x-show="weatherData.rain > 0" x-text="weatherData.rain.toFixed(1) + ' mm/u'"></span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    {{-- Audio toggle --}}
                                    <button @click="toggleMute()" class="text-zinc-400 hover:text-white transition" title="Audio aan/uit">
                                        <svg x-show="!popup.muted" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M11 5L6 9H2v6h4l5 4V5z"/>
                                        </svg>
                                        <svg x-show="popup.muted" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                                        </svg>
                                    </button>
                                    {{-- Close --}}
                                    <button @click="closePopup()" class="text-zinc-400 hover:text-white transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Video --}}
                            <div class="relative border border-zinc-800 border-t-0 rounded-b-sm overflow-hidden">

                                {{-- Sky background layer --}}
                                <div class="absolute inset-0 z-[0]"
                                    :style="{ backgroundColor: skyColor }">
                                    <template x-if="popup.id && getCameraBackgroundIsVideo(popup.id)">
                                        <video
                                            x-ref="popupBgVideo"
                                            :src="getCameraBackgroundUrl(popup.id)"
                                            autoplay loop muted playsinline
                                            class="absolute inset-0 w-full h-full object-cover opacity-90 mix-blend-soft-light"
                                        ></video>
                                    </template>
                                    <template x-if="popup.id && !getCameraBackgroundIsVideo(popup.id) && getCameraBackgroundUrl(popup.id)">
                                        <img
                                            :src="getCameraBackgroundUrl(popup.id)"
                                            class="absolute inset-0 w-full h-full object-cover opacity-90 mix-blend-soft-light"
                                            alt=""
                                        >
                                    </template>
                                </div>

                                {{-- Popup weather effects --}}
                                <div class="absolute inset-0 overflow-hidden pointer-events-none z-[1]" x-show="weatherEnabled" x-cloak>
                                    {{-- Clouds --}}
                                    <div class="absolute inset-0" :style="{ opacity: cloudOpacity, transition: 'opacity 5s ease' }">
                                        <template x-for="cloud in popupClouds" :key="cloud.id">
                                            <div class="cloud-shape absolute" :style="{
                                                top: cloud.top + '%',
                                                width: cloud.width + 'px',
                                                height: cloud.height + 'px',
                                                filter: 'blur(' + cloud.blur + 'px)',
                                                animation: 'cloud-drift-card ' + cloud.speed + 's linear infinite',
                                                animationDelay: cloud.delay + 's',
                                                opacity: cloud.opacity,
                                            }">
                                                <div class="absolute rounded-full" :style="{
                                                    width: '60%', height: '70%', bottom: '0', left: '10%',
                                                    backgroundColor: scaleCloudAlpha(cloudColor, 0.4),
                                                }"></div>
                                                <div class="absolute rounded-full" :style="{
                                                    width: '50%', height: '85%', bottom: '5%', left: '25%',
                                                    backgroundColor: scaleCloudAlpha(cloudColor, 0.5),
                                                }"></div>
                                                <div class="absolute rounded-full" :style="{
                                                    width: '45%', height: '65%', bottom: '0', left: '50%',
                                                    backgroundColor: scaleCloudAlpha(cloudColor, 0.35),
                                                }"></div>
                                                <div class="absolute rounded-full" :style="{
                                                    width: '35%', height: '50%', bottom: '10%', left: '5%',
                                                    backgroundColor: scaleCloudAlpha(cloudColor, 0.3),
                                                }"></div>
                                                <div class="absolute rounded-full" :style="{
                                                    width: '30%', height: '45%', bottom: '5%', left: '60%',
                                                    backgroundColor: scaleCloudAlpha(cloudColor, 0.25),
                                                }"></div>
                                            </div>
                                        </template>
                                    </div>
                                    {{-- Rain canvas --}}
                                    <canvas class="absolute inset-0 w-full h-full rain-canvas" data-type="popup"></canvas>
                                </div>

                                {{-- Video element (relative so it drives container size) --}}
                                <video
                                    x-ref="popupVideo"
                                    autoplay playsinline muted
                                    class="relative z-[2] w-full"
                                    x-show="popup.id && getCameraStatus(popup.id) === 'online' && getCameraVideoUrl(popup.id)"
                                    style="display: none; max-height: 70vh;"
                                ></video>

                                {{-- Separate audio element --}}
                                <audio x-ref="popupAudio" preload="none" style="display: none;"></audio>

                                {{-- Default slot sound (ambient, loops continuously per time slot) --}}
                                <audio x-ref="slotSound" preload="none" loop style="display: none;"></audio>

                                {{-- Fallback aspect ratio when offline / no video --}}
                                <div x-show="!popup.id || getCameraStatus(popup.id) !== 'online' || !getCameraVideoUrl(popup.id)"
                                    class="w-[640px] max-w-full aspect-video"></div>

                                {{-- Color overlay --}}
                                <div class="absolute inset-0 z-[3] pointer-events-none"
                                    :style="{ backgroundColor: overlayColor }"></div>

                                {{-- Offline --}}
                                <div x-show="!popup.id || getCameraStatus(popup.id) !== 'online' || !getCameraVideoUrl(popup.id)"
                                    class="absolute inset-0 z-[4] flex flex-col items-center justify-center bg-zinc-900/80">
                                    <svg class="w-16 h-16 text-zinc-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        <line x1="3" y1="21" x2="21" y2="3" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    <p class="text-zinc-500 text-sm uppercase tracking-wider font-semibold">Camera offline</p>
                                    <p class="text-zinc-600 text-xs uppercase tracking-wider">Geen signaal</p>
                                </div>

                                {{-- Camera static effect --}}
                                <div class="absolute inset-0 pointer-events-none z-[5]" x-show="popup.id && getCameraStaticEnabled(popup.id)" x-cloak>
                                    <div class="camera-scanlines absolute inset-0" :style="{ opacity: popup.id ? getCameraStaticIntensity(popup.id) / 200 : 0 }"></div>
                                    <canvas class="camera-noise absolute inset-0 w-full h-full" :data-camera-id="popup.id" data-static-scope="popup" :style="{ opacity: popup.id ? getCameraStaticIntensity(popup.id) / 250 : 0 }"></canvas>
                                </div>

                                {{-- Timestamp --}}
                                <div class="absolute bottom-2 right-3 text-[10px] font-mono text-white/40 tracking-wider z-[6]"
                                    x-text="getCurrentTimestamp()"></div>

                                {{-- REC --}}
                                <div class="absolute top-3 left-3 flex items-center gap-1.5 z-[6]"
                                    x-show="popup.id && getCameraStatus(popup.id) === 'online'">
                                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                    <span class="text-[10px] font-bold text-red-500/80 tracking-wider">REC</span>
                                </div>
                            </div>

                            {{-- Description --}}
                            <div x-show="popup.id && getCameraDescription(popup.id)"
                                 class="bg-zinc-900 border border-zinc-800 border-t-0 px-4 py-3">
                                <div class="prose prose-invert prose-sm prose-zinc font-description max-w-none content-block-text" x-html="getCameraDescription(popup.id)"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        @endif
    </section>

    <style>
        @keyframes cloud-drift-card {
            from { transform: translateX(-150%); }
            to { transform: translateX(calc(100vw + 100%)); }
        }
        .cloud-shape {
            will-change: transform;
        }
        /* Camera scanlines */
        .camera-scanlines {
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(255, 255, 255, 0.03) 2px,
                rgba(255, 255, 255, 0.03) 4px
            );
            mix-blend-mode: overlay;
        }
        .camera-noise {
            mix-blend-mode: overlay;
        }
    </style>
</div>

@script
<script>
Alpine.data('cameraFeed', () => ({
    cameras: {},
    clockTimer: null,
    skyTimer: null,
    timestampText: '',
    popup: { open: false, id: null, name: '', muted: true },

    // Sky color system
    skyColor: '#0B1026',
    overlayColor: 'rgba(0,0,0,0)',
    cloudColor: 'rgba(255, 255, 255, 0.4)',
    slotKeyframes: [],

    // Static/noise system
    staticAnimFrame: null,

    // Weather system
    weatherEnabled: false,
    weatherData: { cloud_cover: 0, rain: 0, wind_speed: 0, temperature: null, weather_code: undefined },
    cloudOpacity: 0,
    cardClouds: [],
    popupClouds: [],
    weatherTimer: null,
    rainAnimFrame: null,
    rainDrops: [],
    popupPollTimer: null,
    currentSlotSoundUrl: null,
    _scheduleLoading: false,
    _scheduleTimer: null,

    // Weather audio system (Web Audio API)
    slotsData: {},
    weatherAudioCtx: null,
    rainGainNode: null,
    windGainNode: null,
    rainSourceNode: null,
    windSourceNode: null,

    async init() {
        this.updateTimestamp();
        this.clockTimer = setInterval(() => this.updateTimestamp(), 1000);
        this.generateClouds();
        await this.loadSchedule();
        this.skyTimer = setInterval(() => this.updateSkyColor(), 60000);
        this.startStaticNoise();
        this.startRain();
    },

    generateClouds() {
        // Card-level clouds (visible in small cards)
        this.cardClouds = [];
        for (let i = 0; i < 6; i++) {
            this.cardClouds.push({
                id: i,
                top: 2 + Math.random() * 35,
                width: 60 + Math.random() * 80,
                height: 20 + Math.random() * 25,
                speed: 20 + Math.random() * 30,
                delay: -(Math.random() * 40),
                blur: 2 + Math.random() * 3,
                opacity: 0.5 + Math.random() * 0.5,
            });
        }
        // Popup-level clouds (larger, more detail)
        this.popupClouds = [];
        for (let i = 0; i < 8; i++) {
            this.popupClouds.push({
                id: 'p' + i,
                top: 2 + Math.random() * 40,
                width: 120 + Math.random() * 200,
                height: 40 + Math.random() * 60,
                speed: 35 + Math.random() * 45,
                delay: -(Math.random() * 60),
                blur: 4 + Math.random() * 6,
                opacity: 0.4 + Math.random() * 0.6,
            });
        }
    },

    // Rain rendering system
    startRain() {
        if (this.rainAnimFrame) return;
        this.rainDrops = [];

        const renderRain = () => {
            const rain = this.weatherData.rain || 0;
            if (!this.weatherEnabled || rain <= 0) {
                // Clear all rain canvases
                document.querySelectorAll('.rain-canvas').forEach(canvas => {
                    const ctx = canvas.getContext('2d');
                    if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
                });
                this.rainAnimFrame = requestAnimationFrame(renderRain);
                return;
            }

            // Intensity: drops per frame based on rain mm/h
            // 0.1 mm = light drizzle, 2.5 = moderate, 7.5+ = heavy
            const intensity = Math.min(rain / 5, 1); // 0-1 normalized
            const dropsPerFrame = Math.ceil(intensity * 8); // 1-8 new drops per frame
            const maxDrops = Math.ceil(intensity * 200); // max active drops
            const windAngle = 0.15; // slight wind angle

            document.querySelectorAll('.rain-canvas').forEach(canvas => {
                const w = canvas.offsetWidth;
                const h = canvas.offsetHeight;
                if (w === 0 || h === 0) return;

                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, w, h);

                // Get or create drops array for this canvas
                if (!canvas._drops) canvas._drops = [];
                const drops = canvas._drops;

                // Add new drops
                for (let i = 0; i < dropsPerFrame && drops.length < maxDrops; i++) {
                    drops.push({
                        x: Math.random() * (w + 40) - 20,
                        y: -10 - Math.random() * 30,
                        speed: 4 + Math.random() * 6 + intensity * 4,
                        length: 8 + Math.random() * 12 + intensity * 8,
                        opacity: 0.15 + Math.random() * 0.25 + intensity * 0.15,
                        width: 0.5 + Math.random() * 0.8,
                    });
                }

                // Draw and update drops
                ctx.lineCap = 'round';
                for (let i = drops.length - 1; i >= 0; i--) {
                    const d = drops[i];

                    // Draw raindrop
                    ctx.beginPath();
                    ctx.moveTo(d.x, d.y);
                    ctx.lineTo(d.x + windAngle * d.length, d.y + d.length);
                    ctx.strokeStyle = `rgba(180, 210, 240, ${d.opacity})`;
                    ctx.lineWidth = d.width;
                    ctx.stroke();

                    // Move
                    d.y += d.speed;
                    d.x += windAngle * d.speed;

                    // Remove off-screen drops (draw splash)
                    if (d.y > h) {
                        // Splash effect
                        if (intensity > 0.3) {
                            ctx.beginPath();
                            ctx.arc(d.x, h - 1, 1 + Math.random() * 2, 0, Math.PI, true);
                            ctx.strokeStyle = `rgba(180, 210, 240, ${d.opacity * 0.5})`;
                            ctx.lineWidth = 0.5;
                            ctx.stroke();
                        }
                        drops.splice(i, 1);
                    }
                }
            });

            this.rainAnimFrame = requestAnimationFrame(renderRain);
        };
        this.rainAnimFrame = requestAnimationFrame(renderRain);
    },

    stopRain() {
        if (this.rainAnimFrame) {
            cancelAnimationFrame(this.rainAnimFrame);
            this.rainAnimFrame = null;
        }
        document.querySelectorAll('.rain-canvas').forEach(canvas => {
            canvas._drops = [];
            const ctx = canvas.getContext('2d');
            if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
        });
    },

    async fetchWeather() {
        if (!this.weatherEnabled) {
            this.cloudOpacity = 0;
            return;
        }
        try {
            const res = await fetch('https://api.open-meteo.com/v1/forecast?latitude=50.8278&longitude=3.2644&current=cloud_cover,rain,temperature_2m,weather_code,wind_speed_10m');
            if (!res.ok) return;
            const data = await res.json();
            this.weatherData = {
                cloud_cover: data.current?.cloud_cover ?? 0,
                rain: data.current?.rain ?? 0,
                wind_speed: data.current?.wind_speed_10m ?? 0,
                temperature: data.current?.temperature_2m ?? null,
                weather_code: data.current?.weather_code ?? 0,
            };
            this.cloudOpacity = this.weatherData.cloud_cover / 100;
        } catch (e) {
            // Silently fail — weather is cosmetic
        }
    },

    scheduleNextCheck(seconds) {
        if (this._scheduleTimer) clearTimeout(this._scheduleTimer);
        this._scheduleTimer = setTimeout(() => this.loadSchedule(), Math.max(5, seconds) * 1000);
    },

    async loadSchedule() {
        // Prevent concurrent loads
        if (this._scheduleLoading) return;
        this._scheduleLoading = true;

        try {
            const res = await fetch('/api/cameras/schedule');
            if (!res.ok) throw new Error('API returned ' + res.status);
            const data = await res.json();

            // Process slot data for sky colors and weather audio
            if (data.slots) {
                this.slotsData = data.slots;
                this.slotKeyframes = this.buildKeyframes(data.slots);
                this.updateSkyColor();
            }

            // Weather toggle from API
            const wasEnabled = this.weatherEnabled;
            this.weatherEnabled = data.weather_enabled ?? false;

            // Start or stop weather
            if (this.weatherEnabled && !this.weatherTimer) {
                await this.fetchWeather();
                this.weatherTimer = setInterval(() => this.fetchWeather(), 600000); // 10 min
            } else if (!this.weatherEnabled && this.weatherTimer) {
                clearInterval(this.weatherTimer);
                this.weatherTimer = null;
                this.cloudOpacity = 0;
            } else if (this.weatherEnabled && !wasEnabled) {
                await this.fetchWeather();
            }

            // Find the soonest next_check across all cameras
            let minCheckSeconds = 300;

            data.cameras.forEach(cam => {
                const prev = this.cameras[cam.id];
                const videoChanged = !prev || prev.video_url !== cam.video_url;
                const audioChanged = !prev || prev.audio_url !== cam.audio_url;
                const soundChanged = !prev || prev.default_sound_url !== cam.default_sound_url;

                this.cameras[cam.id] = { ...cam };

                if (videoChanged || audioChanged) {
                    this.$nextTick(() => {
                        // Update grid card thumbnail
                        const videoEl = this.$refs['cam' + cam.id];
                        if (videoEl) {
                            if (cam.status === 'online' && cam.video_url) {
                                const offset = cam.video_start_offset_seconds ?? 0;
                                const isRealtime = cam.behaviour_type === 'realtime';
                                videoEl.loop = false; // always show single frame
                                videoEl.src = cam.video_url;
                                videoEl.addEventListener('loadedmetadata', () => {
                                    if (offset > 0) {
                                        videoEl.currentTime = Math.min(offset, Math.max(0, videoEl.duration - 0.1));
                                    } else if (isRealtime) {
                                        videoEl.currentTime = 0;
                                    }
                                    videoEl.pause();
                                }, { once: true });
                                videoEl.load();
                            } else {
                                videoEl.pause();
                                videoEl.removeAttribute('src');
                                videoEl.load();
                            }
                        }

                        // Update popup only if video/audio actually changed
                        if (this.popup.open && this.popup.id === cam.id) {
                            this.loadPopupVideo();
                        }
                    });
                }

                // Update slot sound if it changed (without reloading video)
                if (soundChanged && this.popup.open && this.popup.id === cam.id) {
                    this.updateSlotSound();
                }

                // Track the soonest check time
                minCheckSeconds = Math.min(minCheckSeconds, cam.next_check_seconds ?? 300);
            });

            // Single timer for the next schedule refresh
            this.scheduleNextCheck(minCheckSeconds);
        } catch (e) {
            this.scheduleNextCheck(30);
        } finally {
            this._scheduleLoading = false;
        }
    },

    buildKeyframes(slots) {
        const keyframes = [];
        const slotArr = Object.values(slots);
        const count = slotArr.length;

        for (let i = 0; i < count; i++) {
            const slot = slotArr[i];
            const startMin = this.timeToMin(slot.start);
            const endMin = slot.end === '24:00' ? 1440 : this.timeToMin(slot.end);

            if (slot.is_transition) {
                // Transition: prev color → own color (midpoint) → next color
                const prev = slotArr[(i - 1 + count) % count];
                const next = slotArr[(i + 1) % count];
                let midpoint;
                if (endMin > startMin) {
                    midpoint = (startMin + endMin) / 2;
                } else {
                    const totalDuration = (1440 - startMin) + endMin;
                    midpoint = (startMin + totalDuration / 2) % 1440;
                }
                // 0% = previous slot's color
                keyframes.push({
                    minutes: startMin,
                    bgColor: prev.bg_color || '#000000',
                    overlayColor: prev.overlay_color || '#00000000',
                    cloudColor: prev.cloud_color || '#FFFFFF66',
                });
                // 50% = this slot's own color
                keyframes.push({
                    minutes: midpoint,
                    bgColor: slot.bg_color || '#000000',
                    overlayColor: slot.overlay_color || '#00000000',
                    cloudColor: slot.cloud_color || '#FFFFFF66',
                });
                // 100% = next slot's color
                keyframes.push({
                    minutes: endMin >= 1440 ? 1439.99 : endMin,
                    bgColor: next.bg_color || '#000000',
                    overlayColor: next.overlay_color || '#00000000',
                    cloudColor: next.cloud_color || '#FFFFFF66',
                });
            } else {
                // Solid slot: holds color flat from start to end
                keyframes.push({
                    minutes: startMin,
                    bgColor: slot.bg_color || '#000000',
                    overlayColor: slot.overlay_color || '#00000000',
                    cloudColor: slot.cloud_color || '#FFFFFF66',
                });
                keyframes.push({
                    minutes: endMin >= 1440 ? 1439.99 : endMin,
                    bgColor: slot.bg_color || '#000000',
                    overlayColor: slot.overlay_color || '#00000000',
                    cloudColor: slot.cloud_color || '#FFFFFF66',
                });
            }
        }

        keyframes.sort((a, b) => a.minutes - b.minutes);
        return keyframes;
    },

    timeToMin(t) {
        const [h, m] = t.split(':').map(Number);
        return h * 60 + m;
    },

    updateSkyColor() {
        if (this.slotKeyframes.length === 0) return;

        const now = new Date();
        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'Europe/Brussels',
            hour: 'numeric', minute: 'numeric', hour12: false,
        }).formatToParts(now);
        const h = parseInt(parts.find(p => p.type === 'hour').value) || 0;
        const m = parseInt(parts.find(p => p.type === 'minute').value) || 0;
        const currentMin = h * 60 + m;

        const kf = this.slotKeyframes;
        const totalDay = 1440;

        let prevKf, nextKf, t;
        let nextIdx = kf.findIndex(k => k.minutes > currentMin);

        if (nextIdx === -1) {
            prevKf = kf[kf.length - 1];
            nextKf = kf[0];
            const gap = (nextKf.minutes + totalDay) - prevKf.minutes;
            const elapsed = currentMin - prevKf.minutes;
            t = gap > 0 ? elapsed / gap : 0;
        } else if (nextIdx === 0) {
            prevKf = kf[kf.length - 1];
            nextKf = kf[0];
            const gap = (nextKf.minutes + totalDay) - prevKf.minutes;
            const elapsed = (currentMin + totalDay) - prevKf.minutes;
            t = gap > 0 ? elapsed / gap : 0;
        } else {
            prevKf = kf[nextIdx - 1];
            nextKf = kf[nextIdx];
            const gap = nextKf.minutes - prevKf.minutes;
            const elapsed = currentMin - prevKf.minutes;
            t = gap > 0 ? elapsed / gap : 0;
        }

        t = Math.max(0, Math.min(1, t));

        this.skyColor = this.lerpHex(prevKf.bgColor, nextKf.bgColor, t);
        this.overlayColor = this.lerpHexAlpha(prevKf.overlayColor, nextKf.overlayColor, t);
        this.cloudColor = this.lerpHexAlpha(prevKf.cloudColor, nextKf.cloudColor, t);
    },

    lerpHex(c1, c2, t) {
        const r1 = parseInt(c1.slice(1, 3), 16), g1 = parseInt(c1.slice(3, 5), 16), b1 = parseInt(c1.slice(5, 7), 16);
        const r2 = parseInt(c2.slice(1, 3), 16), g2 = parseInt(c2.slice(3, 5), 16), b2 = parseInt(c2.slice(5, 7), 16);
        const r = Math.round(r1 + (r2 - r1) * t);
        const g = Math.round(g1 + (g2 - g1) * t);
        const b = Math.round(b1 + (b2 - b1) * t);
        return '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join('');
    },

    lerpHexAlpha(c1, c2, t) {
        const r1 = parseInt(c1.slice(1, 3), 16), g1 = parseInt(c1.slice(3, 5), 16), b1 = parseInt(c1.slice(5, 7), 16);
        const a1 = c1.length > 7 ? parseInt(c1.slice(7, 9), 16) / 255 : 1;
        const r2 = parseInt(c2.slice(1, 3), 16), g2 = parseInt(c2.slice(3, 5), 16), b2 = parseInt(c2.slice(5, 7), 16);
        const a2 = c2.length > 7 ? parseInt(c2.slice(7, 9), 16) / 255 : 1;
        const r = Math.round(r1 + (r2 - r1) * t);
        const g = Math.round(g1 + (g2 - g1) * t);
        const b = Math.round(b1 + (b2 - b1) * t);
        const a = (a1 + (a2 - a1) * t).toFixed(3);
        return `rgba(${r}, ${g}, ${b}, ${a})`;
    },

    scaleCloudAlpha(rgbaStr, scale) {
        const m = rgbaStr.match(/rgba?\((\d+),\s*(\d+),\s*(\d+),?\s*([\d.]+)?\)/);
        if (!m) return rgbaStr;
        const a = parseFloat(m[4] ?? 1) * scale;
        return `rgba(${m[1]}, ${m[2]}, ${m[3]}, ${a.toFixed(3)})`;
    },

    getCameraStatus(id) {
        return this.cameras[id]?.status ?? 'loading';
    },

    getCameraVideoUrl(id) {
        return this.cameras[id]?.video_url ?? '';
    },

    getCameraAudioUrl(id) {
        return this.cameras[id]?.audio_url ?? '';
    },

    getCameraBackgroundUrl(id) {
        return this.cameras[id]?.background_url ?? '';
    },

    getCameraBackgroundIsVideo(id) {
        return this.cameras[id]?.background_is_video ?? false;
    },

    getCameraStaticEnabled(id) {
        return this.cameras[id]?.static_enabled ?? false;
    },

    getCameraStaticIntensity(id) {
        return this.cameras[id]?.static_intensity ?? 15;
    },

    getCameraDescription(id) {
        return this.cameras[id]?.description ?? '';
    },

    // Weather audio helpers
    getCurrentSlot() {
        const now = new Date();
        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'Europe/Brussels', hour: 'numeric', minute: 'numeric', hour12: false,
        }).formatToParts(now);
        const h = parseInt(parts.find(p => p.type === 'hour').value) || 0;
        const m = parseInt(parts.find(p => p.type === 'minute').value) || 0;
        const currentMin = h * 60 + m;

        for (const [key, slot] of Object.entries(this.slotsData)) {
            const startMin = this.timeToMin(slot.start);
            const endMin = slot.end === '24:00' ? 1440 : this.timeToMin(slot.end);
            if (endMin > startMin) {
                if (currentMin >= startMin && currentMin < endMin) return slot;
            } else {
                if (currentMin >= startMin || currentMin < endMin) return slot;
            }
        }
        return null;
    },

    updateSlotSound() {
        const cam = this.cameras[this.popup.id];
        const soundUrl = cam?.default_sound_url ?? null;
        const slotSoundEl = this.$refs.slotSound;
        if (!slotSoundEl) return;

        // Only reload if URL changed
        if (soundUrl !== this.currentSlotSoundUrl) {
            this.currentSlotSoundUrl = soundUrl;
            if (soundUrl) {
                slotSoundEl.src = soundUrl;
                slotSoundEl.load();
                if (!this.popup.muted) {
                    slotSoundEl.play().catch(() => {});
                }
            } else {
                slotSoundEl.pause();
                slotSoundEl.removeAttribute('src');
                slotSoundEl.load();
            }
        }
    },

    startSlotSound() {
        const slotSoundEl = this.$refs.slotSound;
        if (!slotSoundEl) return;

        this.updateSlotSound();

        if (this.currentSlotSoundUrl && !this.popup.muted) {
            slotSoundEl.play().catch(() => {});
        }
    },

    stopSlotSound() {
        const slotSoundEl = this.$refs.slotSound;
        if (slotSoundEl) {
            slotSoundEl.pause();
            slotSoundEl.removeAttribute('src');
            slotSoundEl.load();
        }
        this.currentSlotSoundUrl = null;
    },

    initWeatherAudio() {
        if (this.weatherAudioCtx) return;
        this.weatherAudioCtx = new (window.AudioContext || window.webkitAudioContext)();

        // Rain: brown noise (low-pass filtered white noise)
        const rainBufferSize = this.weatherAudioCtx.sampleRate * 2;
        const rainBuffer = this.weatherAudioCtx.createBuffer(1, rainBufferSize, this.weatherAudioCtx.sampleRate);
        const rainData = rainBuffer.getChannelData(0);
        let lastOut = 0;
        for (let i = 0; i < rainBufferSize; i++) {
            const white = Math.random() * 2 - 1;
            rainData[i] = (lastOut + (0.02 * white)) / 1.02;
            lastOut = rainData[i];
            rainData[i] *= 3.5;
        }
        this.rainSourceNode = this.weatherAudioCtx.createBufferSource();
        this.rainSourceNode.buffer = rainBuffer;
        this.rainSourceNode.loop = true;
        const rainFilter = this.weatherAudioCtx.createBiquadFilter();
        rainFilter.type = 'lowpass';
        rainFilter.frequency.value = 800;
        this.rainGainNode = this.weatherAudioCtx.createGain();
        this.rainGainNode.gain.value = 0;
        this.rainSourceNode.connect(rainFilter);
        rainFilter.connect(this.rainGainNode);
        this.rainGainNode.connect(this.weatherAudioCtx.destination);
        this.rainSourceNode.start();

        // Wind: bandpass-filtered noise with slow modulation
        const windBufferSize = this.weatherAudioCtx.sampleRate * 3;
        const windBuffer = this.weatherAudioCtx.createBuffer(1, windBufferSize, this.weatherAudioCtx.sampleRate);
        const windData = windBuffer.getChannelData(0);
        let b0 = 0, b1 = 0, b2 = 0;
        for (let i = 0; i < windBufferSize; i++) {
            const white = Math.random() * 2 - 1;
            b0 = 0.99765 * b0 + white * 0.0990460;
            b1 = 0.96300 * b1 + white * 0.2965164;
            b2 = 0.57000 * b2 + white * 1.0526913;
            windData[i] = (b0 + b1 + b2 + white * 0.1848) * 0.25;
        }
        this.windSourceNode = this.weatherAudioCtx.createBufferSource();
        this.windSourceNode.buffer = windBuffer;
        this.windSourceNode.loop = true;
        const windFilter = this.weatherAudioCtx.createBiquadFilter();
        windFilter.type = 'bandpass';
        windFilter.frequency.value = 400;
        windFilter.Q.value = 0.5;
        this.windGainNode = this.weatherAudioCtx.createGain();
        this.windGainNode.gain.value = 0;
        this.windSourceNode.connect(windFilter);
        windFilter.connect(this.windGainNode);
        this.windGainNode.connect(this.weatherAudioCtx.destination);
        this.windSourceNode.start();
    },

    updateWeatherAudioVolumes() {
        if (!this.weatherAudioCtx || this.popup.muted) return;

        const cam = this.cameras[this.popup.id];
        if (!cam) return;

        const slot = this.getCurrentSlot();
        const rainEnabled = slot?.rain_enabled ?? false;
        const windEnabled = slot?.wind_enabled ?? false;

        // Camera volume (0-100) as base, weather intensity as multiplier
        const cameraRainVol = (cam.rain_volume ?? 50) / 100;
        const cameraWindVol = (cam.wind_volume ?? 50) / 100;

        // Weather intensity: rain mm/h (0-10 mapped to 0-1), wind speed km/h (0-50 mapped to 0-1)
        const rainIntensity = this.weatherEnabled ? Math.min((this.weatherData.rain || 0) / 5, 1) : 0;
        const windIntensity = this.weatherEnabled ? Math.min((this.weatherData.wind_speed || 0) / 40, 1) : 0;

        // Effective volume = camera setting * weather intensity (with minimum floor when weather is active)
        const rainVol = rainEnabled ? cameraRainVol * Math.max(rainIntensity, 0.05) : 0;
        const windVol = windEnabled ? cameraWindVol * Math.max(windIntensity, 0.1) : 0;

        const t = this.weatherAudioCtx.currentTime;
        this.rainGainNode.gain.linearRampToValueAtTime(rainVol * 0.6, t + 1);
        this.windGainNode.gain.linearRampToValueAtTime(windVol * 0.5, t + 1);
    },

    startWeatherAudio() {
        this.initWeatherAudio();
        if (this.weatherAudioCtx.state === 'suspended') {
            this.weatherAudioCtx.resume();
        }
        this.updateWeatherAudioVolumes();
        // Update volumes periodically (weather changes, slot changes)
        if (!this._weatherAudioInterval) {
            this._weatherAudioInterval = setInterval(() => this.updateWeatherAudioVolumes(), 5000);
        }
    },

    stopWeatherAudio() {
        if (this._weatherAudioInterval) {
            clearInterval(this._weatherAudioInterval);
            this._weatherAudioInterval = null;
        }
        if (this.weatherAudioCtx && this.rainGainNode && this.windGainNode) {
            const t = this.weatherAudioCtx.currentTime;
            this.rainGainNode.gain.linearRampToValueAtTime(0, t + 0.3);
            this.windGainNode.gain.linearRampToValueAtTime(0, t + 0.3);
        }
    },

    getWeatherIcon() {
        const code = this.weatherData.weather_code;
        if (code === undefined || code === null) return '';
        if (code === 0) return '☀️';
        if (code <= 3) return this.weatherData.cloud_cover > 50 ? '☁️' : '⛅';
        if (code >= 45 && code <= 48) return '🌫️';
        if (code >= 51 && code <= 57) return '🌦️';
        if (code >= 61 && code <= 67) return '🌧️';
        if (code >= 71 && code <= 77) return '🌨️';
        if (code >= 80 && code <= 82) return '🌧️';
        if (code >= 85 && code <= 86) return '🌨️';
        if (code >= 95) return '⛈️';
        return '☁️';
    },

    getWeatherDescription() {
        const code = this.weatherData.weather_code;
        if (code === undefined || code === null) return '';
        const descriptions = {
            0: 'Helder',
            1: 'Licht bewolkt', 2: 'Half bewolkt', 3: 'Bewolkt',
            45: 'Mistig', 48: 'Rijpmist',
            51: 'Lichte motregen', 53: 'Motregen', 55: 'Zware motregen',
            56: 'Aanvriezende motregen', 57: 'Zware aanvriezende motregen',
            61: 'Lichte regen', 63: 'Regen', 65: 'Zware regen',
            66: 'Aanvriezende regen', 67: 'Zware aanvriezende regen',
            71: 'Lichte sneeuw', 73: 'Sneeuw', 75: 'Zware sneeuw',
            77: 'Sneeuwkorrels',
            80: 'Lichte buien', 81: 'Buien', 82: 'Zware buien',
            85: 'Lichte sneeuwbuien', 86: 'Zware sneeuwbuien',
            95: 'Onweer', 96: 'Onweer met hagel', 99: 'Zwaar onweer met hagel',
        };
        return descriptions[code] ?? 'Bewolkt';
    },

    openPopup(id, name) {
        this.popup.open = true;
        this.popup.id = id;
        this.popup.name = name;
        this.popup.muted = true;

        this.$nextTick(() => {
            this.loadPopupVideo();
            this.updateSlotSound(); // Preload slot sound (plays on unmute)
        });
        this.startPopupPolling();
    },

    closePopup() {
        this.stopPopupPolling();
        const videoEl = this.$refs.popupVideo;
        if (videoEl) {
            videoEl.pause();
            videoEl.removeAttribute('src');
            videoEl.load();
        }
        const audioEl = this.$refs.popupAudio;
        if (audioEl) {
            audioEl.pause();
            audioEl.removeAttribute('src');
            audioEl.load();
        }
        const bgVideoEl = this.$refs.popupBgVideo;
        if (bgVideoEl) {
            bgVideoEl.pause();
            bgVideoEl.removeAttribute('src');
            bgVideoEl.load();
        }
        this.stopSlotSound();
        this.stopWeatherAudio();
        this.popup.open = false;
        this.popup.id = null;
    },

    startPopupPolling() {
        this.stopPopupPolling();
        this.popupPollTimer = setInterval(() => {
            if (this.popup.open) {
                this.loadSchedule();
            }
        }, 10000);
    },

    stopPopupPolling() {
        if (this.popupPollTimer) {
            clearInterval(this.popupPollTimer);
            this.popupPollTimer = null;
        }
    },

    loadPopupVideo() {
        const cam = this.cameras[this.popup.id];
        const videoUrl = cam?.video_url ?? '';
        const audioUrl = cam?.audio_url ?? '';
        const behaviourType = cam?.behaviour_type ?? 'loop';
        const offsetSeconds = cam?.video_start_offset_seconds ?? 0;
        const isRealtime = behaviourType === 'realtime';
        const videoEl = this.$refs.popupVideo;
        const audioEl = this.$refs.popupAudio;

        if (videoEl) {
            if (videoUrl) {
                videoEl.loop = !isRealtime;
                videoEl.muted = true;
                videoEl.src = videoUrl;
                if (isRealtime && offsetSeconds > 0) {
                    videoEl.addEventListener('loadedmetadata', () => {
                        videoEl.currentTime = Math.min(offsetSeconds, Math.max(0, videoEl.duration - 0.5));
                        videoEl.play().catch(() => {});
                    }, { once: true });
                } else {
                    videoEl.addEventListener('canplay', () => {
                        videoEl.play().catch(() => {});
                    }, { once: true });
                }
                videoEl.load();
            } else {
                videoEl.pause();
                videoEl.removeAttribute('src');
                videoEl.load();
            }
        }

        if (audioEl) {
            if (audioUrl) {
                audioEl.loop = !isRealtime;
                audioEl.src = audioUrl;
                audioEl.load();
                if (isRealtime && offsetSeconds > 0) {
                    audioEl.addEventListener('loadedmetadata', () => {
                        audioEl.currentTime = Math.min(offsetSeconds, Math.max(0, audioEl.duration - 0.5));
                        if (!this.popup.muted) {
                            audioEl.play().catch(() => {});
                        }
                    }, { once: true });
                } else if (!this.popup.muted) {
                    audioEl.addEventListener('canplay', () => {
                        audioEl.play().catch(() => {});
                    }, { once: true });
                }
            } else {
                audioEl.pause();
                audioEl.removeAttribute('src');
                audioEl.load();
            }
        }
    },

    toggleMute() {
        this.popup.muted = !this.popup.muted;
        const audioEl = this.$refs.popupAudio;
        if (audioEl && audioEl.src) {
            if (this.popup.muted) {
                audioEl.pause();
            } else {
                audioEl.play().catch(() => {});
            }
        }
        // Slot default sound
        const slotSoundEl = this.$refs.slotSound;
        if (slotSoundEl && slotSoundEl.src) {
            if (this.popup.muted) {
                slotSoundEl.pause();
            } else {
                slotSoundEl.play().catch(() => {});
            }
        }
        // Weather audio
        if (this.popup.muted) {
            this.stopWeatherAudio();
        } else {
            this.startWeatherAudio();
        }
    },

    updateTimestamp() {
        const now = new Date();
        this.timestampText = now.toLocaleString('nl-BE', {
            timeZone: 'Europe/Brussels',
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
        });
    },

    getCurrentTimestamp() {
        return this.timestampText;
    },

    // Static noise canvas animation — each camera gets independent timing
    startStaticNoise() {
        const baseFps = 8;
        const baseInterval = 1000 / baseFps;
        const canvasTimers = new WeakMap();

        const drawNoiseFrame = (canvas) => {
            const w = canvas.offsetWidth;
            const h = canvas.offsetHeight;
            if (w === 0 || h === 0) return;

            // Use small canvas and scale up for performance + chunky look
            const scale = 4;
            const sw = Math.ceil(w / scale);
            const sh = Math.ceil(h / scale);

            canvas.width = sw;
            canvas.height = sh;
            canvas.style.imageRendering = 'pixelated';

            const ctx = canvas.getContext('2d');
            const imageData = ctx.createImageData(sw, sh);
            const data = imageData.data;

            for (let i = 0; i < data.length; i += 4) {
                const v = Math.random() * 255;
                data[i] = v;
                data[i + 1] = v;
                data[i + 2] = v;
                data[i + 3] = Math.random() * 60;
            }

            ctx.putImageData(imageData, 0, 0);
        };

        const renderNoise = (timestamp) => {
            document.querySelectorAll('.camera-noise').forEach(canvas => {
                const cameraId = canvas.dataset.cameraId;
                const camData = cameraId ? this.cameras[cameraId] : null;
                if (!camData?.static_enabled || !camData.static_intensity) return;

                const scope = canvas.dataset.staticScope || 'card';

                // For list cards: draw once (frozen) and skip animation
                if (scope === 'card') {
                    if (canvas.dataset.rendered === '1') return;
                    drawNoiseFrame(canvas);
                    canvas.dataset.rendered = '1';
                    return;
                }

                // Give each canvas its own last-frame timer and fps variation
                if (!canvasTimers.has(canvas)) {
                    canvasTimers.set(canvas, {
                        lastFrame: timestamp - Math.random() * baseInterval,
                        interval: baseInterval + (Math.random() - 0.5) * 40,
                    });
                }

                const timer = canvasTimers.get(canvas);
                if (timestamp - timer.lastFrame < timer.interval) return;
                timer.lastFrame = timestamp;

                drawNoiseFrame(canvas);
            });
            this.staticAnimFrame = requestAnimationFrame(renderNoise);
        };
        this.staticAnimFrame = requestAnimationFrame(renderNoise);
    },

    destroy() {
        if (this._scheduleTimer) clearTimeout(this._scheduleTimer);
        if (this.clockTimer) clearInterval(this.clockTimer);
        if (this.skyTimer) clearInterval(this.skyTimer);
        if (this.weatherTimer) clearInterval(this.weatherTimer);
        if (this.staticAnimFrame) cancelAnimationFrame(this.staticAnimFrame);
        this.stopPopupPolling();
        this.stopRain();
        this.stopSlotSound();
        this.stopWeatherAudio();
        if (this.weatherAudioCtx) {
            this.weatherAudioCtx.close().catch(() => {});
            this.weatherAudioCtx = null;
        }
    },
}));
</script>
@endscript
