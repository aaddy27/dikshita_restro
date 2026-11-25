<div
    class="relative"
    wire:poll.4s="checkActivities"
    x-data="{
        isOpen: @entangle('isOpen'),
        soundEnabled: localStorage.getItem('pos_sound_notifications') !== 'false',
        toasts: [],

        init() {
            window.addEventListener('play-activity-sound', (event) => {
                const notifications = event.detail.notifications || [];
                if (notifications.length === 0) return;

                if (this.soundEnabled) {
                    this.playChime();
                    const latest = notifications[notifications.length - 1];
                    this.speakVoice(latest.speech || 'New notification!');
                }

                notifications.forEach(item => {
                    this.addToast(item);
                });
            });
        },

        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            localStorage.setItem('pos_sound_notifications', this.soundEnabled ? 'true' : 'false');
        },

        playTestSound() {
            this.playChime();
            this.speakVoice('New notification! Sound notifications are active.');
            this.addToast({
                id: Date.now(),
                title: 'Sound Test',
                message: 'Audio chime and voice notification verified successfully!',
                table: null,
                time: 'Just now',
                action: 'info'
            });
        },

        playChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }

                const now = ctx.currentTime;

                // Tone 1: Clear musical chime (D5 -> A5)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now);
                osc1.frequency.exponentialRampToValueAtTime(880, now + 0.12);

                gain1.gain.setValueAtTime(0.35, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.38);

                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.38);

                // Tone 2: Shimmering bell harmonic (A5 -> D6)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'triangle';
                osc2.frequency.setValueAtTime(880, now + 0.10);
                osc2.frequency.setValueAtTime(1174.66, now + 0.22);

                gain2.gain.setValueAtTime(0.001, now);
                gain2.gain.linearRampToValueAtTime(0.28, now + 0.12);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.55);

                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now);
                osc2.stop(now + 0.55);
            } catch (err) {
                console.warn('Audio chime error:', err);
            }
        },

        speakVoice(text) {
            try {
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.rate = 1.05;
                    utterance.pitch = 1.05;
                    utterance.volume = 1.0;

                    // Try to pick an English voice or default
                    const voices = window.speechSynthesis.getVoices();
                    const preferredVoice = voices.find(v => v.lang.startsWith('en') || v.lang.startsWith('hi'));
                    if (preferredVoice) {
                        utterance.voice = preferredVoice;
                    }

                    window.speechSynthesis.speak(utterance);
                }
            } catch (err) {
                console.warn('Speech synthesis error:', err);
            }
        },

        addToast(item) {
            const toastId = item.id || Date.now() + Math.random();
            this.toasts.push({ ...item, toastId });
            setTimeout(() => {
                this.removeToast(toastId);
            }, 6500);
        },

        removeToast(toastId) {
            this.toasts = this.toasts.filter(t => t.toastId !== toastId);
        }
    }"
>
    <!-- Notification Bell Button in Header -->
    <button
        type="button"
        wire:click="toggleOpen"
        class="relative p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-none"
        title="Restaurant Activity Notifications"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Unread Badge -->
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-600 text-[10px] font-black text-white items-center justify-center font-mono leading-none">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="isOpen"
        x-cloak
        @click.outside="isOpen = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden"
    >
        <!-- Header with Sound Controls -->
        <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-black text-slate-900">Notifications &amp; Activity</span>
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 text-[10px] font-extrabold bg-rose-100 text-rose-700 rounded-full">
                        {{ $unreadCount }} New
                    </span>
                @endif
            </div>

            <!-- Sound Toggle & Test -->
            <div class="flex items-center gap-1.5">
                <button
                    type="button"
                    @click="toggleSound()"
                    :class="soundEnabled ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-slate-200 text-slate-500 border-slate-300'"
                    class="px-2 py-1 rounded-lg border text-[11px] font-bold flex items-center gap-1 transition-colors"
                    :title="soundEnabled ? 'Voice & Chime Sound Active (Click to Mute)' : 'Sound is Muted (Click to Enable)'"
                >
                    <span x-text="soundEnabled ? '🔊 Sound ON' : '🔇 Muted'"></span>
                </button>

                <button
                    type="button"
                    @click="playTestSound()"
                    class="px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-800 border border-amber-300 rounded-lg text-[11px] font-bold transition-colors"
                    title="Play Test Sound"
                >
                    Test 🔔
                </button>
            </div>
        </div>

        <!-- Recent Activities List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
            @forelse($recentLogs as $log)
                @php
                    $isOrderCreated = $log->action === 'order_created';
                    $isOrderCancelled = $log->action === 'order_cancelled';
                    $isBillSettled = $log->action === 'bill_settled';
                    $isItem = in_array($log->action, ['item_added', 'item_deleted']);
                    $isShift = in_array($log->action, ['table_shifted', 'table_merged']);
                    $tableNum = $log->order?->table?->table_number;
                @endphp
                <div class="p-3 hover:bg-slate-50/80 transition-colors flex items-start gap-3 text-xs">
                    <!-- Icon Bubble -->
                    <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center font-bold text-xs
                        {{ $isOrderCancelled ? 'bg-rose-100 text-rose-700' : ($isOrderCreated ? 'bg-emerald-100 text-emerald-700' : ($isBillSettled ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-800')) }}">
                        @if($isOrderCancelled)
                            ✕
                        @elseif($isOrderCreated)
                            ✨
                        @elseif($isBillSettled)
                            ₹
                        @elseif($isItem)
                            🍽️
                        @elseif($isShift)
                            ↔️
                        @else
                            🔔
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <span class="font-extrabold text-slate-900 truncate">
                                @if($tableNum)
                                    <span class="text-indigo-600 font-mono-numbers">[Table {{ $tableNum }}]</span>
                                @endif
                                {{ ucwords(str_replace('_', ' ', $log->action)) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono shrink-0">
                                {{ $log->created_at->diffForHumans(null, true) }}
                            </span>
                        </div>
                        <p class="text-slate-600 text-[11px] mt-0.5 leading-snug line-clamp-2">
                            {{ $log->remarks ?: 'Activity recorded on order' }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-400 text-xs">
                    No recent activity recorded.
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="p-2.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-[11px]">
            <span class="text-slate-500 font-medium">Automatic real-time sync</span>
            <button
                type="button"
                wire:click="markAllAsRead"
                class="font-bold text-indigo-600 hover:text-indigo-800"
            >
                Clear / Mark Read &check;
            </button>
        </div>
    </div>

    <!-- Floating Live Toast Notifications (Bottom Right of screen) -->
    <div class="fixed bottom-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm pointer-events-none">
        <template x-for="t in toasts" :key="t.toastId">
            <div
                class="pointer-events-auto bg-slate-950/95 text-white p-3.5 rounded-2xl shadow-2xl border border-slate-800 backdrop-blur-md flex items-start gap-3 transition-all duration-300 transform translate-y-0"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            >
                <!-- Alert Icon -->
                <div
                    class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center font-bold text-xs"
                    :class="t.action === 'order_cancelled' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : (t.action === 'order_created' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'bg-amber-500/20 text-amber-400 border border-amber-500/40')"
                >
                    <span x-text="t.action === 'order_cancelled' ? '🚫' : (t.action === 'order_created' ? '🔔' : '⚡')"></span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1">
                        <h4 class="text-xs font-black text-white leading-tight truncate" x-text="t.title"></h4>
                        <span class="text-[10px] text-slate-400 font-mono shrink-0" x-text="t.time"></span>
                    </div>
                    <p class="text-[11px] text-slate-300 mt-0.5 leading-snug line-clamp-2" x-text="t.message"></p>
                </div>

                <!-- Dismiss Button -->
                <button
                    type="button"
                    @click="removeToast(t.toastId)"
                    class="text-slate-400 hover:text-white text-base font-bold leading-none p-1"
                >
                    &times;
                </button>
            </div>
        </template>
    </div>
</div>
