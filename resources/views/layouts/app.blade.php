<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GazManager') }} - @yield('title', 'Tableau de bord')</title>

    {{-- Assets locaux (pas de dépendance CDN) --}}
    <script>
        tailwind = { config: { theme: { extend: { colors: { brand: '#2D3A8C' } } } } }
    </script>
    <script src="{{ asset('tailwind.min.js') }}"></script>
    <script src="{{ asset('alpine.min.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('fa.min.css') }}">

    {{-- 5. Styles personnalisés via type="text/tailwindcss" → @apply fonctionne --}}
    <style type="text/tailwindcss">
        [x-cloak] { display: none !important; }

        .badge       { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium; }

        .btn         { @apply inline-flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm transition-all duration-200 cursor-pointer border-0; }
        .btn-primary   { @apply bg-blue-600 text-white hover:bg-blue-700; }
        .btn-success   { @apply bg-green-600 text-white hover:bg-green-700; }
        .btn-danger    { @apply bg-red-600   text-white hover:bg-red-700; }
        .btn-warning   { @apply bg-yellow-500 text-white hover:bg-yellow-600; }
        .btn-secondary { @apply bg-gray-100 text-gray-700 hover:bg-gray-200; }

        .card        { @apply bg-white rounded-2xl shadow-sm border border-gray-100 p-6; }
        .stat-card   { @apply bg-white rounded-2xl shadow-sm border border-gray-100 p-5; }

        .form-input  { @apply w-full border border-gray-300 rounded-xl px-3 py-2 text-sm outline-none
                              transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100; }
        .form-label  { @apply block text-sm font-medium text-gray-700 mb-1; }
        .form-select { @apply w-full border border-gray-300 rounded-xl px-3 py-2 text-sm outline-none
                              transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white; }

        /* Navigation horizontale */
        .nav-item {
            @apply flex flex-col items-center gap-0.5 px-3.5 py-2 rounded-xl
                   text-white/75 font-semibold whitespace-nowrap cursor-pointer
                   transition-all duration-150 border-0 bg-transparent no-underline;
            font-size: 0.70rem;
        }
        .nav-item i  { @apply text-base mb-px; }
        .nav-item:hover  { @apply bg-white/15 text-white; }
        .nav-item.active { @apply bg-white/20 text-white shadow-md; }

        /* Tables */
        .table-header { @apply text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide; }
        .table-cell   { @apply px-4 py-3 text-sm text-gray-700; }
        .table-row    { @apply hover:bg-gray-50/60 transition-colors; }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col antialiased">

{{-- ══════════════════════ NAVBAR ══════════════════════ --}}
<nav class="w-full shadow-lg z-50 sticky top-0" style="background:#2D3A8C;">
    <div class="flex items-center h-16 px-4 gap-1 max-w-screen-2xl mx-auto">

        {{-- Logo --}}
        <a href="{{ auth('admin')->check() ? route('admin.dashboard') : route('store.dashboard') }}"
           class="flex items-center gap-2.5 mr-3 shrink-0">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shadow"
                 style="background:linear-gradient(135deg,#fbbf24,#f59e0b);">
                <i class="fas fa-fire text-base" style="color:#1e3a8a;"></i>
            </div>
            <span class="text-white font-bold text-base tracking-wide hidden sm:inline">GazManager</span>
        </a>

        {{-- Séparateur vertical --}}
        <div class="w-px h-7 bg-white/20 mx-2 shrink-0"></div>

        {{-- ADMIN NAV --}}
        @if(auth('admin')->check())
        <div class="flex items-center gap-0.5 flex-1 overflow-x-auto">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>Tableau
            </a>
            <a href="{{ route('admin.inscriptions') }}"
               class="nav-item {{ request()->routeIs('admin.inscriptions') ? 'active' : '' }}">
                @php $pending = \App\Models\Store::where('status','pending')->count(); @endphp
                <i class="fas fa-store"></i>
                <span class="flex items-center gap-1">
                    Magasins
                    @if($pending > 0)
                        <span class="bg-yellow-400 text-yellow-900 text-xs rounded-full px-1.5 leading-none font-bold">{{ $pending }}</span>
                    @endif
                </span>
            </a>
            <a href="{{ route('admin.accounts') }}"
               class="nav-item {{ request()->routeIs('admin.accounts*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>Comptes
            </a>
            <a href="{{ route('admin.currencies') }}"
               class="nav-item {{ request()->routeIs('admin.currencies*') ? 'active' : '' }}">
                <i class="fas fa-coins"></i>Devises
            </a>
            <a href="{{ route('admin.subscription') }}"
               class="nav-item {{ request()->routeIs('admin.subscription*') ? 'active' : '' }}">
                <i class="fas fa-credit-card"></i>Abonnement
            </a>
            <a href="{{ route('admin.livreurs.index') }}"
               class="nav-item {{ request()->routeIs('admin.livreurs.*') ? 'active' : '' }}">
                <i class="fas fa-motorcycle"></i>Livreurs
            </a>
            <a href="{{ route('admin.settings') }}"
               class="nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>Paramètres
            </a>
        </div>

        {{-- STORE / STAFF NAV --}}
        @else
        <div class="flex items-center gap-0.5 flex-1 overflow-x-auto">
            <a href="{{ route('store.dashboard') }}"
               class="nav-item {{ request()->routeIs('store.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>Tableau
            </a>
            <a href="{{ route('orders.index') }}"
               class="nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>Commandes
            </a>
            <a href="{{ route('stock.index') }}"
               class="nav-item {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                <i class="fas fa-boxes"></i>Stock
            </a>
            <a href="{{ route('sales.index') }}"
               class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i>Ventes
            </a>
            <a href="{{ route('expenses.index') }}"
               class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i class="fas fa-wallet"></i>Dépenses
            </a>
            <a href="{{ route('profit.index') }}"
               class="nav-item {{ request()->routeIs('profit.*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>Bénéfices
            </a>
            <a href="{{ route('clients.index') }}"
               class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>Clients
            </a>
            @if(auth('store')->check())
            <a href="{{ route('staff.index') }}"
               class="nav-item {{ request()->routeIs('staff.*') ? 'active' : '' }}">
                <i class="fas fa-user-tie"></i>Personnel
            </a>
            <a href="{{ route('salaries.index') }}"
               class="nav-item {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i>Salaires
            </a>
            <a href="{{ route('loyalty.index') }}"
               class="nav-item {{ request()->routeIs('loyalty.*') ? 'active' : '' }}">
                <i class="fas fa-star"></i>Fidélité
            </a>
            @endif
            <a href="{{ route('profile.index') }}"
               class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i>Profil
            </a>
        </div>
        @endif

        {{-- Droite : user + logout --}}
        @php
            $authUser = auth('admin')->user() ?? auth('store')->user() ?? auth('staff')->user();
            $authRole = auth('admin')->check() ? 'Administrateur'
                : (auth('store')->check() ? 'Manager' : ucfirst(auth('staff')->user()?->role ?? ''));
            $authName = $authUser?->owner_name ?? $authUser?->name ?? $authUser?->store_name ?? 'Utilisateur';
        @endphp
        <div class="flex items-center gap-2 ml-auto shrink-0 pl-2 border-l border-white/20">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white"
                 style="background:rgba(255,255,255,0.2);">
                {{ strtoupper(substr($authName, 0, 1)) }}
            </div>
            <div class="hidden md:block leading-tight">
                <div class="text-white text-xs font-semibold">{{ $authName }}</div>
                <div class="text-xs" style="color:rgba(255,255,255,0.55);">{{ $authRole }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="ml-1">
                @csrf
                <button type="submit"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-all"
                        style="color:rgba(255,255,255,0.65);"
                        onmouseover="this.style.background='rgba(255,255,255,0.15)';this.style.color='white';"
                        onmouseout="this.style.background='transparent';this.style.color='rgba(255,255,255,0.65)';"
                        title="Déconnexion">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </button>
            </form>
        </div>

    </div>
</nav>

{{-- ══════════════════════ FLASH MESSAGES ══════════════════════ --}}
<div class="px-6 max-w-screen-2xl mx-auto w-full">
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mt-4"
             id="flash-success">
            <i class="fas fa-check-circle text-green-500"></i>
            <span class="flex-1 text-sm">{{ session('success') }}</span>
            <button onclick="document.getElementById('flash-success').remove()" class="text-green-400 hover:text-green-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mt-4"
             id="flash-error">
            <i class="fas fa-exclamation-circle text-red-400"></i>
            <span class="flex-1 text-sm">{{ session('error') }}</span>
            <button onclick="document.getElementById('flash-error').remove()" class="text-red-400 hover:text-red-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mt-4">
            <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
            <ul class="text-sm list-disc pl-4 flex-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif
</div>

{{-- ══════════════════════ CONTENT ══════════════════════ --}}
<main class="flex-1 px-6 pb-10 max-w-screen-2xl mx-auto w-full">
    @yield('content')
</main>

{{-- Real-time alerts + alarm (store only) --}}
@if(auth('store')->check() || auth('staff')->check())
<div id="rt-alerts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 w-80"></div>

{{-- Alarm banner (slides in from top when new order arrives) --}}
<div id="order-alarm"
     style="display:none;background:linear-gradient(90deg,#1e3a8a,#2563eb);"
     class="fixed top-0 left-0 right-0 z-[60] flex items-center justify-between gap-4 px-6 py-4 shadow-2xl">
    <div class="flex items-center gap-3 text-white font-bold text-base">
        <span class="text-2xl animate-bounce">🛒</span>
        <span id="alarm-text">Nouvelle commande reçue !</span>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('orders.index') }}"
           class="bg-white text-blue-700 font-bold text-sm px-4 py-2 rounded-xl hover:bg-blue-50 transition">
            Voir les commandes
        </a>
        <button onclick="dismissAlarm()"
                class="text-white/70 hover:text-white text-xl leading-none">✕</button>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const box      = document.getElementById('rt-alerts');
    const alarmEl  = document.getElementById('order-alarm');
    const alarmKey = 'lastOrderId_{{ auth("store")->id() ?? auth("staff")->user()?->store_id }}';
    const apiPath  = window.location.origin + '{{ parse_url(url("/api/stock-alerts"), PHP_URL_PATH) }}';
    const csrf     = document.querySelector('meta[name=csrf-token]').content;

    // ── Audio context (must be created after a user gesture) ──────────────
    let audioCtx = null;
    document.addEventListener('click', () => {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        else if (audioCtx.state === 'suspended') audioCtx.resume();
    }, { passive: true });

    function playAlarm() {
        if (!audioCtx) {
            try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); }
            catch(e) { return; }
        }
        const play = () => {
            // Three ascending beeps: 660 Hz → 880 Hz → 1100 Hz
            [[0, 660], [0.28, 880], [0.56, 1100]].forEach(([delay, freq]) => {
                const osc  = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.35, audioCtx.currentTime + delay);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + delay + 0.22);
                osc.start(audioCtx.currentTime + delay);
                osc.stop(audioCtx.currentTime + delay + 0.22);
            });
        };
        if (audioCtx.state === 'suspended') audioCtx.resume().then(play);
        else play();
    }

    // ── Toast helper ──────────────────────────────────────────────────────
    function toast(msg, color) {
        const d = document.createElement('div');
        d.className = 'flex items-start gap-3 border rounded-xl px-4 py-3 shadow-xl text-sm ' + color;
        d.innerHTML = msg + '<button onclick="this.parentElement.remove()" class="ml-auto opacity-50 hover:opacity-100 shrink-0">✕</button>';
        box.appendChild(d);
        setTimeout(() => d.remove(), 12000);
    }

    // ── Alarm banner ──────────────────────────────────────────────────────
    window.dismissAlarm = function() {
        alarmEl.style.display = 'none';
    };

    function showAlarm(count) {
        document.getElementById('alarm-text').textContent =
            count > 1 ? count + ' nouvelles commandes reçues !' : 'Nouvelle commande reçue !';
        alarmEl.style.display = 'flex';
        alarmEl.style.background = 'linear-gradient(90deg,#1e3a8a,#2563eb)';
        // Auto-dismiss after 30s
        clearTimeout(window._alarmTimer);
        window._alarmTimer = setTimeout(() => alarmEl.style.display = 'none', 30000);
    }

    // ── Poll ──────────────────────────────────────────────────────────────
    async function check() {
        try {
            const r = await fetch(apiPath, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
            });
            if (!r.ok) return;
            const d = await r.json();

            // Stock alerts (unchanged)
            if (d.low_stock > 0) {
                toast(`<i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 shrink-0"></i><span>⚠ ${d.low_stock} article(s) en stock faible</span>`,
                      'bg-yellow-50 border-yellow-200 text-yellow-800');
            }

            // New order detection
            const lastSeen  = parseInt(sessionStorage.getItem(alarmKey) || '0', 10);
            const latestId  = d.latest_order_id || 0;

            if (latestId > lastSeen) {
                // First load: just silently record current state, no alarm
                if (lastSeen === 0) {
                    sessionStorage.setItem(alarmKey, latestId);
                } else {
                    // New order(s) arrived since last check → ALARM
                    playAlarm();
                    showAlarm(d.pending_orders);
                    toast(`<i class="fas fa-bell text-blue-500 mt-0.5 shrink-0 animate-bounce"></i><span><strong>Nouvelle commande !</strong> ${d.pending_orders} commande(s) en attente</span>`,
                          'bg-blue-50 border-blue-300 text-blue-900');
                    sessionStorage.setItem(alarmKey, latestId);
                }
            }
        } catch(e) {}
    }

    setTimeout(check, 4000);   // first check 4s after page load
    setInterval(check, 20000); // then every 20s (faster than before)
})();
</script>
@endpush
@endif

@stack('scripts')
</body>
</html>
