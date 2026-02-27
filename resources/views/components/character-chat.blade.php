<div
    x-data="{
        open: false,
        characters: [],
        activeCharacterId: null,
        messages: [],
        input: '',
        loading: false,
        sending: false,
        blocked: false,
        blockedInput: '',
        notificationSoundUrl: null,
        blockedSoundUrl: null,
        defaultCharacterId: null,
        visitorUuid: null,
        conversationId: null,
        lastMessageId: null,
        chatMode: 'ai',
        chatOnline: true,
        pollInterval: null,
        blockedCheckInterval: null,
        waitingForReply: false,
        hasMore: false,
        loadingMore: false,
        cooldown: false,
        errorMsg: null,

        async init() {
            this.visitorUuid = this.getOrCreateUuid();
            await this.fetchCharacters();
        },

        getOrCreateUuid() {
            let uuid = localStorage.getItem('chat_visitor_uuid');
            if (!uuid) {
                uuid = crypto.randomUUID();
                localStorage.setItem('chat_visitor_uuid', uuid);
            }
            return uuid;
        },

        async fetchCharacters() {
            this.loading = true;
            try {
                const res = await fetch('/api/chat/characters');
                const data = await res.json();
                this.characters = data.characters || [];
                this.defaultCharacterId = data.default_character_id;
                this.notificationSoundUrl = data.notification_sound_url || null;
                this.blockedSoundUrl = data.blocked_sound_url || null;

                if (this.characters.length === 0) return;

                const savedId = localStorage.getItem('chat_last_character');
                const savedExists = savedId && this.characters.find(c => c.id == savedId);

                if (savedExists) {
                    this.activeCharacterId = parseInt(savedId);
                } else if (this.defaultCharacterId && this.characters.find(c => c.id == this.defaultCharacterId)) {
                    this.activeCharacterId = this.defaultCharacterId;
                } else {
                    this.activeCharacterId = this.characters[0].id;
                }

                this.chatMode = this.activeCharacter?.chat_mode || 'ai';
                this.chatOnline = this.activeCharacter?.chat_online ?? true;
                await this.loadHistory();
            } catch (e) {
                console.error('Chat init failed:', e);
            } finally {
                this.loading = false;
            }
        },

        get activeCharacter() {
            return this.characters.find(c => c.id === this.activeCharacterId) || null;
        },

        async switchCharacter(id) {
            this.stopPolling();
            this.activeCharacterId = parseInt(id);
            localStorage.setItem('chat_last_character', this.activeCharacterId);
            this.chatMode = this.activeCharacter?.chat_mode || 'ai';
            this.chatOnline = this.activeCharacter?.chat_online ?? true;
            this.conversationId = null;
            this.lastMessageId = null;
            this.waitingForReply = false;
            this.hasMore = false;

            await this.loadHistory();
        },

        async loadHistory() {
            if (!this.activeCharacterId || !this.visitorUuid) {
                this.messages = [];
                return;
            }
            try {
                const res = await fetch(`/api/chat/history?visitor_uuid=${this.visitorUuid}&character_id=${this.activeCharacterId}&limit=30`);
                if (res.status === 403) {
                    this.blocked = true;
                    this.messages = [];
                    this.startBlockedCheck();
                    return;
                }
                this.blocked = false;
                this.stopBlockedCheck();
                const data = await res.json();
                this.conversationId = data.conversation_id;
                this.messages = data.messages || [];
                this.hasMore = data.has_more || false;
                if (this.messages.length > 0) {
                    this.lastMessageId = Math.max(...this.messages.map(m => m.id));
                }
                this.$nextTick(() => this.scrollToBottom());
                this.startPollingIfNeeded();
            } catch (e) {
                console.error('Failed to load history:', e);
                this.messages = [];
            }
        },

        async loadMore() {
            if (this.loadingMore || !this.hasMore || this.messages.length === 0) return;
            this.loadingMore = true;
            const oldestId = Math.min(...this.messages.filter(m => m.id).map(m => m.id));
            try {
                const res = await fetch(`/api/chat/history?visitor_uuid=${this.visitorUuid}&character_id=${this.activeCharacterId}&limit=30&before_id=${oldestId}`);
                const data = await res.json();
                const older = data.messages || [];
                this.hasMore = data.has_more || false;
                if (older.length > 0) {
                    const el = this.$refs.messagesContainer;
                    const prevHeight = el.scrollHeight;
                    this.messages = [...older, ...this.messages];
                    this.$nextTick(() => {
                        el.scrollTop = el.scrollHeight - prevHeight;
                    });
                }
            } catch (e) {
                console.error('Failed to load more:', e);
            } finally {
                this.loadingMore = false;
            }
        },

        scrollToBottom() {
            const el = this.$refs.messagesContainer;
            if (el) el.scrollTop = el.scrollHeight;
        },

        async sendMessage() {
            const text = this.input.trim();
            if (!text || this.sending || this.cooldown || !this.activeCharacterId || !this.chatOnline) return;
            if (text.length > 1000) return;

            this.input = '';
            this.errorMsg = null;

            this.waitingForReply = false;
            this.messages.push({ role: 'user', content: text });
            this.sending = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const res = await fetch('/api/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                    },
                    body: JSON.stringify({
                        character_id: this.activeCharacterId,
                        message: text,
                        visitor_uuid: this.visitorUuid,
                    }),
                });

                if (res.status === 403) {
                    this.blocked = true;
                    // Remove the optimistic user message since it wasn't stored
                    this.messages.pop();
                    this.sending = false;
                    // Keep polling so blocked visitor still receives admin replies
                    this.startPollingIfNeeded();
                    return;
                }

                if (res.status === 429) {
                    const data = await res.json();
                    this.errorMsg = data.error || 'Wacht even voordat je een nieuw bericht stuurt.';
                    // Remove the optimistic message we just added
                    this.messages.pop();
                    this.input = text;
                    setTimeout(() => { this.errorMsg = null; }, 4000);
                    return;
                }

                const data = await res.json();

                if (data.error) {
                    this.errorMsg = data.error;
                    setTimeout(() => { this.errorMsg = null; }, 4000);
                    this.startPolling();
                } else {
                    this.conversationId = data.conversation_id;

                    if (data.mode === 'ai' && data.message) {
                        this.messages.push({ id: data.message_id, role: 'assistant', content: data.message });
                        this.lastMessageId = data.message_id;
                    } else {
                        this.waitingForReply = true;
                        this.startPolling();
                    }
                }
            } catch (e) {
                this.startPolling();
            } finally {
                this.sending = false;
                // Cooldown: prevent sending again for 3 seconds
                this.cooldown = true;
                setTimeout(() => { this.cooldown = false; }, 3000);
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        startPollingIfNeeded() {
            if (this.conversationId) {
                this.startPolling();
            }
        },

        startPolling() {
            this.stopPolling();
            this.pollInterval = setInterval(() => this.pollForMessages(), 3000);
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        },

        startBlockedCheck() {
            this.stopBlockedCheck();
            this.blockedCheckInterval = setInterval(() => this.checkIfStillBlocked(), 5000);
        },

        stopBlockedCheck() {
            if (this.blockedCheckInterval) {
                clearInterval(this.blockedCheckInterval);
                this.blockedCheckInterval = null;
            }
        },

        async checkIfStillBlocked() {
            if (!this.activeCharacterId || !this.visitorUuid) return;
            try {
                const res = await fetch(`/api/chat/history?visitor_uuid=${this.visitorUuid}&character_id=${this.activeCharacterId}&limit=30`);
                if (res.status !== 403) {
                    this.blocked = false;
                    this.stopBlockedCheck();
                    const data = await res.json();
                    this.conversationId = data.conversation_id;
                    this.messages = data.messages || [];
                    this.hasMore = data.has_more || false;
                    if (this.messages.length > 0) {
                        this.lastMessageId = Math.max(...this.messages.map(m => m.id));
                    }
                    this.$nextTick(() => this.scrollToBottom());
                    this.startPollingIfNeeded();
                }
            } catch (e) {}
        },

        async pollForMessages() {
            if (!this.conversationId || !this.visitorUuid) return;

            // Refresh online status
            try {
                const charRes = await fetch('/api/chat/characters');
                const charData = await charRes.json();
                this.characters = charData.characters || [];
                this.chatOnline = this.activeCharacter?.chat_online ?? true;
                this.notificationSoundUrl = charData.notification_sound_url || null;
                this.blockedSoundUrl = charData.blocked_sound_url || null;
            } catch (e) {}

            try {
                const params = new URLSearchParams({
                    conversation_id: this.conversationId,
                    visitor_uuid: this.visitorUuid,
                });
                if (this.lastMessageId) {
                    params.set('after_id', this.lastMessageId);
                }

                const res = await fetch(`/api/chat/poll?${params}`);
                if (!res.ok) return;
                const data = await res.json();

                if (data.messages && data.messages.length > 0) {
                    let hasNew = false;
                    for (const msg of data.messages) {
                        if (!this.messages.find(m => m.id === msg.id)) {
                            this.messages.push({ id: msg.id, role: 'assistant', content: msg.content });
                            hasNew = true;
                        }
                        this.lastMessageId = msg.id;
                    }
                    this.waitingForReply = false;
        
                    if (hasNew) {
                        this.playPingSound();
                    }
                    this.$nextTick(() => this.scrollToBottom());
                }

                // Update blocked status from server
                if (data.blocked !== undefined) {
                    this.blocked = data.blocked;
                }

                if (data.status === 'closed') {
                    this.stopPolling();
                }
            } catch (e) {
                console.error('Poll failed:', e);
            }
        },

        playPingSound() {
            if (this.notificationSoundUrl) {
                try { new Audio(this.notificationSoundUrl).play(); } catch (e) {}
                return;
            }
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.frequency.setValueAtTime(660, ctx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.3);
            } catch (e) {}
        },

        playBlockedSound() {
            if (this.blockedSoundUrl) {
                try { new Audio(this.blockedSoundUrl).play(); } catch (e) {}
                return;
            }
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(200, ctx.currentTime);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.5);
            } catch (e) {}
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => {
                    this.scrollToBottom();
                    this.$refs.chatInput?.focus();
                });
                this.startPollingIfNeeded();
            }
        }
    }"
    x-cloak
    class="fixed bottom-6 right-6 z-50"
>
    {{-- Chat Bubble --}}
    <template x-if="characters.length > 0">
        <div>
            {{-- Chat Panel --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="absolute bottom-16 right-0 w-[360px] h-[500px] bg-zinc-900 border border-zinc-700 rounded-sm shadow-2xl flex flex-col overflow-hidden"
            >
                {{-- Header --}}
                <div class="bg-zinc-800 border-b border-zinc-700 px-4 py-3 flex items-center gap-3 shrink-0">
                    <template x-if="activeCharacter?.profile_image_url">
                        <img :src="activeCharacter.profile_image_url" class="w-8 h-8 rounded-full object-cover border border-zinc-600" :alt="activeCharacter.name">
                    </template>
                    <template x-if="!activeCharacter?.profile_image_url">
                        <div class="w-8 h-8 rounded-full bg-accent/20 flex items-center justify-center text-accent text-xs font-bold">
                            <span x-text="activeCharacter?.name?.charAt(0) || '?'"></span>
                        </div>
                    </template>

                    <div class="flex-1 min-w-0">
                        <select
                            x-model="activeCharacterId"
                            @change="switchCharacter($event.target.value)"
                            class="w-full bg-zinc-700 border-0 text-white text-sm rounded-sm py-1 px-2 focus:ring-accent"
                        >
                            <template x-for="c in characters" :key="c.id">
                                <option :value="c.id" x-text="c.first_name || c.name"></option>
                            </template>
                        </select>
                    </div>

                    <button @click="toggle()" class="text-zinc-500 hover:text-zinc-300 transition shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Messages --}}
                <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3">
                    <template x-if="hasMore">
                        <div class="text-center py-1">
                            <button @click="loadMore()" :disabled="loadingMore" class="text-xs text-accent border border-accent/30 px-3 py-1 hover:bg-accent/10 transition disabled:opacity-50">
                                <span x-show="!loadingMore">Laad meer</span>
                                <span x-show="loadingMore">Laden...</span>
                            </button>
                        </div>
                    </template>

                    <template x-if="messages.length === 0 && !sending">
                        <div class="flex flex-col items-center justify-center h-full text-center px-4">
                            <template x-if="activeCharacter?.profile_image_url">
                                <img :src="activeCharacter.profile_image_url" class="w-16 h-16 rounded-full object-cover border-2 border-accent/30 mb-3" :alt="activeCharacter.name">
                            </template>
                            <p class="text-zinc-400 text-sm">Start een gesprek met</p>
                            <p class="text-white font-semibold" x-text="activeCharacter?.first_name || activeCharacter?.name || ''"></p>
                        </div>
                    </template>

                    <template x-for="(msg, i) in messages" :key="msg.id || ('m-' + i)">
                        <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                            <div
                                :class="msg.role === 'user'
                                    ? 'bg-accent text-black rounded-sm rounded-br-none px-4 py-2 max-w-[80%] text-sm overflow-hidden break-words'
                                    : 'bg-zinc-800 text-white rounded-sm rounded-bl-none px-4 py-2 max-w-[80%] text-sm border border-zinc-700 overflow-hidden break-words'"
                                x-text="msg.content"
                            ></div>
                        </div>
                    </template>


                </div>

                {{-- Input --}}
                <div class="border-t border-zinc-700 shrink-0">
                    <template x-if="errorMsg">
                        <div class="px-3 pt-2 text-xs text-yellow-400" x-text="errorMsg"></div>
                    </template>
                    <div class="p-3">
                        <template x-if="blocked">
                            <form @submit.prevent="if (blockedInput.trim()) { playBlockedSound(); blockedInput = ''; }" class="flex items-center gap-2">
                                <input
                                    x-model="blockedInput"
                                    type="text"
                                    maxlength="1000"
                                    placeholder="Typ een bericht..."
                                    class="flex-1 bg-zinc-800 border border-zinc-700 text-white text-sm rounded-sm px-3 py-2 focus:border-accent focus:ring-accent placeholder-zinc-500"
                                    @keydown.enter.prevent="if (blockedInput.trim()) { playBlockedSound(); blockedInput = ''; }"
                                >
                                <button
                                    type="submit"
                                    :disabled="!blockedInput.trim()"
                                    class="bg-accent text-black p-2 rounded-sm transition hover:brightness-90 disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                </button>
                            </form>
                        </template>
                        <template x-if="!blocked && !chatOnline">
                            <div class="flex items-center justify-center gap-2 py-2 text-sm text-zinc-500">
                                <div class="w-2 h-2 rounded-full bg-zinc-600"></div>
                                <span x-text="(activeCharacter?.first_name || activeCharacter?.name || '') + ' is momenteel niet online'"></span>
                            </div>
                        </template>
                        <template x-if="!blocked && chatOnline">
                            <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                                <input
                                    x-ref="chatInput"
                                    x-model="input"
                                    type="text"
                                    maxlength="1000"
                                    :disabled="sending || cooldown"
                                    placeholder="Typ een bericht..."
                                    class="flex-1 bg-zinc-800 border border-zinc-700 text-white text-sm rounded-sm px-3 py-2 focus:border-accent focus:ring-accent placeholder-zinc-500"
                                    @keydown.enter.prevent="sendMessage()"
                                >
                                <button
                                    type="submit"
                                    :disabled="sending || cooldown || !input.trim()"
                                    class="bg-accent text-black p-2 rounded-sm transition hover:brightness-90 disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                </button>
                            </form>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Floating Bubble Button --}}
            <button
                @click="toggle()"
                class="w-14 h-14 rounded-full bg-accent text-black shadow-lg hover:brightness-90 transition flex items-center justify-center"
                :class="open ? 'ring-2 ring-accent/50' : ''"
            >
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
