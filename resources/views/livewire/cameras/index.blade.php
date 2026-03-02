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

                            {{-- Weather effects --}}
                            <div class="absolute inset-0 overflow-hidden pointer-events-none z-[1]" x-show="weatherEnabled" x-cloak>
                                {{-- Clouds --}}
                                <div class="absolute inset-0" :style="{ opacity: cloudOpacity, transition: 'opacity 5s ease' }">
                                    <template x-for="cloud in cardClouds" :key="cloud.id">
                                        <div class="cloud-shape absolute" :style="{
                                            top: cloud.top + '%',
                                            width: cloud.width + 'px',
                                            height: cloud.height + 'px',
                                            filter: 'blur(' + cloud.blur + 'px)',
                                            animation: 'cloud-drift-card ' + cloud.speed + 's linear infinite',
                                            animationDelay: cloud.delay + 's',
                                            opacity: cloud.opacity,
                                        }">
                                            <div class="absolute rounded-full bg-white/40" :style="{
                                                width: '60%', height: '70%', bottom: '0', left: '10%',
                                            }"></div>
                                            <div class="absolute rounded-full bg-white/50" :style="{
                                                width: '50%', height: '85%', bottom: '5%', left: '25%',
                                            }"></div>
                                            <div class="absolute rounded-full bg-white/35" :style="{
                                                width: '45%', height: '65%', bottom: '0', left: '50%',
                                            }"></div>
                                            <div class="absolute rounded-full bg-white/30" :style="{
                                                width: '35%', height: '50%', bottom: '10%', left: '5%',
                                            }"></div>
                                            <div class="absolute rounded-full bg-white/25" :style="{
                                                width: '30%', height: '45%', bottom: '5%', left: '60%',
                                            }"></div>
                                        </div>
                                    </template>
                                </div>
                                {{-- Rain canvas --}}
                                <canvas class="absolute inset-0 w-full h-full rain-canvas" data-type="card"></canvas>
                            </div>

                            {{-- Video (first frame only, absolute so it composites over sky bg) --}}
                            <video
                                x-ref="cam{{ $camera->id }}"
                                muted playsinline preload="auto"
                                class="absolute inset-0 w-full h-full object-cover z-[2]"
                                x-show="getCameraStatus({{ $camera->id }}) === 'online' && getCameraVideoUrl({{ $camera->id }})"
                                style="display: none;"
                            ></video>

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
                            <div class="absolute inset-0 pointer-events-none z-[5]" x-show="staticEnabled" x-cloak>
                                <div class="camera-scanlines absolute inset-0" :style="{ opacity: staticIntensity / 200 }"></div>
                                <canvas class="camera-noise absolute inset-0 w-full h-full" :style="{ opacity: staticIntensity / 250 }"></canvas>
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
                                                <div class="absolute rounded-full bg-white/40" :style="{
                                                    width: '60%', height: '70%', bottom: '0', left: '10%',
                                                }"></div>
                                                <div class="absolute rounded-full bg-white/50" :style="{
                                                    width: '50%', height: '85%', bottom: '5%', left: '25%',
                                                }"></div>
                                                <div class="absolute rounded-full bg-white/35" :style="{
                                                    width: '45%', height: '65%', bottom: '0', left: '50%',
                                                }"></div>
                                                <div class="absolute rounded-full bg-white/30" :style="{
                                                    width: '35%', height: '50%', bottom: '10%', left: '5%',
                                                }"></div>
                                                <div class="absolute rounded-full bg-white/25" :style="{
                                                    width: '30%', height: '45%', bottom: '5%', left: '60%',
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
                                    autoplay loop playsinline muted
                                    class="relative z-[2] w-full"
                                    x-show="popup.id && getCameraStatus(popup.id) === 'online' && getCameraVideoUrl(popup.id)"
                                    style="display: none; max-height: 70vh;"
                                ></video>

                                {{-- Separate audio element --}}
                                <audio x-ref="popupAudio" loop preload="none" style="display: none;"></audio>

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
                                <div class="absolute inset-0 pointer-events-none z-[5]" x-show="staticEnabled" x-cloak>
                                    <div class="camera-scanlines absolute inset-0" :style="{ opacity: staticIntensity / 200 }"></div>
                                    <canvas class="camera-noise absolute inset-0 w-full h-full" :style="{ opacity: staticIntensity / 250 }"></canvas>
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
    timers: {},
    clockTimer: null,
    skyTimer: null,
    timestampText: '',
    popup: { open: false, id: null, name: '', muted: true },

    // Sky color system
    skyColor: '#0B1026',
    overlayColor: 'rgba(0,0,0,0)',
    slotKeyframes: [],

    // Static/noise system
    staticEnabled: false,
    staticIntensity: 15,
    staticAnimFrame: null,

    // Weather system
    weatherEnabled: false,
    weatherData: { cloud_cover: 0, rain: 0, temperature: null, weather_code: undefined },
    cloudOpacity: 0,
    cardClouds: [],
    popupClouds: [],
    weatherTimer: null,
    rainAnimFrame: null,
    rainDrops: [],
    popupPollTimer: null,

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
            const res = await fetch('https://api.open-meteo.com/v1/forecast?latitude=50.8278&longitude=3.2644&current=cloud_cover,rain,temperature_2m,weather_code');
            if (!res.ok) return;
            const data = await res.json();
            this.weatherData = {
                cloud_cover: data.current?.cloud_cover ?? 0,
                rain: data.current?.rain ?? 0,
                temperature: data.current?.temperature_2m ?? null,
                weather_code: data.current?.weather_code ?? 0,
            };
            this.cloudOpacity = this.weatherData.cloud_cover / 100;
        } catch (e) {
            // Silently fail — weather is cosmetic
        }
    },

    async loadSchedule() {
        try {
            const res = await fetch('/api/cameras/schedule');
            if (!res.ok) throw new Error('API returned ' + res.status);
            const data = await res.json();

            // Process slot data for sky colors
            if (data.slots) {
                this.slotKeyframes = this.buildKeyframes(data.slots);
                this.updateSkyColor();
            }

            // Static effect settings
            this.staticEnabled = data.static_enabled ?? false;
            this.staticIntensity = data.static_intensity ?? 15;

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

            data.cameras.forEach(cam => {
                const prev = this.cameras[cam.id];
                const videoChanged = !prev || prev.video_url !== cam.video_url;
                const audioChanged = !prev || prev.audio_url !== cam.audio_url;

                this.cameras[cam.id] = { ...cam };

                if (videoChanged || audioChanged) {
                    this.$nextTick(() => {
                        // Update grid card thumbnail
                        const videoEl = this.$refs['cam' + cam.id];
                        if (videoEl) {
                            if (cam.status === 'online' && cam.video_url) {
                                videoEl.src = cam.video_url;
                                videoEl.load();
                                videoEl.addEventListener('loadeddata', () => {
                                    videoEl.currentTime = 0.001;
                                }, { once: true });
                            } else {
                                videoEl.removeAttribute('src');
                                videoEl.load();
                            }
                        }

                        // Update popup if it's showing this camera
                        if (this.popup.open && this.popup.id === cam.id) {
                            this.loadPopupVideo();
                        }
                    });
                }

                // Schedule next check based on when the current block ends
                if (this.timers[cam.id]) clearTimeout(this.timers[cam.id]);
                const checkIn = Math.max(5, cam.next_check_seconds) * 1000;
                this.timers[cam.id] = setTimeout(() => this.loadSchedule(), checkIn);
            });
        } catch (e) {
            setTimeout(() => this.loadSchedule(), 30000);
        }
    },

    buildKeyframes(slots) {
        const keyframes = [];
        for (const [key, slot] of Object.entries(slots)) {
            const startMin = this.timeToMin(slot.start);
            const endMin = slot.end === '24:00' ? 1440 : this.timeToMin(slot.end);

            let midpoint;
            if (endMin > startMin) {
                midpoint = (startMin + endMin) / 2;
            } else {
                const totalDuration = (1440 - startMin) + endMin;
                midpoint = (startMin + totalDuration / 2) % 1440;
            }

            keyframes.push({
                minutes: midpoint,
                bgColor: slot.bg_color || '#000000',
                overlayColor: slot.overlay_color || '#00000000',
            });
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

        this.$nextTick(() => this.loadPopupVideo());
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
        const videoUrl = this.getCameraVideoUrl(this.popup.id);
        const audioUrl = this.getCameraAudioUrl(this.popup.id);
        const videoEl = this.$refs.popupVideo;
        const audioEl = this.$refs.popupAudio;

        if (videoEl) {
            if (videoUrl) {
                videoEl.src = videoUrl;
                videoEl.muted = true;
                videoEl.load();
                videoEl.play().catch(() => {});
            } else {
                videoEl.pause();
                videoEl.removeAttribute('src');
                videoEl.load();
            }
        }

        if (audioEl) {
            if (audioUrl) {
                audioEl.src = audioUrl;
                audioEl.load();
                if (!this.popup.muted) {
                    audioEl.play().catch(() => {});
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

    // Static noise canvas animation
    startStaticNoise() {
        let lastFrame = 0;
        const fps = 8; // Low FPS for authentic static look
        const interval = 1000 / fps;

        const renderNoise = (timestamp) => {
            if (timestamp - lastFrame >= interval) {
                lastFrame = timestamp;
                if (this.staticEnabled && this.staticIntensity > 0) {
                    document.querySelectorAll('.camera-noise').forEach(canvas => {
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
                    });
                }
            }
            this.staticAnimFrame = requestAnimationFrame(renderNoise);
        };
        this.staticAnimFrame = requestAnimationFrame(renderNoise);
    },

    destroy() {
        Object.values(this.timers).forEach(t => clearTimeout(t));
        if (this.clockTimer) clearInterval(this.clockTimer);
        if (this.skyTimer) clearInterval(this.skyTimer);
        if (this.weatherTimer) clearInterval(this.weatherTimer);
        if (this.staticAnimFrame) cancelAnimationFrame(this.staticAnimFrame);
        this.stopPopupPolling();
        this.stopRain();
    },
}));
</script>
@endscript
