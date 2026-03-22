<div class="min-h-screen -mt-16 pt-16 text-white bg-zinc-950"
     x-data="cameraShowFeed({{ $camera->id }})"
     x-init="init()">

    {{-- Header bar --}}
    <div class="bg-zinc-900/80 border-b border-zinc-800">
        <div class="max-w-[1800px] mx-auto px-4 py-2 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('cameras.index') }}" class="text-zinc-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <span class="text-sm uppercase tracking-wider font-semibold text-white" x-text="cameraName"></span>
                <span class="flex items-center gap-1.5"
                    :class="status === 'online' ? 'text-green-400' : 'text-red-400'">
                    <span class="w-2 h-2 rounded-full"
                        :class="status === 'online' ? 'bg-green-400 animate-pulse' : 'bg-red-500'"></span>
                    <span class="text-[10px] uppercase tracking-wider font-bold"
                        x-text="status === 'online' ? 'LIVE' : 'OFFLINE'"></span>
                </span>
                {{-- Weather info --}}
                <span class="hidden sm:flex items-center gap-1.5 text-zinc-400 ml-2" x-show="weatherEnabled && weatherData.weather_code !== undefined" x-cloak>
                    <span class="text-sm" x-text="getWeatherIcon()"></span>
                    <span class="text-[10px] font-mono text-white/60" x-text="weatherData.temperature !== null ? Math.round(weatherData.temperature) + '°C' : ''"></span>
                    <span class="text-[10px] text-zinc-500" x-text="getWeatherDescription()"></span>
                    <span class="text-[10px] text-blue-400/70" x-show="weatherData.rain > 0" x-text="weatherData.rain.toFixed(1) + ' mm/u'"></span>
                </span>
            </div>
            <div class="flex items-center gap-3">
                {{-- Audio toggle --}}
                <button @click="toggleMute()" class="text-zinc-400 hover:text-white transition" title="Audio aan/uit">
                    <svg x-show="!muted" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M17.95 6.05a8 8 0 010 11.9M11 5L6 9H2v6h4l5 4V5z"/>
                    </svg>
                    <svg x-show="muted" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Main content: feed + chat --}}
    <div class="max-w-[1800px] mx-auto flex flex-col lg:flex-row min-h-0" style="height: calc(100vh - 7rem);">

        {{-- Camera Feed --}}
        <div class="flex-1 min-h-0 min-w-0 flex items-center justify-center bg-black p-2 sm:p-4">
          <div class="relative aspect-square w-full max-w-full" style="max-height: calc(100vh - 9rem); max-width: calc(100vh - 9rem);">
            {{-- Sky background layer --}}
            <div class="absolute inset-0 z-[0]" :style="{ backgroundColor: skyColor }">
                <template x-if="backgroundIsVideo && backgroundUrl">
                    <video x-ref="bgVideo" :src="backgroundUrl" autoplay loop muted playsinline
                        class="absolute inset-0 w-full h-full object-cover opacity-90 mix-blend-soft-light"></video>
                </template>
                <template x-if="!backgroundIsVideo && backgroundUrl">
                    <img :src="backgroundUrl" class="absolute inset-0 w-full h-full object-cover opacity-90 mix-blend-soft-light" alt="">
                </template>
            </div>

            {{-- Weather effects --}}
            <div class="absolute inset-0 overflow-hidden pointer-events-none z-[1]" x-show="weatherEnabled" x-cloak>
                {{-- Clouds --}}
                <div class="absolute inset-0" :style="{ opacity: cloudOpacity, transition: 'opacity 5s ease' }">
                    <template x-for="cloud in clouds" :key="cloud.id">
                        <div class="cloud-shape absolute" :style="{
                            top: cloud.top + '%',
                            width: cloud.width + 'px',
                            height: cloud.height + 'px',
                            filter: 'blur(' + cloud.blur + 'px)',
                            animation: 'cloud-drift-show ' + cloud.speed + 's linear infinite',
                            animationDelay: cloud.delay + 's',
                            opacity: cloud.opacity,
                        }">
                            <div class="absolute rounded-full" :style="{ width: '60%', height: '70%', bottom: '0', left: '10%', backgroundColor: scaleCloudAlpha(cloudColor, 0.4) }"></div>
                            <div class="absolute rounded-full" :style="{ width: '50%', height: '85%', bottom: '5%', left: '25%', backgroundColor: scaleCloudAlpha(cloudColor, 0.5) }"></div>
                            <div class="absolute rounded-full" :style="{ width: '45%', height: '65%', bottom: '0', left: '50%', backgroundColor: scaleCloudAlpha(cloudColor, 0.35) }"></div>
                            <div class="absolute rounded-full" :style="{ width: '35%', height: '50%', bottom: '10%', left: '5%', backgroundColor: scaleCloudAlpha(cloudColor, 0.3) }"></div>
                            <div class="absolute rounded-full" :style="{ width: '30%', height: '45%', bottom: '5%', left: '60%', backgroundColor: scaleCloudAlpha(cloudColor, 0.25) }"></div>
                        </div>
                    </template>
                </div>
                {{-- Rain canvas --}}
                <canvas class="absolute inset-0 w-full h-full rain-canvas"></canvas>
            </div>

            {{-- Video element --}}
            <video x-ref="mainVideo" autoplay playsinline muted
                class="absolute inset-0 z-[2] w-full h-full object-cover"
                x-show="status === 'online' && videoUrl"
                style="display: none;"></video>

            {{-- Audio elements --}}
            <audio x-ref="mainAudio" preload="none" style="display: none;"></audio>
            <audio x-ref="slotSound" preload="none" loop style="display: none;"></audio>

            {{-- Offline fallback --}}
            <div x-show="status !== 'online' || !videoUrl"
                class="absolute inset-0 z-[4] flex flex-col items-center justify-center bg-zinc-900/80">
                <svg class="w-16 h-16 text-zinc-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    <line x1="3" y1="21" x2="21" y2="3" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <p class="text-zinc-500 text-sm uppercase tracking-wider font-semibold">Camera offline</p>
                <p class="text-zinc-600 text-xs uppercase tracking-wider">Geen signaal</p>
            </div>

            {{-- Color overlay --}}
            <div class="absolute inset-0 z-[3] pointer-events-none" :style="{ backgroundColor: overlayColor }"></div>

            {{-- Static effect --}}
            <div class="absolute inset-0 pointer-events-none z-[5]" x-show="staticEnabled" x-cloak>
                <div class="camera-scanlines absolute inset-0" :style="{ opacity: staticIntensity / 200 }"></div>
                <canvas class="camera-noise absolute inset-0 w-full h-full" :data-camera-id="cameraId" data-static-scope="popup" :style="{ opacity: staticIntensity / 250 }"></canvas>
            </div>

            {{-- Timestamp --}}
            <div class="absolute bottom-2 right-3 text-[10px] font-mono text-white/40 tracking-wider z-[6]"
                x-text="timestampText"></div>

            {{-- REC --}}
            <div class="absolute top-3 left-3 flex items-center gap-1.5 z-[6]"
                x-show="status === 'online'">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                <span class="text-[10px] font-bold text-red-500/80 tracking-wider">REC</span>
            </div>
          </div>
        </div>

        {{-- Chat Panel --}}
        <div class="w-full lg:w-80 xl:w-96 shrink-0 border-t lg:border-t-0 lg:border-l border-zinc-800 flex flex-col max-h-[40vh] lg:max-h-none">
            @livewire('cameras.live-chat', ['camera' => $camera])
        </div>
    </div>

    {{-- Description --}}
    @if ($camera->description)
        <div class="max-w-[1800px] mx-auto border-t border-zinc-800 px-4 py-4">
            <div class="prose prose-invert prose-sm prose-zinc font-description max-w-none content-block-text">
                {!! $camera->description !!}
            </div>
        </div>
    @endif

    <style>
        @keyframes cloud-drift-show {
            from { transform: translateX(-150%); }
            to { transform: translateX(calc(100vw + 100%)); }
        }
        .cloud-shape { will-change: transform; }
        .camera-scanlines {
            background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255, 255, 255, 0.03) 2px, rgba(255, 255, 255, 0.03) 4px);
            mix-blend-mode: overlay;
        }
        .camera-noise { mix-blend-mode: overlay; }
    </style>
</div>

@script
<script>
Alpine.data('cameraShowFeed', (targetCameraId) => ({
    cameraId: targetCameraId,
    cameraName: @js($camera->name),
    status: 'loading',
    videoUrl: '',
    audioUrl: '',
    backgroundUrl: @js($camera->backgroundUrl()),
    backgroundIsVideo: @js($camera->backgroundIsVideo()),
    staticEnabled: @js($camera->static_enabled),
    staticIntensity: @js($camera->static_intensity ?? 15),
    muted: true,
    timestampText: '',
    currentSlotSoundUrl: null,

    // Sky color system
    skyColor: '#0B1026',
    overlayColor: 'rgba(0,0,0,0)',
    cloudColor: 'rgba(255, 255, 255, 0.4)',
    slotKeyframes: [],

    // Weather
    weatherEnabled: false,
    rainMode: 'automatic',
    manualRainIntensity: 50,
    manualCloudCover: 50,
    manualWindSpeed: 50,
    weatherData: { cloud_cover: 0, rain: 0, wind_speed: 0, temperature: null, weather_code: undefined },
    cloudOpacity: 0,
    clouds: [],
    slotsData: {},

    // Internal timers
    _clockTimer: null,
    _skyTimer: null,
    _scheduleTimer: null,
    _scheduleLoading: false,
    _pollTimer: null,
    _weatherTimer: null,
    _staticAnimFrame: null,
    _rainAnimFrame: null,
    _weatherAudioInterval: null,

    // Weather audio
    weatherAudioCtx: null,
    rainGainNode: null,
    windGainNode: null,
    rainSourceNode: null,
    windSourceNode: null,

    async init() {
        this.updateTimestamp();
        this._clockTimer = setInterval(() => this.updateTimestamp(), 1000);
        this.generateClouds();
        await this.loadSchedule();
        this._skyTimer = setInterval(() => this.updateSkyColor(), 60000);
        this.startStaticNoise();
        this.startRain();
        // Poll schedule every 10s for live updates
        this._pollTimer = setInterval(() => this.loadSchedule(), 10000);
    },

    generateClouds() {
        this.clouds = [];
        for (let i = 0; i < 8; i++) {
            this.clouds.push({
                id: i,
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

    updateTimestamp() {
        const now = new Date();
        this.timestampText = now.toLocaleString('nl-BE', {
            timeZone: 'Europe/Brussels',
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
        });
    },

    // ─── Schedule ───

    async loadSchedule() {
        if (this._scheduleLoading) return;
        this._scheduleLoading = true;

        try {
            const res = await fetch('/api/cameras/schedule');
            if (!res.ok) throw new Error('API ' + res.status);
            const data = await res.json();

            if (data.slots) {
                this.slotsData = data.slots;
                this.slotKeyframes = this.buildKeyframes(data.slots);
                this.updateSkyColor();
            }

            this.weatherEnabled = data.weather_enabled ?? false;
            this.rainMode = data.rain_mode ?? 'automatic';
            this.manualRainIntensity = data.manual_rain_intensity ?? 50;
            this.manualCloudCover = data.manual_cloud_cover ?? 50;
            this.manualWindSpeed = data.manual_wind_speed ?? 50;

            if (this.weatherEnabled && !this._weatherTimer) {
                await this.fetchWeather();
                this._weatherTimer = setInterval(() => this.fetchWeather(), 600000);
            } else if (!this.weatherEnabled && this._weatherTimer) {
                clearInterval(this._weatherTimer);
                this._weatherTimer = null;
                this.cloudOpacity = 0;
            }

            const cam = data.cameras.find(c => c.id === this.cameraId);
            if (cam) {
                const videoChanged = this.videoUrl !== (cam.video_url ?? '');
                const audioChanged = this.audioUrl !== (cam.audio_url ?? '');
                const soundChanged = this.currentSlotSoundUrl !== (cam.default_sound_url ?? null);

                this.status = cam.status;
                this.videoUrl = cam.video_url ?? '';
                this.audioUrl = cam.audio_url ?? '';
                this.backgroundUrl = cam.background_url ?? this.backgroundUrl;
                this.backgroundIsVideo = cam.background_is_video ?? this.backgroundIsVideo;
                this.staticEnabled = cam.static_enabled ?? false;
                this.staticIntensity = cam.static_intensity ?? 15;

                if (videoChanged || audioChanged) {
                    this.$nextTick(() => this.loadVideo(cam));
                }
                if (soundChanged) {
                    this.updateSlotSound(cam.default_sound_url ?? null);
                }
            }
        } catch (e) {
            // Retry silently
        } finally {
            this._scheduleLoading = false;
        }
    },

    loadVideo(cam) {
        const videoEl = this.$refs.mainVideo;
        const audioEl = this.$refs.mainAudio;
        const isRealtime = (cam.behaviour_type ?? 'loop') === 'realtime';
        const offset = cam.video_start_offset_seconds ?? 0;

        if (videoEl) {
            if (cam.video_url) {
                videoEl.loop = !isRealtime;
                videoEl.muted = true;
                videoEl.src = cam.video_url;
                if (isRealtime && offset > 0) {
                    videoEl.addEventListener('loadedmetadata', () => {
                        videoEl.currentTime = Math.min(offset, Math.max(0, videoEl.duration - 0.5));
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
            if (cam.audio_url) {
                audioEl.loop = !isRealtime;
                audioEl.src = cam.audio_url;
                audioEl.load();
                if (isRealtime && offset > 0) {
                    audioEl.addEventListener('loadedmetadata', () => {
                        audioEl.currentTime = Math.min(offset, Math.max(0, audioEl.duration - 0.5));
                        if (!this.muted) audioEl.play().catch(() => {});
                    }, { once: true });
                } else if (!this.muted) {
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

    // ─── Audio ───

    toggleMute() {
        this.muted = !this.muted;
        const audioEl = this.$refs.mainAudio;
        if (audioEl && audioEl.src) {
            this.muted ? audioEl.pause() : audioEl.play().catch(() => {});
        }
        const slotSoundEl = this.$refs.slotSound;
        if (slotSoundEl && slotSoundEl.src) {
            this.muted ? slotSoundEl.pause() : slotSoundEl.play().catch(() => {});
        }
        this.muted ? this.stopWeatherAudio() : this.startWeatherAudio();
    },

    updateSlotSound(soundUrl) {
        const slotSoundEl = this.$refs.slotSound;
        if (!slotSoundEl) return;

        if (soundUrl !== this.currentSlotSoundUrl) {
            this.currentSlotSoundUrl = soundUrl;
            if (soundUrl) {
                slotSoundEl.src = soundUrl;
                slotSoundEl.load();
                if (!this.muted) slotSoundEl.play().catch(() => {});
            } else {
                slotSoundEl.pause();
                slotSoundEl.removeAttribute('src');
                slotSoundEl.load();
            }
        }
    },

    // ─── Weather Audio ───

    initWeatherAudio() {
        if (this.weatherAudioCtx) return;
        this.weatherAudioCtx = new (window.AudioContext || window.webkitAudioContext)();

        // Rain: brown noise
        const rainBufSize = this.weatherAudioCtx.sampleRate * 2;
        const rainBuf = this.weatherAudioCtx.createBuffer(1, rainBufSize, this.weatherAudioCtx.sampleRate);
        const rainData = rainBuf.getChannelData(0);
        let lastOut = 0;
        for (let i = 0; i < rainBufSize; i++) {
            const white = Math.random() * 2 - 1;
            rainData[i] = (lastOut + (0.02 * white)) / 1.02;
            lastOut = rainData[i];
            rainData[i] *= 3.5;
        }
        this.rainSourceNode = this.weatherAudioCtx.createBufferSource();
        this.rainSourceNode.buffer = rainBuf;
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

        // Wind: bandpass noise
        const windBufSize = this.weatherAudioCtx.sampleRate * 3;
        const windBuf = this.weatherAudioCtx.createBuffer(1, windBufSize, this.weatherAudioCtx.sampleRate);
        const windData = windBuf.getChannelData(0);
        let b0 = 0, b1 = 0, b2 = 0;
        for (let i = 0; i < windBufSize; i++) {
            const white = Math.random() * 2 - 1;
            b0 = 0.99765 * b0 + white * 0.0990460;
            b1 = 0.96300 * b1 + white * 0.2965164;
            b2 = 0.57000 * b2 + white * 1.0526913;
            windData[i] = (b0 + b1 + b2 + white * 0.1848) * 0.25;
        }
        this.windSourceNode = this.weatherAudioCtx.createBufferSource();
        this.windSourceNode.buffer = windBuf;
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
        if (!this.weatherAudioCtx || this.muted) return;

        const slot = this.getCurrentSlot();
        const rainEnabled = slot?.rain_enabled ?? false;
        const windEnabled = slot?.wind_enabled ?? false;

        const cameraRainVol = ({{ $camera->rain_volume ?? 50 }}) / 100;
        const cameraWindVol = ({{ $camera->wind_volume ?? 50 }}) / 100;

        const rainIntensity = this.weatherEnabled ? Math.min((this.weatherData.rain || 0) / 5, 1) : 0;
        const windIntensity = this.weatherEnabled ? Math.min((this.weatherData.wind_speed || 0) / 40, 1) : 0;

        const rainVol = rainEnabled ? cameraRainVol * Math.max(rainIntensity, 0.05) : 0;
        const windVol = windEnabled ? cameraWindVol * Math.max(windIntensity, 0.1) : 0;

        const t = this.weatherAudioCtx.currentTime;
        this.rainGainNode.gain.linearRampToValueAtTime(rainVol * 0.6, t + 1);
        this.windGainNode.gain.linearRampToValueAtTime(windVol * 0.5, t + 1);
    },

    startWeatherAudio() {
        this.initWeatherAudio();
        if (this.weatherAudioCtx.state === 'suspended') this.weatherAudioCtx.resume();
        this.updateWeatherAudioVolumes();
        if (!this._weatherAudioInterval) {
            this._weatherAudioInterval = setInterval(() => this.updateWeatherAudioVolumes(), 5000);
        }
    },

    stopWeatherAudio() {
        if (this._weatherAudioInterval) { clearInterval(this._weatherAudioInterval); this._weatherAudioInterval = null; }
        if (this.weatherAudioCtx && this.rainGainNode && this.windGainNode) {
            const t = this.weatherAudioCtx.currentTime;
            this.rainGainNode.gain.linearRampToValueAtTime(0, t + 0.3);
            this.windGainNode.gain.linearRampToValueAtTime(0, t + 0.3);
        }
    },

    // ─── Weather ───

    async fetchWeather() {
        if (!this.weatherEnabled) { this.cloudOpacity = 0; return; }

        if (this.rainMode === 'manual') {
            this.weatherData = {
                cloud_cover: this.manualCloudCover,
                rain: (this.manualRainIntensity / 100) * 10,
                wind_speed: (this.manualWindSpeed / 100) * 50,
                temperature: this.weatherData.temperature,
                weather_code: this.manualRainIntensity > 60 ? 65 : (this.manualRainIntensity > 20 ? 61 : (this.manualRainIntensity > 0 ? 51 : 0)),
            };
            this.cloudOpacity = this.weatherData.cloud_cover / 100;
            return;
        }

        try {
            const res = await fetch('https://api.open-meteo.com/v1/forecast?latitude=50.8278&longitude=3.2644&current=cloud_cover,rain,showers,precipitation,temperature_2m,weather_code,wind_speed_10m');
            if (!res.ok) return;
            const data = await res.json();
            const totalRain = Math.max(data.current?.precipitation ?? 0, (data.current?.rain ?? 0) + (data.current?.showers ?? 0));
            this.weatherData = {
                cloud_cover: data.current?.cloud_cover ?? 0,
                rain: totalRain,
                wind_speed: data.current?.wind_speed_10m ?? 0,
                temperature: data.current?.temperature_2m ?? null,
                weather_code: data.current?.weather_code ?? 0,
            };
            this.cloudOpacity = this.weatherData.cloud_cover / 100;
        } catch (e) {}
    },

    getCurrentSlot() {
        const now = new Date();
        const parts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Brussels', hour: 'numeric', minute: 'numeric', hour12: false }).formatToParts(now);
        const h = parseInt(parts.find(p => p.type === 'hour').value) || 0;
        const m = parseInt(parts.find(p => p.type === 'minute').value) || 0;
        const currentMin = h * 60 + m;

        for (const [key, slot] of Object.entries(this.slotsData)) {
            const startMin = this.timeToMin(slot.start);
            const endMin = slot.end === '24:00' ? 1440 : this.timeToMin(slot.end);
            if (endMin > startMin) { if (currentMin >= startMin && currentMin < endMin) return slot; }
            else { if (currentMin >= startMin || currentMin < endMin) return slot; }
        }
        return null;
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
        const d = { 0:'Helder', 1:'Licht bewolkt', 2:'Half bewolkt', 3:'Bewolkt', 45:'Mistig', 48:'Rijpmist', 51:'Lichte motregen', 53:'Motregen', 55:'Zware motregen', 61:'Lichte regen', 63:'Regen', 65:'Zware regen', 71:'Lichte sneeuw', 73:'Sneeuw', 75:'Zware sneeuw', 80:'Lichte buien', 81:'Buien', 82:'Zware buien', 95:'Onweer', 96:'Onweer met hagel', 99:'Zwaar onweer met hagel' };
        return d[code] ?? 'Bewolkt';
    },

    // ─── Sky Color ───

    buildKeyframes(slots) {
        const keyframes = [];
        const slotArr = Object.values(slots);
        const count = slotArr.length;
        for (let i = 0; i < count; i++) {
            const slot = slotArr[i];
            const startMin = this.timeToMin(slot.start);
            const endMin = slot.end === '24:00' ? 1440 : this.timeToMin(slot.end);
            if (slot.is_transition) {
                const prev = slotArr[(i - 1 + count) % count];
                const next = slotArr[(i + 1) % count];
                let midpoint;
                if (endMin > startMin) { midpoint = (startMin + endMin) / 2; }
                else { const dur = (1440 - startMin) + endMin; midpoint = (startMin + dur / 2) % 1440; }
                keyframes.push({ minutes: startMin, bgColor: prev.bg_color || '#000000', overlayColor: prev.overlay_color || '#00000000', cloudColor: prev.cloud_color || '#FFFFFF66' });
                keyframes.push({ minutes: midpoint, bgColor: slot.bg_color || '#000000', overlayColor: slot.overlay_color || '#00000000', cloudColor: slot.cloud_color || '#FFFFFF66' });
                keyframes.push({ minutes: endMin >= 1440 ? 1439.99 : endMin, bgColor: next.bg_color || '#000000', overlayColor: next.overlay_color || '#00000000', cloudColor: next.cloud_color || '#FFFFFF66' });
            } else {
                keyframes.push({ minutes: startMin, bgColor: slot.bg_color || '#000000', overlayColor: slot.overlay_color || '#00000000', cloudColor: slot.cloud_color || '#FFFFFF66' });
                keyframes.push({ minutes: endMin >= 1440 ? 1439.99 : endMin, bgColor: slot.bg_color || '#000000', overlayColor: slot.overlay_color || '#00000000', cloudColor: slot.cloud_color || '#FFFFFF66' });
            }
        }
        keyframes.sort((a, b) => a.minutes - b.minutes);
        return keyframes;
    },

    timeToMin(t) { const [h, m] = t.split(':').map(Number); return h * 60 + m; },

    updateSkyColor() {
        if (this.slotKeyframes.length === 0) return;
        const now = new Date();
        const parts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Brussels', hour: 'numeric', minute: 'numeric', hour12: false }).formatToParts(now);
        const h = parseInt(parts.find(p => p.type === 'hour').value) || 0;
        const m = parseInt(parts.find(p => p.type === 'minute').value) || 0;
        const currentMin = h * 60 + m;
        const kf = this.slotKeyframes;
        let prevKf, nextKf, t;
        let nextIdx = kf.findIndex(k => k.minutes > currentMin);
        if (nextIdx === -1) { prevKf = kf[kf.length - 1]; nextKf = kf[0]; const gap = (nextKf.minutes + 1440) - prevKf.minutes; t = gap > 0 ? (currentMin - prevKf.minutes) / gap : 0; }
        else if (nextIdx === 0) { prevKf = kf[kf.length - 1]; nextKf = kf[0]; const gap = (nextKf.minutes + 1440) - prevKf.minutes; t = gap > 0 ? ((currentMin + 1440) - prevKf.minutes) / gap : 0; }
        else { prevKf = kf[nextIdx - 1]; nextKf = kf[nextIdx]; const gap = nextKf.minutes - prevKf.minutes; t = gap > 0 ? (currentMin - prevKf.minutes) / gap : 0; }
        t = Math.max(0, Math.min(1, t));
        this.skyColor = this.lerpHex(prevKf.bgColor, nextKf.bgColor, t);
        this.overlayColor = this.lerpHexAlpha(prevKf.overlayColor, nextKf.overlayColor, t);
        this.cloudColor = this.lerpHexAlpha(prevKf.cloudColor, nextKf.cloudColor, t);
    },

    lerpHex(c1, c2, t) {
        const r1 = parseInt(c1.slice(1,3),16), g1 = parseInt(c1.slice(3,5),16), b1 = parseInt(c1.slice(5,7),16);
        const r2 = parseInt(c2.slice(1,3),16), g2 = parseInt(c2.slice(3,5),16), b2 = parseInt(c2.slice(5,7),16);
        return '#' + [Math.round(r1+(r2-r1)*t), Math.round(g1+(g2-g1)*t), Math.round(b1+(b2-b1)*t)].map(v => v.toString(16).padStart(2,'0')).join('');
    },

    lerpHexAlpha(c1, c2, t) {
        const r1 = parseInt(c1.slice(1,3),16), g1 = parseInt(c1.slice(3,5),16), b1 = parseInt(c1.slice(5,7),16);
        const a1 = c1.length > 7 ? parseInt(c1.slice(7,9),16)/255 : 1;
        const r2 = parseInt(c2.slice(1,3),16), g2 = parseInt(c2.slice(3,5),16), b2 = parseInt(c2.slice(5,7),16);
        const a2 = c2.length > 7 ? parseInt(c2.slice(7,9),16)/255 : 1;
        return `rgba(${Math.round(r1+(r2-r1)*t)}, ${Math.round(g1+(g2-g1)*t)}, ${Math.round(b1+(b2-b1)*t)}, ${(a1+(a2-a1)*t).toFixed(3)})`;
    },

    scaleCloudAlpha(rgbaStr, scale) {
        const m = rgbaStr.match(/rgba?\((\d+),\s*(\d+),\s*(\d+),?\s*([\d.]+)?\)/);
        if (!m) return rgbaStr;
        let r = parseInt(m[1]), g = parseInt(m[2]), b = parseInt(m[3]);
        const a = parseFloat(m[4] ?? 1) * scale;
        const rain = this.weatherData.rain || 0;
        if (rain > 0) { const f = Math.min(rain/10,1); const d = 0.10 + f * 0.50; r = Math.round(r*(1-d)); g = Math.round(g*(1-d)); b = Math.round(b*(1-d)); }
        return `rgba(${r}, ${g}, ${b}, ${a.toFixed(3)})`;
    },

    // ─── Rain ───

    startRain() {
        if (this._rainAnimFrame) return;
        const renderRain = () => {
            const rain = this.weatherData.rain || 0;
            if (!this.weatherEnabled || rain <= 0) {
                document.querySelectorAll('.rain-canvas').forEach(c => { const ctx = c.getContext('2d'); if (ctx) ctx.clearRect(0, 0, c.width, c.height); });
                this._rainAnimFrame = requestAnimationFrame(renderRain);
                return;
            }
            const intensity = Math.min(rain / 5, 1);
            const dropsPerFrame = Math.ceil(2 + intensity * 14);
            const maxDrops = Math.ceil(40 + intensity * 360);
            const windAngle = 0.15;
            document.querySelectorAll('.rain-canvas').forEach(canvas => {
                const w = canvas.offsetWidth, h = canvas.offsetHeight;
                if (w === 0 || h === 0) return;
                canvas.width = w; canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, w, h);
                if (!canvas._drops) canvas._drops = [];
                const drops = canvas._drops;
                for (let i = 0; i < dropsPerFrame && drops.length < maxDrops; i++) {
                    drops.push({ x: Math.random()*(w+40)-20, y: -10-Math.random()*30, speed: 18+Math.random()*18+intensity*15, length: 18+Math.random()*22+intensity*15, opacity: 0.5+Math.random()*0.3+intensity*0.2, width: 1.5+Math.random()*1.5 });
                }
                ctx.lineCap = 'round';
                for (let i = drops.length-1; i >= 0; i--) {
                    const d = drops[i];
                    ctx.beginPath(); ctx.moveTo(d.x, d.y); ctx.lineTo(d.x+windAngle*d.length, d.y+d.length);
                    ctx.strokeStyle = `rgba(200,220,255,${d.opacity})`; ctx.lineWidth = d.width; ctx.stroke();
                    d.y += d.speed; d.x += windAngle * d.speed;
                    if (d.y > h) {
                        if (intensity > 0.2) { ctx.beginPath(); ctx.arc(d.x, h-1, 1.5+Math.random()*2.5, 0, Math.PI, true); ctx.strokeStyle = `rgba(200,220,255,${d.opacity*0.6})`; ctx.lineWidth = 0.8; ctx.stroke(); }
                        drops.splice(i, 1);
                    }
                }
            });
            this._rainAnimFrame = requestAnimationFrame(renderRain);
        };
        this._rainAnimFrame = requestAnimationFrame(renderRain);
    },

    stopRain() {
        if (this._rainAnimFrame) { cancelAnimationFrame(this._rainAnimFrame); this._rainAnimFrame = null; }
        document.querySelectorAll('.rain-canvas').forEach(c => { c._drops = []; const ctx = c.getContext('2d'); if (ctx) ctx.clearRect(0, 0, c.width, c.height); });
    },

    // ─── Static Noise ───

    startStaticNoise() {
        const baseInterval = 1000 / 8;
        const canvasTimers = new WeakMap();
        const drawFrame = (canvas) => {
            const w = canvas.offsetWidth, h = canvas.offsetHeight;
            if (w === 0 || h === 0) return;
            const scale = 4, sw = Math.ceil(w/scale), sh = Math.ceil(h/scale);
            canvas.width = sw; canvas.height = sh;
            canvas.style.imageRendering = 'pixelated';
            const ctx = canvas.getContext('2d');
            const img = ctx.createImageData(sw, sh);
            const d = img.data;
            for (let i = 0; i < d.length; i += 4) { const v = Math.random()*255; d[i]=v; d[i+1]=v; d[i+2]=v; d[i+3]=Math.random()*60; }
            ctx.putImageData(img, 0, 0);
        };
        const render = (ts) => {
            document.querySelectorAll('.camera-noise').forEach(canvas => {
                if (!this.staticEnabled || !this.staticIntensity) return;
                if (!canvasTimers.has(canvas)) canvasTimers.set(canvas, { lastFrame: ts - Math.random()*baseInterval, interval: baseInterval + (Math.random()-0.5)*40 });
                const timer = canvasTimers.get(canvas);
                if (ts - timer.lastFrame < timer.interval) return;
                timer.lastFrame = ts;
                drawFrame(canvas);
            });
            this._staticAnimFrame = requestAnimationFrame(render);
        };
        this._staticAnimFrame = requestAnimationFrame(render);
    },

    destroy() {
        if (this._scheduleTimer) clearTimeout(this._scheduleTimer);
        if (this._clockTimer) clearInterval(this._clockTimer);
        if (this._skyTimer) clearInterval(this._skyTimer);
        if (this._weatherTimer) clearInterval(this._weatherTimer);
        if (this._pollTimer) clearInterval(this._pollTimer);
        if (this._staticAnimFrame) cancelAnimationFrame(this._staticAnimFrame);
        this.stopRain();
        this.stopWeatherAudio();
        if (this.weatherAudioCtx) { this.weatherAudioCtx.close().catch(() => {}); this.weatherAudioCtx = null; }
    },
}));
</script>
@endscript
