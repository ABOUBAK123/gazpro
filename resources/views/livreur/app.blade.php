<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $livreur->name }}">
    <link rel="manifest" href="{{ route('livreur.manifest', $token) }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/livreur-192.png') }}">
    <title>{{ $livreur->name }} — Livreur</title>

    <script>tailwind = { config: {} }</script>
    <script src="{{ asset('tailwind.min.js') }}"></script>
    <script src="{{ asset('alpine.min.js') }}" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style type="text/tailwindcss">
        * { -webkit-tap-highlight-color: transparent; }
        .order-card {
            @apply bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-200;
        }
        .action-btn {
            @apply flex items-center justify-center gap-2 py-3 rounded-2xl font-bold text-sm
                   transition-all active:scale-[0.97] cursor-pointer;
        }
        .tab-btn { @apply flex-1 py-2.5 text-sm font-bold rounded-xl transition-all; }
        .tab-btn.active   { @apply bg-white shadow text-gray-900; }
        .tab-btn.inactive { @apply text-gray-500; }
        .enter { animation: fadeUp .25s ease-out both; }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(12px); }
            to   { opacity:1; transform:translateY(0); }
        }
    </style>
</head>
<body style="background:#f1f5f9;min-height:100dvh;">

<div x-data="livreurApp()" x-init="init()" style="min-height:100dvh;display:flex;flex-direction:column;">

    {{-- ══════ HEADER ══════ --}}
    <div style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 60%,#2563eb 100%);
                padding-top:env(safe-area-inset-top,0);" class="shrink-0">
        <div class="px-4 pt-4 pb-3">

            {{-- Livreur identity + disponibilité --}}
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black text-xl text-white shrink-0"
                     style="background:rgba(255,255,255,0.15);">
                    {{ strtoupper(substr($livreur->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h1 class="text-white font-black text-lg leading-tight">{{ $livreur->name }}</h1>
                    <div class="flex items-center gap-3 mt-0.5">
                        <span class="flex items-center gap-1.5 text-blue-200 text-xs font-semibold">
                            <i class="fas {{ $livreur->vehicle_icon }}"></i>
                            {{ $livreur->vehicle_label }}
                            @if($livreur->vehicle_plate)
                                <span class="font-mono text-blue-300">({{ $livreur->vehicle_plate }})</span>
                            @endif
                        </span>
                    </div>
                    <a href="tel:{{ $livreur->phone }}"
                       class="flex items-center gap-1 text-blue-300 text-xs mt-0.5 hover:text-white transition">
                        <i class="fas fa-phone text-xs"></i>{{ $livreur->phone }}
                    </a>
                </div>
                {{-- Availability toggle --}}
                <div class="shrink-0 flex flex-col items-center gap-1.5">
                    <button @click="toggleAvailability()"
                            :disabled="togglingAvail"
                            class="w-16 h-8 rounded-full transition-all duration-300 flex items-center px-1 disabled:opacity-60"
                            :style="available ? 'background:#22c55e' : 'background:#ef4444'">
                        <div class="w-6 h-6 bg-white rounded-full shadow transition-all duration-300"
                             :style="available ? 'margin-left:auto' : 'margin-left:0'"></div>
                    </button>
                    <span class="text-xs font-bold" :class="available ? 'text-green-300' : 'text-red-300'"
                          x-text="available ? 'Disponible' : 'En course'"></span>
                </div>
            </div>

            {{-- Install PWA button (shown by JS when Chrome prompt is available) --}}
            <button id="install-btn" onclick="triggerInstall()"
                    style="display:none"
                    class="mt-3 w-full flex items-center justify-center gap-2 bg-amber-400 hover:bg-amber-300
                           text-blue-900 font-bold text-sm py-2.5 rounded-xl transition active:scale-[0.98]">
                <i class="fas fa-download"></i>Installer l'application
            </button>

            {{-- GPS status bar --}}
            <div class="mt-3 flex items-center gap-2 text-xs"
                 :class="gpsOk ? 'text-green-300' : 'text-yellow-300'">
                <i class="fas" :class="gpsOk ? 'fa-location-dot' : 'fa-location-slash'"></i>
                <span x-text="gpsOk ? 'Position GPS partagée' : 'Partage de position en attente…'"></span>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="px-4 pb-3">
            <div class="flex gap-1 bg-white/10 rounded-2xl p-1">
                <button @click="tab='active'"
                        :class="tab==='active' ? 'tab-btn active' : 'tab-btn inactive'">
                    <i class="fas fa-motorcycle mr-1.5"></i>Actives
                    <span x-show="{{ $active->count() }} > 0"
                          class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-black"
                          :class="tab==='active' ? 'bg-blue-600 text-white' : 'bg-white/20 text-white'">
                        {{ $active->count() }}
                    </span>
                </button>
                <button @click="tab='history'"
                        :class="tab==='history' ? 'tab-btn active' : 'tab-btn inactive'">
                    <i class="fas fa-history mr-1.5"></i>Historique
                </button>
            </div>
        </div>
    </div>

    {{-- ══════ FLASH ══════ --}}
    <div x-show="flash" x-cloak
         class="mx-4 mt-3 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-2xl px-4 py-3 text-sm shrink-0">
        <i class="fas fa-check-circle shrink-0"></i>
        <span x-text="flash"></span>
        <button @click="flash=''" class="ml-auto text-green-400 shrink-0"><i class="fas fa-times text-xs"></i></button>
    </div>
    <div x-show="error" x-cloak
         class="mx-4 mt-3 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-4 py-3 text-sm shrink-0">
        <i class="fas fa-exclamation-circle shrink-0"></i>
        <span x-text="error"></span>
        <button @click="error=''" class="ml-auto text-red-400 shrink-0"><i class="fas fa-times text-xs"></i></button>
    </div>

    {{-- ══════ CONTENT ══════ --}}
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4">

        {{-- ACTIVE ORDERS --}}
        <div x-show="tab==='active'" class="space-y-4 enter">

            @if($active->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-20 h-20 rounded-3xl flex items-center justify-center mb-4"
                     style="background:rgba(37,99,235,0.08);">
                    <i class="fas fa-motorcycle text-blue-300 text-3xl"></i>
                </div>
                <p class="font-bold text-gray-700 text-lg">Aucune course active</p>
                <p class="text-gray-400 text-sm mt-1">Vos nouvelles commandes apparaîtront ici</p>
                <button @click="refresh()"
                        class="mt-5 flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-50 text-blue-600 font-semibold text-sm hover:bg-blue-100 transition">
                    <i class="fas fa-rotate-right"></i>Actualiser
                </button>
            </div>
            @else
            @foreach($active as $order)
            <div class="order-card enter" :style="`animation-delay:${{{ $loop->index }}*0.07}s`"
                 id="order-{{ $order->id }}">

                {{-- Status banner + magasin --}}
                <div class="px-4 py-2.5 flex items-center justify-between
                    {{ $order->status === 'en_route' ? 'bg-orange-500' : 'bg-blue-600' }}">
                    <div class="flex items-center gap-2 text-white text-xs font-bold">
                        <i class="fas {{ $order->status === 'en_route' ? 'fa-motorcycle' : 'fa-check' }}"></i>
                        {{ $order->status_label }}
                    </div>
                    <div class="text-white/80 text-xs">
                        <i class="fas fa-store mr-1 opacity-70"></i>{{ $order->store->store_name ?? '' }}
                        <span class="font-mono opacity-60 ml-2">#{{ $order->id }}</span>
                    </div>
                </div>

                <div class="p-4 space-y-4">

                    {{-- Client info --}}
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-black text-gray-900 text-lg leading-tight">{{ $order->client_name }}</p>
                            @if($order->client_address)
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>{{ $order->client_address }}
                                </p>
                            @endif
                            @if($order->notes)
                                <p class="text-xs text-amber-700 mt-0.5 italic">
                                    <i class="fas fa-sticky-note mr-1 text-amber-400"></i>{{ $order->notes }}
                                </p>
                            @endif
                        </div>
                        @if($order->client_phone)
                        <a href="tel:{{ $order->client_phone }}"
                           class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-lg shadow-green-200 transition active:scale-95"
                           style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                            <i class="fas fa-phone text-lg"></i>
                        </a>
                        @endif
                    </div>

                    @if($order->client_phone)
                    <div class="flex items-center gap-2 bg-gray-50 rounded-2xl px-3 py-2.5">
                        <i class="fas fa-phone text-gray-400 text-sm shrink-0"></i>
                        <span class="font-mono font-bold text-gray-700">{{ $order->client_phone }}</span>
                        <a href="tel:{{ $order->client_phone }}"
                           class="ml-auto text-xs text-blue-600 font-semibold underline shrink-0">Appeler</a>
                    </div>
                    @endif

                    {{-- GPS client --}}
                    @if($order->latitude && $order->longitude)
                    <a href="https://www.google.com/maps?q={{ $order->latitude }},{{ $order->longitude }}"
                       target="_blank"
                       class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-2xl px-3 py-3 transition active:bg-green-100">
                        <div class="w-9 h-9 bg-green-500 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fas fa-map-marker-alt text-white text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-green-800">Naviguer vers le client</p>
                            <p class="text-xs text-green-600 font-mono">{{ $order->latitude }}, {{ $order->longitude }}</p>
                        </div>
                        <i class="fas fa-external-link-alt text-green-400 text-xs shrink-0"></i>
                    </a>
                    @else
                    <div class="flex items-center gap-3 bg-yellow-50 border border-yellow-200 rounded-2xl px-3 py-2.5">
                        <i class="fas fa-location-slash text-yellow-500 text-sm shrink-0"></i>
                        <p class="text-xs text-yellow-700">Pas de GPS — contacter le client par téléphone</p>
                    </div>
                    @endif

                    {{-- Product --}}
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-gray-50 rounded-2xl p-3 text-center">
                            <p class="text-xs text-gray-500 mb-0.5">Produit</p>
                            <p class="font-black text-gray-900 text-sm">{{ $order->brand }}</p>
                            <p class="text-xs text-gray-600">{{ $order->weight }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-3 text-center">
                            <p class="text-xs text-gray-500 mb-0.5">Qté</p>
                            <p class="font-black text-gray-900 text-2xl">{{ $order->quantity }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-2xl p-3 text-center">
                            <p class="text-xs text-blue-500 mb-0.5">Total</p>
                            <p class="font-black text-blue-800 text-sm">
                                {{ number_format($order->total_price, 0, ',', ' ') }}
                            </p>
                            <p class="text-xs text-blue-600">{{ $order->currency }}</p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-1">
                        @if($order->status === 'confirmed')
                        <button @click="updateStatus({{ $order->id }}, 'en_route')"
                                :disabled="loading === {{ $order->id }}"
                                class="action-btn flex-1 text-white disabled:opacity-60"
                                style="background:linear-gradient(135deg,#f97316,#ea580c);">
                            <template x-if="loading === {{ $order->id }}">
                                <div class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></div>
                            </template>
                            <template x-if="loading !== {{ $order->id }}">
                                <span><i class="fas fa-motorcycle mr-1.5"></i>Démarrer la course</span>
                            </template>
                        </button>
                        @endif

                        @if($order->status === 'en_route')
                        <button @click="updateStatus({{ $order->id }}, 'delivered')"
                                :disabled="loading === {{ $order->id }}"
                                class="action-btn flex-1 text-white disabled:opacity-60"
                                style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                            <template x-if="loading === {{ $order->id }}">
                                <div class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></div>
                            </template>
                            <template x-if="loading !== {{ $order->id }}">
                                <span><i class="fas fa-check-circle mr-1.5"></i>Confirmer la livraison</span>
                            </template>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- HISTORY --}}
        <div x-show="tab==='history'" class="space-y-3 enter">
            @if($history->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fas fa-history text-gray-300 text-2xl"></i>
                </div>
                <p class="text-gray-500 font-medium">Aucun historique</p>
            </div>
            @else
            @foreach($history as $order)
            <div class="order-card">
                <div class="flex items-center gap-4 px-4 py-3.5">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0
                        {{ $order->status === 'delivered' ? 'bg-green-100' : 'bg-red-50' }}">
                        <i class="fas {{ $order->status === 'delivered' ? 'fa-check-circle text-green-500' : 'fa-ban text-red-400' }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-800 text-sm">{{ $order->client_name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $order->brand }} {{ $order->weight }} × {{ $order->quantity }}
                            · {{ number_format($order->total_price, 0, ',', ' ') }} {{ $order->currency }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <i class="fas fa-store mr-1 opacity-60"></i>{{ $order->store->store_name ?? '' }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="badge text-xs
                            {{ $order->status==='delivered' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $order->status_label }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">{{ $order->updated_at->format('d/m H:i') }}</p>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

    </div>

    {{-- ══════ BOTTOM BAR ══════ --}}
    <div class="shrink-0 bg-white border-t border-gray-100 px-4 py-3 flex items-center justify-between"
         style="padding-bottom:env(safe-area-inset-bottom,12px);">
        <p class="text-xs text-gray-400">
            <i class="fas fa-sync-alt mr-1"></i>Màj : <span x-text="lastRefresh">maintenant</span>
        </p>
        <button @click="refresh()" :disabled="refreshing"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-50 text-blue-600 font-bold text-xs hover:bg-blue-100 transition disabled:opacity-50">
            <i class="fas fa-rotate-right text-xs" :class="refreshing ? 'animate-spin' : ''"></i>
            Actualiser
        </button>
    </div>

</div>

<script>
// ── Service Worker registration ─────────────────────────────────────────
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(
        window.location.origin + '{{ parse_url(asset("sw-livreur.js"), PHP_URL_PATH) }}'
    ).catch(() => {});
}

// ── PWA install prompt ──────────────────────────────────────────────────
let _installPrompt = null;
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    _installPrompt = e;
    const btn = document.getElementById('install-btn');
    if (btn) btn.style.display = 'flex';
});
window.addEventListener('appinstalled', () => {
    const btn = document.getElementById('install-btn');
    if (btn) btn.style.display = 'none';
    _installPrompt = null;
});

function triggerInstall() {
    if (!_installPrompt) return;
    _installPrompt.prompt();
    _installPrompt.userChoice.then(() => { _installPrompt = null; });
}

function livreurApp() {
    const csrf      = document.querySelector('meta[name=csrf-token]').content;
    const basePath  = '{{ parse_url(url("/livreur/' . $token . '"), PHP_URL_PATH) }}';

    return {
        tab:           '{{ $active->count() > 0 ? "active" : "history" }}',
        loading:       null,
        refreshing:    false,
        togglingAvail: false,
        flash:         '',
        error:         '',
        lastRefresh:   'maintenant',
        available:     {{ $livreur->is_available ? 'true' : 'false' }},
        gpsOk:         false,

        init() {
            this.requestGps();
            // Refresh GPS every 3 minutes
            setInterval(() => this.requestGps(), 180000);
            // Silent clock refresh
            setInterval(() => {
                const now = new Date();
                this.lastRefresh = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
            }, 60000);
        },

        requestGps() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(
                pos => this.sendLocation(pos.coords.latitude, pos.coords.longitude),
                () => { this.gpsOk = false; },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        async sendLocation(lat, lng) {
            try {
                await fetch(window.location.origin + basePath + '/position', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ latitude: lat, longitude: lng }),
                });
                this.gpsOk = true;
            } catch(e) {
                this.gpsOk = false;
            }
        },

        async toggleAvailability() {
            this.togglingAvail = true;
            const newVal = !this.available;
            try {
                const r = await fetch(window.location.origin + basePath + '/position', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ is_available: newVal }),
                });
                if (r.ok) {
                    this.available = newVal;
                    this.flash = newVal ? '🟢 Vous êtes maintenant disponible.' : '🔴 Vous êtes marqué en course.';
                }
            } catch(e) {
                this.error = 'Erreur de connexion.';
            }
            this.togglingAvail = false;
        },

        async updateStatus(orderId, newStatus) {
            this.loading = orderId;
            this.flash = '';
            this.error = '';
            try {
                const r = await fetch(window.location.origin + basePath + '/commandes/' + orderId + '/statut', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });
                const data = await r.json();
                if (r.ok && data.success) {
                    const labels = {
                        en_route:  '🏍️ Course démarrée ! Bonne route.',
                        delivered: '✅ Livraison confirmée ! Bien joué.',
                        cancelled: 'Commande annulée.',
                    };
                    this.flash = labels[newStatus] || 'Statut mis à jour.';
                    if (newStatus === 'delivered') this.available = true;
                    const card = document.getElementById('order-' + orderId);
                    if (card) {
                        card.style.transition = 'all .35s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'translateX(60px)';
                    }
                    setTimeout(() => window.location.reload(), 420);
                } else {
                    this.error = data.message || 'Erreur lors de la mise à jour.';
                }
            } catch(e) {
                this.error = 'Erreur de connexion. Vérifiez votre réseau.';
            }
            this.loading = null;
        },

        refresh() {
            this.refreshing = true;
            window.location.reload();
        },
    };
}
</script>
</body>
</html>
