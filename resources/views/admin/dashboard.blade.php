@extends('layouts.app')
@section('title', 'Tableau de bord Admin')

@section('content')
@php
    $activeRate = $stats['total_stores'] > 0
        ? round(($stats['active_stores'] / $stats['total_stores']) * 100)
        : 0;
@endphp

<div class="space-y-6 py-4">

{{-- ════════════ HEADER BAND ════════════ --}}
<div class="rounded-2xl p-6 text-white relative overflow-hidden"
     style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #3b82f6 100%);">
    <div class="absolute inset-0 opacity-10"
         style="background-image: radial-gradient(circle at 80% 50%, white 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-blue-200 text-xs font-medium uppercase tracking-widest mb-1">Administration</div>
            <h2 class="text-2xl font-bold">GazManager</h2>
            <p class="text-blue-200 text-sm mt-1">
                <i class="fas fa-calendar mr-1"></i>{{ now()->translatedFormat('l d F Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white/20 backdrop-blur rounded-xl px-5 py-3 text-center">
                <div class="text-white/70 text-xs">Taux d'activation</div>
                <div class="text-white font-black text-2xl">{{ $activeRate }}<span class="text-sm font-normal">%</span></div>
            </div>
            <div class="bg-white/20 backdrop-blur rounded-xl px-5 py-3 text-center">
                <div class="text-white/70 text-xs">Revenus totaux</div>
                <div class="text-white font-bold text-lg">{{ number_format($stats['total_revenue'], 0, ',', ' ') }}<span class="text-xs ml-1">XOF</span></div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════ KPI CARDS ════════════ --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

    <div class="rounded-2xl p-5 text-white relative overflow-hidden"
         style="background: linear-gradient(135deg, #2563eb, #60a5fa);">
        <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-store text-white text-lg"></i>
        </div>
        <div class="text-3xl font-black">{{ $stats['total_stores'] }}</div>
        <div class="text-xs text-blue-100 mt-1">Magasins inscrits</div>
        <a href="{{ route('admin.inscriptions') }}" class="mt-2 text-xs text-blue-200 hover:text-white transition block">
            Voir tout <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>

    <div class="rounded-2xl p-5 text-white relative overflow-hidden"
         style="background: linear-gradient(135deg, #059669, #34d399);">
        <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-check-circle text-white text-lg"></i>
        </div>
        <div class="text-3xl font-black">{{ $stats['active_stores'] }}</div>
        <div class="text-xs text-emerald-100 mt-1">Magasins actifs</div>
        <div class="mt-2">
            <div class="bg-white/20 rounded-full h-1">
                <div class="bg-white/70 h-1 rounded-full" style="width: {{ $activeRate }}%"></div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl p-5 text-white relative overflow-hidden"
         style="background: linear-gradient(135deg, #d97706, #fbbf24);">
        <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-clock text-white text-lg"></i>
        </div>
        <div class="text-3xl font-black">{{ $stats['pending_stores'] }}</div>
        <div class="text-xs text-yellow-100 mt-1">En attente de validation</div>
        @if($stats['pending_stores'] > 0)
        <a href="{{ route('admin.inscriptions') }}" class="mt-2 text-xs text-yellow-200 hover:text-white transition block">
            <i class="fas fa-bell mr-1"></i>Traiter maintenant
        </a>
        @endif
    </div>

    <div class="rounded-2xl p-5 text-white relative overflow-hidden"
         style="background: linear-gradient(135deg, #7c3aed, #c084fc);">
        <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-ban text-white text-lg"></i>
        </div>
        <div class="text-3xl font-black">{{ $stats['rejected_stores'] }}</div>
        <div class="text-xs text-purple-100 mt-1">Magasins rejetés</div>
        <div class="mt-2 text-xs text-purple-200 opacity-80">
            <i class="fas fa-info-circle mr-1"></i>Accès refusé
        </div>
    </div>

</div>

{{-- ════════════ ACTIVITY PANEL ════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- Pending stores (left, wider) --}}
    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-sm"></i>
                </div>
                <h3 class="font-bold text-gray-800">Inscriptions en attente</h3>
            </div>
            @if($pending_stores->count() > 0)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></span>
                {{ $pending_stores->count() }} à traiter
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-600">
                <i class="fas fa-check"></i> Tout traité
            </span>
            @endif
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($pending_stores as $store)
            <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/50 transition">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center font-bold text-blue-600 text-sm shrink-0">
                    {{ strtoupper(substr($store->store_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm text-gray-800 truncate">{{ $store->store_name }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $store->owner_name }} · {{ $store->phone }}</div>
                    <div class="text-xs text-gray-300">{{ $store->created_at->diffForHumans() }}</div>
                </div>
                <div class="flex gap-2 shrink-0">
                    <form action="{{ route('admin.stores.approve', $store) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="flex items-center gap-1.5 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                            <i class="fas fa-check text-xs"></i> Approuver
                        </button>
                    </form>
                    <form action="{{ route('admin.stores.reject', $store) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                            <i class="fas fa-times text-xs"></i> Rejeter
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                </div>
                <p class="text-gray-500 text-sm font-medium">Aucune inscription en attente</p>
                <p class="text-gray-400 text-xs mt-1">Tous les magasins ont été traités</p>
            </div>
            @endforelse
        </div>

        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
            <a href="{{ route('admin.inscriptions') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                Voir toutes les inscriptions <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    {{-- Recent stores (right) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-store text-blue-600 text-sm"></i>
            </div>
            <h3 class="font-bold text-gray-800">Magasins récents</h3>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($recent_stores as $store)
            <div class="flex items-center gap-3 px-6 py-3.5 hover:bg-gray-50/50 transition">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm shrink-0
                    {{ $store->status === 'active' ? 'bg-green-100 text-green-700' : ($store->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-600') }}">
                    {{ strtoupper(substr($store->store_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm text-gray-800 truncate">{{ $store->store_name }}</div>
                    <div class="text-xs text-gray-400 truncate">{{ $store->email }}</div>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold shrink-0
                    {{ $store->status === 'active'  ? 'bg-green-100 text-green-700'  : '' }}
                    {{ $store->status === 'pending' ? 'bg-yellow-100 text-yellow-700': '' }}
                    {{ $store->status === 'rejected'? 'bg-red-100 text-red-600'      : '' }}">
                    {{ $store->status === 'active' ? 'Actif' : ($store->status === 'pending' ? 'En attente' : 'Rejeté') }}
                </span>
            </div>
            @empty
            <div class="py-12 text-center text-gray-400 text-sm">Aucun magasin</div>
            @endforelse
        </div>

        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
            <a href="{{ route('admin.accounts') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                Gérer tous les comptes <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

</div>

{{-- ════════════ QUICK ACTIONS ════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    @foreach([
        ['route' => 'admin.accounts',     'icon' => 'fa-users-cog',    'color' => 'blue',   'label' => 'Comptes',   'sub' => 'Magasins & Employés'],
        ['route' => 'admin.currencies',   'icon' => 'fa-coins',        'color' => 'amber',  'label' => 'Devises',   'sub' => 'Multi-currencies'],
        ['route' => 'admin.subscription', 'icon' => 'fa-credit-card',  'color' => 'purple', 'label' => 'Abonnement','sub' => 'Tarifs & Paiements'],
        ['route' => 'admin.settings',     'icon' => 'fa-cog',          'color' => 'slate',  'label' => 'Paramètres','sub' => 'Marques, Email...'],
    ] as $action)
    @php
        $colors = [
            'blue'   => 'bg-blue-50 hover:bg-blue-100 text-blue-600',
            'amber'  => 'bg-amber-50 hover:bg-amber-100 text-amber-600',
            'purple' => 'bg-purple-50 hover:bg-purple-100 text-purple-600',
            'slate'  => 'bg-slate-50 hover:bg-slate-100 text-slate-600',
        ];
    @endphp
    <a href="{{ route($action['route']) }}"
       class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3 hover:shadow-md transition group">
        <div class="w-11 h-11 {{ $colors[$action['color']] }} rounded-xl flex items-center justify-center transition">
            <i class="fas {{ $action['icon'] }} text-lg"></i>
        </div>
        <div>
            <div class="font-bold text-gray-800 text-sm">{{ $action['label'] }}</div>
            <div class="text-xs text-gray-400">{{ $action['sub'] }}</div>
        </div>
    </a>
    @endforeach
</div>

</div>
@endsection
