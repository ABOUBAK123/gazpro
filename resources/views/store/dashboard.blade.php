@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')
@php
    $periodLabels = ['week' => 'Cette semaine', 'month' => 'Ce mois', 'year' => 'Cette année', 'custom' => 'Personnalisé'];
    $currentLabel = $periodLabels[$period] ?? 'Ce mois';
    $profitPct = $stats['period_revenue'] > 0
        ? round(($stats['period_profit'] / $stats['period_revenue']) * 100)
        : 0;
    $expensePct = $stats['period_revenue'] > 0
        ? min(100, round(($stats['period_expenses'] / $stats['period_revenue']) * 100))
        : 0;
@endphp

<div class="space-y-6 py-4">

{{-- ════════════ HEADER BAND ════════════ --}}
<div class="rounded-2xl p-6 text-white relative overflow-hidden"
     style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);">
    <div class="absolute inset-0 opacity-10"
         style="background-image: radial-gradient(circle at 80% 50%, white 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-blue-200 text-xs font-medium uppercase tracking-widest mb-1">Bienvenue</div>
            <h2 class="text-2xl font-bold">{{ $store->store_name }}</h2>
            <div class="flex items-center gap-3 mt-2">
                <span class="text-blue-200 text-sm"><i class="fas fa-user mr-1"></i>{{ $store->owner_name }}</span>
                <span class="text-blue-300">·</span>
                <span class="text-blue-200 text-sm"><i class="fas fa-calendar mr-1"></i>{{ now()->translatedFormat('d F Y') }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($store->hasActiveSubscription())
            <div class="bg-white/20 backdrop-blur rounded-xl px-4 py-2 text-center">
                <div class="text-white/70 text-xs">Abonnement</div>
                <div class="text-white font-semibold text-sm flex items-center gap-1">
                    <span class="w-2 h-2 bg-green-400 rounded-full"></span> Actif
                </div>
            </div>
            @endif
            <div class="bg-white/20 backdrop-blur rounded-xl px-4 py-2 text-center">
                <div class="text-white/70 text-xs">Ventes aujourd'hui</div>
                <div class="text-white font-bold text-lg">{{ number_format($stats['today_sales'], 0, ',', ' ') }}<span class="text-xs font-normal ml-1">XOF</span></div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════ KPI CARDS ════════════ --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

    {{-- Ventes mois --}}
    <div class="rounded-2xl p-5 text-white relative overflow-hidden"
         style="background: linear-gradient(135deg, #059669, #10b981);">
        <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-chart-line text-white text-lg"></i>
        </div>
        <div class="text-2xl font-bold leading-none">{{ number_format($stats['month_sales'], 0, ',', ' ') }}</div>
        <div class="text-xs text-green-100 mt-1">XOF · Ventes ce mois</div>
        <div class="mt-2 text-xs text-green-100 opacity-80">
            <i class="fas fa-arrow-up mr-1"></i>{{ now()->format('M Y') }}
        </div>
    </div>

    {{-- Stock --}}
    <div class="rounded-2xl p-5 text-white relative overflow-hidden"
         style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
        <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-boxes text-white text-lg"></i>
        </div>
        <div class="text-2xl font-bold leading-none">{{ $stats['total_stock'] }}</div>
        <div class="text-xs text-purple-100 mt-1">bouteilles · {{ $stats['stock_items'] }} types</div>
        <div class="mt-2 text-xs text-purple-100 opacity-80">
            @if($stats['low_stock'] > 0)
                <i class="fas fa-exclamation-triangle mr-1 text-yellow-300"></i>{{ $stats['low_stock'] }} en alerte
            @else
                <i class="fas fa-check-circle mr-1"></i>Stocks OK
            @endif
        </div>
    </div>

    {{-- Commandes --}}
    <div class="rounded-2xl p-5 text-white relative overflow-hidden"
         style="background: linear-gradient(135deg, #d97706, #f59e0b);">
        <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-shopping-cart text-white text-lg"></i>
        </div>
        <div class="text-2xl font-bold leading-none">{{ $stats['pending_orders'] }}</div>
        <div class="text-xs text-yellow-100 mt-1">commandes en attente</div>
        <div class="mt-2 text-xs text-yellow-100 opacity-80">
            <i class="fas fa-clock mr-1"></i>À traiter
        </div>
    </div>

    {{-- Clients --}}
    <div class="rounded-2xl p-5 text-white relative overflow-hidden"
         style="background: linear-gradient(135deg, #0284c7, #38bdf8);">
        <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-users text-white text-lg"></i>
        </div>
        <div class="text-2xl font-bold leading-none">{{ $stats['total_clients'] }}</div>
        <div class="text-xs text-sky-100 mt-1">clients enregistrés</div>
        <div class="mt-2 text-xs text-sky-100 opacity-80">
            <i class="fas fa-star mr-1"></i>Programme fidélité actif
        </div>
    </div>

</div>

{{-- ════════════ FINANCIAL SUMMARY ════════════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-gray-100">
        <div>
            <h3 class="font-bold text-gray-800">Résumé financier</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $currentLabel }} · {{ $periodStart->format('d/m/Y') }} → {{ $periodEnd->format('d/m/Y') }}</p>
        </div>
        <form method="GET" action="{{ route('store.dashboard') }}" class="flex flex-wrap items-center gap-2" id="periodForm">
            <input type="hidden" name="period" id="periodInput" value="{{ $period }}">
            <div class="flex bg-gray-100 rounded-xl p-1 gap-1">
                @foreach(['week' => 'Semaine', 'month' => 'Mois', 'year' => 'Année'] as $key => $label)
                <button type="submit" name="period" value="{{ $key }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all
                            {{ $period === $key ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </button>
                @endforeach
                <button type="button" onclick="toggleCustom()"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all
                            {{ $period === 'custom' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-calendar-alt"></i>
                </button>
            </div>
            <div id="customDates" class="{{ $period === 'custom' ? 'flex' : 'hidden' }} items-center gap-2 mt-1 sm:mt-0">
                <input type="date" name="from" value="{{ $fromDate }}" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <span class="text-gray-300">—</span>
                <input type="date" name="to" value="{{ $toDate }}" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <button type="submit" class="bg-blue-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">OK</button>
            </div>
        </form>
    </div>

    {{-- Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
        {{-- Revenue --}}
        <div class="px-6 py-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                </div>
                <span class="text-sm text-gray-500 font-medium">Revenus</span>
            </div>
            <div class="text-3xl font-black text-gray-800 tabular-nums">{{ number_format($stats['period_revenue'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-400 mt-1">XOF</div>
            <div class="mt-3 bg-emerald-100 rounded-full h-1.5">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 100%"></div>
            </div>
        </div>

        {{-- Expenses --}}
        <div class="px-6 py-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-down text-red-500 text-xs"></i>
                </div>
                <span class="text-sm text-gray-500 font-medium">Dépenses</span>
            </div>
            <div class="text-3xl font-black text-gray-800 tabular-nums">{{ number_format($stats['period_expenses'], 0, ',', ' ') }}</div>
            <div class="text-xs text-gray-400 mt-1">XOF</div>
            <div class="mt-3 bg-gray-100 rounded-full h-1.5">
                <div class="bg-red-400 h-1.5 rounded-full transition-all" style="width: {{ $expensePct }}%"></div>
            </div>
        </div>

        {{-- Profit --}}
        <div class="px-6 py-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 {{ $stats['period_profit'] >= 0 ? 'bg-blue-100' : 'bg-orange-100' }} rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-pie {{ $stats['period_profit'] >= 0 ? 'text-blue-600' : 'text-orange-500' }} text-xs"></i>
                </div>
                <span class="text-sm text-gray-500 font-medium">Bénéfice net</span>
            </div>
            <div class="text-3xl font-black {{ $stats['period_profit'] >= 0 ? 'text-blue-700' : 'text-orange-600' }} tabular-nums">
                {{ ($stats['period_profit'] >= 0 ? '+' : '') }}{{ number_format($stats['period_profit'], 0, ',', ' ') }}
            </div>
            <div class="text-xs text-gray-400 mt-1">XOF · Marge {{ $profitPct }}%</div>
            <div class="mt-3 bg-gray-100 rounded-full h-1.5">
                <div class="{{ $stats['period_profit'] >= 0 ? 'bg-blue-500' : 'bg-orange-400' }} h-1.5 rounded-full transition-all"
                     style="width: {{ min(100, max(0, abs($profitPct))) }}%"></div>
            </div>
        </div>
    </div>

    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-right">
        <a href="{{ route('profit.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
            Analyse mensuelle complète <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
</div>

{{-- ════════════ ORDERS + STOCK ════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Recent Orders --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-sm"></i>
                </div>
                <h3 class="font-bold text-gray-800">Commandes récentes</h3>
            </div>
            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                Tout voir <i class="fas fa-chevron-right ml-0.5 text-xs"></i>
            </a>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($recent_orders as $order)
            <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50/50 transition">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                    {{ $order->status === 'pending'   ? 'bg-yellow-100' : '' }}
                    {{ $order->status === 'confirmed' ? 'bg-blue-100'   : '' }}
                    {{ $order->status === 'delivered' ? 'bg-green-100'  : '' }}
                    {{ $order->status === 'cancelled' ? 'bg-red-100'    : '' }}">
                    <i class="fas fa-fire text-sm
                        {{ $order->status === 'pending'   ? 'text-yellow-600' : '' }}
                        {{ $order->status === 'confirmed' ? 'text-blue-600'   : '' }}
                        {{ $order->status === 'delivered' ? 'text-green-600'  : '' }}
                        {{ $order->status === 'cancelled' ? 'text-red-500'    : '' }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm text-gray-800 truncate">{{ $order->client_name }}</div>
                    <div class="text-xs text-gray-400">{{ $order->brand }} {{ $order->weight }} × {{ $order->quantity }}</div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-sm font-bold text-gray-700">{{ number_format($order->total_price, 0, ',', ' ') }}<span class="text-xs font-normal text-gray-400 ml-1">{{ $order->currency }}</span></div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $order->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $order->status === 'confirmed' ? 'bg-blue-100 text-blue-700'     : '' }}
                        {{ $order->status === 'delivered' ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-600'       : '' }}
                    ">{{ $order->status_label }}</span>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-10 text-center px-6">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fas fa-shopping-cart text-gray-300 text-2xl"></i>
                </div>
                <p class="text-gray-400 text-sm">Aucune commande récente</p>
            </div>
            @endforelse
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            <a href="{{ route('orders.create') }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                <i class="fas fa-plus"></i> Nouvelle commande
            </a>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-sm"></i>
                </div>
                <h3 class="font-bold text-gray-800">Alertes stock</h3>
            </div>
            @if($low_stock->count() > 0)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600">
                <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                {{ $low_stock->count() }} alertes
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-600">
                <i class="fas fa-check"></i> OK
            </span>
            @endif
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($low_stock as $item)
            @php
                $pct = $item->alert_threshold > 0
                    ? min(100, round(($item->quantity / ($item->alert_threshold * 3)) * 100))
                    : 0;
                $isEmpty = $item->quantity <= 0;
            @endphp
            <div class="px-6 py-3.5 hover:bg-gray-50/50 transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 {{ $isEmpty ? 'bg-gray-100' : 'bg-red-100' }} rounded-lg flex items-center justify-center">
                            <i class="fas fa-fire {{ $isEmpty ? 'text-gray-400' : 'text-red-500' }} text-xs"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-sm text-gray-800">{{ $item->brand }} {{ $item->weight }}</div>
                            <div class="text-xs text-gray-400">Seuil min : {{ $item->alert_threshold }} unités</div>
                        </div>
                    </div>
                    <span class="font-black text-xl {{ $isEmpty ? 'text-gray-400' : 'text-red-600' }}">{{ $item->quantity }}</span>
                </div>
                <div class="bg-gray-100 rounded-full h-1.5">
                    <div class="{{ $isEmpty ? 'bg-gray-300' : 'bg-red-400' }} h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-10 text-center px-6">
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                </div>
                <p class="text-gray-500 text-sm font-medium">Tous les stocks sont suffisants</p>
                <p class="text-gray-400 text-xs mt-1">Aucune alerte en cours</p>
            </div>
            @endforelse
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            <a href="{{ route('stock.index') }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition">
                <i class="fas fa-boxes"></i> Gérer le stock
            </a>
        </div>
    </div>

</div>

{{-- ════════════ QUICK STATS ════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <a href="{{ route('expenses.index') }}"
       class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center group-hover:bg-red-100 transition">
            <i class="fas fa-wallet text-red-500 text-xl"></i>
        </div>
        <div>
            <div class="text-xs text-gray-400 mb-0.5">Dépenses ce mois</div>
            <div class="text-xl font-black text-gray-800">{{ number_format($stats['month_expenses'], 0, ',', ' ') }}<span class="text-xs font-normal text-gray-400 ml-1">XOF</span></div>
        </div>
        <i class="fas fa-chevron-right text-gray-300 ml-auto group-hover:text-gray-500 transition"></i>
    </a>

    <a href="{{ route('clients.index') }}"
       class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center group-hover:bg-blue-100 transition">
            <i class="fas fa-users text-blue-500 text-xl"></i>
        </div>
        <div>
            <div class="text-xs text-gray-400 mb-0.5">Clients fidèles</div>
            <div class="text-xl font-black text-gray-800">{{ $stats['total_clients'] }}</div>
        </div>
        <i class="fas fa-chevron-right text-gray-300 ml-auto group-hover:text-gray-500 transition"></i>
    </a>

    <a href="{{ route('profit.index') }}"
       class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4 hover:shadow-md transition group">
        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center group-hover:bg-indigo-100 transition">
            <i class="fas fa-chart-bar text-indigo-500 text-xl"></i>
        </div>
        <div>
            <div class="text-xs text-gray-400 mb-0.5">Analyse bénéfices</div>
            <div class="text-xl font-black text-gray-800">12 mois</div>
        </div>
        <i class="fas fa-chevron-right text-gray-300 ml-auto group-hover:text-gray-500 transition"></i>
    </a>
</div>

</div>
@endsection

@push('scripts')
<script>
function toggleCustom() {
    const el = document.getElementById('customDates');
    const pi = document.getElementById('periodInput');
    const isHidden = el.classList.contains('hidden');
    el.classList.toggle('hidden', !isHidden);
    if (!isHidden) {
        pi.value = 'month';
        document.getElementById('periodForm').submit();
    } else {
        pi.value = 'custom';
    }
}
</script>
@endpush
